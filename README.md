<p align="center">
  <a href="https://api-platform.com" target="_blank" rel="noopener noreferrer">
    <img src="https://api-platform.com/images/super-webby.svg" width="260" alt="API Platform Logo">
  </a>
</p>

<p align="center">
  <strong>API RENIEC + API Platform</strong>
</p>

<p align="center">
  API REST para consultar datos de RENIEC, AMDOCS y CLARO desde SQL Server.
</p>

## Descripción

Este proyecto expone endpoints REST construidos con Laravel 12 y API Platform para consultar tablas de `BD_CRUCES` en SQL Server (`dbo.reniec`, `dbo.amdocs`, `dbo.claro`).

**Todos los endpoints requieren autenticación por Bearer Token.**

Los tokens tienen expiración real. Por defecto duran 30 días, salvo que se indique otro valor al generarlos.

### Endpoint principal

- `GET /api/reniec/{dni}`
- Ejemplo: `http://127.0.0.1:9010/api/reniec/12345678`
- Documentación: `http://127.0.0.1:9010/api/docs`
- **Requiere:** Header `Authorization: Bearer YOUR_TOKEN`

### Endpoints secundarios

- `GET /api/document/{document}`
- Ejemplo: `http://127.0.0.1:9010/api/document/12345678`

- `GET /api/telefono/{telefono}`
- Ejemplo: `http://127.0.0.1:9010/api/telefono/942890820`

### Endpoints Claro

- `GET /api/claro/document/{document}`
- Ejemplo: `http://127.0.0.1:9010/api/claro/document/46798772`

- `GET /api/claro/telefono/{telefono}`
- Ejemplo: `http://127.0.0.1:9010/api/claro/telefono/944271091`

Regla para `GET /api/claro/document/{document}`:

- Si todos los registros encontrados tienen el mismo `titular`, `documento` y `plan_claro`, la respuesta consolida y devuelve una lista de `telefonos`.
- Si no coinciden esos campos entre filas, devuelve todas las filas en `results`.

### Endpoints RUC

- `GET /api/ruc/{ruc}`
- Ejemplo: `http://127.0.0.1:9010/api/ruc/10100214283`
- RUC debe tener exactamente 11 dígitos.

### Endpoints públicos (sin autenticación)

- `GET /api/health` - Verificación de estado del servicio (sin token requerido)
- `POST /api/tokens/generate` - Genera un token nuevo usando el header `X-API-ADMIN-TOKEN`

### Fuente de datos

- Servidor SQL Server: `127.0.0.1`
- Base de datos: `BD_CRUCES`
- Tabla: `dbo.reniec`
- Filtro principal: `dni`

## Requisitos

- PHP 8.2 o superior
- Composer
- Extensiones PHP para SQL Server:
  - `sqlsrv`
  - `pdo_sqlsrv`
- Acceso al puerto `1433` del servidor SQL Server

## Instalación

### 1. Instalar dependencias:

```bash
composer install
```

### 2. Copiar y configurar variables de entorno:

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Revisar y completar los datos de SQL Server en `.env`:

```dotenv
# Conexión principal (para tablas de la app)
DB_CONNECTION=sqlsrv_main
SQLSRV_MAIN_HOST=127.0.0.1
SQLSRV_MAIN_PORT=1433
SQLSRV_MAIN_DATABASE=BD_CRUCES
SQLSRV_MAIN_USERNAME=sa
SQLSRV_MAIN_PASSWORD=tu_clave
SQLSRV_MAIN_ENCRYPT=yes
SQLSRV_MAIN_TRUST_SERVER_CERTIFICATE=true

# Conexión a RENIEC (puede ser la misma que arriba)
RENIEC_DB_HOST=127.0.0.1
RENIEC_DB_PORT=1433
RENIEC_DB_DATABASE=BD_CRUCES
RENIEC_DB_USERNAME=sa
RENIEC_DB_PASSWORD=tu_clave
RENIEC_DB_ENCRYPT=yes
RENIEC_DB_TRUST_SERVER_CERTIFICATE=true
```

### 4. Limpiar caché de Laravel:

```bash
php artisan optimize:clear
```

### 5. Ejecutar migraciones para crear tabla de tokens:

```bash
php artisan migrate
```

### 6. Publicar assets de API Platform:

```bash
php artisan vendor:publish --tag=api-platform-assets --force
```

## Generación de Tokens

Usa el comando artisan para generar nuevos tokens:

```bash
php artisan token:generate --name="Mi Aplicación" --description="Token para prod"
```

Ejemplo de salida:

```
✅ Token creado exitosamente!

ID: 1
Nombre: Mi Aplicación
Descripción: Token para prod

Token:
token_qs0CnOvCCPLioThNWDEIdxfYp1nOx9emM9s1NLRU8u0IMvy5jUuLXFg2BTxK

⚠️  Copia este token en un lugar seguro. No podras verlo nuevamente.
```

## Ejecución local

Para pruebas locales puedes levantar el servidor de desarrollo en el puerto `9010`:

```bash
php artisan serve --host=0.0.0.0 --port=9010
```

### Consultar con token válido:

```bash
curl -H "Authorization: Bearer token_qs0CnOvCCPLioThNWDEIdxfYp1nOx9emM9s1NLRU8u0IMvy5jUuLXFg2BTxK" \
     http://127.0.0.1:9010/api/reniec/12345678
```

### Consultar Claro por documento (consolidado de teléfonos):

```bash
curl -H "Authorization: Bearer token_qs0CnOvCCPLioThNWDEIdxfYp1nOx9emM9s1NLRU8u0IMvy5jUuLXFg2BTxK" \
  http://127.0.0.1:9010/api/claro/document/46798772
```

### Consultar Claro por teléfono:

```bash
curl -H "Authorization: Bearer token_qs0CnOvCCPLioThNWDEIdxfYp1nOx9emM9s1NLRU8u0IMvy5jUuLXFg2BTxK" \
  http://127.0.0.1:9010/api/claro/telefono/944271091
```

### Consultar RUC (11 dígitos):

```bash
curl -H "Authorization: Bearer token_qs0CnOvCCPLioThNWDEIdxfYp1nOx9emM9s1NLRU8u0IMvy5jUuLXFg2BTxK" \
     http://127.0.0.1:9010/api/ruc/10100214283
```

## Respuesta esperada

El endpoint devuelve una respuesta JSON-LD de API Platform con la información encontrada en la base de datos.

Ejemplo de estructura:

```json
{
  "@context": "/api/contexts/Reniec",
  "@id": "/api/reniec/12345678",
  "@type": "Reniec",
  "dni": "12345678",
  "attributes": {
    "DNI": "12345678",
    "NOMBRES": "ALEJANDRO MANUEL",
    "PATERNO": "MONTALVAN",
    "MATERNO": "BRAVO",
    "SEXO": "M"
  }
}
```

## Errores de autenticación

### 401 Unauthorized
Falta header Authorization o token no válido:

```json
{
  "error": "Missing or invalid Authorization header",
  "message": "Please provide a valid Bearer token in the Authorization header",
  "example": "Authorization: Bearer YOUR_TOKEN_HERE"
}
```

### 403 Forbidden
Token inválido o expirado:

```json
{
  "error": "Unauthorized",
  "message": "Invalid or expired token"
}
```

## Generación de tokens por API

Puedes generar tokens desde HTTP con este request:

```bash
curl -X POST http://127.0.0.1:9010/api/tokens/generate \
  -H "X-API-ADMIN-TOKEN: TU_SECRETO_ADMIN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Mi App","description":"Token de producción","expires_in_days":30}'
```

El secreto se configura en `API_TOKEN_GENERATOR_SECRET`.

## Rutas útiles

- `GET /api/docs` - Documentación interactiva de API Platform
- `GET /api/health` - Verificación básica del servicio (sin token)
- `GET /api/reniec/{dni}` - Consulta RENIEC por DNI (requiere token)
- `GET /api/document/{document}` - Consulta AMDOCS por documento (requiere token)
- `GET /api/telefono/{telefono}` - Consulta AMDOCS por teléfono (requiere token)
- `GET /api/claro/document/{document}` - Consulta CLARO por documento (requiere token)
- `GET /api/claro/telefono/{telefono}` - Consulta CLARO por teléfono (requiere token)
- `GET /api/ruc/{ruc}` - Consulta RUC (11 dígitos) (requiere token)

## Log de consultas de endpoints

Todas las consultas a endpoints bajo `/api/*` se registran automáticamente en:

- `storage/logs/endpoint-queries.log`

Cada registro incluye método, ruta, URL completa, código HTTP, tiempo de respuesta, IP, User-Agent, parámetros y body saneado.

Para monitorear en tiempo real:

```bash
tail -f storage/logs/endpoint-queries.log
```

Variables opcionales en `.env`:

```dotenv
LOG_ENDPOINT_QUERIES_LEVEL=info
LOG_ENDPOINT_QUERIES_DAYS=30
```

## Gestión de tokens en BD

Los tokens se almacenan en la tabla `api_tokens` con los campos:

- `id` - ID único del token
- `name` - Nombre/descripción del cliente
- `token` - Token único (nunca se repite)
- `description` - Descripción adicional del uso
- `last_used_at` - Último acceso (se actualiza automáticamente)
- `created_at` - Fecha de creación
- `updated_at` - Última actualización

Para ver los tokens registrados:

```sql
USE BD_CRUCES;
SELECT id, name, description, last_used_at, created_at FROM api_tokens;
```

## Despliegue en dominio

Cuando vayas a publicar el proyecto en un dominio, lo recomendado es:

1. Apuntar Nginx o Apache al directorio `public/`
2. Usar PHP-FPM en lugar de `php artisan serve`
3. Configurar SSL con Let's Encrypt
4. Cambiar `APP_URL` al dominio final
5. Verificar que el puerto `1433` (SQL Server) esté accesible desde el servidor
6. Cambiar las contraseñas de los tokens regularmente
7. Implementar rate limiting por token (opcional)

## Notas

- El parámetro `dni` es obligatorio y va en la ruta, no en el body
- La consulta usa la conexión `sqlsrv_reniec`
- Si no hay coincidencia para el DNI, el servicio devuelve error 404
- Los tokens se almacenan en texto plano en BD; usa SSL/TLS en producción
- El token se registra cada vez que se accede (`last_used_at` se actualiza)
- Genera un nuevo token por cada cliente/aplicación para mejor auditoría

## Licencia

Proyecto interno para consumo de datos RENIEC.
