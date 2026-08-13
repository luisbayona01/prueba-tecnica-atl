<?php

declare(strict_types=1);

namespace App\Utils;

use RuntimeException;

/**
 * Excepcion de dominio que transporta el codigo HTTP correspondiente.
 */
final class ApiException extends RuntimeException
{
    private int $statusCode;
    private array $errors;

    public function __construct(string $message, int $statusCode, array $errors = [])
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errors     = $errors;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}