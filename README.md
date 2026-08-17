# Prueba Tecnica Full Stack — Contactos y Telefonos

Proyecto completo con un **backend de API REST en PHP nativo** (sin frameworks)
y un **frontend en Angular** que gestiona contactos y telefonos con validaciones,
persistencia en LocalStorage y una arquitectura preparada para consumir la API.

---

## 1. Descripcion general

La aplicacion permite **crear, listar, editar y eliminar contactos**, donde cada
contacto puede tener **uno o varios telefonos**.

- **Backend** (`backend/`): API REST con PHP nativo + MySQL + PDO.
  Persistencia real en base de datos con la relacion `contactos 1 a N telefonos`.
  Incluye **documentacion interactiva (Swagger UI)** generada con swagger-php.
- **Frontend** (`frontend/`): SPA en Angular 21 (standalone components).
  Carga los datos iniciales desde `contacts.json`, persiste en LocalStorage y
  queda lista para cambiar su origen de datos a la API PHP.

## 2. Arquitectura

```
prueba-tecnica/
├── backend/                API REST PHP nativo
│   ├── public/             front controller + .htaccess
│   ├── config/             Env (carga .env) y Database (PDO singleton)
│   ├── routes/             Router + tabla de rutas
│   ├── controllers/        capa HTTP
│   ├── services/           logica de negocio y transacciones
│   ├── validators/         reglas de validacion
│   ├── repositories/       acceso a datos (SQL + PDO)
│   ├── models/             entidades Contact y Phone (+ esquemas OpenAPI)
│   ├── docs/               OpenApiSpec.php (Info, Tags, esquemas de respuesta)
│   ├── utils/              Response, Input, ApiException
│   ├── database/           database.sql
│   ├── postman_collection.json
│   ├── composer.json       autoloading PSR-4 (+ swagger-php como dev)
│   └── .env / .env.example
│
├── frontend/               SPA Angular 21
│   ├── src/app/components/ contact-list, contact-form, phone-list
│   ├── src/app/services/   ContactService (estado + LocalStorage + HttpClient)
│   ├── src/app/interfaces/ Contact, Telefono
│   ├── src/assets/data/    contacts.json (datos iniciales)
│   └── ...
│
└── README.md
```

**Backend** (flujo de una peticion):

```
Ruta  ->  Controller  ->  Service  ->  Repository  ->  PDO  ->  MySQL
```

**Frontend** (flujo de datos):

```
Component  ->  ContactService  ->  HttpClient (json) / LocalStorage
```

## 3. Backend

API REST en **PHP nativo** (>= 8.1), **MySQL 8** y **PDO**:

- Consultas preparadas en todos los accesos a datos.
- PSR-4 con Composer (solo autoloading, sin frameworks).
- Arquitectura por capas (Rutas, Controllers, Services, Repositories, Validators).
- Respuestas JSON consistentes `{ success, message, data }` / `{ success, message, errors }`.
- Transacciones para garantizar la integridad de contacto + telefonos.
- Codigos HTTP: `200`, `201`, `400`, `404`, `409`, `422`, `500`.

Ver `backend/README.md` para la documentacion completa.

## 4. Frontend

SPA en **Angular 21** (standalone components) con:

- Reactive Forms (FormGroup, FormControl, FormArray, FormBuilder).
- HttpClient para cargar `contacts.json`.
- LocalStorage para persistencia.
- Estado reactivo con **señales** de Angular (la lista se actualiza sola).
- Componentes reutilizables con `@Input` / `@Output`.
- Diseño responsive sin librerias visuales.

Ver `frontend/README.md` para la documentacion completa.

## 5. Base de datos

`backend/database/database.sql` crea la base `prueba_contactos` con:

- `contactos`: `id` (PK), `nombre`, `apellido`, `email` (**UNIQUE**), `created_at`, `updated_at`.
- `telefonos`: `id` (PK), `contacto_id` (FK hacia `contactos.id`, `ON DELETE CASCADE`),
  `numero` (UNIQUE por contacto), `created_at`, `updated_at`.

Aplicacion de tablas:

```bash
mysql -u root -p < backend/database/database.sql
```

## 6. Instalacion

**Backend**

```bash
cd backend
composer install
cp .env.example .env      # editar credenciales
mysql -u root -p < database/database.sql
```

**Frontend**

```bash
cd frontend
npm install
```

## 7. Configuracion

`backend/.env`:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prueba_contactos
DB_USERNAME=root
DB_PASSWORD=tu_password
APP_ENV=development        # "production" oculta detalles de error
APP_CORS_ALLOWED_ORIGINS=* # u origenes separados por coma para CORS
```

> `.env` esta en `.gitignore`. Nunca se sube al repositorio.

## 8. Ejecucion

**Backend**

```bash
cd backend/public
php -S localhost:8080
```

**Frontend**

```bash
cd frontend
npm start
# -> http://localhost:4200
```

## 9. API

| Metodo | Ruta | Descripcion |
| ------ | ---- | ----------- |
| POST | `/api/contactos` | Crear contacto con telefonos |
| GET  | `/api/contactos` | Listar contactos |
| GET  | `/api/contactos/{id}` | Obtener un contacto |
| DELETE | `/api/contactos/{id}` | Eliminar contacto |
| POST | `/api/contactos/{id}/telefonos` | Agregar telefono |
| DELETE | `/api/contactos/{id}/telefonos/{telefonoId}` | Eliminar telefono |

**Crear contacto** (`POST /api/contactos`):

```json
{
  "nombre": "Luis",
  "apellido": "Bayona",
  "email": "luis@example.com",
  "telefonos": ["3001234567", "3109876543"]
}
```

> Los telefonos se guardan en la tabla `telefonos` (relacion 1-N), nunca separados por comas.

### Documentacion interactiva (Swagger UI)

La API incluye una interfaz **Swagger UI** que documenta y permite probar los
endpoints desde el navegador:

- Local: `http://localhost:8080/swagger.html`
- Docker: `http://localhost:8089/swagger.html`

El spec OpenAPI (`backend/public/openapi.json`) se genera a partir de atributos
PHP con swagger-php (`zircote/swagger-php`, dependencia de desarrollo) y se
regenera con:

```bash
cd backend && composer docs:generate
```

## 10. Postman

Importa `backend/postman_collection.json`. Incluye ejemplos de: crear, listar,
obtener, eliminar, datos invalidos (422), email duplicado (409), contacto
inexistente (404) y contacto con multiples telefonos.

## 11. LocalStorage

Clave `prueba_tecnica_contactos` en el frontend. Flujo:

1. **Primera carga**: si LocalStorage tiene contactos, se usan; si no, se cargan
   de `contacts.json` y se guardan.
2. **Crear / Editar / Eliminar**: actualizan estado, LocalStorage y lista.
3. **Cancelar edicion**: no guarda nada.

El acceso a LocalStorage solo ocurre dentro de `ContactService`.

## 12. JSON

`frontend/src/assets/data/contacts.json` contiene **5 contactos** y simula una API.
Se carga con **HttpClient** (nunca se importa directamente en un componente).

## 13. Formularios

`ContactFormComponent` usa **Reactive Forms**:

- `FormGroup` + `FormControl` para nombre, apellido y email.
- `FormArray` para telefonos (`PhoneListComponent` reutilizable).
- Botones `+ Agregar` y `Eliminar` por telefono.

## 14. Validaciones

| Campo | Backend | Frontend |
| ----- | ------- | -------- |
| nombre | obligatorio, min 2, max 100 | requerido, min 2, max 100 |
| apellido | obligatorio, min 2, max 100 | requerido, min 2, max 100 |
| email | obligatorio, formato valido, unico | requerido, formato valido |
| telefono | obligatorio, 7-20 digitos | requerido, 7-20 digitos, caracteres validos |
| telefonos | al menos uno | al menos uno |

## 15. Telefonos

- Relacion real `contactos 1 a N telefonos` en la base de datos.
- `FormArray` de telefonos en el formulario con alta/baja por fila.
- La API expone endpoints anidados `POST / DELETE /api/contactos/{id}/telefonos[/{telefonoId}]`.

## 16. Decisiones tecnicas

- **PHP nativo sin frameworks** por requisito de la prueba; la arquitectura por
  capas replica las buenas practicas de cualquier framework (routing propio).
- **PDO con `EMULATE_PREPARES=false`** para sentencias preparadas nativas y seguras.
- **Composer solo para autoloading PSR-4**, sin dependencias de terceros en
  produccion; `zircote/swagger-php` es la unica dependencia de **desarrollo**
  (genera el spec OpenAPI).
- **Angular 21 standalone** (version recomendada para el entorno Node 22 actual).
- **Señales reactivas** en lugar de NgRx: estado simple, tipado y sin dependencias.
- **`ContactService` como unico punto de datos**: permite migrar de `contacts.json`
  a la API PHP sin reescribir los componentes.
- **Copias inmutables** en el servicio para evitar mutaciones accidentales.
- .env fuera del repositorio; en produccion los errores no exponen detalles, SQL
  ni stack traces.

## 17. Docker (opcional)

Levanta el backend (PHP + Apache), la base de datos (MySQL), el frontend
(Angular compilado, servido por nginx) y phpMyAdmin con un solo comando:

```bash
docker compose up -d --build
```

| Servicio | Acceso |
| -------- | ------ |
| API | http://localhost:8089/api/... |
| Swagger UI | http://localhost:8089/swagger.html |
| Frontend | http://localhost:4400 |
| MySQL | localhost:3307 (usuario `contactos`, clave `contactos123`) |
| phpMyAdmin | http://localhost:8081 (usuario `contactos`, clave `contactos123`) |

Detalles:

- El esquema de `backend/database/database.sql` se aplica automaticamente la
  primera vez que arranca el contenedor de MySQL. Los datos persisten en el
  volumen `db_data`.
- phpMyAdmin se conecta al servicio `db` por la red interna (no necesita el
  puerto `3307`): usa `PMA_HOST=db` y las credenciales `contactos/contactos123`.
- La configuracion se inyecta por variables de entorno en `docker-compose.yml`.
  Esas variables tienen prioridad sobre el archivo `.env` (por eso
  `config/Env.php` respeta primero las variables de entorno reales del
  contenedor).
- `.env` y `vendor/` quedan fuera de la imagen (`.dockerignore`); la imagen usa
  `.env.example` como env por defecto y Composer regenera el autoload.
- Los puertos del host se pueden ajustar en `docker-compose.yml` si estan
  ocupados (por ejemplo, si `4400` lo usa un `ng serve` local): cambia el
  mapeo `"4400:80"`.
- Comandos utiles: `docker compose down` (conserva los datos),
  `docker compose down -v` (borra tambien los datos).

## 18. Demo en linea (desplegada)

La prueba tambien esta desplegada y en ejecucion. Si queres verla en vivo:

| Servicio | URL |
| -------- | --- |
| API (produccion) | https://apirestpruebatecnica.devsoftai.com/api/ |
| Frontend | https://frontpruebatecnica.devsoftai.com/ |
| phpMyAdmin (acceso a la base de datos) | https://prubdonline.devsoftai.com/ |

Para consumir la API desplegada desde Postman, cambia la variable `baseUrl` de
la coleccion `postman_collection.json` a:

```
https://apirestpruebatecnica.devsoftai.com
```

Los endpoints quedarian asi:

| Metodo | Ruta (produccion) |
| ------ | ----------------- |
| POST | `https://apirestpruebatecnica.devsoftai.com/api/contactos` |
| GET | `https://apirestpruebatecnica.devsoftai.com/api/contactos` |
| GET | `https://apirestpruebatecnica.devsoftai.com/api/contactos/{id}` |
| DELETE | `https://apirestpruebatecnica.devsoftai.com/api/contactos/{id}` |
| POST | `https://apirestpruebatecnica.devsoftai.com/api/contactos/{id}/telefonos` |
| DELETE | `https://apirestpruebatecnica.devsoftai.com/api/contactos/{id}/telefonos/{telefonoId}` |