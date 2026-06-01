#!/bin/sh

# Roda as novas migrations e executa os Seeders automaticamente
php artisan migrate --force --seed

# Iniciar o servidor do Apache
exec apache2-foreground
