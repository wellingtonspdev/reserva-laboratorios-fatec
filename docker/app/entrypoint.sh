#!/usr/bin/env sh
set -eu

mkdir -p local/logs local/cache local/sessions uploads

APP_PORT="${PORT:-8000}"
APP_PUBLIC_URL="${APP_BASE_URL:-}"
if [ -z "${APP_PUBLIC_URL}" ] && [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ]; then
    APP_PUBLIC_URL="https://${RAILWAY_PUBLIC_DOMAIN}/"
fi
APP_PUBLIC_URL="${APP_PUBLIC_URL:-http://127.0.0.1:${APP_PORT}/}"

APP_DB_HOST="${DB_HOST:-${MYSQLHOST:-db}}"
APP_DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
APP_DB_NAME="${DB_NAME:-${MYSQLDATABASE:-classroombookings}}"
APP_DB_USER="${DB_USER:-${MYSQLUSER:-classroombookings}}"
APP_DB_PASSWORD="${DB_PASSWORD:-${MYSQLPASSWORD:-}}"

cat > local/config.php <<PHP
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'config' => array(
        'base_url' => '${APP_PUBLIC_URL}',
        'log_threshold' => 1,
        'index_page' => '',
        'uri_protocol' => 'REQUEST_URI',
    ),

    'database' => array(
        'hostname' => '${APP_DB_HOST}',
        'port' => '${APP_DB_PORT}',
        'username' => '${APP_DB_USER}',
        'password' => '${APP_DB_PASSWORD}',
        'database' => '${APP_DB_NAME}',
        'dbdriver' => 'mysqli',
    ),
);
PHP

if [ "${APP_ENV:-development}" = "development" ]; then
    printf '%s' development > .env
else
    printf '%s' production > .env
fi

if [ "${AUTO_BOOTSTRAP_DB:-1}" = "1" ]; then
    classroombookings-bootstrap-db
fi

exec "$@"
