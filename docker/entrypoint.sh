#!/bin/bash

# Ensure .env exists
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
    php artisan key:generate
fi

# Wait for MySQL to be ready
echo "Waiting for database..."
until (echo > /dev/tcp/mysql/3306) >/dev/null 2>&1; do
    sleep 1
done

# Run migrations if database is empty or as requested
# For production, you might want to run this manually, but for "zero-dependency" first boot:
# php artisan migrate --force

exec "$@"