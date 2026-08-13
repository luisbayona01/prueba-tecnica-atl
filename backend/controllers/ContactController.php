<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ContactService;
use App\Utils\ApiException;
use App\Utils\Input;
use App\Utils\Response;
use JsonException;

/**
 * Controlador de contactos: orquesta peticiones HTTP -> servicio -> respuesta.
 * No contiene SQL ni reglas de negocio.
 */
final class ContactController
{
    public function __construct(private ContactService $service)
    {
    }

    public function index(): void
    {
        Response::success(
            $this->service->list(),
            'Contactos obtenidos correctamente.'
        );
    }

    public function show(int $id): void
    {
        Response::success(
            $this->service->find($id),
            'Contacto obtenido correctamente.'
        );
    }

    public function store(): void
    {
        try {
            $data = Input::body();
        } catch (JsonException) {
            Response::error('El cuerpo de la peticion no es un JSON valido.', 400);
            return;
        }

        Response::success(
            $this->service->create($data),
            'Contacto creado correctamente.',
            201
        );
    }

    public function destroy(int $id): void
    {
        $this->service->delete($id);

        Response::success([], 'Contacto eliminado correctamente.', 200);
    }
}