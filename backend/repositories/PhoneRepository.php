<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use PDO;

/**
 * Acceso a datos de la tabla telefonos.
 * Solo contiene SQL y no tiene logica de negocio.
 */
final class PhoneRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function create(int $contactoId, string $numero): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO telefonos (contacto_id, numero)
             VALUES (:contacto_id, :numero)'
        );
        $stmt->execute([
            ':contacto_id' => $contactoId,
            ':numero'      => $numero,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByContact(int $contactoId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, contacto_id, numero, created_at, updated_at
             FROM telefonos
             WHERE contacto_id = :contacto_id
             ORDER BY id ASC'
        );
        $stmt->execute([':contacto_id' => $contactoId]);

        return $stmt->fetchAll() ?: [];
    }

    public function findForContact(int $contactoId, int $telefonoId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, contacto_id, numero, created_at, updated_at
             FROM telefonos
             WHERE id = :telefono_id AND contacto_id = :contacto_id
             LIMIT 1'
        );
        $stmt->execute([
            ':telefono_id' => $telefonoId,
            ':contacto_id' => $contactoId,
        ]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function delete(int $telefonoId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM telefonos WHERE id = :id');
        $stmt->execute([':id' => $telefonoId]);

        return $stmt->rowCount() > 0;
    }
}