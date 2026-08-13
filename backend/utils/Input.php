<?php

declare(strict_types=1);

namespace App\Utils;

use JsonException;

/**
 * Helper para leer el cuerpo JSON de la peticion HTTP.
 */
final class Input
{
    /**
     * Devuelve el cuerpo de la peticion como arreglo asociativo.
     *
     * @throws JsonException  Si el JSON recibido es invalido o no es objeto.
     */
    public static function body(): array
    {
        $raw = file_get_contents('php://input');

        if ($raw === '' || $raw === false) {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw $e;
        }

        if (!is_array($decoded)) {
            throw new JsonException('El cuerpo JSON no es un objeto valido.');
        }

        return $decoded;
    }

    /**
     * Normaliza un valor de texto: recorta espacios y evita cadenas vacias.
     */
    public static function trim($value): string
    {
        if ($value === null || is_bool($value)) {
            return '';
        }

        $clean = trim((string) $value);
        return $clean;
    }
}