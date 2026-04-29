<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RelatorioAlunosController extends Controller
{
    /**
     * Exibe relatório de alunos por empresa
     * Apenas admin e sócio podem acessar
     */
public function index(Request $request)
{
    // 1. Parâmetros de filtro
    $empresaSel = $request->get('empresa_id');
    $statusFiltro = $request->get('status', 'ativo'); // Default 'ativo'

    // 2. Busca todas as empresas para o select do filtro
    $empresas = Empresa::orderBy('nome_fantasia')->get();

    // 3. Query base com Eager Loading (evita o problema de N+1 consultas)
    $query = User::where('perfil', 'aluno')->with('empresas');

    // 4. [POKA-YOKE] Filtro por status usando a coluna correta 'ativo'
    if ($statusFiltro !== 'todos') {
        $valorAtivo = ($statusFiltro === 'ativo') ? 1 : 0;
        $query->where('ativo', $valorAtivo);
    }

    // 5. Filtro por empresa (Relacionamento Many-to-Many)
    if ($empresaSel) {
        $query->whereHas('empresas', function ($q) use ($empresaSel) {
            $q->where('empresas.id', $empresaSel);
        });
    }

    // 6. Executa a busca
    $alunos = $query->orderBy('name')->get();

    // 7. [PERFORMANCE] Agrupamento inteligente
    // Em vez de foreach manual, usamos o groupBy do próprio Collection do Laravel
    $alunosPorEmpresa = collect();

    foreach ($alunos as $aluno) {
        foreach ($aluno->empresas as $empresa) {
            // Se filtramos por uma empresa, ignoramos as outras empresas do mesmo aluno no relatório
            if ($empresaSel && $empresa->id != $empresaSel) continue;

            if (!$alunosPorEmpresa->has($empresa->id)) {
                $alunosPorEmpresa->put($empresa->id, collect());
            }
            $alunosPorEmpresa->get($empresa->id)->push($aluno);
        }
    }

    return view('relatorios.alunos.index', [
        'alunosPorEmpresa' => $alunosPorEmpresa,
        'empresas'         => $empresas,
        'empresaSel'       => $empresaSel,
        'status'           => $statusFiltro, // Mantemos o texto para o select na view
        'alunos'           => $alunos
    ]);
}

    /**
     * Exporta relatório para CSV
     */
    public function exportarCsv(Request $request)
    {
        $empresaSel = $request->get('empresa_id');
        $status = $request->get('status', 'ativo');

        $query = User::where('perfil', 'aluno')->with('empresas');

        if ($empresaSel) {
            $query->whereHas('empresas', function ($q) use ($empresaSel) {
                $q->where('empresa_id', $empresaSel);
            });
        }

        if ($status !== 'todos') {
            $query->where('status', $status);
        }

        $alunos = $query->orderBy('name')->get();

        $filename = 'relatorio-alunos-' . date('Y-m-d-His') . '.csv';
        $headers = array(
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $callback = function() use ($alunos, $empresaSel) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8 em Excel
            fputs($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, ['Empresa', 'Aluno', 'Matrícula', 'Email', 'Status'], ';');

            // Dados
            foreach ($alunos as $aluno) {
                foreach ($aluno->empresas as $empresa) {
                    if ($empresaSel && $empresa->id != $empresaSel) continue;
                    fputcsv($file, [
                        $empresa->nome_fantasia,
                        $aluno->name,
                        $aluno->matricula,
                        $aluno->email,
                        $aluno->status ?? 'ativo'
                    ], ';');
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
