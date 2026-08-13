import { Component, Input } from '@angular/core';
import { FormArray, FormControl, ReactiveFormsModule, Validators } from '@angular/forms';

/**
 * Componente reutilizable que gestiona el FormArray de telefonos.
 * Recibe el FormArray tipado como input y agrega/elimina filas.
 */
@Component({
  selector: 'app-phone-list',
  imports: [ReactiveFormsModule],
  templateUrl: './phone-list.html',
  styleUrl: './phone-list.scss',
})
export class PhoneListComponent {
  private static readonly MAX_PHONES = 5;

  @Input({ required: true }) telefonos!: FormArray<FormControl<string>>;

  readonly maxPhones = PhoneListComponent.MAX_PHONES;

  addPhone(): void {
    if (this.telefonos.length < PhoneListComponent.MAX_PHONES) {
      this.telefonos.push(this.buildPhoneControl());
    }
  }

  removePhone(index: number): void {
    this.telefonos.removeAt(index);
  }

  /** Mensaje de error para el telefono en la fila dada, o string vacio. */
  phoneError(index: number): string {
    const control = this.telefonos.at(index);

    if (control.touched && control.errors) {
      if (control.errors['required']) {
        return 'El telefono es obligatorio.';
      }
      if (control.errors['minlength']) {
        return 'El telefono debe tener al menos 7 digitos.';
      }
      if (control.errors['maxlength']) {
        return 'El telefono no debe exceder 20 digitos.';
      }
      if (control.errors['pattern']) {
        return 'El telefono contiene caracteres no validos.';
      }
    }

    return '';
  }

  private buildPhoneControl(): FormControl<string> {
    return new FormControl<string>('', {
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