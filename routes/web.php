<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\{
    AuthController,
    AlunosController,
    PresencaController,
    EmpresaController,
    UserController,
    EscolhaEmpresaController,
    FinanceiroController,
    FaturamentoController,
    OuvidoriaController,
    HomeController,
    AgendaSocioController,
    PresencaAlunoController,
    LocalizacaoAuditoriaController,
    RelatorioAlunosController,
    ProfessorLiquidacaoController,
    RelatorioController
};

// --- 1. ÁREA PÚBLICA ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ROTA DE SEEDS - TOTALMENTE ISOLADA DE GRUPOS E MIDDLEWARES
Route::get('/rodar-seeds-obrigatorio', function () {
    try {
        Artisan::call('db:seed', ['--force' => true]);
        return 'Sucesso! O output do Laravel foi: <br>' . nl2br(Artisan::output());
    } catch (\Exception $e) {
        return 'Erro ao rodar seeders: ' . $e->getMessage();
    }
});

// ROTA DE DIAGNÓSTICO - LEITURA DOS ERROS INTERNOS DO LARAVEL (ERRO 500)
Route::get('/ver-meus-erros-secretos', function () {
    $caminhoLog = storage_path('logs/laravel.log');
    
    if (!file_exists($caminhoLog)) {
        return 'Nenhum erro registrado no arquivo laravel.log ainda!';
    }
    
    // Lê o arquivo de log e pega os últimos 4000 caracteres (o final do arquivo)
    $conteudo = file_get_contents($caminhoLog);
    $finalDoLog = substr($conteudo, -4000);
    
    return '<pre style="background: #1e1e1e; color: #f1f1f1; padding: 15px; border-radius: 5px; overflow-x: auto;">' . htmlspecialchars($finalDoLog) . '</pre>';
});

// --- 2. ÁREA RESTRITA ---
Route::middleware(['auth'])->group(function () {

    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/home', [HomeController::class, 'index']);

    // Seleção de Unidade
    Route::get('/escolha-unidade', [EscolhaEmpresaController::class, 'index'])->name('escolha_unidade');
    Route::get('/selecionar-unidade/{id}', [EscolhaEmpresaController::class, 'selecionar'])->name('selecionar_empresa');

    // Cadastros
    Route::resource('empresas', EmpresaController::class);
    Route::resource('usuarios', UserController::class);
    Route::post('/usuarios/{id}/reajuste', [UserController::class, 'reajuste'])->name('professores.reajuste');

    // Extrato de Presenças (Global)
    Route::get('/presencas/index', [PresencaController::class, 'index'])->name('presencas.index');
    Route::get('/presencas/relatorio', [PresencaController::class, 'relatorio'])->name('presencas.relatorio');

    // Agenda
    Route::get('/agenda', [AgendaSocioController::class, 'index'])->name('agenda.index');
    Route::get('/agenda/solicitar-ajuste/{id}', [AgendaSocioController::class, 'solicitarAjuste'])->name('agenda.solicitar_ajuste');
    Route::post('/agenda/checkin/{escala_id}', [AgendaSocioController::class, 'checkinProfessor'])->name('agenda.checkin');

    // Presença do Aluno
    Route::prefix('aluno/presenca')->name('aluno.presenca.')->group(function () {
        Route::get('/', [PresencaAlunoController::class, 'index'])->name('index');
        Route::post('/{escala_id}/confirmar', [PresencaAlunoController::class, 'confirmarPresenca'])->name('confirmar');
        Route::get('/relatorio', [PresencaAlunoController::class, 'relatorio'])->name('relatorio');
        Route::get('/relatorio-professor', [PresencaAlunoController::class, 'relatorioPresencaProfessor'])->name('relatorio_professor');
        Route::get('/historico/{aluno_id}', [PresencaAlunoController::class, 'historico'])->name('historico');
    });

    // --- 3. OPERACIONAL (DEPENDEM DE UNIDADE SELECIONADA) ---
    Route::middleware(['check.empresa'])->group(function () {
        Route::prefix('presencas')->name('presencas.')->group(function () {
            Route::get('/hoje', [PresencaController::class, 'index'])->name('hoje');
            Route::get('/registrar', [PresencaController::class, 'create'])->name('registrar');
            Route::post('/store', [PresencaController::class, 'store'])->name('store');
        });

        Route::get('/alunos', [AlunosController::class, 'index'])->name('alunos.gestao');

        // faturamento da Empresa contratante (Grupo Consolidado)
        Route::prefix('faturamento')->name('faturamento.')->group(function () {
            Route::get('/empresa', [FaturamentoController::class, 'index'])->name('index');
            Route::post('faturamento/baixa/{id}', [FaturamentoController::class, 'darBaixa'])->name('baixa');
            Route::get('/historico/{empresa_id}', [FaturamentoController::class, 'historico'])->name('historico');
        });

        Route::post('/agenda/confirmar-falta', [AgendaSocioController::class, 'confirmarFalta'])->name('agenda.confirmar_falta');
    });

    // --- 4. GESTÃO ESTRATÉGICA (ADMIN / SÓCIO) ---
    Route::middleware(['checkPerfil:admin,socio'])->group(function () {

        Route::prefix('agenda-socio')->name('agenda_socio.')->group(function () {
            Route::get('/', [AgendaSocioController::class, 'index'])->name('index');
            Route::post('/agenda', [AgendaSocioController::class, 'salvarEscala'])->name('agendar');
            Route::get('/fechamento', [AgendaSocioController::class, 'financeiro'])->name('financeiro');
        });

        Route::prefix('relatorio/alunos')->name('relatorio.alunos.')->group(function () {
            Route::get('/', [RelatorioAlunosController::class, 'index'])->name('index');
            Route::get('/exportar', [RelatorioAlunosController::class, 'exportarCsv'])->name('exportar');
        });

        // PAGAMENTOS PROFESSORES (Grupo Consolidado)
        Route::prefix('financeiro/professores')->name('financeiro.prof.')->group(function () {
            Route::get('/pagar', [FinanceiroController::class, 'contasPagarProfessor'])->name('pagar');
            Route::post('/confirmar/{escalaId}', [FinanceiroController::class, 'confirmarPresenca'])->name('confirmar');

            // Liquidação e Auditoria NF
            Route::controller(ProfessorLiquidacaoController::class)->group(function () {
                Route::get('/liquidar/{professor_id}', 'create')->name('liquidar');
                Route::post('/liquidar/store', 'store')->name('liquidar.store');
                Route::get('/historico', 'index')->name('liquidar.index');
                Route::get('/liquidar/{id}/detalhes', [ProfessorLiquidacaoController::class, 'show'])->name('liquidar.show');
            });
        });

        // Auditoria
        Route::prefix('auditoria/localizacoes')->name('auditoria.localizacoes.')->group(function () {
            Route::get('/', [LocalizacaoAuditoriaController::class, 'index'])->name('index');
            Route::get('/{id}', [LocalizacaoAuditoriaController::class, 'show'])->name('show');
            Route::get('/relatorio/conformidade', [LocalizacaoAuditoriaController::class, 'relatorio'])->name('relatorio');
            Route::get('/exportar/csv', [LocalizacaoAuditoriaController::class, 'exportar'])->name('exportar');
        });
    });

    // Ouvidoria
    Route::prefix('ouvidoria')->name('ouvidoria.')->group(function () {
        Route::get('/painel', [OuvidoriaController::class, 'index'])->name('index');
        Route::get('/minhas-mensagens', [OuvidoriaController::class, 'minhasMensagens'])->name('aluno');
        Route::post('/store', [OuvidoriaController::class, 'store'])->name('store');
        Route::post('/responder/{id}', [OuvidoriaController::class, 'responder'])->name('responder');
    });

    Route::get('/relatorios/escalas-canceladas', [RelatorioController::class, 'escalasCanceladas'])
        ->name('relatorios.escalas.canceladas');

}); // FIM AUTH
