# Publicar PixelStore en Railway

Railway construye el proyecto directamente desde GitHub con **Railpack**
(detecta Laravel por el archivo `artisan`, instala PHP 8.4 con las extensiones
de `composer.json`, compila los assets con Vite y sirve `public/` con FrankenPHP).

## Cuentas de MySQL (DCL)

| Cuenta | Quién la usa | Permisos |
|---|---|---|
| `root` (la crea Railway) | Script de pre-despliegue: migraciones, datos de ejemplo y creación de `px_app_web` | Todos |
| `px_app_web` | La página, en cada petición | `SELECT, INSERT, UPDATE, DELETE` sobre la base `railway` |

La cuenta `px_app_web` la crea el comando `php artisan pixelstore:usuario-app`
(archivo `app/Console/Commands/CrearUsuarioApp.php`), que ejecuta:

```sql
CREATE USER IF NOT EXISTS 'px_app_web'@'%' IDENTIFIED BY '…';
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'px_app_web'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `railway`.* TO 'px_app_web'@'%';
```

Así, aunque alguien lograra inyectar SQL a través de la página, no podría
borrar ni alterar tablas ni dar permisos.

## Pasos

1. **Proyecto**: en Railway, *New Project → Deploy from GitHub repo →*
   `nuevo-pixelstore`. El primer despliegue fallará porque aún no hay
   variables; es normal.
2. **Base de datos**: en el mismo proyecto, *+ Create → Database → MySQL*.
   El servicio debe llamarse `MySQL` (así lo usan las referencias de abajo).
3. **Dominio**: en el servicio de la página, *Settings → Networking →
   Generate Domain* (puerto sugerido, normalmente 8080).
4. **Pre-deploy command**: *Settings → Deploy → Pre-deploy Command*:
   `sh scripts/railway-predeploy.sh`
5. **Variables**: en el servicio de la página, *Variables → Raw Editor*,
   pegar lo siguiente y reemplazar lo que está entre `< >`:

   ```env
   APP_NAME=PixelStore
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=<resultado de: php artisan key:generate --show>
   APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}
   LOG_CHANNEL=stderr

   DB_CONNECTION=mysql
   DB_HOST=${{MySQL.MYSQLHOST}}
   DB_PORT=${{MySQL.MYSQLPORT}}
   DB_DATABASE=${{MySQL.MYSQLDATABASE}}
   DB_USERNAME=px_app_web
   DB_PASSWORD=<contraseña inventada para px_app_web>
   MIGRATION_DB_USERNAME=${{MySQL.MYSQLUSER}}
   MIGRATION_DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

   ADMIN_PASSWORD=<contraseña inventada para admin@pixelstore.com>
   MAIL_MAILER=log
   RAILPACK_SKIP_MIGRATIONS=true

   GITHUB_CLIENT_ID=<Client ID de la OAuth App de Railway>
   GITHUB_CLIENT_SECRET=<Client secret de la OAuth App de Railway>
   GITHUB_REDIRECT_URI=https://${{RAILWAY_PUBLIC_DOMAIN}}/auth/github/callback
   ```

6. **Desplegar**: *Deploy* (o *Redeploy*). En los logs del pre-deploy deben
   aparecer las migraciones, el sembrado y
   `Cuenta px_app_web lista: SELECT, INSERT, UPDATE y DELETE sobre railway.*`.

## GitHub OAuth

En GitHub → *Settings → Developer settings → OAuth Apps → New OAuth App*:

- Homepage URL: `https://<dominio>.up.railway.app`
- Authorization callback URL: `https://<dominio>.up.railway.app/auth/github/callback`

Se recomienda una OAuth App separada de la local para que ambas sigan
funcionando.

## Limitaciones conocidas

- **Correo**: el plan de prueba/Hobby de Railway bloquea SMTP, por eso
  `MAIL_MAILER=log`. La compra funciona igual y el recibo se puede ver y
  descargar desde la página.
- **Imágenes subidas**: el disco del contenedor se reinicia en cada
  despliegue; las fotos de los productos de ejemplo vienen en el repositorio,
  pero las que se suban desde el panel se pierden al volver a desplegar (para
  conservarlas habría que montar un *Volume* en `storage/app/public`).
- **Datos de ejemplo**: el seeder solo corre si la base está vacía, así que
  los pedidos y productos creados en línea se conservan entre despliegues.
