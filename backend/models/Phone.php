<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Entidad Telefono.
 */
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