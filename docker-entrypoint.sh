#!/bin/sh

# 1. Roda APENAS novas migrations com segurança (NUNCA mais use fresh aqui!)
php artisan migrate --force

# 2. Limpa e renova os caches para as novas rotas funcionarem direto
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# 3. Inicia o servidor Apache em primeiro plano
exec apache2-foreground
