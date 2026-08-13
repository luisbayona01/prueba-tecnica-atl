<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Centraliza las respuestas JSON de la API con un formato consistente.
 */
final class Response
{
    public static function success(
        array $data = [],
        string $message = 'OK',
        int $status = 200
    ): void {
        self::send([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public static function error(
        string $message,
        int $status,
        array $errors = []
    ): void {
        $body = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $body['errors'] = $errors;
        }

        self::send($body, $status);
    }

    private static function send(array $payload, int $status): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }
}