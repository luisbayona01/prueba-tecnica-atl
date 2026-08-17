<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ContactService;
use App\Utils\ApiException;
use App\Utils\Input;
use App\Utils\Response;
use JsonException;
use OpenApi\Attributes as OA;

/**
 * Controlador de contactos: orquesta peticiones HTTP -> servicio -> respuesta.
 * No contiene SQL ni reglas de negocio.
 */
#[OA\Tag(name: 'Contactos', description: 'Gestion de contactos')]
final class ContactController
{
    public function __construct(private ContactService $service)
    {
    }

    #[OA\Get(
        path: '/api/contactos',
        operationId: 'listarContactos',
        summary: 'Listar contactos',
        description: 'Obtiene todos los contactos con sus telefonos.',
        tags: ['Contactos'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Contactos obtenidos correctamente.',
                content: new OA\JsonContent(ref: '#/components/schemas/ContactListResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Error interno del servidor.',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ],
    )]
    public function index(): void
    {
        Response::success(
            $this->service->list(),
            'Contactos obtenidos correctamente.'
        );
    }

    #[OA\Get(
        path: '/api/contactos/{id}',
        operationId: 'obtenerContacto',
        summary: 'Obtener un contacto',
        description: 'Obtiene un contacto por su ID junto con sus telefonos.',
        tags: ['Contactos'],
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: 'Identificador del contacto.',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Contacto obtenido correctamente.',
                content: new OA\JsonContent(ref: '#/components/schemas/ContactResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Contacto no encontrado.',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ],
    )]
    public function show(int $id): void
    {
        Response::success(
            $this->service->find($id),
            'Contacto obtenido correctamente.'
        );
    }

    #[OA\Post(
        path: '/api/contactos',
        operationId: 'crearContacto',
        summary: 'Crear un contacto',
        description: 'Crea un contacto junto con al menos un telefono.',
        tags: ['Contactos'],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Datos del contacto a crear.',
            content: new OA\JsonContent(ref: '#/components/schemas/ContactInput')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Contacto creado correctamente.',
                content: new OA\JsonContent(ref: '#/components/schemas/ContactResponse')
            ),
            new OA\Response(
                response: 400,
                description: 'Cuerpo de la peticion no es JSON valido.',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Datos invalidos.',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 409,
                description: 'El email ya esta registrado.',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ],
    )]
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

    #[OA\Delete(
        path: '/api/contactos/{id}',
        operationId: 'eliminarContacto',
        summary: 'Eliminar un contacto',
        description: 'Elimina un contacto y sus telefonos asociados.',
        tags: ['Contactos'],
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: 'Identificador del contacto.',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Contacto eliminado correctamente.',
                content: new OA\JsonContent(ref: '#/components/schemas/EmptyDataResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Contacto no encontrado.',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ],
    )]
    public function destroy(int $id): void
    {
        $this->service->delete($id);

        Response::success([], 'Contacto eliminado correctamente.', 200);
    }
}