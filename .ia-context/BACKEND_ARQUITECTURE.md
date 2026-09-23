# Arquitectura Backend — api-platform (api.metadatape.com)

API REST **de solo lectura** para consultar cruces de datos (RENIEC, AMDOCS, CLARO, RUC/SUNAT) almacenados en SQL Server (`BD_CRUCES`). Construida con **Laravel 12 + API Platform 4.3** (`api-platform/laravel`), PHP 8.2. No tiene frontend propio: la única UI es el Swagger UI que genera API Platform en `/api/docs`.

## 1. Stack y despliegue

| Pieza | Detalle |
|---|---|
| Framework | Laravel 12 (`laravel/framework` 12.x), estructura Laravel 11+ (`bootstrap/app.php`, sin `Kernel.php`) |
| API | `api-platform/laravel` 4.3 — recursos declarados con atributos PHP `#[ApiResource]` |
| PHP | 8.2 con extensiones `sqlsrv` y `pdo_sqlsrv` |
| BD | SQL Server, base `BD_CRUCES` (host y credenciales en `.env`, nunca en el repo) |
| Servidor | Nginx → PHP-FPM 8.2 (`/run/php/php8.2-fpm.sock`), root `/var/www/api-platform/public` |
| Dominio | `https://api.metadatape.com` (SSL Let's Encrypt/Certbot; HTTP redirige a HTTPS) |
| Local | `php artisan serve --host=0.0.0.0 --port=9010` |

## 2. Estructura de carpetas relevante

```
app/
  ApiResource/            # DTOs expuestos por API Platform (1 clase = 1 recurso/endpoint)
  State/                  # State Providers: aquí vive la consulta a SQL Server
  Http/Middleware/        # CheckApiToken (auth Bearer) y LogEndpointQuery (log)
  Http/Controllers/       # ApiTokenController (generación de tokens por HTTP)
  Console/Commands/       # GenerateApiToken (php artisan token:generate)
  Models/ApiToken.php     # Único modelo Eloquent de negocio (tabla api_tokens)
config/api-platform.php   # título, formatos de docs, Swagger UI habilitado
config/database.php       # conexiones sqlsrv_main y sqlsrv_reniec
config/logging.php        # canal endpoint_queries
config/services.php       # api_token_generator (secret + días de expiración)
routes/api.php            # rutas Laravel "clásicas": /api/health y /api/tokens/generate
bootstrap/app.php         # registro global de middlewares
```

## 3. Patrón principal: ApiResource + State Provider (sin Eloquent)

Cada endpoint de consulta es un par **Resource + Provider**:

- `app/ApiResource/XxxResource.php`: clase simple (no es modelo Eloquent) con
  - un identificador (`#[ApiProperty(identifier: true)]`, p.ej. `$dni`, `$document`, `$telefono`, `$ruc`)
  - `public array $attributes` con la fila (o filas) devuelta por SQL Server tal cual.
  - Atributo `#[ApiResource(shortName, operations: [new Get(uriTemplate, requirements, provider)], paginationEnabled: false)]`.
- `app/State/XxxProvider.php`: implementa `ApiPlatform\State\ProviderInterface::provide()`:
  1. Lee el parámetro de `$uriVariables` y lo valida (`preg_match`) → `BadRequestHttpException` (400) si no cumple.
  2. Consulta con **Query Builder** `DB::connection('...')->table('dbo.tabla')` (no Eloquent).
  3. Sin resultados → `NotFoundHttpException` (404).
  4. Devuelve el Resource con `attributes: (array) $row`.

El prefijo `/api` lo pone API Platform; el `uriTemplate` se declara sin él.

### Endpoints actuales

| Método / Ruta | Resource | Provider | Conexión | Tabla | Filtro / regla |
|---|---|---|---|---|---|
| `GET /api/reniec/{dni}` | `ReniecResource` | `ReniecProvider` | `sqlsrv_reniec` | `dbo.reniec` | `dni`, exactamente 8 dígitos |
| `GET /api/document/{document}` | `AmdocsByDocumentResource` | `AmdocsByDocumentProvider` | `sqlsrv_reniec` | `dbo.amdocs` | `document` alfanumérico; prioriza `subscriber_status_key = 'ACTIVO'` con menor `subscriber_key`, si no hay ACTIVO toma el menor `subscriber_key` |
| `GET /api/telefono/{telefono}` | `AmdocsByPhoneResource` | `AmdocsByPhoneProvider` | `sqlsrv_reniec` | `dbo.amdocs` | `primary_resource_value`, 7–15 dígitos |
| `GET /api/claro/document/{document}` | `ClaroByDocumentResource` | `ClaroByDocumentProvider` | `sqlsrv_main` | `dbo.claro` | `DOCUMENTO`; ver regla de consolidación abajo |
| `GET /api/claro/telefono/{telefono}` | `ClaroByPhoneResource` | `ClaroByPhoneProvider` | `sqlsrv_main` | `dbo.claro` | `TELEFONO`, 7–15 dígitos |
| `GET /api/ruc/{ruc}` | `RucResource` | `RucProvider` | `sqlsrv_main` | `dbo.ruc` | `RUC`, exactamente 11 dígitos |

**Regla Claro por documento** (`ClaroByDocumentProvider`): normaliza las claves de cada fila a minúsculas; si todas las filas tienen el mismo `titular` y `plan_claro`, responde consolidado (`titular`, `documento`, `plan_claro`, `telefonos[]`, `rows_count`); si no, responde `results[]` con todas las filas + `rows_count`. (El README menciona también `documento` en la comparación, pero el código solo compara `titular` y `plan_claro`; el documento ya es el filtro.)

### Rutas Laravel fuera de API Platform (`routes/api.php`)

- `GET /api/health` — health check público.
- `POST /api/tokens/generate` — `ApiTokenController::generate`, protegido por header `X-API-ADMIN-TOKEN` (compara con `hash_equals` contra `services.api_token_generator.secret`). Body: `name` (req.), `description`, `expires_in_days` (1–3650). Devuelve el token en claro una sola vez (201).

### Respuesta

JSON-LD de API Platform (`@context`, `@id`, `@type`, identificador y `attributes`). Los nombres de columnas dentro de `attributes` son los de SQL Server tal cual (mayúsculas/minúsculas según la tabla), excepto en Claro por documento, que se normalizan a minúsculas.

## 4. Autenticación: Bearer token propio (no Sanctum/Passport)

- Middleware `App\Http\Middleware\CheckApiToken`, registrado **global** en `bootstrap/app.php` (`$middleware->use([...])`), por lo que corre en **todas** las requests, no solo `/api/*`.
- Rutas públicas (whitelist por prefijo): `api`, `api/health`, `api/docs`, `api/contexts`, `api/errors`, `api/validation_errors`, `api/.well-known`, `api/tokens/generate`.
- Resto: exige `Authorization: Bearer <token>`:
  - Sin header / formato incorrecto → **401**.
  - Token no existe → **403** `Invalid token`; `expires_at` vencido → **403** `Token expired`.
  - Si es válido actualiza `last_used_at` y agrega `api_token_id` / `api_token_name` al request (los usa el log).
- Tokens: formato `token_` + 60 caracteres aleatorios, guardados **en texto plano** en `api_tokens.token`. Expiración por defecto 30 días (`API_TOKEN_DEFAULT_EXPIRES_IN_DAYS`).
- Dos formas de crear tokens: `php artisan token:generate --name= --description= --expires-in-days=` o `POST /api/tokens/generate`.

## 5. Log de consultas

- Middleware `App\Http\Middleware\LogEndpointQuery` (global, se ejecuta antes que `CheckApiToken`), solo actúa sobre `api` y `api/*`.
- Escribe en el canal `endpoint_queries` (`config/logging.php`, driver `daily`) → `storage/logs/endpoint-queries-YYYY-MM-DD.log`.
- Registra: método, path, URL, status, duración (ms), IP, user-agent, query params y body saneados (enmascara `authorization`, `token`, `password`, `secret`, `api_key`; trunca strings > 1000), `api_token_id`, `api_token_name`, excepción.
- Configurable con `LOG_ENDPOINT_QUERIES_LEVEL` y `LOG_ENDPOINT_QUERIES_DAYS`.

## 6. Conexiones a base de datos (`config/database.php`)

| Conexión | Variables `.env` | Uso |
|---|---|---|
| `sqlsrv_main` (default, `DB_CONNECTION`) | `SQLSRV_MAIN_*` | `api_tokens`, migraciones, `dbo.claro`, `dbo.ruc` |
| `sqlsrv_reniec` | `RENIEC_DB_*` | `dbo.reniec`, `dbo.amdocs` |

Hoy ambas apuntan a la misma BD `BD_CRUCES`; están separadas para poder mover RENIEC/AMDOCS a otro servidor sin tocar código. Ambas usan `encrypt=yes` y `trust_server_certificate=true`.

## 7. Documentación OpenAPI

- `config/api-platform.php`: título `Metadatape`, Swagger UI activo (ReDoc y Scalar desactivados), `docs_formats` prioriza `html` para que `/api/docs` abra la UI.
- Assets publicados con `php artisan vendor:publish --tag=api-platform-assets --force`.
- Todo endpoint nuevo declarado como `#[ApiResource]` aparece automáticamente en `/api/docs`; las rutas de `routes/api.php` **no** aparecen ahí (documentarlas en el README).

## 8. Cómo agregar un endpoint nuevo de consulta

1. Crear `app/ApiResource/NuevoResource.php` copiando el patrón (shortName único, `uriTemplate` sin `/api`, `requirements` con regex, `paginationEnabled: false`).
2. Crear `app/State/NuevoProvider.php`: validar parámetro (400), consultar con `DB::connection(...)->table('dbo.xxx')`, 404 si vacío.
3. Elegir la conexión correcta (`sqlsrv_main` o `sqlsrv_reniec`).
4. No hace falta tocar rutas ni middleware: queda protegido por token automáticamente.
5. `php artisan optimize:clear` y probar con `curl` (con y sin token).
6. Actualizar README (sección endpoints + ejemplo curl) y `CAMBIOS_IMPLEMENTADOS.md`.
