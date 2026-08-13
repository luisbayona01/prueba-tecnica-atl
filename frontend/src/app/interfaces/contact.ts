/**
 * Telefono de un contacto.
 * `id` es opcional en el frontend: no existe en contacts.json y
 * solo estara presente cuando se consuma la API PHP del backend.
 */
export interface Telefono {
  id?: number;
  numero: string;
}

/**
 * Contacto.
 * `telefonos` se normaliza siempre a Telefono[] internamente,
 * aunque contacts.json los trae como string[].
 */
export interface Contact {
  id: number;
  nombre: string;
  apellido: string;
  email: string;
  telefonos: Telefono[];
  createdAt?: string;
  updatedAt?: string;
}

/**
 * Forma cruda en que llegan los datos de contacts.json (telefonos como string[]).
 */
export interface RawContact {
  id: number;
  nombre: string;
  apellido: string;
  email: string;
  telefonos: string[];
}