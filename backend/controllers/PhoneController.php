<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ContactService;
use App\Utils\Input;
use App\Utils\Response;
use JsonException;
use OpenApi\Attributes as OA;

/**
 * Controlador de telefonos (recursos anidados bajo /contactos/{id}/telefonos).
 */
#[OA\Tag(name: 'Telefonos', description: 'Telefonos anidados bajo un contacto')]
final class PhoneController
{
    public function __construct(private ContactService $service)
    {
    }

    #[OA\Post(
        path: '/api/contactos/{id}/telefonos',
        operationId: 'agregarTelefono',
        summary: 'Agregar un telefono a un contacto',
        description: 'Agrega un nuevo telefono a un contacto existente.',
        tags: ['Telefonos'],
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: 'Identificador del contacto.',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Datos del telefono a agregar.',
            content: new OA\JsonContent(ref: '#/components/schemas/PhoneInput')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Telefono agregado correctamente.',
                content: new OA\JsonContent(ref: '#/components/schemas/PhoneResponse')
            ),
            new OA\Response(
                response: 400,
                description: 'Cuerpo de la peticion no es JSON valido.',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Contacto no encontrado.',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Datos invalidos.',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ],
    )]
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

    #[OA\Delete(
        path: '/api/contactos/{id}/telefonos/{telefonoId}',
        operationId: 'eliminarTelefono',
        summary: 'Eliminar un telefono de un contacto',
        description: 'Elimina un telefono especifico de un contacto.',
        tags: ['Telefonos'],
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: 'Identificador del contacto.',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\PathParameter(
                name: 'telefonoId',
                description: 'Identificador del telefono.',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Telefono eliminado correctamente.',
                content: new OA\JsonContent(ref: '#/components/schemas/EmptyDataResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Contacto o telefono no encontrado.',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ],
    )]
    public function destroy(int $contactoId, int $telefonoId): void
    {
        $this->service->deletePhone($contactoId, $telefonoId);

        Response::success([], 'Telefono eliminado correctamente.', 200);
    }
}