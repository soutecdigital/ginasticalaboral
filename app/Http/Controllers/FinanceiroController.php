<?php

namespace App\Http\Controllers;

use App\Models\Faturamento;
use App\Models\Empresa;
use App\Models\Escala;
use App\Models\ProfessorConfiguracao;
use App\Models\ProfessorPagamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Importante para manipular as datas

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        // Padrão: Mês e Ano atuais. 'all' para ano inteiro.
        $mesSel = $request->get('mes', date('m'));
        $anoSel = $request->get('ano', date('Y'));
        $empresaSel = $request->get('empresa_id');

        $empresas = Empresa::orderBy('nome_fantasia')->get();

        // Carrega faturamentos com os relacionamentos necessários
        $query = Faturamento::with(['empresa.historicos'])
            ->whereYear('mes_referencia', $anoSel);

        if ($mesSel !== 'all') {
            $query->whereMonth('mes_referencia', $mesSel);
        }

        if ($empresaSel) {
            $query->where('empresa_id', $empresaSel);
        }

        $faturamentos = $query->orderBy('mes_referencia', 'desc')->get();

        return view('financeiro.index', compact('faturamentos', 'empresas', 'mesSel', 'anoSel', 'empresaSel'));
    }
    //historico que busca os faturamentos passados e os reajustes de contrato
  public function historico($id)
{
    // O with('historicos') é o que "preenche" a variável para o Blade
    $empresa = Empresa::with(['historicos', 'faturamentos'])->findOrFail($id);

    return view('financeiro.historico', compact('empresa'));
}

public function contasPagarProfessor(Request $request)
{
    $mesSel = $request->get('mes', date('m'));
    $anoSel = $request->get('ano', date('Y'));

    // Buscamos as escalas agrupadas por professor
    $escalas = \App\Models\Escala::with(['professor.configuracaoAtual', 'empresa'])
        ->whereYear('data', $anoSel)
        ->when($mesSel !== 'all', function($q) use ($mesSel) {
            return $q->whereMonth('data', $mesSel);
        })
        ->get()
        ->groupBy('user_id');

    $relatorioPagamento = $escalas->map(function ($profsEscalas) {
        $primeiraEscala = $profsEscalas->first();
        $totalAPagar = 0;
        $detalhes = [];

        foreach ($profsEscalas as $item) {
            $pagamentoRegistrado = DB::table('professor_pagamentos')
                ->where('escala_id', $item->id)
                ->where('status_pagamento', 'pendente')
                ->whereNull('liquidacao_id')
                ->first();

            if (! $pagamentoRegistrado) {
                continue;
            }

            $valorBase = $item->valor_venda_avulso ?? ($item->professor->configuracaoAtual->valor_aula ?? 0);
            $totalAPagar += $valorBase;

            $detalhes[] = [
                'data' => $item->data,
                'unidade' => $item->empresa->nome_fantasia,
                'valor' => $valorBase,
                'pago' => true
            ];
        }

        return [
            'professor_id' => $primeiraEscala->user_id,
            'nome' => $primeiraEscala->professor->name,
            'total' => $totalAPagar,
            'itens' => $detalhes,
            'status_geral' => $totalAPagar > 0 ? 'Pendente de Liquidação' : 'Sem Pendências'
        ];
    })->filter(function ($dados) {
        return $dados['total'] > 0;
    })->values();

    return view('financeiro.contaspagarprofessor', compact('relatorioPagamento', 'mesSel', 'anoSel'));
}




    public function darBaixa(Request $request, $id)
    {
        try {
            // Buscamos o faturamento e a empresa dona dele
            $faturamento = Faturamento::with('empresa')->findOrFail($id);

            $valorOriginal = (float) $faturamento->valor_mensalidade;
            $valorRecebido = (float) $request->valor_recebido;

            // 1. Lógica de Observação para Descontos/Ajustes
            $obs = $request->observacao_financeira;
            if ($valorRecebido != $valorOriginal) {
                $obs = "Valor ajustado de R$ " . number_format($valorOriginal, 2, ',', '.') .
                    " para R$ " . number_format($valorRecebido, 2, ',', '.') .
                    ". Motivo: " . $obs;
            }

            // 2. Atualiza a fatura atual para PAGO
            $faturamento->update([
                'valor_mensalidade' => $valorRecebido,
                'status' => 'pago',
                'data_pagamento' => $request->data_pagamento ?? now(),
                'user_baixa_id' => Auth::id(),
                'observacao_financeira' => $obs
            ]);

            // 3. GATILHO AUTOMÁTICO: Gerar o próximo mês
            // Pegamos a data atual da fatura e somamos 1 mês
            $proximoMes = Carbon::parse($faturamento->mes_referencia)->addMonth()->startOfMonth();

            // Poka-Yoke: Só cria se não existir faturamento para o próximo mês
            $existeProximo = Faturamento::where('empresa_id', $faturamento->empresa_id)
                ->where('mes_referencia', $proximoMes->format('Y-m-d'))
                ->exists();

            if (!$existeProximo) {
                Faturamento::create([
                    'empresa_id'        => $faturamento->empresa_id,
                    'valor_mensalidade' => $faturamento->empresa->valor_contrato, // Pega o valor ATUALIZADO do Edit Empresa
                    'valor_avulso'      => 0,
                    'mes_referencia'    => $proximoMes,
                    'status'            => 'pendente',
                ]);
            }

            return redirect()->back()->with('success', 'Baixa realizada e próximo mês projetado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao processar: ' . $e->getMessage());
        }
    }

    /**
     * MÉTODO: confirmarPresenca
     * OBJETIVO: Ao confirmar presença da escala, cria automaticamente o registro financeiro (ProfessorPagamento)
     * FLUXO: 
     *  1. Atualiza o status da escala para 'confirmado'
     *  2. Busca a configuração de valores vigente do professor
     *  3. Define o valor baseado no tipo de aula (online, avulso ou presencial)
     *  4. Insere o registro na tabela professor_pagamentos com status 'pendente'
     */
    public function confirmarPresenca(Request $request, $escalaId)
    {
        try {
            $escala = Escala::findOrFail($escalaId);
            
            // 1. Atualiza o status da escala para 'confirmado'
            $escala->update(['status_presenca' => 'confirmado']);

            // 2. Busca a configuração de valores do professor (vigente na data da escala)
            $config = ProfessorConfiguracao::where('user_id', $escala->user_id)
                ->where('data_inicio_vigencia', '<=', $escala->data)
                ->orderBy('data_inicio_vigencia', 'desc')
                ->first();

            // Validação: Se não houver configuração, não prossegue
            if (!$config) {
                return redirect()->back()->with('error', 'Nenhuma configuração de valores encontrada para este professor na data da aula.');
            }

            // 3. Define o valor com base no tipo de aula da escala
            $valorItem = match($escala->tipo_aula) {
                'online' => $config->valor_aula_online,
                'avulso' => $config->valor_aula_avulso,
                default  => $config->valor_aula, // presencial (normal)
            };

            // 4. Insere na tabela de pagamentos
            ProfessorPagamento::create([
                'escala_id'       => $escala->id,
                'user_id'         => $escala->user_id,
                'valor_pago'      => $valorItem,
                'data_referencia' => $escala->data,
                'status_pagamento' => 'pendente'
            ]);

            return redirect()->back()->with('success', 'Presença confirmada e valor lançado no financeiro!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao confirmar presença: ' . $e->getMessage());
        }
    }
}
