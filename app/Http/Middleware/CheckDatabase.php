<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckDatabase
{
    /**
     * Trata uma requisição de entrada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Tenta estabelecer a conexão com o PDO
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            // Se falhar, aborta com erro 503 (Serviço Indisponível)
            // O Laravel buscará automaticamente a view resources/views/errors/503.blade.php
            abort(503, 'Conexão com o banco de dados recusada.');
        }

        return $next($request);
    }
}