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

    // Adicionamos withTrashed() para que, se uma unidade for arquivada, 
    // ela ainda possa ser tratada aqui ou não quebre a consulta.
    $unidades = $usuario->empresas()
        ->withTrashed() 
        ->where('ativo', 1)
        ->get();

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
    
    // Verificamos o acesso permitindo empresas deletadas (soft delete)
    $possuiAcesso = $usuario->empresas()
        ->withTrashed()
        ->where('empresas.id', $id)
        ->exists();

    if (!$possuiAcesso) {
        return back()->withErrors(['erro' => 'Acesso negado a esta unidade.']);
    }

    // Usamos withTrashed() aqui também para evitar o erro 404/500 caso a empresa esteja deletada
    $empresa = \App\Models\Empresa::withTrashed()->findOrFail($id);

    session([
        'empresa_id'   => $empresa->id,
        'empresa_nome' => $empresa->nome_fantasia,
    ]);

    return redirect()->route('home');
}
}
