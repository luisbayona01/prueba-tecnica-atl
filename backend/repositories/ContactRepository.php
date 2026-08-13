<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\Contact;
use App\Models\Phone;
use PDO;

/**
 * Acceso a datos de la tabla contactos.
 * Solo contiene SQL y no tiene logica de negocio.
 */
final class ContactRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function getConnection(): PDO
    {
        return $this->db;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nombre, apellido, email, created_at, updated_at
             FROM contactos
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nombre, apellido, email, created_at, updated_at
             FROM contactos
             WHERE email = :email
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nombre, apellido, email, created_at, updated_at
             FROM contactos
             ORDER BY created_at DESC'
        );

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Inserta un contacto y devuelve el id generado.
     */
    public function create(string $nombre, string $apellido, string $email): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO contactos (nombre, apellido, email)
             VALUES (:nombre, :apellido, :email)'
        );
        $stmt->execute([
            ':nombre'   => $nombre,
            ':apellido' => $apellido,
            ':email'    => $email,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM contactos WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Carga los telefonos de un contacto y los adjunta al modelo.
     */
    public function withPhones(Contact $contact, array $phones): Contact
    {
        $contact->telefonos = array_map(
            static fn (array $row) => Phone::fromRow($row),
            $phones
        );

        return $contact;
    }
}