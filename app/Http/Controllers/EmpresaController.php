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

class EmpresaController extends Controller
{
    public function index()
    {
        // Poka-Yoke: Traz as mais recentes primeiro para facilitar a gestão
        $empresas = Empresa::orderBy('created_at', 'desc')->get();
        return view('empresas.index', compact('empresas'));
    }

    public function create()
    {
        return view('empresas.create');
    }

    public function store(Request $request)
    {
        // [SANITIZAÇÃO] Limpa dados antes da validação
        $request->merge([
            'cnpj' => preg_replace('/\D/', '', $request->cnpj),
            'celular' => preg_replace('/\D/', '', $request->celular ?? '')
        ]);

        $request->validate([
            'nome_fantasia'  => 'required|string|max:255',
            'razao_social'   => 'nullable|string|max:255',
            'cnpj'           => 'required|string|unique:empresas,cnpj',
            'plano'          => 'required|in:basic,pro,premium',
            'valor_contrato' => Auth::user()->perfil === 'admin' ? 'required|numeric|min:0' : 'nullable|numeric',
            'cidade'         => 'required|string',
            'estado'         => 'required|string|max:2',
            'lat'            => 'nullable|numeric',
            'lng'            => 'nullable|numeric',
            'raio_gps_metros'=> 'nullable|numeric|min:0',
        ], [
            'cnpj.unique' => 'Este CNPJ já está cadastrado no sistema.',
            'valor_contrato.required' => 'Defina o valor mensal para gerar a primeira fatura.'
        ]);

        try {
            $msg = 'Empresa cadastrada com sucesso!';

            $empresa = DB::transaction(function () use ($request, &$msg) {
                $dados = $request->all();
                $dados['ativo'] = 1; // Poka-Yoke: Nasce ativa por padrão
                $dados['user_id'] = Auth::id(); // Registra quem cadastrou

                // [BOOLEANOS] Garante tratamento de checkboxes (se não enviado, vira false)
                foreach (['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'] as $dia) {
                    $dados[$dia] = $request->boolean($dia);
                }

                // [COORDENADAS] Normaliza separadores decimais
                if ($request->lat) $dados['lat'] = str_replace(',', '.', $request->lat);
                if ($request->lng) $dados['lng'] = str_replace(',', '.', $request->lng);

                $novaEmpresa = Empresa::create($dados);

                // [FINANCEIRO] Geração automática da primeira fatura para Admins
                if (Auth::user()->perfil === 'admin' && $request->valor_contrato > 0) {
                    $novaEmpresa->faturamentos()->create([
                        'valor_mensalidade' => $request->valor_contrato,
                        'valor_avulso'      => 0,
                        'mes_referencia'    => now()->startOfMonth(),
                        'status'            => 'pendente'
                    ]);
                    $msg = 'Empresa e faturamento gerados com sucesso!';
                }

                return novaEmpresa;
            });

            // [NOTIFICAÇÃO] Fora da transação para não travar o banco se o serviço de email falhar
            $this->notificarCadastroAdmin($empresa);

            return redirect()->route('empresas.index')->with('success', $msg);

        } catch (Exception $e) {
            Log::error("Erro ao salvar empresa: " . $e->getMessage());
            return redirect()->back()->with('error', 'Erro interno ao processar cadastro.')->withInput();
        }
    }

    public function edit(Empresa $empresa)
    {
        // Otimizado: Conta direto da relação mapeada se preferir, ou mantém o count leve
        $totalAlunosAtual = DB::table('empresa_user')->where('empresa_id', $empresa->id)->count();
        
        $ultimaFatura = $empresa->faturamentos()
                                ->orderBy('mes_referencia', 'desc')
                                ->first();

        return view('empresas.edit', compact('empresa', 'totalAlunosAtual', 'ultimaFatura'));
    }

    public function update(Request $request, Empresa $empresa)
    {
        // [SANITIZAÇÃO]
        $request->merge([
            'cnpj' => preg_replace('/\D/', '', $request->cnpj),
            'celular' => preg_replace('/\D/', '', $request->celular ?? '')
        ]);

        $request->validate([
            'nome_fantasia'   => 'required|string|max:255',
            'razao_social'    => 'nullable|string|max:255',
            'cnpj'            => 'required|string|unique:empresas,cnpj,' . $empresa->id,
            'plano'           => 'required|in:basic,pro,premium',
            'ativo'           => 'required|boolean',
            'valor_contrato'  => 'nullable|numeric|min:0',
            'dia_vencimento'  => 'required|integer|min:1|max:31',
            'cidade'          => 'required|string',
            'estado'          => 'required|string|max:2',
            'lat'             => 'nullable|numeric',
            'lng'             => 'nullable|numeric',
            'raio_gps_metros' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $empresa) {
                $dados = $request->all();
                $isAdminOrSocio = in_array(Auth::user()->perfil, ['admin', 'socio']);
                
                // [BOOLEANOS] Garante que dias desmarcados sejam atualizados para 0 (false)
                foreach (['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'] as $dia) {
                    $dados[$dia] = $request->boolean($dia);
                }

                if ($request->lat) $dados['lat'] = str_replace(',', '.', $request->lat);
                if ($request->lng) $dados['lng'] = str_replace(',', '.', $request->lng);

                $valorAntigo = (float) $empresa->valor_contrato;
                $valorNovo = $isAdminOrSocio ? (float) ($request->valor_contrato ?? $valorAntigo) : $valorAntigo;

                // [AUDITORIA] Registra reajuste e projeta próxima fatura
                if ($isAdminOrSocio && $valorNovo != $valorAntigo) {
                    HistoricoContrato::create([
                        'empresa_id'     => $empresa->id,
                        'user_id'        => Auth::id(),
                        'valor_anterior' => $valorAntigo,
                        'valor_novo'     => $valorNovo,
                        'motivo'         => $request->motivo_alteracao ?? 'Reajuste contratual'
                    ]);

                    Faturamento::create([
                        'empresa_id'        => $empresa->id,
                        'valor_mensalidade' => $valorNovo,
                        'mes_referencia'    => now()->addMonth()->startOfMonth(),
                        'status'            => 'pendente'
                    ]);
                }

                // Proteção de Mass Assignment estrutural para Segurança
                if (!$isAdminOrSocio) {
                    unset($dados['valor_contrato'], $dados['dia_vencimento']);
                }

                $empresa->update($dados);
            });

            return redirect()->route('empresas.index')->with('success', 'Cadastro atualizado com sucesso!');

        } catch (Exception $e) {
            Log::error("Erro na atualização da empresa ID {$empresa->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Erro na atualização: Erro interno do servidor.')->withInput();
        }
    }

    public function destroy(Empresa $empresa)
    {
        try {
            // Com SoftDeletes ativo no Model, apenas o 'deleted_at' será preenchido
            $empresa->delete(); 
            return redirect()->route('empresas.index')->with('success', 'Empresa arquivada com sucesso.');
        } catch (Exception $e) {
            Log::error("Erro ao deletar empresa ID {$empresa->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Não foi possível remover a empresa.');
        }
    }

    private function notificarCadastroAdmin($empresa)
    {
        try {
            $adminEmail = 'soutecdigital@gmail.com';
            $usuario = Auth::user()->name ?? 'Sistema';

            Mail::raw("🚀 NOVO PARCEIRO NO HUB\n\nA empresa '{$empresa->nome_fantasia}' foi cadastrada por {$usuario}.\nLocal: {$empresa->cidade}-{$empresa->estado}", function ($message) use ($adminEmail) {
                $message->to($adminEmail)->subject('🔔 Nova Empresa: ' . config('app.name'));
            });
        } catch (Exception $e) {
            // Apenas registra o log, sem quebrar a experiência do usuário final se o servidor de e-mail oscilar
            Log::error("Falha silenciosa na notificação de e-mail: " . $e->getMessage());
        }
    }
}
