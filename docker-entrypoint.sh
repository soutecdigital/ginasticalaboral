#!/bin/sh

# 1. Roda as novas migrations na produção
php artisan migrate --force

# 2. Limpa todos os caches antigos que travam rotas novas
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# 3. Inicia o servidor Apache
exec apache2-foreground
