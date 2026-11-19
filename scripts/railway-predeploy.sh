#!/bin/sh
# Comando de pre-despliegue para Railway («Pre-deploy command»):
#     sh scripts/railway-predeploy.sh
#
# La página se conecta con DB_USERNAME / DB_PASSWORD (cuenta px_app_web, solo
# DML). Este script usa en su lugar MIGRATION_DB_USERNAME / MIGRATION_DB_PASSWORD
# (la cuenta root del servicio MySQL) para:
#   1. crear o actualizar las tablas (migraciones, DDL),
#   2. cargar los datos de ejemplo la primera vez,
#   3. crear la cuenta px_app_web con sus permisos (DCL).
set -eu

APP_DB_USER="${DB_USERNAME:-}"
PX_APP_DB_PASSWORD="${DB_PASSWORD:-}"
export PX_APP_DB_PASSWORD

DB_USERNAME="${MIGRATION_DB_USERNAME:-$APP_DB_USER}"
DB_PASSWORD="${MIGRATION_DB_PASSWORD:-$PX_APP_DB_PASSWORD}"
export DB_USERNAME DB_PASSWORD

php artisan config:clear
php artisan migrate --force
php artisan db:seed --force

if [ -n "$APP_DB_USER" ]; then
    php artisan pixelstore:usuario-app "$APP_DB_USER"
fi
