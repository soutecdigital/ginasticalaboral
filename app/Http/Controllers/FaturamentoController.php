<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faturamento;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\HistoricoContrato;
use App\Models\User;



class FaturamentoController extends Controller
{
    public function index(Request $request)
    {
        // 1. Definição dos filtros (Padrão: Mês e Ano atuais)
        $mesSel = $request->get('mes', date('m'));
        $anoSel = $request->get('ano', date('Y'));
        $empresaSel = $request->get('empresa_id');

        // 2. Busca lista de empresas para o select do filtro
        $empresas = Empresa::orderBy('nome_fantasia')->get();

        // 3. Query principal com Eager Loading (evita lentidão no banco)
        // Carregamos a empresa e os históricos de contrato juntos
        $query = Faturamento::with(['empresa.historicos'])
            ->whereYear('mes_referencia', $anoSel);

        // Lógica Poka-Yoke para "Ano Inteiro"
        if ($mesSel !== 'all') {
            $query->whereMonth('mes_referencia', $mesSel);
        }

        // Filtro por empresa específica, se selecionada
        if ($empresaSel) {
            $query->where('empresa_id', $empresaSel);
        }

        $faturamentos = $query->orderBy('mes_referencia', 'desc')->get();

        // 4. Retorna a View com todas as variáveis necessárias
        return view('faturamento.index', compact('faturamentos', 'empresas', 'mesSel', 'anoSel', 'empresaSel'));
    }

    public function historico($empresa_id)
    {
        // Eager loading para performance       
        $empresa = Empresa::with(['historicos.usuario'])->findOrFail($empresa_id);

        // Cálculo de tempo de contrato (do primeiro registro até hoje)
        $primeiroRegistro = $empresa->historicos->last();
        $created_at = $primeiroRegistro ? $primeiroRegistro->created_at : $empresa->created_at;
        $tempoContrato = Carbon::parse($created_at)->diffForHumans(null, true);

        return view('faturamento.historico', compact('empresa', 'tempoContrato'));
    }

    /**
     * Exemplo de método para dar baixa (pagamento)
     */
    public function darBaixa(Request $request, $id)
    {
        $request->validate([
            'valor_recebido' => 'required|numeric|min:0',
            'data_pagamento' => 'required|date',
        ]);

        try {
            return DB::transaction(function () use ($request, $id) {
                // 1. Localiza o faturamento atual
                $faturamentoAtual = Faturamento::findOrFail($id);

                // 2. Registra a baixa no registro atual
                $faturamentoAtual->update([
                    'valor_mensalidade' => $request->valor_recebido,
                    'data_pagamento'    => $request->data_pagamento,
                    'status'            => 'pago',
                    'observacao'        => $request->observacao_financeira,
                    'user_id'           => Auth::id(), // Auditoria de quem deu baixa
                ]);

                // 3. [AUTOMAÇÃO] Calcular o próximo mês
                $proximaRef = Carbon::parse($faturamentoAtual->mes_referencia)->addMonth();

                // 4. [POKA-YOKE] Evitar duplicidade: Só cria se não existir para o próximo mês
                $existeProximo = Faturamento::where('empresa_id', $faturamentoAtual->empresa_id)
                    ->where('mes_referencia', $proximaRef->format('Y-m-d'))
                    ->exists();

                if (!$existeProximo) {
                    // 5. Cria o novo registro baseado no VALOR ATUAL DO CONTRATO da empresa
                    Faturamento::create([
                        'empresa_id'        => $faturamentoAtual->empresa_id,
                        'mes_referencia'    => $proximaRef,
                        'valor_mensalidade' => $faturamentoAtual->empresa->valor_contrato, // Pega valor original
                        'status'            => 'pendente',
                    ]);
                }

                return redirect()->route('faturamento.index')
                    ->with('success', "Baixa realizada e próximo faturamento (" . $proximaRef->format('m/Y') . ") gerado com sucesso!");
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao processar automação: ' . $e->getMessage());
        }
    }
}
