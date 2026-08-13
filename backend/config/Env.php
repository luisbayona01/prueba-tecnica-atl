<?php

declare(strict_types=1);

namespace App\Config;

use RuntimeException;

/**
 * Carga las variables del archivo .env sin depender de librerias externas.
 */
final class Env
{
    private static array $loaded = [];

    /**
     * Carga el archivo .env en variables de entorno.
     */
    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }

        if (!is_file($path)) {
            throw new RuntimeException("No se encontro el archivo .env en: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $value = self::cleanQuotes($value);

            // Si ya existe una variable de entorno real (por ejemplo, la que
            // inyecta docker-compose), tiene prioridad sobre el archivo .env.
            if (getenv($key)) {
                continue;
            }

            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            self::$loaded[$key] = $value;
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = self::$loaded[$key] ?? getenv($key);

        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return $value;
    }

    private static function cleanQuotes(string $value): string
    {
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last  = $value[strlen($value) - 1];

            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                return substr($value, 1, -1);
            }
        }

        return $value;
    }
}
