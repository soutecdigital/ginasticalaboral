#!/bin/sh
php artisan route:clear
php artisan config:clear
php artisan cache:clear

exec apache2-foreground
