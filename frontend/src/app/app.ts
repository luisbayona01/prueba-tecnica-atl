import { Component, inject } from '@angular/core';
import { ContactListComponent } from './components/contact-list/contact-list';
import { ContactFormComponent } from './components/contact-form/contact-form';
import { Contact } from './interfaces/contact';
import { ContactService } from './services/contact.service';

/**
 * Componente raiz: orquesta la lista y el formulario de contactos.
 */
@Component({
  selector: 'app-root',
  imports: [ContactListComponent, ContactFormComponent],
  templateUrl: './app.html',
  styleUrl: './app.scss',
})
export class App {
  private readonly service = inject(ContactService);

  readonly notice = this.service.notice;

  showForm = false;
  editingContact: Contact | null = null;

  constructor() {
    void this.service.loadInitialData();
  }

  onNewContact(): void {
    this.editingContact = null;
    this.showForm = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  onEditContact(contact: Contact): void {
    this.editingContact = contact;
    this.showForm = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  onSaved(contact: Contact): void {
    if (contact.id === 0) {
      this.service.create(contact);
    } else {
      this.service.update(contact);
    }
    this.closeForm();
  }

  onCancelled(): void {
    this.closeForm();
  }

  dismissNotice(): void {
    this.service.clearNotice();
  }

  private closeForm(): void {
    this.showForm = false;
    this.editingContact = null;
  }
}
