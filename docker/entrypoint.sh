#!/bin/sh
set -eu

if [ ! -f .env ]; then
    {
        printf "APP_ENV=%s\n" "${APP_ENV:-production}"
        printf "APP_DEBUG=%s\n" "${APP_DEBUG:-false}"
        printf "APP_URL=%s\n" "${APP_URL:-http://localhost}"

        if [ -n "${APP_KEY:-}" ]; then
            printf "APP_KEY=%s\n" "$APP_KEY"
        fi

        if [ -n "${DB_CONNECTION:-}" ]; then
            printf "DB_CONNECTION=%s\n" "$DB_CONNECTION"
        fi

        if [ -n "${DB_HOST:-}" ]; then
            printf "DB_HOST=%s\n" "$DB_HOST"
        fi

        if [ -n "${DB_PORT:-}" ]; then
            printf "DB_PORT=%s\n" "$DB_PORT"
        fi

        if [ -n "${DB_DATABASE:-}" ]; then
            printf "DB_DATABASE=%s\n" "$DB_DATABASE"
        fi

        if [ -n "${DB_USERNAME:-}" ]; then
            printf "DB_USERNAME=%s\n" "$DB_USERNAME"
        fi

        if [ -n "${DB_PASSWORD:-}" ]; then
            printf "DB_PASSWORD=%s\n" "$DB_PASSWORD"
        fi

        if [ -n "${SESSION_DRIVER:-}" ]; then
            printf "SESSION_DRIVER=%s\n" "$SESSION_DRIVER"
        fi

        if [ -n "${CACHE_STORE:-}" ]; then
            printf "CACHE_STORE=%s\n" "$CACHE_STORE"
        fi

        if [ -n "${QUEUE_CONNECTION:-}" ]; then
            printf "QUEUE_CONNECTION=%s\n" "$QUEUE_CONNECTION"
        fi
    } > .env
fi

php artisan storage:link >/dev/null 2>&1 || true

exec php -S 0.0.0.0:"${PORT:-8080}" -t public
