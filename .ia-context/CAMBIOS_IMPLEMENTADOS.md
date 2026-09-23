# Cambios implementados — api-platform

Historial de cambios del proyecto. **Solo agregar entradas nuevas al final**, no reescribir las anteriores. Cada entrada: fecha, archivos, problema/objetivo, verificación.

## Historial previo (reconstruido desde git)

| Commit | Cambio |
|---|---|
| `fa276dc` | Proyecto inicial: Laravel 12 + API Platform, endpoint `GET /api/reniec/{dni}` sobre `dbo.reniec` (`sqlsrv_reniec`). |
| `595b635` | Autenticación Bearer token: tabla `api_tokens`, middleware `CheckApiToken`, comando `token:generate`. |
| `a54106f`…`17a0367` | Generación de tokens por HTTP (`POST /api/tokens/generate` con `X-API-ADMIN-TOKEN`) y expiración de tokens (`expires_at`, migración `2026_04_18_000001`). |
| `f187379`, `21d83ab` | Endpoints AMDOCS: `GET /api/document/{document}` y `GET /api/telefono/{telefono}` sobre `dbo.amdocs`. |
| `fcaec62`, `5369d11` | Middleware `LogEndpointQuery` + canal `endpoint_queries` (log diario en `storage/logs`). |
| `9f1cfb7` | AMDOCS por documento: prioriza registro `ACTIVO` con menor `subscriber_key`. |
| `d8449b3` | Endpoints Claro: `GET /api/claro/document/{document}` (con consolidación de teléfonos) y `GET /api/claro/telefono/{telefono}` sobre `dbo.claro`. |
| `99181bf` | Endpoint `GET /api/ruc/{ruc}` (11 dígitos) sobre `dbo.ruc`. |

---

# Sesión 2026-09-22

## 1 Actualización de `.ia-context` al proyecto real

- La carpeta `.ia-context` contenía documentación de otro proyecto (tienda Bagisto: paquetes `Webkul`, EAV de productos, Vue/Blade, HTMLPurifier, imágenes) que no aplica a esta API.
- Reescritos con el contexto real (Laravel 12 + API Platform 4.3 + SQL Server `BD_CRUCES`):
  - `BACKEND_ARQUITECTURE.md`, `BACKEND_STANDARDS.md`, `DATABASE_STANDARDS.md`, `AGENTS_ROLES.md`, `SECURITY.md`, `CAMBIOS_IMPLEMENTADOS.md`.
- Eliminados por no aplicar (no hay frontend propio ni son documentos de este proyecto): `FRONTEND_ARQUITECTURE.md`, `FRONTEND_STANDARDS.md`, `CODE_OF_CONDUCT.md`, `CONTRIBUTING.md`, `UPGRADE.md`.
- Sin cambios de código. Riesgos detectados durante la revisión anotados en `SECURITY.md` → "Pendientes / riesgos detectados".

## 2 Token filtrado en README y script de pruebas

- Token id 1 (`Test Token`, vigente hasta 2026-12-31, último uso 2026-04-18) estaba escrito en claro en `README.md` y `test-api.sh`, y también está en el historial de git.
- BD: `UPDATE api_tokens SET expires_at = <ahora> WHERE id = 1` (vía tinker, acotado por id y nombre). Verificado con `curl`: responde `403 Token expired`.
- `README.md`: token reemplazado por `YOUR_TOKEN` en todos los ejemplos.
- `test-api.sh`: ahora lee el token de la variable `API_TOKEN` (falla si no está definida) y `BASE_URL` es configurable por variable de entorno.

## 3 `.env` de producción en modo debug (pendiente)

- El `.env` del servidor tiene `APP_ENV=local` y `APP_DEBUG=true`. El cambio a `production`/`false` quedó pendiente de que el usuario lo ejecute manualmente (el agente no tuvo permiso para editar `.env`).
