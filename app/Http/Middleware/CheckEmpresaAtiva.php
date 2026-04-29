<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckEmpresaAtiva
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Se não estiver logado, segue o fluxo (o middleware 'auth' barrará se necessário)
        if (!Auth::check()) {
            return $next($request);
        }

        /**
         * 2. POKA-YOKE: Rotas que NÃO podem ser barradas.
         * Se o usuário estiver tentando escolher a unidade ou sair, deixamos passar.
         */
        if ($request->routeIs('escolha_unidade') || 
            $request->routeIs('selecionar_empresa') || 
            $request->routeIs('logout')) {
            return $next($request);
        }

        /**
         * 3. VERIFICAÇÃO DE SESSÃO:
         * Se não houver uma empresa selecionada na sessão, 
         * mandamos ele para a tela de escolha.
         */
        if (!session()->has('empresa_id')) { // Use o mesmo nome que você grava no EscolhaEmpresaController
            return redirect()->route('escolha_unidade');
        }

        return $next($request);
    }
}