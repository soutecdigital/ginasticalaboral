<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 

class AlunosController extends Controller
{
    /**
     * Exibe a lista de alunos da unidade selecionada.
     */
   public function index()
{
    // Lendo a gaveta certa
    $empresaId = session('empresa_ativa'); 

    if (!$empresaId) {
        return redirect()->route('escolha_unidade');
    }

    $alunos = User::where('perfil', 'aluno')
        ->whereHas('empresas', function($query) use ($empresaId) {
            $query->where('empresas.id', $empresaId);
        })
        ->get();

    return view('alunos.index', compact('alunos'));
}
}