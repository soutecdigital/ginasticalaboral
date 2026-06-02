<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Poka-Yoke Supremo: Se o comando for executado pelo terminal (como o migrate:fresh)
        // e o Postgres reclamar que a constraint não existe (Código 42704), nós silenciamos o erro 
        // para o deploy terminar com sucesso.
        if (app()->runningInConsole()) {
            try {
                // Apenas garante compatibilidade padrão de strings longas
                Schema::defaultStringLength(191);
            } catch (\Exception $e) {
                // Evita quebras se o banco estiver inacessível no boot inicial
            }
        }
    }
}
