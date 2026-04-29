<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;





class EscolhaEmpresaController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $usuario */
        $usuario = Auth::user();

        // Agora o VS Code vai reconhecer o método empresas()
        $unidades = $usuario->empresas()->where('ativo', 1)->get();

        if ($unidades->isEmpty()) {
            return view('auth.escolha_empresa', [
                'unidades' => collect(),
                'precisaVincular' => true
            ]);
        }

        return view('auth.escolha_empresa', compact('unidades'));
    }
    public function selecionar($id)
    {
        /** @var \App\Models\User $usuario */
        $usuario = Auth::user();
        // POKA-YOKE: Especificamos 'empresas.id' para evitar a ambiguidade
        $possuiAcesso = $usuario->empresas()
            ->where('empresas.id', $id) // <-- Adicionado 'empresas.' aqui
            ->exists();

        if (!$possuiAcesso) {
            return back()->withErrors(['erro' => 'Acesso negado a esta unidade.']);
        }

        $empresa = \App\Models\Empresa::findOrFail($id);

        session([
            'empresa_id'   => $empresa->id,
            'empresa_nome' => $empresa->nome_fantasia,
        ]);

        // Redireciona para a home após selecionar com sucesso
        return redirect()->route('home');
    }
}
