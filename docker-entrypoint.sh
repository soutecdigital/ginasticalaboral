#!/bin/sh

# 1. Derruba o banco antigo e recria com as colunas novas de softDeletes
php artisan migrate:fresh --force

# 2. Executa os Seeders padrão
php artisan db:seed --force

# 3. Limpa caches estruturais
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# 4. Inicia o Apache
exec apache2-foreground
