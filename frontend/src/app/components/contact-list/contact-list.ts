import { Component, EventEmitter, inject, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Contact, Telefono } from '../../interfaces/contact';
import { ContactService } from '../../services/contact.service';

/**
 * Lista los contactos en tarjetas.
 * Emite eventos de creacion/edicion; la eliminacion se delega a ContactService.
 */
@Component({
  selector: 'app-contact-list',
  imports: [CommonModule],
  templateUrl: './contact-list.html',
  styleUrl: './contact-list.scss',
})
export class ContactListComponent {
  private readonly service = inject(ContactService);

  @Output() readonly newContact = new EventEmitter<void>();
  @Output() readonly editContact = new EventEmitter<Contact>();

  readonly contacts = this.service.contacts;
  readonly loading = this.service.loading;

  onCreate(): void {
    this.newContact.emit();
  }

  onEdit(contact: Contact): void {
    this.editContact.emit(contact);
  }

  onDelete(contact: Contact): void {
    if (window.confirm(`¿Eliminar a ${contact.nombre} ${contact.apellido}?`)) {
      this.service.delete(contact.id);
    }
  }

  phoneList(contact: Contact): string {
    return contact.telefonos.map((t: Telefono) => t.numero).join(' · ');
  }
}