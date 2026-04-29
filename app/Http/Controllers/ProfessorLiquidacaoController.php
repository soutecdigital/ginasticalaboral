<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ProfessorPagamento;
use App\Models\ProfessorLiquidacao;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class ProfessorLiquidacaoController extends Controller
{
    /**
     * Exibe a tela de conciliação para um professor específico.
     */
    public function create($professor_id)
    {
        $professor = User::findOrFail($professor_id);

        // Busca pagamentos que estão confirmados, mas ainda pendentes de liquidação
        $pagamentosPendentes = ProfessorPagamento::with('escala.empresa')
            ->where('user_id', $professor_id)
            ->where('status_pagamento', 'pendente')
            ->whereNull('liquidacao_id')
            ->orderBy('data_referencia', 'asc')
            ->get();

        // Lista todas as empresas ativas
        $empresas = Empresa::where('ativo', true)->get();

        return view('financeiro.liquidar', compact('professor', 'pagamentosPendentes', 'empresas'));
    }

    /**
     * Grava a liquidação e amarra os IDs dos pagamentos à NF.
     * Se já existe liquidação para o mesmo professor/empresa/mês, atualiza em vez de criar nova.
     */
    public function store(Request $request)
    {
        // [VALIDAÇÃO POKA-YOKE]
        $request->validate([
            'professor_id'    => 'required|exists:users,id',
            'empresa_id'      => 'required|exists:empresas,id',
            'numero_nf'       => 'required|string|max:50',
            'data_pagamento'  => 'required|date',
            'pagamentos_ids'  => 'required|array|min:1',
            'valor_total'     => 'required|numeric|min:0.01',
            'mes_referencia'  => 'required|date_format:Y-m',
        ], [
            'pagamentos_ids.required' => 'Você precisa selecionar pelo menos uma aula para liquidar.',
            'numero_nf.required'      => 'O número da NF é obrigatório para auditoria.'
        ]);

        try {
            // [ATOMICIDADE] Transação garante que ou salva tudo ou nada
            return DB::transaction(function () use ($request) {
                $mesReferencia = $request->mes_referencia . '-01';

                // [VERIFICAÇÃO DE DUPLICATA] Verifica se já existe uma liquidação para este professor/empresa/mês
                $liquidacao = ProfessorLiquidacao::where('professor_id', $request->professor_id)
                    ->where('empresa_id', $request->empresa_id)
                    ->where('mes_referencia', $mesReferencia)
                    ->first();

                if ($liquidacao) {
                    // [ATUALIZAR] Se existe, apenas adiciona mais pagamentos e atualiza o valor total
                    $novoValor = $liquidacao->valor_total_pago + $request->valor_total;
                    $liquidacao->update([
                        'valor_total_pago' => $novoValor,
                        'data_pagamento'   => $request->data_pagamento, // Atualiza para a data mais recente
                        'numero_nf'        => trim($request->numero_nf),
                        'observacao'       => $request->observacao,
                        'user_baixa_id'    => Auth::id(),
                    ]);
                    
                    $tipoOperacao = 'Liquidação atualizada';
                } else {
                    // [CRIAR] Se não existe, cria uma nova liquidação
                    $liquidacao = ProfessorLiquidacao::create([
                        'professor_id'     => $request->professor_id,
                        'empresa_id'       => $request->empresa_id,
                        'numero_nf'        => trim($request->numero_nf),
                        'mes_referencia'   => $mesReferencia,
                        'valor_total_pago' => $request->valor_total,
                        'data_pagamento'   => $request->data_pagamento,
                        'forma_pagamento'  => $request->forma_pagamento ?? 'banco',
                        'user_baixa_id'    => Auth::id(),
                        'observacao'       => $request->observacao,
                    ]);
                    
                    $tipoOperacao = 'Liquidação criada';
                }

                // 2. [VÍNCULO] Atualizar os pagamentos individuais
                // Amarra cada aula à NF criada e muda o status para 'pago'
                $updated = ProfessorPagamento::whereIn('id', $request->pagamentos_ids)
                    ->where('status_pagamento', 'pendente') // Segurança extra
                    ->update([
                        'liquidacao_id'    => $liquidacao->id,
                        'status_pagamento' => 'pago',
                        'updated_at'       => now()
                    ]);

                // [LOG DE SEGURANÇA]
                return redirect()->route('financeiro.prof.pagar')
                    ->with('success', "{$tipoOperacao}! NF {$liquidacao->numero_nf} com {$updated} aulas vinculadas. Total: R$ " . number_format($liquidacao->valor_total_pago, 2, ',', '.'));
            });
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Falha na liquidação: ' . $e->getMessage());
        }
    }

    /**
     * Histórico de Liquidações (Auditoria)
     */
    public function index(Request $request)
    {
        $query = ProfessorLiquidacao::with(['professor', 'empresa', 'usuarioBaixa']);

        // Filtros...
        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }
        if ($request->filled('data_inicio')) {
            $query->where('data_pagamento', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->where('data_pagamento', '<=', $request->data_fim);
        }

        // Clone a query para calcular o total antes de paginar
        $totalPago = (clone $query)->sum('valor_total_pago') ?? 0;

        $liquidacoes = $query->orderBy('data_pagamento', 'desc')->paginate(20);
        $empresas = \App\Models\Empresa::where('ativo', true)->orderBy('nome_fantasia')->get();

        // Correto (de acordo com sua imagem)
        return view('financeiro.liquidacoes.index', compact('liquidacoes', 'empresas', 'totalPago'));
    }

    // ProfessorLiquidacaoController.php
    public function show($id)
    {
        try {
            $liquidacao = ProfessorLiquidacao::with(['professor', 'empresa', 'usuarioBaixa', 'pagamentos.escala'])
                ->findOrFail($id);

            return response()->json($liquidacao);
        } catch (Exception $e) {
            // Isso vai retornar o erro real no console do navegador para você ler
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
