<?php

namespace App\Http\Controllers;

use App\Models\Escala;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Presenca;
use App\Models\EmpresaUserPresenca;
use App\Models\ProfessorConfiguracao;
use App\Models\ProfessorPagamento;
use App\Models\LocalizacaoProfEmp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AgendaSocioController extends Controller
{
    /**
     * INDEX: Grade Semanal
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $dataRef = $request->get('data') ? Carbon::parse($request->get('data')) : Carbon::now();
        $inicioSemana = $dataRef->copy()->startOfWeek();
        $fimSemana = $dataRef->copy()->endOfWeek();
        $hoje = Carbon::now()->startOfDay();

        $empresas_lista = Empresa::where('ativo', true)->orderBy('nome_fantasia')->get();
        $professores = User::where('perfil', 'professor')->with('configuracaoAtual')->get();

        $empresas = collect();
        $escalas = collect();
        $empresaSelecionada = $request->get('empresa_id');

        if ($user->perfil === 'professor') {
            $empresas = Empresa::where('ativo', true)->get();
            $escalas = Escala::with(['professor'])
                ->where('user_id', $user->id)
                ->whereBetween('data', [$inicioSemana->format('Y-m-d'), $fimSemana->format('Y-m-d')])
                ->get()
                ->groupBy(['empresa_id', function ($item) {
                    return Carbon::parse($item->data)->format('Y-m-d');
                }]);
            return view('agenda.professor', compact('empresas', 'escalas', 'inicioSemana', 'hoje'));
        }

        if ($empresaSelecionada) {
            $empresas = Empresa::where('id', $empresaSelecionada)->get();
            $escalas = Escala::with(['professor'])
                ->where('empresa_id', (int)$empresaSelecionada)
                ->whereBetween('data', [$inicioSemana->format('Y-m-d'), $fimSemana->format('Y-m-d')])
                ->get()
                ->groupBy(['empresa_id', function ($item) {
                    return Carbon::parse($item->data)->format('Y-m-d');
                }]);
        }

        return view('agenda.index', compact('empresas', 'empresas_lista', 'professores', 'escalas', 'inicioSemana', 'hoje'));
    }

    /**
     * SALVAR ESCALA (Ação do Sócio)
     * 🛡️  O Sócio apenas reserva a agenda. A Presença não é criada aqui para evitar duplicidade.
     */
    public function salvarEscala(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $escalaExistente = $request->id ? Escala::find($request->id) : null;
            if (!$escalaExistente) {
                $escalaExistente = Escala::where('user_id', $request->user_id)
                    ->where('empresa_id', $request->empresa_id)
                    ->where('data', $request->data)
                    ->first();
            }
            // --- BLOCO 2: TRAVA DE SEGURANÇA (PERMISSÕES) ---
            // 🛡️  Se a aula já foi confirmada, apenas ADMIN ou SOCIO podem alterar.
            // Se o perfil do usuário logado NÃO FOR 'admin' E NÃO FOR 'socio', ele é barrado.
            if ($escalaExistente && $escalaExistente->status_presenca === 'confirmada') {
                if (Auth::user()->perfil !== 'admin' && Auth::user()->perfil !== 'socio') {
                    return redirect()->back()->with('error', 'Ação bloqueada: Apenas gestores alteram escalas confirmadas.');
                }
            }

            // Fluxo de Cancelamento
            if ($request->status_cancelamento === 'cancelado') {
                if ($escalaExistente) {
                    $escalaExistente->update([
                        'status_cancelamento' => 'cancelado',
                        'data_cancelamento' => now(),
                        'user_cancelamento_id' => Auth::id()
                    ]);
                    // Remove presença se houver
                    Presenca::where('professor_id', $escalaExistente->user_id)
                        ->where('data_presenca', $escalaExistente->data)
                        ->delete();
                    return redirect()->back()->with('success', 'Escala cancelada.');
                }
            }

            // 🛡️  Bloqueia duplicidade de turno para o mesmo professor (restrição de chave única no banco)
            $conflito = Escala::where('user_id', $request->user_id)
                ->where('data', $request->data)
                ->where('turno', $request->turno)
                ->where('status_cancelamento', '!=', 'cancelado');
                
            if ($escalaExistente) {
                $conflito->where('id', '!=', $escalaExistente->id);
            }
            
            if ($conflito->exists()) {
                return redirect()->back()->with('error', 'O professor já possui uma escala ativa neste turno.');
            }

            // Lógica de Valor
            $professor = User::with('configuracaoAtual')->find($request->user_id);
            $tipo = $request->input('tipo_aula', 'normal');
            $valorManual = floatval($request->input('valor_venda_avulso', 0));
            $config = $professor->configuracaoAtual;
            $valorTipo = $config ? match ($tipo) {
                'online' => $config->valor_aula_online ?? 0,
                'avulso' => $config->valor_aula_avulso ?? 0,
                default => $config->valor_aula ?? 0,
            } : 0;
            $valorFinal = ($valorManual > 0) ? $valorManual : $valorTipo;

            $dataEscala = Carbon::parse($request->data)->startOfDay();
            $hoje = Carbon::now()->startOfDay();
            $statusFinal = 'pendente';
            $checkinManual = $escalaExistente ? $escalaExistente->checkin : null;
            $observacaoFinal = $request->input('observacao', '');
            // 🛡️  Verifica se o perfil é 'admin' ou 'socio' (sem parênteses no perfil)
            if (Auth::user()->perfil === 'admin' || Auth::user()->perfil === 'socio') {

                // Se a data for PASSADA (lt = less than) ou houver pedido de ajuste
                if ($dataEscala->lt($hoje) || ($escalaExistente && $escalaExistente->solicitou_ajuste)) {
                    $statusFinal = 'confirmada';
                    $checkinManual = $checkinManual ?? now();
                    $observacaoFinal .= " [Validado manualmente por " . Auth::user()->name . "]";
                    
                    // 🚀 NOVO: Cria presencas em empresa_user_presenca quando sócio valida aula passada
                    $escalaTemp = $escalaExistente ?? new Escala([
                        'empresa_id' => $request->empresa_id,
                        'user_id' => $request->user_id,
                        'data' => $request->data
                    ]);
                    $this->criarPresencasAlunos($escalaTemp);
                }
            }

            Escala::updateOrCreate(
                ['user_id' => $request->user_id, 'empresa_id' => $request->empresa_id, 'data' => $request->data],
                [
                    'turno' => $request->turno,
                    'valor_venda_avulso' => $valorFinal,
                    'status_presenca' => $statusFinal,
                    'checkin' => $checkinManual,
                    'tipo_aula' => $tipo,
                    'status_cancelamento' => 'ativo',
                    'solicitou_ajuste' => 0,
                    'observacao' => $observacaoFinal
                ]
            );

            return redirect()->back()->with('success', 'Escala processada com sucesso!');
        });
    }

    /**
     * CHECKIN DO PROFESSOR (Ação na Blade do Professor)
     * 🚀 Aqui a Presença nasce oficialmente.
     * 🛡️ NOVO: Registra auditoria de localização com Haversine
     */
    public function checkinProfessor(Request $request, $escala_id)
    {
        $escala = Escala::findOrFail($escala_id);

        if (Carbon::parse($escala->data)->format('Y-m-d') !== Carbon::now()->format('Y-m-d')) {
            return redirect()->back()->with('error', '❌ Erro: Só pode confirmar presença hoje!');
        }

        return DB::transaction(function () use ($request, $escala) {
            // 🛡️ NOVO: Determina tipo de confirmação (GPS ou Horário)
            $temGPS = $request->lat_prof && $request->lng_prof;
            $msgAudit = $temGPS ? " [ GPS Localizado]" : " [⏱️ Horário]";
            $tipoConfirmacao = $temGPS ? 'gps' : 'horario';

            // --- AUDITORIA DE LOCALIZAÇÃO ---
            $empresa = $escala->empresa;
            $distanciaMetros = null;
            $dentroRaio = false;
            $motivoGPSFraco = null;

            if ($temGPS && $empresa->lat && $empresa->lng) {
                // Calcula distância usando Haversine
                $distanciaMetros = LocalizacaoProfEmp::calcularDistanciaHaversine(
                    (float)$empresa->lat,
                    (float)$empresa->lng,
                    (float)$request->lat_prof,
                    (float)$request->lng_prof
                );
                
                $raioTolerancia = (float)($empresa->raio_gps_metros ?? 500);
                $dentroRaio = $distanciaMetros <= $raioTolerancia;

                if (!$dentroRaio) {
                    $msgAudit .= " ⚠️ FORA DO RAIO ({$distanciaMetros}m > {$raioTolerancia}m)";
                }
            } elseif (!$temGPS) {
                $motivoGPSFraco = $request->motivo_gps_fraco ?? 'Sinal GPS fraco ou não disponível';
            }

            $escala->update([
                'status_presenca' => 'confirmada',
                'checkin' => now(),
                'lat_prof' => $request->lat_prof,
                'lng_prof' => $request->lng_prof,
                'geo_valid' => $temGPS ? true : false,
                'observacao' => $escala->observacao . $msgAudit
            ]);

            // 📍 NOVO: Registra auditoria de localização
            LocalizacaoProfEmp::create([
                'escala_id' => $escala->id,
                'professor_id' => $escala->user_id,
                'empresa_id' => $escala->empresa_id,
                'empresa_lat' => $empresa->lat,
                'empresa_lng' => $empresa->lng,
                'empresa_raio_metros' => $empresa->raio_gps_metros ?? 500,
                'prof_lat' => $request->lat_prof,
                'prof_lng' => $request->lng_prof,
                'distancia_metros' => $distanciaMetros,
                'dentro_raio' => $dentroRaio,
                'tipo_confirmacao' => $tipoConfirmacao,
                'motivo_gps_fraco' => $motivoGPSFraco,
                'confirmado_em' => now(),
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
            ]);

            // 💰 NOVO: Lançamento Automático Financeiro
            // Busca a configuração de valores do professor (vigente na data da escala)
            $config = ProfessorConfiguracao::where('user_id', $escala->user_id)
                ->where('data_inicio_vigencia', '<=', $escala->data)
                ->orderBy('data_inicio_vigencia', 'desc')
                ->first();

            if ($config) {
                // Define o valor com base no tipo de aula da escala
                $valorItem = match($escala->tipo_aula) {
                    'online' => $config->valor_aula_online,
                    'avulso' => $config->valor_aula_avulso,
                    default  => $config->valor_aula, // presencial (normal)
                };

                // Verifica se já existe lançamento para evitar duplicidade (poka-yoke)
                $pagamentoExiste = ProfessorPagamento::where('escala_id', $escala->id)->exists();

                if (!$pagamentoExiste && $valorItem > 0) {
                    // Insere na tabela de pagamentos
                    ProfessorPagamento::create([
                        'escala_id'        => $escala->id,
                        'user_id'          => $escala->user_id,
                        'valor_pago'       => $valorItem,
                        'data_referencia'  => $escala->data,
                        'status_pagamento' => 'pendente'
                    ]);
                }
            }

            // NOVO: Insere em empresa_user_presenca para os alunos confirmarem
            $this->criarPresencasAlunos($escala);

            return redirect()->back()->with('success', ' Presença confirmada!');
        });
    }

    /**
     * Cria registros de presença para todos os alunos confirmarem
     * presenca = '0' (aluno ainda não confirmou)
     */
    private function criarPresencasAlunos($escala)
    {
        Log::info('=== Iniciando criarPresencasAlunos ===', [
            'empresa_id' => $escala->empresa_id,
            'professor_id' => $escala->user_id,
            'data' => $escala->data
        ]);

        try {
            // Busca todos os alunos vinculados à empresa
            $alunosEmpresa = DB::table('empresa_user')
                ->where('empresa_id', $escala->empresa_id)
                ->pluck('user_id')
                ->toArray();

            Log::info('Alunos encontrados: ' . count($alunosEmpresa), ['alunos' => $alunosEmpresa]);

            // Para cada aluno, cria um registro em empresa_user_presenca
            foreach ($alunosEmpresa as $alunoId) {
                $result = EmpresaUserPresenca::firstOrCreate(
                    [
                        'professor_id' => $escala->user_id,
                        'user_id' => $alunoId,
                        'empresa_id' => $escala->empresa_id,
                    ],
                    [
                        'presenca' => '0', // ✅ STRING: '0' = pendente de confirmação
                        'ativo' => true,
                        'created_at' => Carbon::now()
                    ]
                );
                Log::info('Presença criada/atualizada', ['id' => $result->id, 'aluno_id' => $alunoId]);
            }
            
            Log::info('=== criarPresencasAlunos finalizado com sucesso ===');
        } catch (\Exception $e) {
            Log::error('Erro ao criar presenças dos alunos: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function solicitarAjuste($id)
    {
        $escala = Escala::with('empresa')->findOrFail($id);
        if ($escala->user_id !== Auth::id()) return redirect()->back();

        $escala->update([
            'solicitou_ajuste' => 1,
            'observacao' => ($escala->observacao ?? '') . "\n[⏱️ Ajuste solicitado em " . now()->format('d/m H:i') . "]"
        ]);
        return view('agenda.solicitar_ajuste', compact('escala'));
    }

    public function financeiro()
    {
        return view('agenda_socio.financeiro');
    }
}
