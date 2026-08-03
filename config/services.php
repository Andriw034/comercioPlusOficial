<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Asistente de la tienda — proveedor de IA
    |--------------------------------------------------------------------------
    |
    | Que IA responde el chat de la tienda. Se cambia con una sola variable, sin
    | tocar codigo ni volver a desplegar:
    |
    |   gemini    -> plan gratuito de Google, sin tarjeta de credito
    |   anthropic -> Claude, requiere saldo de API (Claude Pro NO sirve: es otra cosa)
    |
    | Un valor mal escrito revienta a proposito en vez de caer en un default
    | silencioso: un typo en el panel de Render tiene que verse.
    */
    'ai' => [
        'provider' => env('AI_PROVIDER', 'gemini'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Gemini
    |--------------------------------------------------------------------------
    */
    // El modelo va en variable porque Google apaga los viejos (gemini-2.0-flash ya
    // no existe) y un modelo retirado responde 404 sin explicacion util.
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        // Elegido midiendo contra la API el 2026-08-03, no por ser el mas nuevo:
        //   gemini-3.6-flash      -> 503 UNAVAILABLE intermitente ("high demand")
        //   gemini-3.5-flash      -> anda, pero el plan gratuito da 20 pedidos AL DIA
        //   gemini-3.5-flash-lite -> anda, misma calidad de respuesta, cuota mas alta
        //   gemini-2.5-*          -> NOT_FOUND con clave del plan gratuito
        // La cuota gratuita es POR MODELO: cambiar de modelo es la salida rapida si
        // uno se agota. Los limites de cada cuenta se ven en ai.dev/rate-limit.
        'model'      => env('GEMINI_MODEL', 'gemini-3.5-flash-lite'),
        'max_tokens' => (int) env('GEMINI_MAX_TOKENS', 1200),
        // Presupuesto de razonamiento. 0 lo apaga: para responder "tienes frenos
        // para una AKT?" no aporta y se come tokens de la respuesta. No todos los
        // modelos lo aceptan (flash-lite lo rechaza) y el generador se recupera solo
        // reintentando sin el; vaciar la variable lo quita de una.
        'thinking_budget' => env('GEMINI_THINKING_BUDGET', '0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Anthropic (Claude)
    |--------------------------------------------------------------------------
    */
    // El modelo por defecto debe ser uno vigente: los retirados responden 404 y el
    // asistente queda caido sin explicacion util. Se puede cambiar sin tocar codigo
    // con ANTHROPIC_MODEL (claude-sonnet-5 cuesta menos de la mitad).
    'anthropic' => [
        'key'        => env('ANTHROPIC_API_KEY'),
        'model'      => env('ANTHROPIC_MODEL', 'claude-opus-5'),
        'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 900),
        'version'    => env('ANTHROPIC_VERSION', '2023-06-01'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | MercadoPago
    |--------------------------------------------------------------------------
    */
    'mercadopago' => [
        'public_key'   => env('MERCADOPAGO_PUBLIC_KEY'),
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
        'sandbox'      => env('MERCADOPAGO_SANDBOX', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloudinary
    |--------------------------------------------------------------------------
    */
    'cloudinary' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key' => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
        'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),
        'url' => env('CLOUDINARY_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Social providers (optional)
    |--------------------------------------------------------------------------
    */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],
];
