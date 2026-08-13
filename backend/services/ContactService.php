<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Contact;
use App\Models\Phone;
use App\Repositories\ContactRepository;
use App\Repositories\PhoneRepository;
use App\Utils\ApiException;
use App\Utils\Input;
use App\Validators\ContactValidator;
use App\Validators\PhoneValidator;
use PDO;
use Throwable;

/**
 * Logica de negocio de contactos y telefonos.
 * No contiene SQL directo; delega el acceso a datos en los repositorios.
 */
final class ContactService
{
    private PDO $db;

    public function __construct(
        private ContactRepository $contacts,
        private PhoneRepository $phones,
        ?PDO $db = null
    ) {
        $this->db = $db ?? $contacts->getConnection();
    }

    /**
     * Lista todos los contactos con sus telefonos.
     *
     * @return array<int, array<string, mixed>>
     */
    public function list(): array
    {
        $result = [];

        foreach ($this->contacts->all() as $row) {
            $contact = Contact::fromRow($row);
            $phones  = $this->phones->findByContact($contact->id);
            $result[] = $this->contacts->withPhones($contact, $phones)->toArray();
        }

        return $result;
    }

    public function find(int $id): array
    {
        $row = $this->contacts->find($id);

        if ($row === null) {
            throw new ApiException('Contacto no encontrado.', 404);
        }

        $contact = Contact::fromRow($row);
        $phones  = $this->phones->findByContact($id);

        return $this->contacts->withPhones($contact, $phones)->toArray();
    }

    /**
     * Crea un contacto junto con sus telefonos usando una transaccion.
     */
    public function create(array $data): array
    {
        $nombre    = Input::trim($data['nombre'] ?? '');
        $apellido  = Input::trim($data['apellido'] ?? '');
        $email     = Input::trim($data['email'] ?? '');
        $telefonos = $data['telefonos'] ?? [];

        $errors = ContactValidator::validate([
            'nombre'    => $nombre,
            'apellido'  => $apellido,
            'email'     => $email,
            'telefonos' => $telefonos,
        ]);

        if ($errors !== []) {
            throw new ApiException('Datos invalidos.', 422, $errors);
        }

        $this->db->beginTransaction();

        try {
            if ($this->contacts->findByEmail($email) !== null) {
                throw new ApiException(
                    'El email ya esta registrado.',
                    409,
                    ['email' => 'Ya existe un contacto con este email.']
                );
            }

            $contactoId = $this->contacts->create($nombre, $apellido, $email);

            foreach ($telefonos as $telefono) {
                $numero = is_array($telefono)
                    ? Input::trim($telefono['numero'] ?? '')
                    : Input::trim($telefono);
                $this->phones->create($contactoId, $numero);
            }

            $this->db->commit();
        } catch (ApiException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        } catch (Throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new ApiException('No se pudo guardar el contacto.', 500);
        }

        return $this->find($contactoId);
    }

    public function delete(int $id): void
    {
        if ($this->contacts->find($id) === null) {
            throw new ApiException('Contacto no encontrado.', 404);
        }

        $this->contacts->delete($id);
    }

    /**
     * Agrega un telefono a un contacto existente.
     */
    public function addPhone(int $contactoId, array $data): array
    {
        if ($this->contacts->find($contactoId) === null) {
            throw new ApiException('Contacto no encontrado.', 404);
        }

        $numero = Input::trim($data['numero'] ?? '');

        $errors = PhoneValidator::validate(['numero' => $numero]);

        if ($errors !== []) {
            throw new ApiException('Datos invalidos.', 422, $errors);
        }

        $phoneId = $this->phones->create($contactoId, $numero);
        $phone   = $this->phones->findForContact($contactoId, $phoneId);

        if ($phone === null) {
            throw new ApiException('No se pudo guardar el telefono.', 500);
        }

        return Phone::fromRow($phone)->toArray();
    }

    public function deletePhone(int $contactoId, int $telefonoId): void
    {
        if ($this->contacts->find($contactoId) === null) {
            throw new ApiException('Contacto no encontrado.', 404);
        }

        if ($this->phones->findForContact($contactoId, $telefonoId) === null) {
            throw new ApiException('Telefono no encontrado.', 404);
        }

        $this->phones->delete($telefonoId);
    }
}