<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Faturamento;
use App\Models\HistoricoContrato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * [LEGENDA DE DEV]
 * Controller responsável pela gestão do ecossistema de Empresas.
 * Gerencia: Cadastro, Localização (Geolocalização), Financeiro Inicial e Auditoria de Contratos.
 */
class EmpresaController extends Controller
{
    public function index()
    {
        $empresas = Empresa::orderBy('created_at', 'desc')->get();
        return view('empresas.index', compact('empresas'));
    }

    public function create()
    {
        return view('empresas.create');
    }

    /**
     * SALVAR NOVA EMPRESA
     * Lógica Poka-Yoke aplicada para garantir integridade de dados financeiros e geográficos.
     */
    public function store(Request $request)
    {
        // [SANITIZAÇÃO] Remove caracteres não numéricos do CNPJ para validação unique no DB
        $request->merge(['cnpj' => preg_replace('/\D/', '', $request->cnpj)]);

        $request->validate([
            'nome_fantasia'  => 'required|string|max:255',
            'cnpj'           => 'required|string|unique:empresas,cnpj',
            'plano'          => 'required|in:basic,pro,premium',
            'valor_contrato' => Auth::user()->perfil === 'admin' ? 'required|numeric' : 'nullable|numeric',
            'cidade'         => 'required|string',
            'estado'         => 'required|string|max:2',
            'lat'            => 'nullable|numeric',
            'lng'            => 'nullable|numeric',
        ], [
            'cnpj.unique' => 'Este CNPJ já está cadastrado no sistema.',
            'valor_contrato.required' => 'Defina o valor mensal para gerar a primeira fatura.'
        ]);

        $dados = $request->all();

        // [ESTADO INICIAL] Poka-Yoke: Toda nova empresa nasce ativa
        $dados['ativo'] = 1;

        // [BOLEANOS] Converte presença de checkbox em 1 ou 0 para o MySQL
        foreach (['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'] as $dia) {
            $dados[$dia] = $request->has($dia) ? 1 : 0;
        }

        if ($request->celular) {
            $dados['celular'] = preg_replace('/\D/', '', $request->celular);
        }

        // [GEOLOCALIZAÇÃO] Padronização: Troca vírgula por ponto para o tipo DECIMAL do banco
        if ($request->lat) $dados['lat'] = str_replace(',', '.', $request->lat);
        if ($request->lng) $dados['lng'] = str_replace(',', '.', $request->lng);

        // [ATOMICIDADE] DB::transaction garante que se o financeiro falhar, a empresa não é criada
        return DB::transaction(function () use ($dados, $request) {
            try {
                $empresa = Empresa::create($dados);

                // [REGRA DE NEGÓCIO] Se admin cadastrou com valor, gera automaticamente a fatura do mês atual
                if (Auth::user()->perfil === 'admin' && $request->valor_contrato > 0) {
                    Faturamento::create([
                        'empresa_id'        => $empresa->id,
                        'valor_mensalidade' => $request->valor_contrato,
                        'valor_avulso'      => 0,
                        'mes_referencia'    => now()->startOfMonth(),
                        'status'            => 'pendente'
                    ]);
                    $msg = 'Empresa e faturamento gerados com sucesso!';
                } else {
                    $msg = 'Empresa cadastrada! O financeiro deve ser configurado pelo Admin.';
                }

                $this->notificarCadastroAdmin($empresa);

                return redirect()->route('empresas.index')->with('success', $msg);
            } catch (Exception $e) {
                return redirect()->back()->with('error', 'Erro ao salvar: ' . $e->getMessage())->withInput();
            }
        });
    }

    public function edit(Empresa $empresa)
    {
        // [DADOS ACUMULADOS] Carrega contagem de alunos e última fatura para visualização rápida no Edit
        $totalAlunosAtual = DB::table('empresa_user')->where('empresa_id', $empresa->id)->count();

        $ultimaFatura = Faturamento::where('empresa_id', $empresa->id)
                            ->orderBy('mes_referencia', 'desc')
                            ->first();

        return view('empresas.edit', compact('empresa', 'totalAlunosAtual', 'ultimaFatura'));
    }

    /**
     * ATUALIZAR EMPRESA E AUDITORIA
     * Se o valor do contrato mudar, o sistema registra o histórico (log de reajuste).
     */
    public function update(Request $request, Empresa $empresa)
    {
        $request->merge(['cnpj' => preg_replace('/\D/', '', $request->cnpj)]);

        $request->validate([
            'nome_fantasia'  => 'required|string|max:255',
            'cnpj'           => 'required|string|unique:empresas,cnpj,' . $empresa->id,
            'plano'          => 'required|in:basic,pro,premium',
            'ativo'          => 'required|boolean',
            'valor_contrato' => 'nullable|numeric',
            'dia_vencimento' => 'required|integer|min:1|max:31',
            'lat'            => 'nullable|numeric',
            'lng'            => 'nullable|numeric',
        ]);

        $dados = $request->all();
        $dados['celular'] = preg_replace('/\D/', '', $request->celular ?? '');

        foreach (['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'] as $dia) {
            $dados[$dia] = $request->has($dia) ? 1 : 0;
        }

        if ($request->lat) $dados['lat'] = str_replace(',', '.', $request->lat);
        if ($request->lng) $dados['lng'] = str_replace(',', '.', $request->lng);

        $isAdminOrSocio = in_array(Auth::user()->perfil, ['admin', 'socio']);
        $valorAntigo = (float) $empresa->valor_contrato;
        $valorNovo = $isAdminOrSocio ? (float) ($request->valor_contrato ?? $valorAntigo) : $valorAntigo;

        // [SEGURANÇA] Bloqueia alteração de dados sensíveis por perfis não-admin/socio
        if (!$isAdminOrSocio) {
            unset($dados['valor_contrato'], $dados['dia_vencimento']);
        }

        return DB::transaction(function () use ($dados, $empresa, $request, $valorAntigo, $valorNovo, $isAdminOrSocio) {
            try {
                // [AUDITORIA] Se houver reajuste de valor, grava no histórico e projeta fatura para o próximo mês
                if ($isAdminOrSocio && $valorNovo != $valorAntigo) {
                    HistoricoContrato::create([
                        'empresa_id'           => $empresa->id,
                        'user_id'              => Auth::id(), // Quem fez a alteração
                        'valor_anterior'       => $valorAntigo,
                        'valor_novo'           => $valorNovo,
                        'motivo'               => $request->motivo_alteracao ?? 'Reajuste contratual',
                        'total_alunos_momento' => $request->total_alunos ?? 0
                    ]);

                    Faturamento::create([
                        'empresa_id'        => $empresa->id,
                        'valor_mensalidade' => $valorNovo,
                        'valor_avulso'      => 0,
                        'mes_referencia'    => now()->addMonth()->startOfMonth(),
                        'status'            => 'pendente'
                    ]);
                }

                $empresa->update($dados);

                return redirect()->route('empresas.index')->with('success', 'Cadastro e localização atualizados!');
            } catch (Exception $e) {
                return redirect()->back()->with('error', 'Erro na atualização: ' . $e->getMessage());
            }
        });
    }

    public function destroy(Empresa $empresa)
    {
        try {
            $empresa->delete();
            return redirect()->route('empresas.index')->with('success', 'Empresa removida com sucesso.');
        } catch (Exception $e) {
            Log::error("Erro ao deletar empresa ID {$empresa->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Erro: Verifique se existem registros (alunos/faturas) vinculados.');
        }
    }

    /**
     * NOTIFICAÇÃO EXTERNA
     * Envia e-mail para a Soutec Digital informando sobre novos parceiros no Hub.
     */
    private function notificarCadastroAdmin($empresa)
    {
        try {
            $adminEmail = 'soutecdigital@gmail.com';
            $usuarioQueCadastrou = Auth::user()->name;

            Mail::raw("🚀 ALERTA DE NOVO CADASTRO - LABORAL HUB\n\n" .
                "A empresa '{$empresa->nome_fantasia}' acaba de ser cadastrada por {$usuarioQueCadastrou}.\n" .
                "Localização: {$empresa->cidade}-{$empresa->estado}\n" .
                "Acesse o painel para validar o contrato.", function ($message) use ($adminEmail) {
                $message->to($adminEmail)->subject('🔔 Nova Empresa no Sistema');
            });
        } catch (Exception $e) {
            Log::error("Falha ao notificar admin: " . $e->getMessage());
        }
    }
}