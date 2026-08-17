<?php

declare(strict_types=1);

namespace App\Models;

use OpenApi\Attributes as OA;

/**
 * Entidad Contacto.
 */
#[OA\Schema(
    schema: 'Contact',
    description: 'Contacto con sus telefonos.',
    required: ['id', 'nombre', 'apellido', 'email'],
    properties: [
        new OA\Property(property: 'id', description: 'Identificador unico del contacto.', type: 'integer', example: 1),
        new OA\Property(property: 'nombre', type: 'string', example: 'Juan'),
        new OA\Property(property: 'apellido', type: 'string', example: 'Perez'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan.perez@example.com'),
        new OA\Property(
            property: 'telefonos',
            description: 'Telefonos asociados al contacto.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Phone')
        ),
        new OA\Property(property: 'created_at', description: 'Fecha de creacion.', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', description: 'Fecha de ultima actualizacion.', type: 'string', format: 'date-time'),
    ],
)]
#[OA\Schema(
    schema: 'ContactInput',
    description: 'Datos para crear un contacto.',
    required: ['nombre', 'apellido', 'email', 'telefonos'],
    properties: [
        new OA\Property(property: 'nombre', type: 'string', minLength: 2, maxLength: 100, example: 'Juan'),
        new OA\Property(property: 'apellido', type: 'string', minLength: 2, maxLength: 100, example: 'Perez'),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 190, example: 'juan.perez@example.com'),
        new OA\Property(
            property: 'telefonos',
            description: 'Al menos un telefono es obligatorio.',
            type: 'array',
            minItems: 1,
            items: new OA\Items(ref: '#/components/schemas/PhoneInput')
        ),
    ],
)]
final class Contact
{
    public function __construct(
        public int $id = 0,
        public string $nombre = '',
        public string $apellido = '',
        public string $email = '',
        public string $createdAt = '',
        public string $updatedAt = '',
        /** @var Phone[] */
        public array $telefonos = []
    ) {
    }

    /**
     * Convierte el modelo en un arreglo listo para JSON.
     */
    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'nombre'     => $this->nombre,
            'apellido'   => $this->apellido,
            'email'      => $this->email,
            'telefonos'  => array_map(
                static fn (Phone $phone) => $phone->toArray(),
                $this->telefonos
            ),
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id:        (int) $row['id'],
            nombre:    $row['nombre'],
            apellido:  $row['apellido'],
            email:     $row['email'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
        );
    }
}