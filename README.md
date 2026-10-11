# PixelStore

Plataforma de comercio electrónico desarrollada en Laravel para la gestión y venta de hardware y equipos de alto rendimiento. Cuenta con un catálogo dinámico, carrito de compras y sistema de autenticación con roles de usuario (Administrador y Cliente).

## Requisitos Previos
* PHP (v8.2 o superior)
* Composer
* Servidor de base de datos (MySQL/MariaDB o SQLite)

## Guía de Instalación

**Paso 1: Clonar el repositorio**
Elige el método que prefieras:
* SSH: git@github.com:Gael-Lepe-Ramirez/pixelstore.git
* HTTPS: https://github.com/Gael-Lepe-Ramirez/pixelstore.git

    git clone <ENLACE_ELEGIDO>
    cd pixelstore ,
    composer install ,
    npm install ,
    npm run build ,
    cp .env.example .env ,
    php artisan key:generate ,

**Paso 2: Configurar la base de datos**
1. Abre tu gestor de base de datos (phpMyAdmin, TablePlus, DBeaver, etc.) y crea una base de datos vacía llamada "pixelstore".
2. Abre el archivo .env en la raíz del proyecto y configura tus credenciales locales:
   - DB_DATABASE=pixelstore
   - DB_USERNAME=root (o tu usuario local)
   - DB_PASSWORD= (o tu contraseña local)

**Paso 3: Preparar el entorno y servidor**
Ejecuta el siguiente bloque para generar los accesos a las imágenes, poblar los productos y encender el servidor:

    php artisan storage:link
    php artisan migrate:fresh --seed

**Paso 4: Acceso a la tienda**
* Si usas Laravel Herd (o dominio personalizado): Accede a http://pixelstore.test/products
* Si usas terminal: Ejecuta `php artisan serve` y accede a http://localhost:8000

---

## Credenciales de Prueba

Al correr las migraciones, el sistema genera automáticamente una cuenta de administrador para gestionar el catálogo.

**Rol Administrador (Panel de control):**
* Correo: admin@pixelstore.com
* Contraseña: password

**Rol Cliente:**
Para probar la vista de usuario normal y el carrito, por favor ve a la pestaña de "Registrarse" en la página principal y crea una cuenta nueva con cualquier correo de prueba.