# Seguridad — api-platform

La API expone datos personales (RENIEC, líneas y titulares de operadores, RUC). Toda decisión debe priorizar no filtrar datos ni credenciales.

## Modelo actual

- Acceso por **Bearer token** propio (`api_tokens`), validado en `CheckApiToken` para todo lo que no esté en la lista pública.
- Tokens con expiración (`expires_at`, 30 días por defecto) y registro de uso (`last_used_at`).
- Generación de tokens: comando artisan (acceso al servidor) o `POST /api/tokens/generate` con header `X-API-ADMIN-TOKEN` = `API_TOKEN_GENERATOR_SECRET`.
- Todas las requests a `/api/*` quedan auditadas en `storage/logs/endpoint-queries-*.log` (con token id/nombre, IP, parámetros).
- HTTPS obligatorio en `api.metadatape.com` (Certbot); HTTP redirige.

## Reglas

- Un token por cliente/aplicación, con `name`/`description` claros, para poder auditar y revocar.
- Revocar = poner `expires_at` en el pasado o borrar la fila (con confirmación).
- Los secretos viven solo en `.env`. No copiarlos en README, scripts, commits, issues ni `.ia-context`.
- No ampliar `$publicPaths` de `CheckApiToken` sin pedido explícito.
- No devolver trazas en producción: `APP_DEBUG=false` y `APP_ENV=production` en el `.env` del servidor.

## Pendientes / riesgos detectados (2026-09-22)

- ~~`README.md` y `test-api.sh` contienen un token real en claro.~~ **Resuelto 2026-09-22**: token id 1 ("Test Token") anulado (`expires_at` = fecha de anulación, responde 403) y reemplazado por `YOUR_TOKEN` / variable `API_TOKEN`. Sigue visible en el historial de git, por eso se anuló en vez de solo borrarlo del texto.
- El `.env` del servidor tiene `APP_ENV=local` y `APP_DEBUG=true` aunque se sirve en producción → riesgo de exponer stacktraces. Confirmar con el usuario antes de cambiarlo.
- Tokens guardados en texto plano. Mejora posible: guardar `hash('sha256', $token)` y comparar por hash (requiere migración y regenerar tokens).
- `CheckApiToken` está registrado como middleware **global**, por lo que también exige token en rutas web (`/`, `/up`). No afecta a la API, pero tenerlo presente si se añaden rutas web.
- Sin rate limiting por token. Mejora posible: `throttle` por `api_token_id`.
- `ApiTokenController` usa `env()` como fallback del secreto; con `config:cache` ese fallback no funciona (solo cuenta `config('services.api_token_generator.secret')`).
