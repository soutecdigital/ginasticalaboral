#!/bin/sh

# Limpa e reconstrói absolutamente tudo do zero
php artisan migrate:fresh --force

# Limpa os caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear

exec apache2-foreground
