<?php

namespace App\Http\Controllers;

use App\Models\Escala;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Presenca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AgendaSocioController extends Controller
{
    /**
     * INDEX: Grade Semanal com Busca Sob Demanda (Poka-Yoke de Performance)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Definição de Período (Poka-Yoke de Navegação)
        $dataRef = $request->get('data') ? \Carbon\Carbon::parse($request->get('data')) : \Carbon\Carbon::now();
        $inicioSemana = $dataRef->copy()->startOfWeek();
        $fimSemana = $dataRef->copy()->endOfWeek();
        $hoje = \Carbon\Carbon::now()->startOfDay();

        // 2. Dados Globais de Apoio
        $empresas_lista = Empresa::where('ativo', true)->orderBy('nome_fantasia')->get();
        $professores = User::where('perfil', 'professor')->with('configuracaoAtual')->get();

        // 3. Inicialização de Variáveis
        $empresas = collect();
        $escalas = collect();
        $empresaSelecionada = $request->get('empresa_id');

        // --- LÓGICA POR PERFIL ---

        if ($user->perfil === 'professor') {
            // O Professor vê as escalas DELE em qualquer empresa nesta semana
            $empresas = Empresa::where('ativo', true)->get(); // Para ele saber o nome da unidade no card

            $escalas = Escala::with(['professor'])
                ->where('user_id', $user->id) // Trava no ID do professor logado
                ->whereBetween('data', [$inicioSemana->format('Y-m-d'), $fimSemana->format('Y-m-d')])
                ->get()
                ->groupBy([
                    'empresa_id',
                    function ($item) {
                        return \Carbon\Carbon::parse($item->data)->format('Y-m-d');
                    }
                ]);

            return view('agenda.professor', compact('empresas', 'escalas', 'inicioSemana', 'hoje'));
        }

        // --- LÓGICA PARA ADMIN / SÓCIO (Gestão Sob Demanda) ---
        if ($empresaSelecionada) {
            $empresas = Empresa::where('id', $empresaSelecionada)->get();

            $escalas = Escala::with(['professor'])
                ->where('empresa_id', (int)$empresaSelecionada)
                ->whereBetween('data', [$inicioSemana->format('Y-m-d'), $fimSemana->format('Y-m-d')])
                ->get()
                ->groupBy([
                    'empresa_id',
                    function ($item) {
                        return \Carbon\Carbon::parse($item->data)->format('Y-m-d');
                    }
                ]);
        }

        return view('agenda.index', compact(
            'empresas',
            'empresas_lista',
            'professores',
            'escalas',
            'inicioSemana',
            'hoje'
        ));
    }

    /**
     * SALVAR ESCALA: Lógica Anti-Duplicidade com Auditoria Automática
     * @author SouTecDigital - Laboral Hub
     */
    public function salvarEscala(Request $request)
    {
        // 1. LOCALIZAÇÃO DO REGISTRO: Prioriza ID, senão busca por chave única (User + Empresa + Data)
        $escalaExistente = $request->id ? Escala::find($request->id) : null;

        if (!$escalaExistente) {
            $escalaExistente = Escala::where('user_id', $request->user_id)
                ->where('empresa_id', $request->empresa_id)
                ->where('data', $request->data)
                ->first();
        }

        // 2. TRAVA DE SEGURANÇA: Bloqueia edição de aula confirmada para não-admins
        if ($escalaExistente && $escalaExistente->status_presenca === 'confirmada' && Auth::user()->perfil !== 'admin') {
            return redirect()->back()->with('error', 'Ação Bloqueada: Apenas administradores alteram escalas confirmadas.');
        }

        // 3. FLUXO DE CANCELAMENTO
        if ($request->status_cancelamento === 'cancelado') {
            if (empty($request->observacao_cancelamento)) {
                return redirect()->back()->with('error', 'Justificativa obrigatória para cancelamento.');
            }

            if ($escalaExistente) {
                $escalaExistente->update([
                    'status_cancelamento'     => 'cancelado',
                    'status_presenca'         => 'pendente',
                    'data_cancelamento'       => now(),
                    'user_cancelamento_id'    => Auth::id(),
                    'observacao_cancelamento' => $request->observacao_cancelamento,
                    'checkin'                 => null, // Remove checkin em caso de cancelamento
                    'solicitou_ajuste'        => 0
                ]);
                return redirect()->back()->with('success', 'Escala cancelada com sucesso.');
            }
            return redirect()->back()->with('error', 'Escala não encontrada.');
        }

        // 4. FLUXO DE SALVAMENTO / VALIDAÇÃO
        else {
            // INTELEGÊNCIA DE VALOR: Busca preço dinâmico baseado na modalidade
            $professor = User::with('configuracaoAtual')->find($request->user_id);
            $tipo = $request->tipo_aula ?? 'normal';

            $valorSugerido = match ($tipo) {
                'online' => $professor->configuracaoAtual->valor_aula_online ?? 0,
                'avulso' => $professor->configuracaoAtual->valor_aula_avulso ?? 0,
                default  => $professor->configuracaoAtual->valor_aula ?? 0,
            };

            // Poka-Yoke: Se o Admin digitou valor manual, ele prevalece, senão usa o sugerido
            $valorFinal = ($request->valor_venda_avulso > 0) ? $request->valor_venda_avulso : $valorSugerido;

            if ($valorFinal <= 0) {
                return redirect()->back()->withInput()->with('error', '⚠️ Erro Financeiro: Professor sem valor configurado.');
            }

            // --- 🛡️ LÓGICA DE AUDITORIA RETROATIVA ---
            $statusFinal = 'pendente';
            $checkinManual = $escalaExistente ? $escalaExistente->checkin : null;
            $dataEscala = \Carbon\Carbon::parse($request->data)->startOfDay();
            $hoje = \Carbon\Carbon::now()->startOfDay();

            // Poka-Yoke de String: Evita erro de Undefined Property no Request
            $observacaoFinal = $request->input('observacao', '');

            /**
             * REGRA DE NEGÓCIO: Se a data for PASSADA e o usuário for ADMIN/SÓCIO,
             * o sistema automaticamente CONFIRMA a presença e carimba o log.
             */
            if (Auth::user()->perfil === 'admin' || Auth::user()->perfil === 'socio') {
                if ($dataEscala->lt($hoje) || ($escalaExistente && $escalaExistente->solicitou_ajuste)) {
                    $statusFinal = 'confirmada';
                    $checkinManual = $checkinManual ?? now(); // Grava hora atual se não houver checkin

                    // Carimbo de Auditoria (Rastro do Analista)
                    $observacaoFinal .= " [Validado manualmente por Sócio em " . now()->format('d/m H:i') . "]";
                }
            }

            // 5. GRAVAÇÃO BLINDADA (UpdateOrCreate)
            Escala::updateOrCreate(
                [
                    'user_id'    => $request->user_id,
                    'empresa_id' => $request->empresa_id,
                    'data'       => $request->data
                ],
                [
                    'turno'               => $request->turno,
                    'valor_venda_avulso'  => $valorFinal,
                    'status_presenca'     => $statusFinal,
                    'checkin'             => $checkinManual,
                    'status_cancelamento' => 'ativo',
                    'observacao'          => $observacaoFinal,
                    'tipo_aula'           => $tipo,
                    'solicitou_ajuste'    => 0 // Zera a flag: a solicitação foi atendida!
                ]
            );

            return redirect()->back()->with('success', 'Escala processada e auditada com sucesso!');
        }
    }

    /**
     * MÉTODO: solicitarAjuste
     * Objetivo: O professor sinaliza que esqueceu de bater o ponto em uma data passada.
     * 🛡️ SEGURANÇA: Impede múltiplas solicitações para a mesma escala
     */
    public function solicitarAjuste($id)
    {
        // 1. Busca a escala com a empresa (usando Eager Loading para o front ser rápido)
        $escala = Escala::with('empresa')->findOrFail($id);

        // 2. Segurança: Verifica se o ID do professor logado bate com o dono da escala
        if ($escala->user_id !== \Illuminate\Support\Facades\Auth::id()) {
            return redirect()->route('agenda.index')->with('error', '❌ Acesso negado: Esta escala não pertence ao seu perfil.');
        }

        // 3. 🛡️ TRAVA DUPLA: Se já foi solicitado, NÃO PROCESSA
        if ($escala->solicitou_ajuste == 1) {
            return redirect()->route('agenda.index')->with('warning', '⚠️ Você já enviou uma solicitação para esta aula. Por favor, aguarde a análise do Sócio.');
        }

        // 4. Regra de Negócio: Só permite se estiver Pendente (não confirmada)
        if ($escala->status_presenca !== 'pendente' && !$escala->checkin) {
            // Se estiver confirmada, não precisa de ajuste
            if ($escala->status_presenca === 'confirmada' || $escala->checkin) {
                return redirect()->route('agenda.index')->with('info', '✅ Esta escala já consta como confirmada no sistema. Nenhum ajuste é necessário.');
            }
        }

        // 5. Captura a observação atual para concatenar
        $obsAtual = $escala->observacao ?? '';
        $novaObs = $obsAtual . "\n[⏱️ Solicitação de Ajuste enviada em " . now()->format('d/m/Y H:i') . " pela Professora]";

        $escala->update([
            'solicitou_ajuste' => 1,
            'observacao' => $novaObs
        ]);

        // 6. Retorna a View de Sucesso
        return view('agenda.solicitar_ajuste', compact('escala'));
    }


    public function checkinProfessor(Request $request, $escala_id)
    {
        $escala = Escala::findOrFail($escala_id);

        // 🛡️ SEGURANÇA: Validação de data - professor só pode confirmar HOJE
        $hojeData = \Carbon\Carbon::now()->format('Y-m-d');
        $dataEscala = \Carbon\Carbon::parse($escala->data)->format('Y-m-d');

        if ($dataEscala !== $hojeData) {
            return redirect()->back()->with('error', '❌ Erro: Você só pode confirmar presença do dia de hoje! Esta aula é para: ' . \Carbon\Carbon::parse($escala->data)->format('d/m/Y'));
        }

        // 🛡️ SEGURANÇA: Trava dupla - se já foi confirmada, não processa de novo
        if ($escala->status_presenca === 'confirmada') {
            return redirect()->back()->with('info', '✅ Esta presença já foi confirmada anteriormente.');
        }

        // 🛡️ SEGURANÇA: Verifica se o professor logado é dono da escala
        if ($escala->user_id !== \Illuminate\Support\Facades\Auth::id()) {
            return redirect()->back()->with('error', '❌ Acesso negado: Esta escala não pertence ao seu perfil.');
        }

        // Poka-Yoke de Auditoria - Mensagens contextualizadas por tipo de check-in
        $msgAudit = "";

        if ($request->lat_prof && $request->lng_prof) {
            // GPS foi capturado
            if ($request->geo_valid == 1) {
                $msgAudit = " [✅ Check-in Localizado - Dentro do raio da empresa]";
            } else {
                // Fora do raio - Coordenadas foram registradas
                $msgAudit = " [⚠️ Check-in Fora do Raio - Lat: {$request->lat_prof}, Lng: {$request->lng_prof}]";
            }
        } else {
            // GPS não foi capturado - Confirmação apenas por horário
            $msgAudit = " [⏱️ Check-in por Horário - GPS não disponível]";
        }

        $escala->update([
            'status_presenca' => 'confirmada',
            'checkin' => now(),
            'lat_prof' => $request->lat_prof,
            'lng_prof' => $request->lng_prof,
            'geo_valid' => $request->geo_valid,
            'observacao' => $escala->observacao . $msgAudit
        ]);

        return redirect()->back()->with('success', '✅ Presença confirmada com sucesso!');
    }

        /**
        * Insert e  tabela presenca para manter integridade e histórico de presença do aluno, mesmo que a escala seja editada posteriormente.
         * @author SouTecDigital - Laboral Hub
        */  


public function store(Request $request) 
{
    // 1. Validação dos dados vindo do formulário do Sócio
    $request->validate([
        'professor_id' => 'required', // O professor selecionado
        'empresa_id'   => 'required', // A fábrica/unidade
        'data'         => 'required|date',
        'turno'        => 'required'
    ]);

    // 🛡️ POKA-YOKE: Transação garante que ou grava os dois (Escala e Presença) ou nenhum.
    DB::beginTransaction();

    try {
        // 2. Criar a Escala (O agendamento do Sócio)
        $escala = new Escala();
        $escala->user_id    = $request->professor_id; 
        $escala->empresa_id = $request->empresa_id;
        $escala->data       = $request->data;
        $escala->turno      = $request->turno;
        $escala->tipo_aula  = $request->tipo_aula;
        // ... outros campos como valor_venda_avulso
        $escala->save();

        // 3. Criar a Presença (O espelho da agenda para auditoria)
        $presenca = new Presenca();
        
        // Quem está criando (Sócio logado)
        $presenca->user_id      = auth::id(); 
        
        // O Professor que deve estar lá
        $presenca->professor_id = $escala->user_id; 
        
        // A Fábrica específica desta aula
        $presenca->empresa_id   = $escala->empresa_id;
        
        // Data da agenda
        $presenca->data_presenca = $escala->data;
        
        // 🛡️ POKA-YOKE: Hora fica NULL. 
        // Só será preenchida quando o Professor fizer o Check-in na agenda dele.
        $presenca->hora_presenca = null; 
        
        $presenca->observacoes = "Agendado para fábrica: " . $escala->empresa->nome_fantasia;
        
        $presenca->save();

        DB::commit();
        return redirect()->back()->with('success', 'Agenda confirmada e presença preparada!');

    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()->with('error', 'Erro industrial: ' . $e->getMessage());
    }
}

















    /**
     * MÉTODO: financeiro
     */
    public function financeiro(Request $request)
    {
        return view('agenda_socio.financeiro');
    }
}
