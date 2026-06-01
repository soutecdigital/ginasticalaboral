#!/bin/sh

# Rodar as migrations limpando o banco rascunho anterior
php artisan migrate --force

# Iniciar o servidor do Apache em primeiro plano
exec apache2-foreground
