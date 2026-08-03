<?php

namespace App\Services\Ai;

/**
 * Contrato minimo con un proveedor de IA conversacional.
 *
 * Existe para que la tienda pueda cambiar de proveedor sin tocar la logica que
 * arma el contexto (catalogo, productos, compatibilidad con motos). Esa logica
 * vive en StoreAssistantService y es la parte valiosa; esto de aca es solo el
 * enchufe: traduce al formato de cada API y devuelve texto.
 *
 * El formato interno de `$history` es el de Anthropic (roles user/assistant)
 * porque fue el primero: cada implementacion traduce desde ahi si le hace falta.
 */
interface AiTextGenerator
{
    /** Respuesta al cliente cuando el proveedor contesta bien pero sin texto util. */
    public const SIN_RESPUESTA = 'No pude generar una respuesta. Intenta reformular tu pregunta.';

    /**
     * @param  string  $system  Persona e instrucciones; va en el campo aparte que
     *                          cada API tiene para eso, no mezclado con la pregunta.
     * @param  list<array{role: string, content: string}>  $history  Turnos previos ya
     *                          normalizados (roles user/assistant, el primero user).
     * @param  string  $userContent  Pregunta del cliente con el contexto de la tienda.
     *
     * @throws \RuntimeException Si falta la clave o el proveedor responde con error.
     */
    public function generate(string $system, array $history, string $userContent): string;
}
