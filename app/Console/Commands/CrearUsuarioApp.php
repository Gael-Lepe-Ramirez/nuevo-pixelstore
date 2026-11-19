<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * DCL: crea (o actualiza) la cuenta de MySQL con la que se conecta la página.
 *
 * La cuenta solo recibe SELECT, INSERT, UPDATE y DELETE sobre la base de la
 * tienda (mínimo privilegio): puede leer y modificar datos, pero no crear,
 * alterar ni borrar tablas, ni dar permisos a nadie. Las migraciones se
 * ejecutan con la cuenta de administración, nunca con esta.
 *
 * Se ejecuta con la cuenta de administración. La contraseña se lee de la
 * variable de entorno PX_APP_DB_PASSWORD para que no aparezca en los logs.
 */
class CrearUsuarioApp extends Command
{
    protected $signature = 'pixelstore:usuario-app {usuario : Nombre de la cuenta, p. ej. px_app_web}';

    protected $description = 'Crea la cuenta de MySQL de la aplicación con privilegios mínimos (DCL)';

    public function handle(): int
    {
        $usuario = $this->argument('usuario');
        $conexion = DB::connection();

        if (! in_array($conexion->getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->warn('La conexión no es MySQL/MariaDB; no se crea la cuenta.');

            return self::SUCCESS;
        }

        if ($usuario === $conexion->getConfig('username')) {
            $this->warn("La página usa la misma cuenta que las migraciones ({$usuario}); no se crea otra cuenta.");

            return self::SUCCESS;
        }

        if (! preg_match('/^[A-Za-z0-9_]{1,32}$/', $usuario)) {
            $this->error('El nombre de la cuenta solo puede tener letras, números y guion bajo.');

            return self::FAILURE;
        }

        $password = (string) getenv('PX_APP_DB_PASSWORD');
        if ($password === '') {
            $this->error('Falta la contraseña de la cuenta (variable PX_APP_DB_PASSWORD).');

            return self::FAILURE;
        }

        $base = $conexion->getDatabaseName();
        if (! preg_match('/^[A-Za-z0-9_$-]+$/', $base)) {
            $this->error("Nombre de base de datos no válido: {$base}");

            return self::FAILURE;
        }

        $pdo = $conexion->getPdo();
        $cuenta = $pdo->quote($usuario)."@'%'";
        $clave = $pdo->quote($password);

        $conexion->unprepared("CREATE USER IF NOT EXISTS {$cuenta} IDENTIFIED BY {$clave}");
        $conexion->unprepared("ALTER USER {$cuenta} IDENTIFIED BY {$clave}");
        $conexion->unprepared("REVOKE ALL PRIVILEGES, GRANT OPTION FROM {$cuenta}");
        $conexion->unprepared("GRANT SELECT, INSERT, UPDATE, DELETE ON `{$base}`.* TO {$cuenta}");

        $this->info("Cuenta {$usuario} lista: SELECT, INSERT, UPDATE y DELETE sobre {$base}.*");

        return self::SUCCESS;
    }
}
