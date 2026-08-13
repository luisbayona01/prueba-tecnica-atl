import { Component, EventEmitter, inject, Input, OnInit, Output } from '@angular/core';
import {
  AbstractControl,
  FormArray,
  FormBuilder,
  FormControl,
  ReactiveFormsModule,
  ValidatorFn,
  Validators,
} from '@angular/forms';
import { Contact, Telefono } from '../../interfaces/contact';
import { PhoneListComponent } from '../phone-list/phone-list';

/**
 * Formulario de contacto (crear / editar) usando Reactive Forms.
 *
 *  - Sin `contact`  -> modo crear.
 *  - Con `contact`  -> modo editar (prellenado, sin mutar el original).
 */
@Component({
  selector: 'app-contact-form',
  imports: [ReactiveFormsModule, PhoneListComponent],
  templateUrl: './contact-form.html',
  styleUrl: './contact-form.scss',
})
export class ContactFormComponent implements OnInit {
  private readonly fb = inject(FormBuilder);

  @Input() contact: Contact | null = null;
  @Output() readonly saved = new EventEmitter<Contact>();
  @Output() readonly cancelled = new EventEmitter<void>();

  readonly form = this.fb.group(
    {
      nombre: this.buildTextControl(),
      apellido: this.buildTextControl(),
      email: this.fb.control<string>('', {
        nonNullable: true,
        validators: [Validators.required, Validators.maxLength(190), Validators.email],
      }),
      telefonos: this.fb.array<FormControl<string>>([]),
    },
    { validators: [atLeastOnePhone] },
  );

  private submitted = false;

  ngOnInit(): void {
    if (this.contact) {
      this.prefill(this.contact);
    }
  }

  get isEditing(): boolean {
    return this.contact !== null;
  }

  get title(): string {
    return this.isEditing ? 'Editar contacto' : 'Nuevo contacto';
  }

  get telefonos(): FormArray<FormControl<string>> {
    return this.form.controls.telefonos;
  }

  onSubmit(): void {
    this.submitted = true;
    this.form.markAllAsTouched();

    if (this.form.invalid) {
      return;
    }

    const value = this.form.value;

    const contact: Contact = {
      id: this.contact?.id ?? 0,
      nombre: value.nombre ?? '',
      apellido: value.apellido ?? '',
      email: value.email ?? '',
      telefonos: (value.telefonos ?? []).map((n: string) => ({ numero: n }) as Telefono),
    };

    this.saved.emit(contact);
  }

  onCancel(): void {
    this.cancelled.emit();
  }

  errorFor(controlName: 'nombre' | 'apellido' | 'email'): string {
    const control = this.form.controls[controlName];

    if ((control.touched || this.submitted) && control.errors) {
      if (control.errors['required']) {
        return controlName === 'email'
          ? 'El email es obligatorio.'
          : `El ${controlName} es obligatorio.`;
      }
      if (control.errors['minlength']) {
        return `El ${controlName} debe tener al menos ${control.errors['minlength'].requiredLength} caracteres.`;
      }
      if (control.errors['maxlength']) {
        return `El ${controlName} no debe exceder ${control.errors['maxlength'].requiredLength} caracteres.`;
      }
      if (control.errors['email']) {
        return 'El email no es valido.';
      }
    }

    return '';
  }

  /** Valida a nivel de grupo que exista al menos un telefono. */
  atLeastOnePhoneError(): boolean {
    return (this.submitted || this.telefonos.touched) && this.telefonos.length === 0;
  }

  /** Puebla el formulario desde el contacto a editar (sin mutar el original). */
  private prefill(contact: Contact): void {
    this.form.patchValue({
      nombre: contact.nombre,
      apellido: contact.apellido,
      email: contact.email,
    });

    this.telefonos.clear();
    const numeros = contact.telefonos.map((t) => t.numero);

    if (numeros.length === 0) {
      this.telefonos.push(this.buildPhoneControl());
    } else {
      numeros.forEach((n) => this.telefonos.push(this.buildPhoneControl(n)));
    }
  }

  private buildTextControl(): FormControl<string> {
    return this.fb.control<string>('', {
      nonNullable: true,
      validators: [Validators.required, Validators.minLength(2), Validators.maxLength(100)],
    });
  }

  private buildPhoneControl(numero = ''): FormControl<string> {
    return this.fb.control<string>(numero, {
      nonNullable: true,
      validators: [
        Validators.required,
        Validators.minLength(7),
        Validators.maxLength(20),
        Validators.pattern(/^[0-9+()\-\s]+$/),
      ],
    });
  }
}

/** Valida que el grupo tenga al menos un telefono. */
const atLeastOnePhone: ValidatorFn = (control: AbstractControl) => {
  const phones = control.get('telefonos') as FormArray<FormControl<string>> | null;

  if (phones && phones.length === 0) {
    return { atLeastOnePhone: true };
  }

  return null;
};