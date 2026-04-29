<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\QueryException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
   ->withMiddleware(function (Middleware $middleware) {
        // 1. Middlewares Globais
        $middleware->append(\App\Http\Middleware\CheckDatabase::class);

        // 2. Middlewares Nomeados (Apelidos para usar no web.php)
        $middleware->alias([
            'check.empresa' => \App\Http\Middleware\CheckEmpresaAtiva::class,
            // ADICIONE ESTA LINHA ABAIXO:
            'checkPerfil'   => \App\Http\Middleware\CheckPerfil::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        // Tratamento global de erro de banco de dados
        $exceptions->render(function (QueryException $e) {
            return response()->view('errors.500', [], 500);
        });

    })->create();