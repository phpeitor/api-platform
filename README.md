<p align="center">
  <a href="https://api-platform.com" target="_blank" rel="noopener noreferrer">
    <img src="https://api-platform.com/images/logo/logo.svg" width="260" alt="API Platform Logo">
  </a>
</p>

<p align="center">
  <strong>API RENIEC con Laravel + API Platform</strong>
</p>

<p align="center">
  API REST para consultar datos de RENIEC por DNI desde SQL Server.
</p>

## Descripcion

Este proyecto expone un endpoint REST construido con Laravel 12 y API Platform para consultar la tabla `BD_CRUCES.dbo.reniec` en SQL Server.

### Endpoint principal

- `GET /api/reniec/{dni}`
- Ejemplo: `http://127.0.0.1:9010/api/reniec/46798772`
- Documentacion: `http://127.0.0.1:9010/api/docs`

### Fuente de datos

- Servidor SQL Server: `161.132.4.164`
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

## Instalacion

1. Instalar dependencias:

```bash
composer install
```

2. Copiar y configurar variables de entorno:

```bash
cp .env.example .env
php artisan key:generate
```

3. Revisar y completar los datos de SQL Server en `.env`:

```dotenv
RENIEC_DB_HOST=161.132.4.164
RENIEC_DB_PORT=1433
RENIEC_DB_DATABASE=BD_CRUCES
RENIEC_DB_USERNAME=sa
RENIEC_DB_PASSWORD=tu_clave
RENIEC_DB_ENCRYPT=yes
RENIEC_DB_TRUST_SERVER_CERTIFICATE=true
```

4. Limpiar caché de Laravel:

```bash
php artisan optimize:clear
```

5. Publicar assets de API Platform si la carpeta `public/vendor/api-platform` no existe:

```bash
php artisan vendor:publish --tag=api-platform-assets --force
```

## Ejecucion local

Para pruebas locales puedes levantar el servidor de desarrollo en el puerto `9010`:

```bash
php artisan serve --host=0.0.0.0 --port=9010
```

Luego consulta:

```bash
curl http://127.0.0.1:9010/api/reniec/46798772
```

O desde la IP publica del VPS:

```bash
curl http://161.132.4.164:9010/api/reniec/46798772
```

## Respuesta esperada

El endpoint devuelve una respuesta JSON-LD de API Platform con la informacion encontrada en la base de datos.

Ejemplo de estructura:

```json
{
  "@context": "/api/contexts/Reniec",
  "@id": "/api/reniec/46798772",
  "@type": "Reniec",
  "dni": "46798772",
  "attributes": {
    "DNI": "46798772",
    "NOMBRES": "...",
    "PATERNO": "..."
  }
}
```

## Rutas utiles

- `GET /api/docs` - Documentacion interactiva de API Platform
- `GET /api/health` - Verificacion basica del servicio
- `GET /api/reniec/{dni}` - Consulta RENIEC por DNI

## Despliegue en dominio

Cuando vayas a publicar el proyecto en un dominio, lo recomendado es:

1. Apuntar Nginx o Apache al directorio `public/`.
2. Usar PHP-FPM en lugar de `php artisan serve`.
3. Configurar SSL con Let’s Encrypt.
4. Cambiar `APP_URL` al dominio final.
5. Verificar que el puerto `9010` no quede expuesto en produccion si no lo necesitas.

## Notas

- El parametro `dni` es obligatorio y va en la ruta, no en el body.
- La consulta usa la conexion `sqlsrv_reniec`.
- Si no hay coincidencia para el DNI, el servicio devuelve error de no encontrado.
- Si quieres agregar autenticacion por token, puede incorporarse sin cambiar el endpoint.

## Licencia

Proyecto interno para consumo de datos RENIEC.
