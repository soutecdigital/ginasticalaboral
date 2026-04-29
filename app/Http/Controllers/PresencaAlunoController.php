<?php

namespace App\Http\Controllers;

use App\Models\Escala;
use App\Models\Presenca;
use App\Models\EmpresaUserPresenca;
use App\Models\User;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PresencaAlunoController extends Controller
{
    /**
     * Mostra as aulas disponíveis para o aluno registrar presença
     */
    public function index(Request $request)
    {
        $aluno = Auth::user();
        $hoje = Carbon::now()->format('Y-m-d');
        
        // 🛡️ POKA-YOKE: Pega as empresas vinculadas ao aluno
        $empresasAluno = $aluno->empresas()->pluck('empresa_id');
        
        if ($empresasAluno->isEmpty()) {
            return redirect()->back()->with('warning', '⚠️ Você não está vinculado a nenhuma empresa!');
        }

        // 📅 BUSCA ESCALAS apenas do dia atual nas empresas do aluno
        $escalas = Escala::whereIn('empresa_id', $empresasAluno)
            ->where('data', $hoje)
            ->with(['professor', 'empresa'])
            ->orderBy('turno')
            ->get();

        // 🔍 VERIFICA quais escalas o aluno já confirmou presença
        // Retorna array com professor_id => true se confirmado
        $presencasConfirmadas = EmpresaUserPresenca::where('user_id', $aluno->id)
            ->whereIn('empresa_id', $empresasAluno)
            ->where('presenca', '1')
            ->pluck('professor_id')
            ->toArray();

        // 📊 AGRUPA escalas por dia (será apenas hoje)
        $escalasAgrupadas = $escalas->groupBy('data');

        // 📋 BUSCA presencas para a aba de relatório (sem paginação na mesma página)
        $presencas = Presenca::where('user_id', $aluno->id)
            ->whereIn('empresa_id', $empresasAluno)
            ->with(['professor', 'empresa'])
            ->orderByDesc('data_presenca')
            ->limit(10)
            ->get();

        return view('aluno.presenca.index', [
            'escalas' => $escalas,
            'escalasAgrupadas' => $escalasAgrupadas,
            'presencasConfirmadas' => $presencasConfirmadas,
            'presencas' => $presencas,
            'hoje' => $hoje,
            'aluno' => $aluno,
        ]);
    }

    /**
     * Aluno confirma sua presença
     * Faz UPDATE em empresa_user_presenca mudando presenca de 0 para 1
     */
    public function confirmarPresenca(Request $request, $escala_id)
    {
        $escala = Escala::findOrFail($escala_id);
        $aluno = Auth::user();

        // 🛡️ VALIDAÇÕES
        $validar = $request->validate([
            'observacoes' => 'nullable|string|max:500',
        ]);

        // ✅ VERIFICAÇÃO: O aluno está vinculado à empresa desta aula?
        $vinculoEmpresa = $aluno->empresas()
            ->where('empresa_id', $escala->empresa_id)
            ->exists();

        if (!$vinculoEmpresa) {
            return redirect()->back()->with('error', '❌ Você não está vinculado a esta empresa!');
        }

        // 🚫 VERIFICAÇÃO: A aula já passou?
        if (Carbon::parse($escala->data)->lt(Carbon::now()->startOfDay())) {
            return redirect()->back()->with('error', '❌ Não é possível registrar presença em aulas passadas!');
        }

        // 🔄 TRANSACTION para garantir integridade
        return DB::transaction(function () use ($aluno, $escala, $request) {
            // 📝 UPDATE do registro de presença em empresa_user_presenca
            // Busca o registro criado pelo professor quando marcou a aula
            $presenca = EmpresaUserPresenca::where([
                'professor_id' => $escala->user_id,
                'user_id' => $aluno->id,
                'empresa_id' => $escala->empresa_id,
            ])->first();

            if (!$presenca) {
                return redirect()->back()->with('error', '❌ Registro de presença não encontrado. Professor ainda não marcou a aula!');
            }

            // ✅ Marca presença como confirmada
            $presenca->update(['presenca' => '1']);

            return redirect()->back()->with('success', '✅ Presença confirmada com sucesso!');
        });
    }

    /**
     * Relatório de presenças do aluno
     */
    public function relatorio()
    {
        $aluno = Auth::user();
        
        // 🛡️ POKA-YOKE: Pega as empresas vinculadas
        $empresasAluno = $aluno->empresas()->pluck('empresa_id');

        // 📊 BUSCA histórico de presenças
        $presencas = Presenca::where('user_id', $aluno->id)
            ->whereIn('empresa_id', $empresasAluno)
            ->with(['professor', 'empresa'])
            ->orderByDesc('data_presenca')
            ->paginate(20);

        return view('aluno.presenca.relatorio', [
            'presencas' => $presencas,
            'aluno' => $aluno,
        ]);
    }

    /**
     * 📋 Histórico de presenças do aluno com filtro por data
     * Otimizado para mobile 3G com paginação de 5 registros
     */
    public function historico(Request $request)
    {
        $usuario = Auth::user();
        
        // 🛡️ SEGURANÇA: Apenas alunos podem ver este relatório
        if ($usuario->perfil !== 'aluno') {
            abort(403, '❌ Acesso negado! Apenas alunos podem visualizar este relatório.');
        }
        
        // 🛡️ SEGURANÇA: O aluno só pode ver seu próprio histórico
        $alunoId = $request->route('aluno_id');
        if ($usuario->id != $alunoId) {
            abort(403, '❌ Acesso negado! Você só pode ver seu próprio histórico.');
        }

        // 🛡️ POKA-YOKE: Pega as empresas vinculadas ao aluno
        $empresasAluno = $usuario->empresas()->pluck('empresa_id');

        // 📅 PROCESSA filtro de datas
        $dataInicio = $request->input('data_inicio') 
            ? Carbon::createFromFormat('Y-m-d', $request->input('data_inicio')) 
            : Carbon::now()->subMonths(3);
        
        $dataFim = $request->input('data_fim') 
            ? Carbon::createFromFormat('Y-m-d', $request->input('data_fim')) 
            : Carbon::now();

        // 🔍 BUSCA na tabela empresa_user_presenca apenas presenças do aluno
        $query = EmpresaUserPresenca::where('user_id', $usuario->id)
            ->whereIn('empresa_id', $empresasAluno)
            ->with(['aluno', 'professor', 'empresa']);

        // ⏰ Aplica filtro de datas
        if ($request->has('filtro')) {
            $query->whereBetween('created_at', [
                $dataInicio->startOfDay(),
                $dataFim->endOfDay()
            ]);
        }

        // 📊 Ordena por data descendente e pagina (5 por página - light para 3G)
        $presenças = $query->orderByDesc('created_at')
            ->paginate(5)
            ->appends($request->query());

        // 🚫 VERIFICA se há faltas (presenca = '0') do aluno
        $temFaltas = EmpresaUserPresenca::where('user_id', $usuario->id)
            ->whereIn('empresa_id', $empresasAluno)
            ->where('presenca', '0')
            ->exists();

        // 💪 MENSAGENS MOTIVACIONAIS (se não houver faltas)
        $mensagensMotivacionais = [
            ['emoji' => '🌟', 'texto' => 'Você é incrível! Continue com essa dedicação!', 'texto2' => 'Sua consistência é inspiradora! 🚀'],
            ['emoji' => '💪', 'texto' => 'Parabéns pela sua dedicação!', 'texto2' => 'Você está no caminho certo! 🎯'],
            ['emoji' => '🏆', 'texto' => 'Que apresentação perfeita!', 'texto2' => 'Você é um exemplo para todos! ✨'],
            ['emoji' => '🎉', 'texto' => 'Excelente! Você não faltou nenhuma aula!', 'texto2' => 'Sua disciplina é admirável! 🙌'],
            ['emoji' => '⭐', 'texto' => 'Fantástico! Sua frequência é perfeita!', 'texto2' => 'Continue assim, campeão! 🥇'],
        ];

        $mensagemMotivacional = $mensagensMotivacionais[array_rand($mensagensMotivacionais)];

        return view('aluno.presenca.historico', [
            'presenças' => $presenças,
            'aluno' => $usuario,
            'dataInicio' => $dataInicio->format('Y-m-d'),
            'dataFim' => $dataFim->format('Y-m-d'),
            'temFaltas' => $temFaltas,
            'mensagemMotivacional' => $mensagemMotivacional,
        ]);
    }

    /**
     * 📋 Relatório de Presença - Visão do Professor
     * Mostra lista de presença dos alunos do professor com filtro por data
     */
    public function relatorioPresencaProfessor(Request $request)
    {
        $professor = Auth::user();
        
        // 🛡️ SEGURANÇA: Apenas professores podem ver este relatório
        if ($professor->perfil !== 'professor') {
            abort(403, '❌ Acesso negado! Apenas professores podem visualizar este relatório.');
        }

        // 🛡️ POKA-YOKE: Pega as empresas vinculadas ao professor
        $empresasProfessor = $professor->empresas()->pluck('empresa_id');

        // 📅 PROCESSA filtro de datas
        $dataInicio = $request->input('data_inicio') 
            ? Carbon::createFromFormat('Y-m-d', $request->input('data_inicio')) 
            : Carbon::now()->subMonths(3);
        
        $dataFim = $request->input('data_fim') 
            ? Carbon::createFromFormat('Y-m-d', $request->input('data_fim')) 
            : Carbon::now();

        // 🏢 PROCESSA filtro de empresa
        $empresaFiltrada = $request->input('empresa_id');

        // 🔍 BUSCA presenças dos alunos deste professor
        $query = EmpresaUserPresenca::where('professor_id', $professor->id)
            ->whereIn('empresa_id', $empresasProfessor)
            ->with(['aluno', 'professor', 'empresa']);

        // ⏰ Aplica filtro de datas
        if ($request->has('filtro')) {
            $query->whereBetween('created_at', [
                $dataInicio->startOfDay(),
                $dataFim->endOfDay()
            ]);
        }

        // 🏢 Aplica filtro de empresa
        if ($empresaFiltrada && in_array($empresaFiltrada, $empresasProfessor->toArray())) {
            $query->where('empresa_id', $empresaFiltrada);
        }

        // 📊 Ordena por data descendente e pagina (10 por página)
        $presenças = $query->orderByDesc('created_at')
            ->paginate(10)
            ->appends($request->query());

        // 🚫 VERIFICA totais de presenças e faltas
        $totalPresencas = EmpresaUserPresenca::where('professor_id', $professor->id)
            ->whereIn('empresa_id', $empresasProfessor);

        if ($request->has('filtro')) {
            $totalPresencas = $totalPresencas->whereBetween('created_at', [
                $dataInicio->startOfDay(),
                $dataFim->endOfDay()
            ]);
        }

        if ($empresaFiltrada && in_array($empresaFiltrada, $empresasProfessor->toArray())) {
            $totalPresencas = $totalPresencas->where('empresa_id', $empresaFiltrada);
        }

        $totalPresencas = $totalPresencas->get();
        $contagemPresencas = $totalPresencas->where('presenca', '1')->count();
        $contagemFaltas = $totalPresencas->where('presenca', '0')->count();

        // 📋 Pega todas as empresas do professor para o filtro
        $empresasDisponiveis = Empresa::whereIn('id', $empresasProfessor)->get();

        return view('aluno.presenca.relatorio-professor', [
            'presenças' => $presenças,
            'professor' => $professor,
            'dataInicio' => $dataInicio->format('Y-m-d'),
            'dataFim' => $dataFim->format('Y-m-d'),
            'contagemPresencas' => $contagemPresencas,
            'contagemFaltas' => $contagemFaltas,
            'empresasDisponiveis' => $empresasDisponiveis,
            'empresaFiltrada' => $empresaFiltrada,
        ]);
    }
}

