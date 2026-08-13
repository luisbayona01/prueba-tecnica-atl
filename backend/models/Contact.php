<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Entidad Contacto.
 */
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