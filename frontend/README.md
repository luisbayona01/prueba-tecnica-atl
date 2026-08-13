# Frontend — Gestor de Contactos (Angular)

Aplicacion web construida con **Angular 21** (standalone components), **TypeScript**, **Reactive Forms**, **HttpClient** y **LocalStorage**.

---

## Requisitos

- Node.js >= 22.12 (recomendado: 22.x).
- npm >= 10.

Verificacion:

```bash
node --version
npm --version
```

## Instalacion

```bash
cd frontend
npm install
```

## Ejecucion (desarrollo)

```bash
npm start
# -> http://localhost:4200
```

## Compilacion de produccion

```bash
npm run build
# -> dist/frontend/browser
```

Sirve el contenido de `dist/frontend/browser` con cualquier servidor estatico.

## Estructura

```
src/
├── app/
│   ├── components/
│   │   ├── contact-list/     # lista de contactos en tarjetas
│   │   ├── contact-form/     # formulario crear/editar (Reactive Forms)
│   │   └── phone-list/       # FormArray de telefonos (reutilizable)
│   ├── interfaces/
│   │   └── contact.ts        # interfaces Contact, Telefono, RawContact
│   ├── services/
│   │   └── contact.service.ts# estado, LocalStorage y HttpClient
│   ├── app.ts                # componente raiz (orquesta lista + formulario)
│   ├── app.config.ts         # proveedores (HttpClient con fetch)
│   └── ...
├── assets/data/
│   └── contacts.json         # datos iniciales simulando una API
└── styles.scss               # variables y estilos globales
```

## Arquitectura

```
Component
    │
    ▼
ContactService            (unico punto de acceso a datos)
    │
    ├──► HttpClient        -> contacts.json (datos iniciales)
    └──► LocalStorage      -> persistencia (crear/editar/eliminar)
```

- Los **componentes nunca acceden** directamente a LocalStorage ni a HttpClient.
- Todo pasa por `ContactService`, lo que permite cambiar el origen de datos
  (`contacts.json` → `http://localhost/api/contactos`) **sin tocar los componentes**.
- El estado vive en **señales reactivas** (`signal`) de Angular: al mutar los
  contactos, la lista se actualiza automaticamente.
- Se usan **copias inmutables** para evitar mutaciones accidentales.

## Flujo de datos (LocalStorage)

**Primera carga**

1. Se revisa LocalStorage (`prueba_tecnica_contactos`).
2. Si hay contactos almacenados → se usan.
3. Si no → se descarga `assets/data/contacts.json` via HttpClient y se guarda en LocalStorage.

**Crear** → agrega al estado, persiste en LocalStorage y la lista se actualiza.

**Editar** → reemplaza el contacto (inmutable), persiste y actualiza.

**Cancelar** → no guarda nada y restaura la vista anterior.

## Formulario

`ContactFormComponent` usa **Reactive Forms**:

- `FormGroup` con `FormControl` para `nombre`, `apellido` y `email`.
- `FormArray` (`FormBuilder.array`) para los `telefonos`.
- Componente `PhoneListComponent` con `@Input` para el `FormArray`, botones `+ Agregar` y `Eliminar`.

Mensajes de validacion claros:

- "El nombre es obligatorio."
- "El email no es valido."
- "Debe ingresar al menos un telefono."

## Validaciones

| Campo | Reglas |
| ----- | ------ |
| nombre | requerido, minimo 2, maximo 100 |
| apellido | requerido, minimo 2, maximo 100 |
| email | requerido, formato valido, maximo 190 |
| telefono | requerido, 7-20 digitos, caracteres validos (digitos, `+`, `(`, `)`, `-`, espacio) |
| telefonos | al menos un telefono por contacto |

## Integracion futura con la API PHP

Para consumir el backend solo se modifica el origen dentro de `ContactService`
(cambiar `JSON_PATH` por `http://localhost/api/contactos` y ajustar la normalizacion
de datos). Los componentes no requieren cambios.

## Diseño

- Responsive: **desktop**, **tablet** y **movil** (CSS Grid + media queries).
- Sin librerias visuales: estilos con SCSS y variables CSS.
- Incluye header, boton "Nuevo contacto", tarjetas, botones Editar/Eliminar,
  formulario, mensajes de validacion, estado vacio, indicador de carga y avisos de exito/error.
