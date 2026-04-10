<?php

namespace App\Services\ElectronicInvoicing;

use App\Models\ElectronicDocument;
use DOMDocument;
use DOMElement;
use InvalidArgumentException;

class XmlGenerator
{
    private const NS_INVOICE = 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2';
    private const NS_CREDIT  = 'urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2';
    private const NS_DEBIT   = 'urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2';
    private const NS_CAC     = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
    private const NS_CBC     = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';
    private const NS_EXT     = 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2';

    /** Map document_type → DIAN InvoiceTypeCode */
    private const TYPE_CODES = [
        'invoice'     => '01',
        'credit_note' => '91',
        'debit_note'  => '92',
    ];

    /** Map identification_type → DIAN schemeID */
    private const ID_SCHEME = [
        'CC'  => '13',
        'NIT' => '31',
        'CE'  => '22',
        'PP'  => '41',
        'TI'  => '12',
    ];

    private DOMDocument $dom;
    private ElectronicDocument $document;

    /**
     * Generate UBL 2.1 XML for a DIAN electronic document.
     */
    public function generate(ElectronicDocument $document): string
    {
        $this->document = $document->loadMissing(['items', 'taxes']);

        if ($this->document->items->isEmpty()) {
            throw new InvalidArgumentException('El documento no tiene ítems.');
        }

        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;

        $root = $this->createRoot();
        $this->addUblExtensions($root);
        $this->addBasicElements($root);
        $this->addSupplierParty($root);
        $this->addCustomerParty($root);
        $this->addPaymentMeans($root);
        $this->addTaxTotal($root);
        $this->addLegalMonetaryTotal($root);
        $this->addLines($root);

        $this->dom->appendChild($root);

        return $this->dom->saveXML();
    }

    // ─── Root element with namespaces ───

    private function createRoot(): DOMElement
    {
        $isCreditNote = $this->document->document_type === ElectronicDocument::TYPE_CREDIT_NOTE;
        $isDebitNote  = $this->document->document_type === ElectronicDocument::TYPE_DEBIT_NOTE;

        if ($isCreditNote) {
            $rootTag = 'CreditNote';
            $ns      = self::NS_CREDIT;
        } elseif ($isDebitNote) {
            $rootTag = 'DebitNote';
            $ns      = self::NS_DEBIT;
        } else {
            $rootTag = 'Invoice';
            $ns      = self::NS_INVOICE;
        }

        $root = $this->dom->createElementNS($ns, $rootTag);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cac', self::NS_CAC);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cbc', self::NS_CBC);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ext', self::NS_EXT);

        return $root;
    }

    // ─── UBL Extensions (placeholder for digital signature) ───

    private function addUblExtensions(DOMElement $root): void
    {
        $extensions = $this->dom->createElementNS(self::NS_EXT, 'ext:UBLExtensions');
        $extension  = $this->dom->createElementNS(self::NS_EXT, 'ext:UBLExtension');
        $content    = $this->dom->createElementNS(self::NS_EXT, 'ext:ExtensionContent');

        $extension->appendChild($content);
        $extensions->appendChild($extension);
        $root->appendChild($extensions);
    }

    // ─── Basic identification elements ───

    private function addBasicElements(DOMElement $root): void
    {
        $doc = $this->document;

        $this->appendCbc($root, 'UBLVersionID', 'UBL 2.1');
        $this->appendCbc($root, 'CustomizationID', '10');
        $this->appendCbc($root, 'ProfileID', 'DIAN 2.1');
        $this->appendCbc($root, 'ProfileExecutionID', $this->environmentId());
        $this->appendCbc($root, 'ID', $doc->prefix . $doc->number);

        $uuid = $this->appendCbc($root, 'UUID', $doc->cufe ?? '');
        $uuid->setAttribute('schemeName', 'CUFE-SHA384');

        $issueDate = $doc->created_at ? $doc->created_at->format('Y-m-d') : now()->format('Y-m-d');
        $issueTime = $doc->created_at ? $doc->created_at->format('H:i:s-05:00') : now()->format('H:i:s-05:00');

        $this->appendCbc($root, 'IssueDate', $issueDate);
        $this->appendCbc($root, 'IssueTime', $issueTime);

        $typeCode = self::TYPE_CODES[$doc->document_type] ?? '01';

        if ($doc->document_type === ElectronicDocument::TYPE_CREDIT_NOTE) {
            $this->appendCbc($root, 'CreditNoteTypeCode', $typeCode);
        } elseif ($doc->document_type === ElectronicDocument::TYPE_DEBIT_NOTE) {
            $this->appendCbc($root, 'DebitNoteTypeCode', $typeCode);
        } else {
            $this->appendCbc($root, 'InvoiceTypeCode', $typeCode);
        }

        $this->appendCbc($root, 'Note', $doc->notes ?? '');
        $this->appendCbc($root, 'DocumentCurrencyCode', $doc->currency ?? 'COP');
        $this->appendCbc($root, 'LineCountNumeric', (string) $doc->items->count());

        if ($doc->payment_due_date) {
            $period = $this->dom->createElementNS(self::NS_CAC, 'cac:InvoicePeriod');
            $this->appendCbc($period, 'StartDate', $issueDate);
            $this->appendCbc($period, 'EndDate', $doc->payment_due_date->format('Y-m-d'));
            $root->appendChild($period);
        }

        // Reference document for credit/debit notes
        if ($doc->reference_document_id) {
            $ref = $doc->referenceDocument;
            if ($ref) {
                $billingRef = $this->dom->createElementNS(self::NS_CAC, 'cac:BillingReference');
                $invoiceRef = $this->dom->createElementNS(self::NS_CAC, 'cac:InvoiceDocumentReference');
                $this->appendCbc($invoiceRef, 'ID', $ref->prefix . $ref->number);
                $this->appendCbc($invoiceRef, 'UUID', $ref->cufe ?? '');
                $this->appendCbc($invoiceRef, 'IssueDate', $ref->created_at->format('Y-m-d'));
                $billingRef->appendChild($invoiceRef);
                $root->appendChild($billingRef);
            }
        }
    }

    // ─── Supplier (AccountingSupplierParty) ───

    private function addSupplierParty(DOMElement $root): void
    {
        $doc = $this->document;

        $supplier = $this->dom->createElementNS(self::NS_CAC, 'cac:AccountingSupplierParty');
        $this->appendCbc($supplier, 'AdditionalAccountID', '1'); // 1 = Persona Jurídica

        $party = $this->dom->createElementNS(self::NS_CAC, 'cac:Party');

        // PartyName
        $partyName = $this->dom->createElementNS(self::NS_CAC, 'cac:PartyName');
        $this->appendCbc($partyName, 'Name', $doc->issuer_name ?? '');
        $party->appendChild($partyName);

        // PhysicalLocation
        $physLoc = $this->dom->createElementNS(self::NS_CAC, 'cac:PhysicalLocation');
        $address = $this->buildAddress($doc->issuer_address, $doc->issuer_city, $doc->issuer_department);
        $physLoc->appendChild($address);
        $party->appendChild($physLoc);

        // PartyTaxScheme
        $taxScheme = $this->dom->createElementNS(self::NS_CAC, 'cac:PartyTaxScheme');
        $this->appendCbc($taxScheme, 'RegistrationName', $doc->issuer_name ?? '');
        $companyId = $this->appendCbc($taxScheme, 'CompanyID', $doc->issuer_nit ?? '');
        $companyId->setAttribute('schemeID', '31'); // NIT
        $companyId->setAttribute('schemeName', '31');
        $this->appendCbc($taxScheme, 'TaxLevelCode', 'O-48'); // Responsable IVA
        $innerTax = $this->dom->createElementNS(self::NS_CAC, 'cac:TaxScheme');
        $this->appendCbc($innerTax, 'ID', '01');
        $this->appendCbc($innerTax, 'Name', 'IVA');
        $taxScheme->appendChild($innerTax);
        $party->appendChild($taxScheme);

        // PartyLegalEntity
        $legalEntity = $this->dom->createElementNS(self::NS_CAC, 'cac:PartyLegalEntity');
        $this->appendCbc($legalEntity, 'RegistrationName', $doc->issuer_name ?? '');
        $companyId2 = $this->appendCbc($legalEntity, 'CompanyID', $doc->issuer_nit ?? '');
        $companyId2->setAttribute('schemeID', '31');
        $companyId2->setAttribute('schemeName', '31');
        $party->appendChild($legalEntity);

        // Contact
        if ($doc->issuer_email || $doc->issuer_phone) {
            $contact = $this->dom->createElementNS(self::NS_CAC, 'cac:Contact');
            if ($doc->issuer_phone) {
                $this->appendCbc($contact, 'Telephone', $doc->issuer_phone);
            }
            if ($doc->issuer_email) {
                $this->appendCbc($contact, 'ElectronicMail', $doc->issuer_email);
            }
            $party->appendChild($contact);
        }

        $supplier->appendChild($party);
        $root->appendChild($supplier);
    }

    // ─── Customer (AccountingCustomerParty) ───

    private function addCustomerParty(DOMElement $root): void
    {
        $doc = $this->document;

        $customer = $this->dom->createElementNS(self::NS_CAC, 'cac:AccountingCustomerParty');
        $this->appendCbc($customer, 'AdditionalAccountID', '2'); // 2 = Persona Natural default

        $party = $this->dom->createElementNS(self::NS_CAC, 'cac:Party');

        // PartyName
        $partyName = $this->dom->createElementNS(self::NS_CAC, 'cac:PartyName');
        $this->appendCbc($partyName, 'Name', $doc->customer_name ?? '');
        $party->appendChild($partyName);

        // PhysicalLocation
        $physLoc = $this->dom->createElementNS(self::NS_CAC, 'cac:PhysicalLocation');
        $address = $this->buildAddress($doc->customer_address, $doc->customer_city, $doc->customer_department);
        $physLoc->appendChild($address);
        $party->appendChild($physLoc);

        // PartyTaxScheme
        $schemeId = self::ID_SCHEME[$doc->customer_identification_type] ?? '13';
        $taxScheme = $this->dom->createElementNS(self::NS_CAC, 'cac:PartyTaxScheme');
        $this->appendCbc($taxScheme, 'RegistrationName', $doc->customer_name ?? '');
        $companyId = $this->appendCbc($taxScheme, 'CompanyID', $doc->customer_identification ?? '');
        $companyId->setAttribute('schemeID', $schemeId);
        $companyId->setAttribute('schemeName', $schemeId);
        $this->appendCbc($taxScheme, 'TaxLevelCode', 'R-99-PN'); // No responsable
        $innerTax = $this->dom->createElementNS(self::NS_CAC, 'cac:TaxScheme');
        $this->appendCbc($innerTax, 'ID', '01');
        $this->appendCbc($innerTax, 'Name', 'IVA');
        $taxScheme->appendChild($innerTax);
        $party->appendChild($taxScheme);

        // PartyLegalEntity
        $legalEntity = $this->dom->createElementNS(self::NS_CAC, 'cac:PartyLegalEntity');
        $this->appendCbc($legalEntity, 'RegistrationName', $doc->customer_name ?? '');
        $companyId2 = $this->appendCbc($legalEntity, 'CompanyID', $doc->customer_identification ?? '');
        $companyId2->setAttribute('schemeID', $schemeId);
        $companyId2->setAttribute('schemeName', $schemeId);
        $party->appendChild($legalEntity);

        // Contact
        if ($doc->customer_email || $doc->customer_phone) {
            $contact = $this->dom->createElementNS(self::NS_CAC, 'cac:Contact');
            if ($doc->customer_phone) {
                $this->appendCbc($contact, 'Telephone', $doc->customer_phone);
            }
            if ($doc->customer_email) {
                $this->appendCbc($contact, 'ElectronicMail', $doc->customer_email);
            }
            $party->appendChild($contact);
        }

        $customer->appendChild($party);
        $root->appendChild($customer);
    }

    // ─── Payment Means ───

    private function addPaymentMeans(DOMElement $root): void
    {
        $doc = $this->document;

        $payment = $this->dom->createElementNS(self::NS_CAC, 'cac:PaymentMeans');
        // 10=Efectivo, 42=Consignación, 47=Transferencia, 48=Tarjeta crédito, 49=Tarjeta débito
        $this->appendCbc($payment, 'ID', 'Medio de pago');
        $this->appendCbc($payment, 'PaymentMeansCode', $doc->payment_means ?? '10');
        $this->appendCbc($payment, 'PaymentID', $doc->payment_method ?? 'Contado');

        if ($doc->payment_due_date) {
            $this->appendCbc($payment, 'PaymentDueDate', $doc->payment_due_date->format('Y-m-d'));
        }

        $root->appendChild($payment);
    }

    // ─── Tax Total (consolidated) ───

    private function addTaxTotal(DOMElement $root): void
    {
        $doc = $this->document;
        $currency = $doc->currency ?? 'COP';

        $taxTotal = $this->dom->createElementNS(self::NS_CAC, 'cac:TaxTotal');
        $totalAmount = $this->appendCbcAmount($taxTotal, 'TaxAmount', $doc->tax_total, $currency);

        if ($doc->taxes->isNotEmpty()) {
            foreach ($doc->taxes as $tax) {
                $subtotal = $this->dom->createElementNS(self::NS_CAC, 'cac:TaxSubtotal');
                $this->appendCbcAmount($subtotal, 'TaxableAmount', $tax->taxable_amount, $currency);
                $this->appendCbcAmount($subtotal, 'TaxAmount', $tax->tax_amount, $currency);

                $category = $this->dom->createElementNS(self::NS_CAC, 'cac:TaxCategory');
                $this->appendCbc($category, 'Percent', $this->fmt($tax->tax_rate));

                $scheme = $this->dom->createElementNS(self::NS_CAC, 'cac:TaxScheme');
                $this->appendCbc($scheme, 'ID', $this->taxTypeCode($tax->tax_type));
                $this->appendCbc($scheme, 'Name', strtoupper($tax->tax_type));
                $category->appendChild($scheme);
                $subtotal->appendChild($category);

                $taxTotal->appendChild($subtotal);
            }
        } else {
            // Single zero-tax subtotal when no taxes
            $subtotal = $this->dom->createElementNS(self::NS_CAC, 'cac:TaxSubtotal');
            $this->appendCbcAmount($subtotal, 'TaxableAmount', $doc->subtotal, $currency);
            $this->appendCbcAmount($subtotal, 'TaxAmount', $doc->tax_total, $currency);

            $category = $this->dom->createElementNS(self::NS_CAC, 'cac:TaxCategory');
            $this->appendCbc($category, 'Percent', '19.00');
            $scheme = $this->dom->createElementNS(self::NS_CAC, 'cac:TaxScheme');
            $this->appendCbc($scheme, 'ID', '01');
            $this->appendCbc($scheme, 'Name', 'IVA');
            $category->appendChild($scheme);
            $subtotal->appendChild($category);

            $taxTotal->appendChild($subtotal);
        }

        $root->appendChild($taxTotal);
    }

    // ─── Legal Monetary Total ───

    private function addLegalMonetaryTotal(DOMElement $root): void
    {
        $doc      = $this->document;
        $currency = $doc->currency ?? 'COP';

        $monetary = $this->dom->createElementNS(self::NS_CAC, 'cac:LegalMonetaryTotal');
        $this->appendCbcAmount($monetary, 'LineExtensionAmount', $doc->subtotal, $currency);
        $this->appendCbcAmount($monetary, 'TaxExclusiveAmount', $doc->subtotal, $currency);
        $this->appendCbcAmount($monetary, 'TaxInclusiveAmount', $doc->total, $currency);
        $this->appendCbcAmount($monetary, 'AllowanceTotalAmount', $doc->discount_total ?? '0.00', $currency);
        $this->appendCbcAmount($monetary, 'PayableAmount', $doc->total, $currency);

        $root->appendChild($monetary);
    }

    // ─── Invoice / Credit / Debit Lines ───

    private function addLines(DOMElement $root): void
    {
        $isCredit = $this->document->document_type === ElectronicDocument::TYPE_CREDIT_NOTE;
        $isDebit  = $this->document->document_type === ElectronicDocument::TYPE_DEBIT_NOTE;
        $currency = $this->document->currency ?? 'COP';

        foreach ($this->document->items as $item) {
            if ($isCredit) {
                $lineTag = 'cac:CreditNoteLine';
            } elseif ($isDebit) {
                $lineTag = 'cac:DebitNoteLine';
            } else {
                $lineTag = 'cac:InvoiceLine';
            }

            $line = $this->dom->createElementNS(self::NS_CAC, $lineTag);
            $this->appendCbc($line, 'ID', (string) ($item->line_number ?? 1));

            if ($isCredit) {
                $this->appendCbcAmount($line, 'CreditedQuantity', $item->quantity, null, $item->unit_measure ?? 'EA');
            } elseif ($isDebit) {
                $this->appendCbcAmount($line, 'DebitedQuantity', $item->quantity, null, $item->unit_measure ?? 'EA');
            } else {
                $this->appendCbcAmount($line, 'InvoicedQuantity', $item->quantity, null, $item->unit_measure ?? 'EA');
            }

            $this->appendCbcAmount($line, 'LineExtensionAmount', $item->line_total, $currency);

            // Per-line TaxTotal
            $lineTax = $this->dom->createElementNS(self::NS_CAC, 'cac:TaxTotal');
            $this->appendCbcAmount($lineTax, 'TaxAmount', $item->tax_amount, $currency);

            $taxSub = $this->dom->createElementNS(self::NS_CAC, 'cac:TaxSubtotal');
            $lineBase = $this->fmt(($item->unit_price * $item->quantity) - ($item->discount ?? 0));
            $this->appendCbcAmount($taxSub, 'TaxableAmount', $lineBase, $currency);
            $this->appendCbcAmount($taxSub, 'TaxAmount', $item->tax_amount, $currency);

            $taxCat = $this->dom->createElementNS(self::NS_CAC, 'cac:TaxCategory');
            $this->appendCbc($taxCat, 'Percent', $this->fmt($item->tax_rate ?? 0));
            $taxScheme = $this->dom->createElementNS(self::NS_CAC, 'cac:TaxScheme');
            $this->appendCbc($taxScheme, 'ID', $this->taxTypeCode($item->tax_type ?? 'iva'));
            $this->appendCbc($taxScheme, 'Name', strtoupper($item->tax_type ?? 'IVA'));
            $taxCat->appendChild($taxScheme);
            $taxSub->appendChild($taxCat);
            $lineTax->appendChild($taxSub);

            $line->appendChild($lineTax);

            // Item description
            $itemEl = $this->dom->createElementNS(self::NS_CAC, 'cac:Item');
            $this->appendCbc($itemEl, 'Description', $item->description ?? '');

            if ($item->code) {
                $sellerId = $this->dom->createElementNS(self::NS_CAC, 'cac:SellersItemIdentification');
                $this->appendCbc($sellerId, 'ID', $item->code);
                $itemEl->appendChild($sellerId);
            }

            $line->appendChild($itemEl);

            // Price
            $price = $this->dom->createElementNS(self::NS_CAC, 'cac:Price');
            $this->appendCbcAmount($price, 'PriceAmount', $item->unit_price, $currency);
            $this->appendCbc($price, 'BaseQuantity', '1.000');
            $line->appendChild($price);

            $root->appendChild($line);
        }
    }

    // ═══ Helpers ═══

    private function appendCbc(DOMElement $parent, string $tag, string $value): DOMElement
    {
        $el = $this->dom->createElementNS(self::NS_CBC, 'cbc:' . $tag, htmlspecialchars($value, ENT_XML1));
        $parent->appendChild($el);
        return $el;
    }

    private function appendCbcAmount(DOMElement $parent, string $tag, $value, ?string $currency, ?string $unitCode = null): DOMElement
    {
        $el = $this->dom->createElementNS(self::NS_CBC, 'cbc:' . $tag, $this->fmt($value));

        if ($currency !== null) {
            $el->setAttribute('currencyID', $currency);
        }

        if ($unitCode !== null) {
            $el->setAttribute('unitCode', $unitCode);
        }

        $parent->appendChild($el);
        return $el;
    }

    private function buildAddress(?string $street, ?string $city, ?string $department): DOMElement
    {
        $address = $this->dom->createElementNS(self::NS_CAC, 'cac:Address');
        $this->appendCbc($address, 'ID', '11001'); // Bogotá default DANE code
        $this->appendCbc($address, 'CityName', $city ?? 'Bogotá');
        $this->appendCbc($address, 'CountrySubentity', $department ?? 'Bogotá D.C.');
        $this->appendCbc($address, 'CountrySubentityCode', '11'); // Bogotá default

        $addressLine = $this->dom->createElementNS(self::NS_CAC, 'cac:AddressLine');
        $this->appendCbc($addressLine, 'Line', $street ?? '');
        $address->appendChild($addressLine);

        $country = $this->dom->createElementNS(self::NS_CAC, 'cac:Country');
        $this->appendCbc($country, 'IdentificationCode', 'CO');
        $this->appendCbc($country, 'Name', 'Colombia');
        $address->appendChild($country);

        return $address;
    }

    private function fmt($value): string
    {
        return sprintf('%.2f', (float) $value);
    }

    private function taxTypeCode(string $type): string
    {
        return match (strtolower($type)) {
            'iva'    => '01',
            'inc'    => '04',
            'ica'    => '03',
            default  => '01',
        };
    }

    private function environmentId(): string
    {
        return config('invoicing.environment') === 'production' ? '1' : '2';
    }
}
