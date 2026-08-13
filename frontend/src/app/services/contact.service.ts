import { inject, Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { firstValueFrom } from 'rxjs';
import { Contact, RawContact, Telefono } from '../interfaces/contact';

/**
 * Servicio central de contactos.
 *
 * Responsabilidades:
 *  - Cargar datos iniciales (LocalStorage o contacts.json via HttpClient).
 *  - Mantener el estado de la lista en memoria (señales reactivas).
 *  - Persistir los cambios en LocalStorage.
 *  - Exponer operaciones tipadas (listar, crear, editar, eliminar).
 *
 * Los componentes NUNCA acceden directamente a LocalStorage ni a HttpClient:
 * toda la obtencion de datos pasa por aqui, de modo que mas adelante se pueda
 * cambiar contacts.json por la API PHP (http://localhost/api/contactos)
 * sin tocar los componentes.
 */
@Injectable({ providedIn: 'root' })
export class ContactService {
  private readonly http = inject(HttpClient);

  private readonly STORAGE_KEY = 'prueba_tecnica_contactos';
  private readonly JSON_PATH = 'assets/data/contacts.json';

  /** Lista de contactos (estado de la aplicacion). */
  private readonly _contacts = signal<Contact[]>([]);
  readonly contacts = this._contacts.asReadonly();

  /** Indica si los datos iniciales aun se estan cargando. */
  private readonly _loading = signal(false);
  readonly loading = this._loading.asReadonly();

  /** Mensaje global de exito/error para mostrar al usuario. */
  private readonly _notice = signal<{ type: 'success' | 'error'; text: string } | null>(null);
  readonly notice = this._notice.asReadonly();

  /**
   * Carga los datos iniciales:
   *  1. Si LocalStorage ya tiene contactos, los usa.
   *  2. Si no, los descarga de contacts.json y los guarda en LocalStorage.
   */
  async loadInitialData(): Promise<void> {
    if (this._contacts().length > 0) {
      return;
    }

    this._loading.set(true);

    try {
      const stored = this.readFromStorage();

      if (stored.length > 0) {
        this._contacts.set(stored);
        return;
      }

      const raw = await firstValueFrom(this.http.get<RawContact[]>(this.JSON_PATH));
      const normalized = (raw ?? []).map((c) => this.normalize(c));

      this.saveToStorage(normalized);
      this._contacts.set(normalized);
    } catch {
      this._notice.set({
        type: 'error',
        text: 'No se pudieron cargar los contactos. Verifica tu conexion e intenta de nuevo.',
      });
    } finally {
      this._loading.set(false);
    }
  }

  /** Limpia el estado en memoria para forzar una recarga desde el origen. */
  reset(): void {
    this._contacts.set([]);
    this._notice.set(null);
    void this.loadInitialData();
  }

  /** Oculta el aviso global. */
  clearNotice(): void {
    this._notice.set(null);
  }

  /**
   * Agrega un contacto (sin id, el servicio lo asigna).
   * Devuelve el contacto guardado.
   */
  create(contact: Omit<Contact, 'id'>): Contact {
    const nextId = this.nextId();
    const nuevo: Contact = { ...this.cloneContact(contact), id: nextId };

    this._contacts.update((list) => [...list, nuevo]);
    this.saveToStorage(this._contacts());
    this._notice.set({ type: 'success', text: 'Contacto creado correctamente.' });

    return nuevo;
  }

  /**
   * Actualiza un contacto existente (reemplazo inmutable).
   * Si el id no existe, no hace nada.
   */
  update(contact: Contact): void {
    let updated = false;

    this._contacts.update((list) =>
      list.map((c) => {
        if (c.id !== contact.id) {
          return c;
        }
        updated = true;
        return { ...this.cloneContact(contact) };
      }),
    );

    if (!updated) {
      this._notice.set({ type: 'error', text: 'No se encontro el contacto a editar.' });
      return;
    }

    this.saveToStorage(this._contacts());
    this._notice.set({ type: 'success', text: 'Contacto actualizado correctamente.' });
  }

  /** Elimina un contacto por id. */
  delete(id: number): void {
    this._contacts.update((list) => list.filter((c) => c.id !== id));
    this.saveToStorage(this._contacts());
    this._notice.set({ type: 'success', text: 'Contacto eliminado.' });
  }

  /** Devuelve una copia inmutable de un contacto (evita mutaciones accidentales). */
  private cloneContact<T extends Contact | Omit<Contact, 'id'>>(contact: T): T {
    return {
      ...contact,
      telefonos: contact.telefonos.map((t) => ({ ...t })),
    } as T;
  }

  /** Convierte un contacto crudo del JSON (telefonos string[]) al modelo interno. */
  private normalize(raw: RawContact): Contact {
    return {
      id: raw.id,
      nombre: raw.nombre,
      apellido: raw.apellido,
      email: raw.email,
      telefonos: raw.telefonos.map((numero) => ({ numero }) as Telefono),
    };
  }

  private nextId(): number {
    const list = this._contacts();
    return list.length === 0 ? 1 : Math.max(...list.map((c) => c.id)) + 1;
  }

  private readFromStorage(): Contact[] {
    const raw = localStorage.getItem(this.STORAGE_KEY);

    if (!raw) {
      return [];
    }

    try {
      const parsed = JSON.parse(raw) as Contact[];
      return Array.isArray(parsed) ? parsed : [];
    } catch {
      return [];
    }
  }

  private saveToStorage(contacts: Contact[]): void {
    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(contacts));
  }
}
