<?php

namespace App\Http\Controllers;

use App\Models\Ouvidoria;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OuvidoriaController extends Controller
{
    /**
     * LEGENDA: Lista feedbacks filtrando por permissão de Perfil.
     * Admin vê tudo, Sócio vê apenas sua unidade.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Poka-Yoke: Aluno não acessa painel de gestão
        if ($user->perfil === 'aluno') {
            return redirect()->route('ouvidoria.aluno');
        }

        $query = Ouvidoria::with(['usuario', 'empresa'])->orderBy('created_at', 'desc');

        // Filtro de Unidade para Sócios
        if ($user->perfil === 'socio') {
            $query->where('empresa_id', session('empresa_id'));
        }
        
        // Filtro Dinâmico para Admin
        if ($user->perfil === 'admin' && $request->empresa_id) {
            $query->where('empresa_id', $request->empresa_id);
        }

        $feedbacks = $query->get();
        return view('ouvidoria.index', compact('feedbacks'));
    }

    /**
     * LEGENDA: Histórico pessoal do Aluno.
     */
    public function minhasMensagens()
    {
        $mensagens = Ouvidoria::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('ouvidoria.aluno_index', compact('mensagens'));
    }

    /**
     * LEGENDA: Registro de resposta oficial da coordenação.
     */
    public function responder(Request $request, $id)
    {
        $request->validate([
            'resposta' => 'required|string|min:5'
        ]);

        $ouvidoria = Ouvidoria::findOrFail($id);
        
        $ouvidoria->update([
            'resposta_coordenacao' => $request->resposta,
            'status' => 'resolvido',
            'respondido_em' => now(),
        ]);

        return redirect()->back()->with('success', 'Medida registrada com sucesso!');
    }

    /**
     * LEGENDA: Ranking de Elogios (A Pirâmide de Destaques).
     * Conta quantos elogios cada professor recebeu no mês atual.
     */
// No OuvidoriaController, ajuste o método relatorioElogios
public function relatorioElogios()
{
    $destaques = \App\Models\Ouvidoria::where('assunto', 'Elogio')
        ->whereHas('professor') // LEGENDA: Só traz se o relacionamento com 'users' existir
        ->whereMonth('created_at', now()->month)
        ->select('professor_id', DB::raw('count(*) as total'))
        ->groupBy('professor_id')
        ->orderBy('total', 'desc')
        ->with('professor') 
        ->get();

    return view('ouvidoria.relatorio', compact('destaques'));
}

    /**
     * LEGENDA: Salva o feedback do Aluno.
     * AGORA AMARRADO: Recebe o professor_id para alimentar o ranking.
     */
    public function store(Request $request)
    {
        // Validação dos dados de entrada
        $request->validate([
            'assunto' => 'required|string',
            'mensagem' => 'required|string|min:5',
            'professor_id' => 'nullable|exists:users,id', // Valida se o professor existe no banco
        ]);

        // Lógica de recuperação da Empresa (Sessão -> User -> Default)
        $empresaId = session('empresa_id') ?? Auth::user()->empresa_id ?? Empresa::first()->id;

        // Criação do registro com vínculo ao professor elogiado
        Ouvidoria::create([
            'user_id'      => Auth::id(),
            'empresa_id'   => $empresaId, 
            'professor_id' => $request->professor_id, // IMPORTANTE: Amarra o elogio ao professor
            'assunto'      => $request->assunto,
            'mensagem'     => $request->mensagem,
            'status'       => 'pendente'
        ]);

        return redirect()->back()->with('success', 'Sua mensagem foi enviada com sucesso.');
    }




    /**
     * LEGENDA: Troca rápida de status (Pendente, Lido, Resolvido).
     */
    public function alterarStatus($id, $status)
    {
        $item = Ouvidoria::findOrFail($id);
        $item->update(['status' => $status]);

        return redirect()->back()->with('success', 'Status da ouvidoria atualizado!');
    }



}