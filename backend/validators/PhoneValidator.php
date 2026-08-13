<?php

declare(strict_types=1);

namespace App\Validators;

use App\Utils\Input;

/**
 * Valida los datos de un telefono.
 */
final class PhoneValidator
{
    public const MIN_LENGTH = 7;
    public const MAX_LENGTH = 20;

    /**
     * Valida el numero de telefono y devuelve un arreglo de errores.
     *
     * Acepta digitos y los simbolos +, -, espacios o parentesis.
     */
    public static function validate(array $data): array
    {
        $errors = [];

        $numero = Input::trim($data['numero'] ?? '');

        if ($numero === '') {
            $errors['numero'] = 'El telefono es obligatorio.';
        } elseif (preg_match('/[^0-9+()\-\s]/', $numero)) {
            $errors['numero'] = 'El telefono contiene caracteres no validos.';
        } else {
            $digits = preg_replace('/\D/', '', $numero) ?? '';

            if (strlen($digits) < self::MIN_LENGTH) {
                $errors['numero'] = 'El telefono debe tener al menos ' . self::MIN_LENGTH . ' digitos.';
            } elseif (strlen($digits) > self::MAX_LENGTH) {
                $errors['numero'] = 'El telefono no debe exceder ' . self::MAX_LENGTH . ' digitos.';
            }
        }

        return $errors;
    }
}