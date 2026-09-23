# Estándares Backend — api-platform

Reglas prácticas para trabajar en este repo. Ver `BACKEND_ARQUITECTURE.md` para el contexto de cada patrón.

## Estilo de código

- PHP 8.2, PSR-12. Laravel Pint está instalado: `./vendor/bin/pint --dirty` antes de cerrar un cambio.
- Constructor property promotion y argumentos con nombre, como en los Resources/Providers existentes.
- Comentarios solo para explicar el "por qué" (p.ej. la regla de prioridad `ACTIVO` en AMDOCS), en español.
- Mensajes de error al cliente en español y sin tildes problemáticas en encoding (se sigue el estilo actual: "No se encontro informacion para ...").

## Endpoints de consulta (API Platform)

- **Un Resource + un Provider por endpoint.** No crear controllers para consultas que encajan en este patrón; los controllers (`routes/api.php`) quedan para acciones que no son recursos (health, generación de tokens).
- `shortName` único y descriptivo (`ClaroByPhone`, `AmdocsByDocument`). El `uriTemplate` va sin `/api`.
- Validar el parámetro **dos veces**: en `requirements` del `Get` (regex de ruta) y en el Provider con `preg_match` (mensaje claro → 400). Mantener ambas regex iguales (hoy `RucResource` usa `[0-9]+` y el Provider exige 11 dígitos; un RUC de otra longitud llega al Provider y responde 400, lo cual es correcto).
- `BadRequestHttpException` para parámetros inválidos, `NotFoundHttpException` cuando no hay filas. No devolver `200` con resultado vacío.
- `paginationEnabled: false` salvo que el endpoint realmente devuelva colecciones grandes.
- `trim()` del parámetro antes de validar.

## Acceso a datos

- Query Builder con **bindings** (`->where('col', $valor)`), nunca concatenar SQL ni `DB::raw` con input del usuario.
- Siempre indicar la conexión explícita: `DB::connection('sqlsrv_main')` o `DB::connection('sqlsrv_reniec')`.
- Tablas con esquema: `dbo.nombre`.
- Solo lecturas (`first()`, `get()`) sobre tablas de datos (`reniec`, `amdocs`, `claro`, `ruc`). La API **no escribe** en esas tablas; la única tabla que la app modifica es `api_tokens`.
- Seleccionar columnas explícitas si una tabla crece mucho en columnas o tiene datos sensibles que no deben exponerse (hoy se devuelve la fila completa).
- Pensar en índices: todo campo usado como filtro (`dni`, `document`, `primary_resource_value`, `DOCUMENTO`, `TELEFONO`, `RUC`) debe estar indexado en SQL Server; coordinar con el DBA/Jose antes de crear índices.

## Autenticación y middleware

- No quitar `CheckApiToken` ni agregar rutas a la lista pública sin una razón explícita del usuario.
- Si se agrega una ruta pública, añadirla al array `$publicPaths` de `CheckApiToken` y documentarla en el README.
- Nunca loguear tokens ni secretos; si se añaden campos sensibles nuevos al request, agregarlos a `sanitizeData()` de `LogEndpointQuery`.
- Leer configuración con `config('services.api_token_generator...')`, no con `env()` fuera de `config/*.php` (con `config:cache` `env()` devuelve `null`). `ApiTokenController` hoy usa `env()` como fallback; no replicar ese patrón.

## Configuración y secretos

- Credenciales de SQL Server, `APP_KEY` y `API_TOKEN_GENERATOR_SECRET` solo en `.env` (no versionado). Cualquier variable nueva: agregarla también a `.env.example` sin valor real.
- No escribir tokens reales, contraseñas ni IPs internas en README, scripts (`test-api.sh`) ni en `.ia-context`.

## Manejo de errores / diagnóstico

- Ante un 500: `tail -100 storage/logs/laravel.log`.
- Para ver qué consultó quién: `tail -f storage/logs/endpoint-queries-$(date +%F).log`.
- Errores de conexión a SQL Server: revisar extensión `sqlsrv`/`pdo_sqlsrv` (`php -m | grep sqlsrv`), puerto 1433 y variables `SQLSRV_MAIN_*` / `RENIEC_DB_*`.

## Caché — checklist después de cambios

1. `php artisan optimize:clear` tras tocar `config/*.php`, `.env`, `bootstrap/app.php` o agregar/editar un `#[ApiResource]` (API Platform cachea metadata de recursos).
2. PHP-FPM recoge cambios de `.php` por OPcache; si un cambio no se refleja en producción: `sudo systemctl reload php8.2-fpm`.

## Verificación mínima antes de dar algo por terminado

```bash
# sin token -> 401
curl -s -o /dev/null -w "%{http_code}\n" https://api.metadatape.com/api/ruc/10100214283
# con token -> 200 / 404
curl -s -H "Authorization: Bearer $TOKEN" https://api.metadatape.com/api/ruc/10100214283
# parámetro inválido -> 400
curl -s -H "Authorization: Bearer $TOKEN" https://api.metadatape.com/api/ruc/123
# docs siguen accesibles
curl -s -o /dev/null -w "%{http_code}\n" https://api.metadatape.com/api/docs
```

`$TOKEN` se toma de una variable de entorno de la sesión, nunca se pega en archivos del repo.
