# Backend — API REST de Contactos (PHP nativo)

API REST para gestionar **contactos y telefonos** construida con **PHP nativo** (sin frameworks), **MySQL** y **PDO**, siguiendo una **arquitectura por capas** (Route → Controller → Service → Repository → PDO).

---

## Requisitos

- PHP >= 8.1 con extensiones `pdo_mysql`, `json`, `mbstring`.
- MySQL >= 8.0.
- Composer 2.x (solo para autoloading PSR-4).

Verificacion rapida:

```bash
php -v
composer --version
mysql --version
```

## Instalacion

```bash
cd backend
composer install
```

## Configuracion

1. Copia el archivo de entorno:

```bash
cp .env.example .env
```

2. Edita `.env` con tus credenciales:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prueba_contactos
DB_USERNAME=root
DB_PASSWORD=tu_password
APP_ENV=development
APP_CORS_ALLOWED_ORIGINS=*
```

> `.env` contiene credenciales y esta en `.gitignore`. Nunca lo subas al repositorio.

## Base de datos

El script `database/database.sql` crea la base, las tablas, las restricciones y datos semilla:

```bash
mysql -u root -p < database/database.sql
```

Esquema:

- `contactos` — `id` (PK), `nombre`, `apellido`, `email` (UNIQUE), `created_at`, `updated_at`.
- `telefonos` — `id` (PK), `contacto_id` (FK → contactos.id, `ON DELETE CASCADE`), `numero` (UNIQUE por contacto), `created_at`, `updated_at`.

Relacion: **contactos 1 ─── N telefonos**.

## Ejecucion

```bash
cd backend/public
php -S localhost:8080
```

La API queda disponible en `http://localhost:8080/api/...`.

Tambien puedes apuntar el DocumentRoot de Apache/Nginx a `backend/public`.

## Endpoints

| Metodo | Ruta | Descripcion | Codigos |
| ------ | ---- | ----------- | ------- |
| POST   | `/api/contactos` | Crear contacto con telefonos | 201, 400, 409, 422 |
| GET    | `/api/contactos` | Listar contactos | 200 |
| GET    | `/api/contactos/{id}` | Obtener un contacto | 200, 404 |
| DELETE | `/api/contactos/{id}` | Eliminar contacto | 200, 404 |
| POST   | `/api/contactos/{id}/telefonos` | Agregar telefono | 201, 400, 404, 422 |
| DELETE | `/api/contactos/{id}/telefonos/{telefonoId}` | Eliminar telefono | 200, 404 |

### Ejemplo: crear contacto

```bash
curl -X POST http://localhost:8080/api/contactos \
  -H "Content-Type: application/json" \
  -d '{"nombre":"Luis","apellido":"Bayona","email":"luis@example.com","telefonos":["3001234567","3109876543"]}'
```

Respuesta `201`:

```json
{
  "success": true,
  "message": "Contacto creado correctamente.",
  "data": { "id": 1, "nombre": "Luis", "apellido": "Bayona", "email": "luis@example.com", "telefonos": [] }
}
```

Los telefonos se guardan en la tabla `telefonos` (relacion 1-N), nunca separados por comas.

## Postman

Importa `postman_collection.json` en Postman. La coleccion incluye ejemplos para: crear, listar, obtener, eliminar, datos invalidos (422), email duplicado (409), contacto inexistente (404) y multiples telefonos. Ajusta la variable `baseUrl` si el puerto es otro.

## Arquitectura

```
Peticion HTTP
     │
     ▼
public/index.php   (front controller: env, CORS, dispatch, manejo de errores)
     │
     ▼
routes/api.php     (Router: tabla de rutas, sin logica de negocio)
     │
     ▼
controllers/       (HTTP: recibe peticion y construye respuesta)
     │
     ▼
services/          (logica de negocio, transacciones)
     │
     ▼
validators/        (reglas de validacion)
     │
     ▼
repositories/      (acceso a datos, SQL, PDO)
     │
     ▼
config/Database    (conexion PDO)
     │
     ▼
MySQL
```

Principios aplicados:

- **Route → Controller → Service → Repository → PDO**: separacion de responsabilidades.
- Los **Controllers** no contienen SQL.
- Los **Routes** no contienen logica de negocio.
- Los **Repositories** son la unica capa que toca la base de datos.
- Los **Services** contienen la logica de negocio y las transacciones.
- Los **Validators** se encargan de las validaciones.
- Autoloading **PSR-4** vía Composer.

## Seguridad

- **PDO** con consultas preparadas en todos los accesos a datos. Nunca se concatena input del usuario en SQL.
- Opciones PDO:

```php
PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
PDO::ATTR_EMULATE_PREPARES   => false
```

- Credenciales en `.env` (fuera del repositorio).
- En `APP_ENV=production` los errores internos no exponen detalles, stack traces ni SQL.
- Cabeceras CORS controladas por configuracion.
- Respuestas JSON con formato consistente:

```json
{ "success": true,  "message": "OK", "data": {} }
{ "success": false, "message": "Error", "errors": {} }
```

## Validaciones

| Campo | Reglas |
| ----- | ------ |
| nombre | obligatorio, sin espacios vacios, min 2, max 100 |
| apellido | obligatorio, sin espacios vacios, min 2, max 100 |
| email | obligatorio, formato valido, unico, max 190 |
| telefonos | al menos uno |
| numero de telefono | obligatorio, no vacio, 7–20 digitos, caracteres validos |

Codigos HTTP usados: `200`, `201`, `400`, `404`, `409`, `422`, `500`.
