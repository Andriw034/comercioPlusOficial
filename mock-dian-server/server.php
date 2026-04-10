<?php
/**
 * Mock DIAN Provider Server
 * Simulates a certified DIAN technology provider (Saphety, Carvajal, etc.)
 *
 * Usage: php -S localhost:8080 mock-dian-server/server.php
 */

$dataDir = __DIR__ . '/data';
$docsFile = $dataDir . '/documents.json';
$signedDir = $dataDir . '/signed_xmls';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verify Bearer token
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!str_starts_with($authHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['error' => 'Token de autenticación requerido']);
    exit;
}

// Simulate network latency (50-150ms)
usleep(rand(50000, 150000));

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ─── Router ───

// GET /api/ubl2.1/status/xml/{trackId} must be checked BEFORE status/{trackId}
if ($method === 'GET' && preg_match('#^/api/ubl2\.1/status/xml/(.+)$#', $uri, $m)) {
    handleDownloadXml($m[1]);
} elseif ($method === 'GET' && preg_match('#^/api/ubl2\.1/status/(.+)$#', $uri, $m)) {
    handleGetStatus($m[1]);
} elseif ($method === 'POST' && in_array($uri, ['/api/ubl2.1/invoice', '/api/ubl2.1/credit-note', '/api/ubl2.1/debit-note'])) {
    handleSendDocument($uri);
} elseif ($method === 'GET' && $uri === '/') {
    echo json_encode([
        'service' => 'Mock DIAN Provider',
        'version' => '1.0.0',
        'status'  => 'running',
        'endpoints' => [
            'POST /api/ubl2.1/invoice',
            'POST /api/ubl2.1/credit-note',
            'POST /api/ubl2.1/debit-note',
            'GET  /api/ubl2.1/status/{trackId}',
            'GET  /api/ubl2.1/status/xml/{trackId}',
        ],
    ]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint no encontrado', 'uri' => $uri, 'method' => $method]);
}

// ─── Handlers ───

function handleSendDocument(string $endpoint): void
{
    global $docsFile, $signedDir;

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['xml']) || empty($input['xml'])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Campo "xml" (base64) es requerido']);
        return;
    }

    $xmlRaw = base64_decode($input['xml'], true);
    if ($xmlRaw === false) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'XML no es base64 válido']);
        return;
    }

    // Validate it is parseable XML
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    if (!$dom->loadXML($xmlRaw)) {
        http_response_code(422);
        $errors = array_map(fn($e) => trim($e->message), libxml_get_errors());
        libxml_clear_errors();
        echo json_encode(['success' => false, 'error' => 'XML inválido', 'xml_errors' => $errors]);
        return;
    }

    // Extract CUFE from XML
    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
    $uuidNode = $xpath->query('//cbc:UUID')->item(0);
    $cufe = $uuidNode ? $uuidNode->textContent : hash('sha384', $xmlRaw);

    // Extract document number
    $idNode = $xpath->query('//cbc:ID')->item(0);
    $docNumber = $idNode ? $idNode->textContent : 'UNKNOWN';

    // 90% approved, 10% rejected (deterministic per CUFE last char)
    $lastChar = hexdec(substr($cufe, -1));
    $approved = $lastChar < 14; // ~87.5% approval

    $trackId = 'DIAN-' . strtoupper(substr(md5(uniqid('', true)), 0, 16));
    $now = date('c');

    $docRecord = [
        'track_id'   => $trackId,
        'cufe'       => $cufe,
        'doc_number' => $docNumber,
        'endpoint'   => $endpoint,
        'status'     => $approved ? 'approved' : 'rejected',
        'is_valid'   => $approved,
        'message'    => $approved
            ? 'Documento validado exitosamente por la DIAN. CUFE asignado.'
            : 'Documento rechazado: el NIT del emisor no coincide con la resolución de facturación.',
        'created_at' => $now,
    ];

    // Persist
    $docs = loadDocs();
    $docs[$trackId] = $docRecord;
    saveDocs($docs);

    // Generate mock signed XML for approved docs
    if ($approved) {
        if (!is_dir($signedDir)) {
            mkdir($signedDir, 0777, true);
        }
        $signedXml = addMockSignature($xmlRaw, $trackId);
        file_put_contents("{$signedDir}/{$trackId}.xml", $signedXml);
    }

    $type = match ($endpoint) {
        '/api/ubl2.1/invoice'     => 'Factura electrónica',
        '/api/ubl2.1/credit-note' => 'Nota crédito electrónica',
        '/api/ubl2.1/debit-note'  => 'Nota débito electrónica',
        default                   => 'Documento',
    };

    // Log to console
    $statusLabel = $approved ? 'APROBADO' : 'RECHAZADO';
    $logLine = "[" . date('H:i:s') . "] {$type} {$docNumber} -> {$statusLabel} | Track: {$trackId}\n";
    if (defined('STDERR')) {
        fwrite(STDERR, $logLine);
    } else {
        error_log(trim($logLine));
    }

    echo json_encode([
        'success'   => true,
        'track_id'  => $trackId,
        'cufe'      => $cufe,
        'status'    => $docRecord['status'],
        'is_valid'  => $approved,
        'message'   => $approved
            ? "{$type} recibida y aprobada por la DIAN"
            : "{$type} procesada con rechazo",
        'timestamp' => $now,
    ]);
}

function handleGetStatus(string $trackId): void
{
    $docs = loadDocs();

    if (!isset($docs[$trackId])) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => "Track ID '{$trackId}' no encontrado"]);
        return;
    }

    $doc = $docs[$trackId];

    echo json_encode([
        'success'   => true,
        'track_id'  => $trackId,
        'status'    => $doc['status'],
        'is_valid'  => $doc['is_valid'],
        'cufe'      => $doc['cufe'],
        'message'   => $doc['message'],
        'timestamp' => $doc['created_at'],
    ]);
}

function handleDownloadXml(string $trackId): void
{
    global $signedDir;

    $path = "{$signedDir}/{$trackId}.xml";

    if (!file_exists($path)) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error'   => 'XML firmado no disponible. Documento puede estar rechazado o aún en proceso.',
        ]);
        return;
    }

    echo json_encode([
        'success'  => true,
        'track_id' => $trackId,
        'xml'      => base64_encode(file_get_contents($path)),
    ]);
}

// ─── Helpers ───

function loadDocs(): array
{
    global $docsFile;
    if (!file_exists($docsFile)) return [];
    $content = file_get_contents($docsFile);
    return json_decode($content, true) ?: [];
}

function saveDocs(array $docs): void
{
    global $docsFile;
    $dir = dirname($docsFile);
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    file_put_contents($docsFile, json_encode($docs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function addMockSignature(string $xml, string $trackId): string
{
    $signatureValue = base64_encode(hash('sha256', $trackId . $xml, true) . random_bytes(96));
    $certValue = base64_encode(random_bytes(256));

    $signatureBlock = <<<XML

  <!-- === FIRMA DIGITAL SIMULADA (MOCK) === -->
  <ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Id="xmldsig-{$trackId}">
    <ds:SignedInfo>
      <ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
      <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
      <ds:Reference URI="">
        <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
        <ds:DigestValue>{$signatureValue}</ds:DigestValue>
      </ds:Reference>
    </ds:SignedInfo>
    <ds:SignatureValue>{$signatureValue}</ds:SignatureValue>
    <ds:KeyInfo>
      <ds:X509Data>
        <ds:X509Certificate>{$certValue}</ds:X509Certificate>
      </ds:X509Data>
    </ds:KeyInfo>
  </ds:Signature>
XML;

    // Insert before closing root tag
    $xml = preg_replace('#(</(?:Invoice|CreditNote|DebitNote)>)#', $signatureBlock . "\n$1", $xml);

    return $xml;
}
