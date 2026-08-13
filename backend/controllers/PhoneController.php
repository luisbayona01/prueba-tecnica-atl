<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ContactService;
use App\Utils\Input;
use App\Utils\Response;
use JsonException;

/**
 * Controlador de telefonos (recursos anidados bajo /contactos/{id}/telefonos).
 */
final class PhoneController
{
    public function __construct(private ContactService $service)
    {
    }

    public function store(int $contactoId): void
    {
        try {
            $data = Input::body();
        } catch (JsonException) {
            Response::error('El cuerpo de la peticion no es un JSON valido.', 400);
            return;
        }

        Response::success(
            $this->service->addPhone($contactoId, $data),
            'Telefono agregado correctamente.',
            201
        );
    }

    public function destroy(int $contactoId, int $telefonoId): void
    {
        $this->service->deletePhone($contactoId, $telefonoId);

        Response::success([], 'Telefono eliminado correctamente.', 200);
    }
}