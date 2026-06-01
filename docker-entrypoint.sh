#!/bin/sh

# 1. Roda apenas as NOVAS migrations com segurança (NUNCA use fresh aqui em produção!)
php artisan migrate --force

# 2. Mantém os caches limpos para garantir que novas rotas e telas funcionem direto
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# 3. Inicia o servidor Apache em primeiro plano
exec apache2-foreground
