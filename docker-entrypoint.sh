#!/bin/sh

# Força a limpeza e criação de todas as tabelas estruturais de uma vez
php artisan migrate  --force

# Limpa os caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear

exec apache2-foreground
