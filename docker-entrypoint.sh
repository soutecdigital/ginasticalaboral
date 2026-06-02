#!/bin/sh

# Força o reset total do banco estrutural para limpar o Neon
php artisan migrate:fresh --force

# Limpa os caches de produção
php artisan route:clear
php artisan config:clear
php artisan cache:clear

exec apache2-foreground
