#!/usr/bin/env bash

set -Eeuo pipefail

cd "$(dirname "$0")"

if [[ ! -f artisan || ! -f composer.json ]]; then
    echo "Run this script from the Bellyssimo Laravel project." >&2
    exit 1
fi

restore_application() {
    php artisan up >/dev/null 2>&1 || true
}

echo "Installing dependencies needed for verification..."
composer install --no-interaction --prefer-dist --optimize-autoloader
php artisan optimize:clear

echo "Running the automated test suite..."
php artisan test --compact

echo "Creating the pre-migration database backup..."
php artisan app:backup-database

trap restore_application EXIT

echo "Entering maintenance mode..."
php artisan down --retry=60

echo "Installing production dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "Applying database migrations..."
php artisan migrate --force

echo "Caching production configuration, events, routes, and views..."
php artisan optimize

echo "Reloading long-running Laravel services..."
php artisan reload

php artisan up
trap - EXIT

echo "Deployment finished. Verify /health and run: php artisan app:production-status"
