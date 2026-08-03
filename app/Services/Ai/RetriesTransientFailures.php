<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Throwable;

/**
 * Politica de reintentos compartida por los proveedores de IA.
 *
 * Los planes gratuitos se saturan: Google devuelve 503 UNAVAILABLE ("high demand")
 * de forma intermitente, y Anthropic tiene su equivalente (529 overloaded). Sin
 * reintentos, el cliente de la tienda ve un chat roto por algo que se arregla solo
 * en un segundo.
 *
 * Se reintenta SOLO lo transitorio. Un 400 por peticion mal armada o un 401 por
 * clave invalida no mejoran reintentando: ahi hay que fallar rapido y decirlo.
 */
trait RetriesTransientFailures
{
    /** Intentos totales, incluido el primero. */
    private const INTENTOS = 3;

    /** Espera entre intentos, en milisegundos. */
    private const ESPERA_MS = 1200;

    private function reintentable(): callable
    {
        return static function (Throwable $e): bool {
            if ($e instanceof ConnectionException) {
                return true;
            }

            if (! $e instanceof RequestException) {
                return false;
            }

            return in_array($e->response->status(), [429, 500, 502, 503, 504, 529], true);
        };
    }
}
