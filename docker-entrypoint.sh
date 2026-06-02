#!/bin/sh

# Poka-Yoke: Executa apenas as novas migrações com segurança, sem apagar dados existentes
php artisan migrate --force

# Limpa e otimiza os caches para o ambiente produtivo
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

exec apache2-foreground
