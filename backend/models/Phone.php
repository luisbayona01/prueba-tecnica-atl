<?php

declare(strict_types=1);

namespace App\Models;

use OpenApi\Attributes as OA;

/**
 * Entidad Telefono.
 */
#[OA\Schema(
    schema: 'Phone',
    description: 'Telefono asociado a un contacto.',
    required: ['id', 'numero'],
    properties: [
        new OA\Property(property: 'id', description: 'Identificador unico del telefono.', type: 'integer', example: 1),
        new OA\Property(property: 'numero', type: 'string', example: '+57 300 123 4567'),
    ],
)]
#[OA\Schema(
    schema: 'PhoneInput',
    description: 'Datos para agregar un telefono.',
    required: ['numero'],
    properties: [
        new OA\Property(
            property: 'numero',
            description: 'Digitos y los simbolos +, -, espacios o parentesis.',
            type: 'string',
            minLength: 7,
            maxLength: 20,
            example: '+57 300 123 4567'
        ),
    ],
)]
final class Phone
{
    public function __construct(
        public int $id = 0,
        public int $contactoId = 0,
        public string $numero = '',
        public string $createdAt = '',
        public string $updatedAt = ''
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'      => $this->id,
            'numero'  => $this->numero,
        ];
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id:        (int) $row['id'],
            contactoId: (int) $row['contacto_id'],
            numero:    $row['numero'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
        );
    }
}