<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Entorno de facturación electrónica
    |--------------------------------------------------------------------------
    | test: usa endpoints de prueba DIAN (habilitación)
    | production: usa endpoints reales DIAN
    */
    'environment' => env('INVOICING_ENV', 'test'),

    /*
    |--------------------------------------------------------------------------
    | Llave técnica DIAN
    |--------------------------------------------------------------------------
    | Proporcionada por la DIAN al habilitar la resolución de facturación.
    | Se usa para generar el CUFE/CUDE.
    */
    'technical_key' => env('INVOICING_TECHNICAL_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Proveedor tecnológico (Matias API)
    |--------------------------------------------------------------------------
    */
    'matias_api' => [
        'base_url' => env('MATIAS_API_URL', 'https://api.matias-api.com'),
        'api_key'  => env('MATIAS_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Valores por defecto
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'currency'   => 'COP',
        'country'    => 'CO',
        'tax_rate'   => 19.00,
        'tax_name'   => 'IVA',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tipos de documento DIAN
    |--------------------------------------------------------------------------
    | Códigos estándar UBL 2.1 usados por la DIAN.
    */
    'document_types' => [
        'invoice'     => 'FEV',  // Factura Electrónica de Venta
        'credit_note' => 'NCE',  // Nota Crédito Electrónica
        'debit_note'  => 'NDE',  // Nota Débito Electrónica
    ],

    /*
    |--------------------------------------------------------------------------
    | Prefijos por tipo de documento
    |--------------------------------------------------------------------------
    */
    'prefixes' => [
        'invoice'     => env('INVOICING_PREFIX_INVOICE', 'FE'),
        'credit_note' => env('INVOICING_PREFIX_CREDIT', 'NC'),
        'debit_note'  => env('INVOICING_PREFIX_DEBIT', 'ND'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tipos de identificación Colombia
    |--------------------------------------------------------------------------
    */
    'identification_types' => [
        'CC'  => 'Cédula de Ciudadanía',
        'NIT' => 'NIT',
        'CE'  => 'Cédula de Extranjería',
        'PP'  => 'Pasaporte',
        'TI'  => 'Tarjeta de Identidad',
    ],

    /*
    |--------------------------------------------------------------------------
    | Reglas de negocio
    |--------------------------------------------------------------------------
    */
    'rules' => [
        'cancel_max_days' => 5,  // Días máximos para anular factura aprobada
    ],

];
