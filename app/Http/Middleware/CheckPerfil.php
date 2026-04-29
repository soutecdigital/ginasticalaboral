<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CheckPerfil
{
    public function handle(Request $request, Closure $next, ...$perfis)
    {
        // Lógica de verificação...
        if (!Auth::check() || !in_array(Auth::user()->perfil, $perfis)) {
            return redirect('/')->with('error', 'Acesso negado.');
        }
        return $next($request);
    }
}