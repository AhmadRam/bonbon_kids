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

# Ensure storage link exists
echo "Creating storage link..."
php artisan storage:link --force

# Setup database: migrate and seed if empty, or just migrate
echo "Running project setup..."
php artisan project:setup

exec "$@"