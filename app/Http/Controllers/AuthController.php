<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

 public function login(Request $request)
{
    $request->validate([
        'login'    => 'required|string',
        'password' => 'required|string',
    ]);

    $throttleKey = Str::transliterate(Str::lower($request->input('login')) . '|' . $request->ip());

    if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
        $seconds = RateLimiter::availableIn($throttleKey);
        return back()->withErrors([
            'login_error' => "Muitas tentativas. Tente novamente em {$seconds} segundos."
        ])->withInput();
    }

    $loginValue = $request->input('login');
    $loginClean = preg_replace('/[^A-Za-z0-9]/', '', $loginValue);

    if (filter_var($loginValue, FILTER_VALIDATE_EMAIL)) {
        $field = 'email';
    } elseif (is_numeric($loginClean) && strlen($loginClean) == 11) {
        $field = 'cpf';
        $loginValue = $loginClean;
    } else {
        $field = 'matricula';
    }

    if (Auth::attempt([$field => $loginValue, 'password' => $request->password, 'ativo' => 1])) {
        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        /** * POKA-YOKE: Força o redirecionamento para a escolha de unidade.
         * Não usamos intended('/') aqui para garantir que o usuário 
         * defina o contexto da empresa antes de ver qualquer dado.
         */
        return redirect()->route('escolha_unidade');
    }

    RateLimiter::hit($throttleKey, 60);

    return back()->withErrors([
        'login_error' => 'Credenciais inválidas ou usuário inativo.'
    ])->withInput();
}
    public function logout(Request $request)
    {
        // Poka-Yoke: Garantimos que o logout aconteça mesmo se a sessão estiver instável
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Sessão encerrada com segurança.');
    }
} // <--- Verifique se esta chave fecha a classe corretamente