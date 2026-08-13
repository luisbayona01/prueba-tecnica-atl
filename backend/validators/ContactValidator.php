<?php

declare(strict_types=1);

namespace App\Validators;

use App\Utils\Input;

/**
 * Valida los datos de un contacto.
 */
final class ContactValidator
{
    public const NOMBRE_MAX   = 100;
    public const APELLIDO_MAX = 100;
    public const EMAIL_MAX    = 190;

    /**
     * Valida los datos de entrada y devuelve un arreglo de errores por campo.
     */
    public static function validate(array $data): array
    {
        $errors = [];

        $nombre   = Input::trim($data['nombre'] ?? '');
        $apellido = Input::trim($data['apellido'] ?? '');
        $email    = Input::trim($data['email'] ?? '');
        $telefonos = $data['telefonos'] ?? [];

        // --- nombre ---
        if ($nombre === '') {
            $errors['nombre'] = 'El nombre es obligatorio.';
        } elseif (mb_strlen($nombre) < 2) {
            $errors['nombre'] = 'El nombre debe tener al menos 2 caracteres.';
        } elseif (mb_strlen($nombre) > self::NOMBRE_MAX) {
            $errors['nombre'] = "El nombre no debe exceder " . self::NOMBRE_MAX . " caracteres.";
        }

        // --- apellido ---
        if ($apellido === '') {
            $errors['apellido'] = 'El apellido es obligatorio.';
        } elseif (mb_strlen($apellido) < 2) {
            $errors['apellido'] = 'El apellido debe tener al menos 2 caracteres.';
        } elseif (mb_strlen($apellido) > self::APELLIDO_MAX) {
            $errors['apellido'] = "El apellido no debe exceder " . self::APELLIDO_MAX . " caracteres.";
        }

        // --- email ---
        if ($email === '') {
            $errors['email'] = 'El email es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'El email no es valido.';
        } elseif (mb_strlen($email) > self::EMAIL_MAX) {
            $errors['email'] = "El email no debe exceder " . self::EMAIL_MAX . " caracteres.";
        }

        // --- telefonos ---
        if (!is_array($telefonos) || $telefonos === []) {
            $errors['telefonos'] = 'Debe ingresar al menos un telefono.';
        } else {
            foreach ($telefonos as $index => $telefono) {
                $phoneErrors = PhoneValidator::validate(is_array($telefono) ? $telefono : ['numero' => $telefono]);

                if ($phoneErrors !== []) {
                    $errors["telefonos.$index." . array_key_first($phoneErrors)] = $phoneErrors[array_key_first($phoneErrors)];
                }
            }
        }

        return $errors;
    }
}