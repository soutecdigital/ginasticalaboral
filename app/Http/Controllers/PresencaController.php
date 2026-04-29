<?php

namespace App\Http\Controllers;

use App\Models\Presenca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PresencaController extends Controller
{
    /**
     * EXTRATO DE PRESENÇAS: Exibe o histórico real do professor.
     * 🛡️ POKA-YOKE: Filtra estritamente pelo ID da sessão (professor_id).
     */
 // No seu método index do PresencaController

    /**
     * EXTRATO DE PRESENÇAS
     * Exibe os fatos reais filtrados pelo Professor da Sessão.
     */
  


    public function index(Request $request)
{
    // 1. Pega o ID do Lucas (ID 3 ou o que estiver logado)
    $professorId = auth::id();

    // 2. Datas vindas do formulário (vimos no seu print que estão 01/04 a 16/04)
    $dataInicio = $request->get('data_inicio') ?? now()->startOfMonth()->format('Y-m-d');
    $dataFim    = $request->get('data_f')      ?? now()->format('Y-m-d');

    /**
     * 🛡️ POKA-YOKE: BUSCA GLOBAL
     * Note que NÃO filtramos por empresa_id aqui. 
     * Queremos o histórico do professor em TODAS as empresas.
     */
    $presencas = \App\Models\Presenca::with('empresa')
        ->where('professor_id', $professorId) // Filtra só pelo Lucas
        ->whereDate('data_presenca', '>=', $dataInicio)
        ->whereDate('data_presenca', '<=', $dataFim)
        ->orderBy('data_presenca', 'desc')
        ->paginate(15);

    // Se ainda assim der "Nenhuma presença encontrada", descomente abaixo:
    // dd($professorId, $dataInicio, $dataFim, $presencas->toArray());

    return view('presencas.index', compact('presencas', 'dataInicio', 'dataFim'));
}

    /**
     * RELATÓRIO DE PRESENÇAS
     * Exibe estatísticas consolidadas do professor logado
     */
    public function relatorio(Request $request)
    {
        $professorId = auth::id();
        
        $dataInicio = $request->get('data_inicio') ?? now()->startOfMonth()->format('Y-m-d');
        $dataFim    = $request->get('data_fim')    ?? now()->format('Y-m-d');

        // Query base
        $query = Presenca::with('empresa')
            ->where('professor_id', $professorId)
            ->whereDate('data_presenca', '>=', $dataInicio)
            ->whereDate('data_presenca', '<=', $dataFim);

        // Dados para o relatório
        $presencas = $query->orderBy('data_presenca', 'desc')->get();
        
        $totalPresencas = $presencas->count();
        
        // Agrupa por empresa
        $porEmpresa = $presencas->groupBy('empresa.nome_fantasia')
            ->map(function($items) {
                return $items->count();
            });

        // Calcula horas totais
        $horasTotal = $presencas->sum(function($p) {
            return strtotime($p->hora_presenca);
        });

        return view('presencas.relatorio', compact(
            'presencas',
            'totalPresencas',
            'porEmpresa',
            'dataInicio',
            'dataFim'
        ));
    }
}