<?php

declare(strict_types=1);

namespace App\Docs;

use OpenApi\Attributes as OA;

/**
 * Definiciones globales de la documentacion OpenAPI.
 *
 * Los atributos de las operaciones viven en los controladores
 * (App\Controllers) y los esquemas de datos en los modelos.
 *
 * La clase solo ancla los atributos globales (Info, Server, Tags y
 * esquemas de respuesta); debe ser autoloadable para que el generador
 * los reconozca.
 */
#[OA\Info(
    version: '1.0.0',
    title: 'API de Contactos',
    description: 'API REST para la gestion de **contactos y telefonos**. '
        . 'Lista, consulta, crea y elimina contactos, y gestiona telefonos '
        . 'anidados. Las respuestas usan el formato JSON `{ success, message, data }`.',
    contact: new OA\Contact(email: 'dev@example.com'),
)]
#[OA\Server(url: '/', description: 'Servidor local')]

#[OA\Tag(name: 'Contactos', description: 'Gestion de contactos')]
#[OA\Tag(name: 'Telefonos', description: 'Telefonos anidados bajo un contacto')]

// --- Envoltura de respuestas de la API ---

#[OA\Schema(
    schema: 'ErrorResponse',
    description: 'Respuesta de error generica de la API.',
    required: ['success', 'message'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Contacto no encontrado.'),
        new OA\Property(property: 'errors', description: 'Errores por campo (opcional).', type: 'object'),
    ],
)]
#[OA\Schema(
    schema: 'ContactListResponse',
    description: 'Listado de contactos.',
    required: ['success', 'message', 'data'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Contactos obtenidos correctamente.'),
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Contact')
        ),
    ],
)]
#[OA\Schema(
    schema: 'ContactResponse',
    description: 'Un contacto con sus telefonos.',
    required: ['success', 'message', 'data'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Contacto obtenido correctamente.'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Contact'),
    ],
)]
#[OA\Schema(
    schema: 'PhoneResponse',
    description: 'Un telefono.',
    required: ['success', 'message', 'data'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Telefono agregado correctamente.'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Phone'),
    ],
)]
#[OA\Schema(
    schema: 'EmptyDataResponse',
    description: 'Respuesta sin datos (operaciones de borrado).',
    required: ['success', 'message', 'data'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Contacto eliminado correctamente.'),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(), example: []),
    ],
)]
final class OpenApiSpec
{
}