# Estándares de Base de Datos — api-platform

Motor: **SQL Server**, base `BD_CRUCES`, driver `sqlsrv` (`pdo_sqlsrv`). Es la base **productiva** que también usan otros procesos de GEA (cargas, cruces, n8n, reportes). Tratar toda operación como productiva: no hay staging.

## Tablas que usa la API

| Tabla | Conexión | Rol de la API | Columna(s) filtro |
|---|---|---|---|
| `dbo.reniec` | `sqlsrv_reniec` | solo lectura | `dni` |
| `dbo.amdocs` | `sqlsrv_reniec` | solo lectura | `document`, `primary_resource_value`, orden por `subscriber_key`, `subscriber_status_key` |
| `dbo.claro` | `sqlsrv_main` | solo lectura | `DOCUMENTO`, `TELEFONO` (columnas en MAYÚSCULAS) |
| `dbo.ruc` | `sqlsrv_main` | solo lectura | `RUC` |
| `api_tokens` | `sqlsrv_main` | lectura/escritura | `token` (unique) |

Las tablas de datos se cargan por procesos externos a este repo (no hay migraciones para ellas). **La API nunca debe crear, alterar ni borrar esas tablas.**

### `api_tokens`

Migraciones: `2024_04_17_create_api_tokens_table.php` y `2026_04_18_000001_add_expires_at_to_api_tokens_table.php`.

| Columna | Tipo | Nota |
|---|---|---|
| `id` | bigint identity | PK |
| `name` | nvarchar(255) | cliente/app |
| `token` | nvarchar(80) unique | texto plano, prefijo `token_` |
| `description` | nvarchar(max) null | |
| `last_used_at` | datetime null | lo actualiza `CheckApiToken` |
| `expires_at` | datetime null | vencido → 403 |
| `created_at` / `updated_at` | datetime | |

Tablas estándar de Laravel también migradas en `BD_CRUCES`: `users`, `cache`, `jobs`, `migrations` (no se usan activamente).

## Migraciones

- Ubicación: `database/migrations/`, formato `YYYY_MM_DD_HHMMSS_verbo_descripcion.php`.
- Solo para tablas propias de la app (hoy `api_tokens`). Nunca crear migraciones que toquen `reniec`, `amdocs`, `claro`, `ruc`.
- Guards de idempotencia (`Schema::hasColumn`, `Schema::hasTable`) cuando la migración pueda correr en una BD que ya tiene el cambio.
- `down()` simétrico.
- `php artisan migrate` corre contra producción: confirmar con el usuario antes de ejecutarlo y nunca usar `migrate:fresh`, `migrate:reset` ni `db:wipe`.

## Consultas

- Query Builder con bindings; nunca interpolar input del usuario en SQL.
- Mantener en cuenta la collation de SQL Server (normalmente case-insensitive): `'activo' = 'ACTIVO'` es verdadero; no depender de mayúsculas para filtrar.
- Los nombres de columna se devuelven tal cual están en la tabla (en `dbo.claro` son MAYÚSCULAS; `ClaroByDocumentProvider` las pasa a minúsculas).
- Tipos: documentos y teléfonos se comparan como string; si la columna es numérica en SQL Server, revisar conversiones implícitas que impidan usar el índice.

## Operaciones manuales (tinker / sqlcmd)

1. Leer antes de escribir (`SELECT` con el mismo `WHERE`).
2. Escrituras solo sobre `api_tokens` y acotadas por `id`/`token` (p.ej. revocar un token = `UPDATE ... SET expires_at = GETDATE() WHERE id = ?`, o `DELETE` por `id` con confirmación).
3. Confirmar con otro `SELECT`.
4. Registrar la operación en `CAMBIOS_IMPLEMENTADOS.md` (sin copiar el token).

Consultas útiles:

```sql
USE BD_CRUCES;
SELECT id, name, description, last_used_at, expires_at, created_at FROM api_tokens ORDER BY id DESC;
SELECT id, name FROM api_tokens WHERE expires_at < GETDATE();   -- vencidos
```
