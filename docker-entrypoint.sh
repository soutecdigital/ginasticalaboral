#!/bin/sh

# 1. Roda as novas migrations na produção
php artisan migrate --force

# 2. Força a execução dos Seeders mesmo em ambiente de produção
php artisan db:seed --force

# 3. Inicia o servidor Apache
exec apache2-foreground
