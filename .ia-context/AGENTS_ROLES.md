# Roles de Agentes IA — api-platform

Este proyecto (API REST de consultas sobre SQL Server `BD_CRUCES`, publicada en `https://api.metadatape.com`) se itera con asistencia de agentes IA (Claude Code u otros) directamente sobre el servidor de producción. Este documento define roles, alcance y protocolo para que cualquier agente trabaje de forma consistente.

Documentos hermanos (fuente de verdad técnica):

- `BACKEND_ARQUITECTURE.md` — stack, endpoints, patrón Resource + Provider, auth, logs.
- `BACKEND_STANDARDS.md` — reglas de código, seguridad y verificación.
- `DATABASE_STANDARDS.md` — tablas, conexiones y reglas sobre SQL Server.
- `SECURITY.md` — manejo de tokens y secretos, pendientes de seguridad.
- `CAMBIOS_IMPLEMENTADOS.md` — historial de cambios.

## Principio general

No hay equipos separados por capa: un desarrollador apoyado por agentes IA. Los roles son **modos de trabajo** para acotar el cambio y aplicar el estándar correcto. No hay frontend propio (la UI es el Swagger de API Platform en `/api/docs`).

## Rol: API Agent

- **Alcance**: `app/ApiResource`, `app/State`, `app/Http`, `app/Console`, `routes/api.php`, `config/*.php`, `bootstrap/app.php`.
- **Leer antes**: `BACKEND_ARQUITECTURE.md`, `BACKEND_STANDARDS.md`.
- **Responsabilidades**: nuevos endpoints de consulta, validaciones, reglas de negocio de respuesta (p.ej. consolidación Claro), middleware de auth/log, documentación OpenAPI.
- **No debe**: crear endpoints de escritura sobre tablas de datos; quitar o saltarse `CheckApiToken`; usar SQL crudo con input del usuario; exponer secretos en respuestas o logs.

## Rol: Database Agent

- **Alcance**: `database/migrations`, consultas de diagnóstico sobre `BD_CRUCES`, gestión de filas de `api_tokens`.
- **Leer antes**: `DATABASE_STANDARDS.md`.
- **No debe**: alterar/borrar `reniec`, `amdocs`, `claro`, `ruc`; correr `migrate:fresh`/`reset`/`db:wipe`; ejecutar `migrate` sin confirmación del usuario.

## Rol: Release / Housekeeping Agent

- **Alcance**: `README.md`, `.ia-context/*`, `.env.example`, limpieza de caché, verificación end-to-end.
- **Responsabilidad**: al cerrar cada bloque de trabajo, **agregar** una entrada en `CAMBIOS_IMPLEMENTADOS.md` (fecha, archivos, problema resuelto, cómo se verificó). No reescribir entradas anteriores.
- Mantener el README sincronizado con los endpoints reales.

## Protocolo de trabajo (todos los roles)

1. **Diagnosticar antes de tocar código**: leer `storage/logs/laravel.log` y `endpoint-queries-*.log`, reproducir con `curl`.
2. **Cambio mínimo** y siguiendo el patrón existente (Resource + Provider).
3. **Limpiar caché**: `php artisan optimize:clear` tras cambios de config, `.env` o `#[ApiResource]`.
4. **Verificar sin asumir**: `curl` con y sin token, parámetro inválido (400), inexistente (404), y `/api/docs`.
5. **Registrar** en `CAMBIOS_IMPLEMENTADOS.md` y actualizar README si cambian endpoints.
6. **Pedir confirmación** antes de acciones destructivas o irreversibles (migraciones en producción, borrar tokens, reiniciar servicios, force-push, commits).
7. **Nunca** escribir tokens, contraseñas ni credenciales en archivos del repo ni en este contexto.
8. Responder y documentar en **español**.
