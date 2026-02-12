<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceCorsHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = (string) $request->headers->get('Origin', '');
        $allowed = $this->isAllowedOrigin($origin);

        if ($allowed && $request->isMethod('OPTIONS')) {
            return response('', 204, $this->corsHeaders($origin));
        }

        /** @var Response $response */
        $response = $next($request);

        if ($allowed) {
            foreach ($this->corsHeaders($origin) as $header => $value) {
                $response->headers->set($header, $value);
            }
        }

        return $response;
    }

    private function isAllowedOrigin(string $origin): bool
    {
        if ($origin === '') {
            return false;
        }

        $exactOrigins = array_filter((array) config('cors.allowed_origins', []));
        if (in_array($origin, $exactOrigins, true)) {
            return true;
        }

        $patterns = array_filter((array) config('cors.allowed_origins_patterns', []));
        foreach ($patterns as $pattern) {
            if (@preg_match($pattern, $origin) === 1) {
                return true;
            }
        }

        return false;
    }

    private function corsHeaders(string $origin): array
    {
        return [
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization, X-Requested-With',
            'Vary' => 'Origin, Access-Control-Request-Method, Access-Control-Request-Headers',
        ];
    }
}

