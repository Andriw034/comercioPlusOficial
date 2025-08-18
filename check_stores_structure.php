<?php
require_once 'vendor/autoload.php';

// Cargar la configuración de la base de datos
$config = require_once 'config/database.php';
$connection = $config['connections'][$config['default']];

// Conectar a la base de datos
try {
    $pdo = new PDO(
        "mysql:host={$connection['host']};dbname={$connection['database']};charset={$connection['charset']}",
        $connection['username'],
        $connection['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Obtener la estructura de la tabla stores
    $stmt = $pdo->query("DESCRIBE stores");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columnas en la tabla stores:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}" . ($column['Null'] === 'YES' ? ' (nullable)' : '') . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
