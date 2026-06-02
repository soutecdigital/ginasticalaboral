#!/bin/sh

# Roda apenas as novas migrações e os seeds sem apagar os dados existentes
php artisan migrate --seed --force

# Limpa os caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear

exec apache2-foreground
