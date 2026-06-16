#!/usr/bin/env sh
set -eu

MYSQL_HOST="${DB_HOST:-${MYSQLHOST:-db}}"
MYSQL_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
MYSQL_DATABASE="${DB_NAME:-${MYSQLDATABASE:-classroombookings}}"
MYSQL_USER="${DB_USER:-${MYSQLUSER:-classroombookings}}"
MYSQL_PASSWORD="${DB_PASSWORD:-${MYSQLPASSWORD:-}}"
INCLUDE_SEED="${INCLUDE_SEED:-0}"
EXTEND_SEED_UNTIL="${EXTEND_SEED_UNTIL:-}"
MIGRATION_MARKER_FILE="crbs-core/application/modules/install/resources/migration"

reset_seed_tables() {
    mysql --ssl=0 -h"${MYSQL_HOST}" -P"${MYSQL_PORT}" -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" "${MYSQL_DATABASE}" <<'SQL'
SET foreign_key_checks = 0;
DELETE FROM `bookings`;
DELETE FROM `bookings_repeat`;
DELETE FROM `dates`;
DELETE FROM `holidays`;
DELETE FROM `users`;
DELETE FROM `session_schedules`;
DELETE FROM `sessions`;
DELETE FROM `periods`;
DELETE FROM `roomvalues`;
DELETE FROM `roomfields`;
DELETE FROM `rooms`;
DELETE FROM `departments`;
DELETE FROM `room_groups`;
DELETE FROM `auth_roles_permissions`;
DELETE FROM `auth_roles`;
DELETE FROM `auth_permissions`;
DELETE FROM `weeks`;
DELETE FROM `schedules`;
DELETE FROM `settings`;
DELETE FROM `migrations`;
SET foreign_key_checks = 1;
SQL
}

echo "Waiting for MySQL at ${MYSQL_HOST}:${MYSQL_PORT}..."
until mysqladmin --ssl=0 ping -h"${MYSQL_HOST}" -P"${MYSQL_PORT}" -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" --silent; do
    sleep 2
done

TABLE_COUNT="$(mysql --ssl=0 -h"${MYSQL_HOST}" -P"${MYSQL_PORT}" -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" -N -B -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${MYSQL_DATABASE}';")"

if [ "${TABLE_COUNT}" -eq 0 ]; then
    echo "Importing database structure..."
    mysql --ssl=0 -h"${MYSQL_HOST}" -P"${MYSQL_PORT}" -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" "${MYSQL_DATABASE}" < crbs-core/application/modules/install/resources/structure.sql
else
    echo "Database ${MYSQL_DATABASE} already has ${TABLE_COUNT} tables. Structure import skipped."
fi

if [ -f "${MIGRATION_MARKER_FILE}" ]; then
    MIGRATION_VERSION="$(cat "${MIGRATION_MARKER_FILE}")"
    CURRENT_MIGRATION="$(mysql --ssl=0 -h"${MYSQL_HOST}" -P"${MYSQL_PORT}" -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" "${MYSQL_DATABASE}" -N -B -e "SELECT COALESCE(MAX(version), 0) FROM migrations;")"

    if [ "${CURRENT_MIGRATION}" -lt "${MIGRATION_VERSION}" ]; then
        echo "Marking database migration baseline as ${MIGRATION_VERSION}..."
        mysql --ssl=0 -h"${MYSQL_HOST}" -P"${MYSQL_PORT}" -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" "${MYSQL_DATABASE}" -e "DELETE FROM migrations; INSERT INTO migrations (version) VALUES (${MIGRATION_VERSION});"
    else
        echo "Database migration baseline already marked as ${CURRENT_MIGRATION}."
    fi
fi

if [ "${INCLUDE_SEED}" = "1" ]; then
    BOOKING_COUNT="$(mysql --ssl=0 -h"${MYSQL_HOST}" -P"${MYSQL_PORT}" -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" "${MYSQL_DATABASE}" -N -B -e "SELECT COUNT(*) FROM bookings;")"

    if [ "${BOOKING_COUNT}" -eq 0 ]; then
        echo "Generating and importing development seed..."
        php seed/generate_seed.php
        echo "Resetting seed tables before import..."
        reset_seed_tables
        mysql --ssl=0 -h"${MYSQL_HOST}" -P"${MYSQL_PORT}" -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" "${MYSQL_DATABASE}" < seed/seed.sql
    else
        echo "Database already has ${BOOKING_COUNT} bookings. Base seed import skipped."
    fi

    if [ -n "${EXTEND_SEED_UNTIL}" ]; then
        echo "Extending test bookings until ${EXTEND_SEED_UNTIL}..."
        php seed/extend_test_bookings.php "${EXTEND_SEED_UNTIL}"
    fi
else
    echo "Seed skipped. Run with INCLUDE_SEED=1 to import development data."
fi

INSTALL_READY_COUNT="$(mysql --ssl=0 -h"${MYSQL_HOST}" -P"${MYSQL_PORT}" -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" -N -B -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${MYSQL_DATABASE}' AND table_name IN ('users', 'settings', 'migrations');")"

if [ "${INSTALL_READY_COUNT}" -eq 3 ]; then
    if [ ! -f local/installed ] && [ ! -f local/.installed ]; then
        echo "Marking Docker database as installed..."
        date "+%Y-%m-%d %H:%M:%S" > local/installed
    else
        echo "Install marker already exists."
    fi
else
    echo "Install marker skipped. Required tables users/settings/migrations are not all present."
fi
