<?php

namespace App\Http\Controllers;

use App\Models\LocalizacaoProfEmp;
use App\Models\Escala;
use App\Models\User;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LocalizacaoAuditoriaController extends Controller
{
    /**
     * Listar todas as localizações registradas com filtros
     */
    public function index(Request $request)
    {
        $query = LocalizacaoProfEmp::with(['escala', 'professor', 'empresa']);

        // Filtro por Data
        if ($request->data_inicio && $request->data_fim) {
            $query->whereBetween('confirmado_em', [
                Carbon::parse($request->data_inicio)->startOfDay(),
                Carbon::parse($request->data_fim)->endOfDay()
            ]);
        }

        // Filtro por Professor
        if ($request->professor_id) {
            $query->where('professor_id', $request->professor_id);
        }

        // Filtro por Empresa
        if ($request->empresa_id) {
            $query->where('empresa_id', $request->empresa_id);
        }

        // Filtro por Tipo de Confirmação
        if ($request->tipo_confirmacao) {
            $query->where('tipo_confirmacao', $request->tipo_confirmacao);
        }

        // Filtro: Dentro/Fora do Raio
        if ($request->dentro_raio === 'fora') {
            $query->where('dentro_raio', false);
        } elseif ($request->dentro_raio === 'dentro') {
            $query->where('dentro_raio', true);
        }

        $localizacoes = $query->orderBy('confirmado_em', 'desc')->paginate(20);

        // Dados para os filtros
        $professores = User::where('perfil', 'professor')->orderBy('name')->get();
        $empresas = Empresa::where('ativo', true)->orderBy('nome_fantasia')->get();

        return view('auditoria.localizacoes.index', compact(
            'localizacoes',
            'professores',
            'empresas'
        ));
    }

    /**
     * Detalhar uma localização específica
     */
    public function show($id)
    {
        $localizacao = LocalizacaoProfEmp::with(['escala', 'professor', 'empresa'])->findOrFail($id);

        return view('auditoria.localizacoes.show', compact('localizacao'));
    }

    /**
     * Relatório de conformidade de localização
     */
    public function relatorio(Request $request)
    {
        $dataInicio = $request->data_inicio ? Carbon::parse($request->data_inicio) : Carbon::now()->subMonth();
        $dataFim = $request->data_fim ? Carbon::parse($request->data_fim) : Carbon::now();

        // Contagem: Confirmações por tipo
        $confirmacoesPorTipo = LocalizacaoProfEmp::whereBetween('confirmado_em', [
            $dataInicio->startOfDay(),
            $dataFim->endOfDay()
        ])->groupBy('tipo_confirmacao')->selectRaw('tipo_confirmacao, COUNT(*) as total')->get();

        // Contagem: Dentro vs Fora do Raio
        $conformidade = LocalizacaoProfEmp::whereBetween('confirmado_em', [
            $dataInicio->startOfDay(),
            $dataFim->endOfDay()
        ])->groupBy('dentro_raio')->selectRaw('dentro_raio, COUNT(*) as total')->get();

        // Professores com mais confirmações fora do raio
        $professoresAlerta = LocalizacaoProfEmp::with('professor')
            ->where('dentro_raio', false)
            ->whereBetween('confirmado_em', [$dataInicio->startOfDay(), $dataFim->endOfDay()])
            ->groupBy('professor_id')
            ->selectRaw('professor_id, COUNT(*) as total_fora_raio')
            ->orderByRaw('total_fora_raio DESC')
            ->limit(10)
            ->get();

        return view('auditoria.localizacoes.relatorio', compact(
            'dataInicio',
            'dataFim',
            'confirmacoesPorTipo',
            'conformidade',
            'professoresAlerta'
        ));
    }

    /**
     * Exportar relatório de localizações (CSV)
     */
    public function exportar(Request $request)
    {
        $query = LocalizacaoProfEmp::with(['professor', 'empresa']);

        if ($request->data_inicio && $request->data_fim) {
            $query->whereBetween('confirmado_em', [
                Carbon::parse($request->data_inicio)->startOfDay(),
                Carbon::parse($request->data_fim)->endOfDay()
            ]);
        }

        $localizacoes = $query->orderBy('confirmado_em', 'desc')->get();

        $filename = "auditoria_localizacoes_" . Carbon::now()->format('Y-m-d_His') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $callback = function() use ($localizacoes) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Professor', 'Empresa', 'Data Confirmação', 'Tipo', 'Lat Prof', 'Lng Prof', 'Lat Empresa', 'Lng Empresa', 'Distância (m)', 'Dentro Raio', 'IP', 'User Agent']);

            foreach ($localizacoes as $loc) {
                fputcsv($file, [
                    $loc->id,
                    $loc->professor->name,
                    $loc->empresa->nome_fantasia,
                    $loc->confirmado_em->format('Y-m-d H:i:s'),
                    $loc->tipo_confirmacao,
                    $loc->prof_lat,
                    $loc->prof_lng,
                    $loc->empresa_lat,
                    $loc->empresa_lng,
                    $loc->distancia_metros,
                    $loc->dentro_raio ? 'Sim' : 'Não',
                    $loc->ip_address,
                    $loc->user_agent,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
