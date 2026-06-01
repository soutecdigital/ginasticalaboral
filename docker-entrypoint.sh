#!/bin/sh

# Roda o fresh e os seeds para reconstruir o banco limpo que deixamos no Neon
php artisan migrate:fresh --seed --force

# Limpa os caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear

exec apache2-foreground
