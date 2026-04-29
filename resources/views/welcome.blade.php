@extends('layouts.main')

@section('content')
    {{-- 📱 HEADER RESPONSIVO --}}
    <div class="row">
        <div class="col-md-12 text-center py-4 py-md-5">
            <h1 class="display-5 fw-bold antialiased" style="color: #1a2a40;">
                Bem-vindo, {{ explode(' ', Auth::user()->name)[0] }}!
            </h1>

            <div class="d-flex flex-column align-items-center gap-2">
                {{-- Badge da Unidade --}}
                <div class="d-inline-block px-3 py-1 rounded-pill bg-white shadow-sm border">
                    <p class="mb-0 text-muted small">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                        <strong>{{ session('empresa_nome', 'Nenhuma Unidade Selecionada') }}</strong>
                    </p>
                </div>

                {{-- 🚪 Botão Sair Sutil --}}
                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                    @csrf
                    <button title="Sair do sistema" type="submit"
                        class="btn btn-sm text-muted border-0 bg-transparent btn-logout-sutil">
                        <i class="bi bi-box-arrow-right me-1"></i> Sair do sistema
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="row justify-content-center g-3 px-2">

        {{-- 🛡️ CARD DE AGENDA --}}
        @if (in_array(Auth::user()->perfil, ['socio', 'professor', 'admin']))
            <div class="col-md-4 col-sm-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0 p-2 text-center hover-shadow card-compact">
                    <div class="mb-1">
                        <i class="bi bi-calendar-week-fill h3 text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Agenda</h5>
                    <p class="text-muted small mb-2">
                        Escala de aulas.
                    </p>
                    <a href="{{ route('agenda.index') }}" class="btn btn-primary btn-sm mt-auto shadow-sm">
                        <i class="bi bi-calendar-check me-1"></i>Marcar
                    </a>
                </div>
            </div>
        @endif

          {{-- 📊 CARD DE HISTÓRICO (Aluno) --}}
        @if (in_array(Auth::user()->perfil, ['aluno']))
            <div class="col-md-4 col-sm-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0 p-2 text-center hover-shadow card-compact">
                    <div class="mb-1">
                        <i class="bi bi-journal-check h3 text-success"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Minhas Presença</h5>
                    <p class="text-muted small mb-2">
                        Visualize seus check-ins!
                    </p>
                    <a href="{{ route('aluno.presenca.index') }}"
                        class="btn btn-outline-success btn-sm mt-auto">
                        <i class="bi bi-clock-history me-1"></i> HISTÓRICO
                    </a>
                </div>
            </div>
        @endif

        {{-- 📊 CARD DE HISTÓRICO (Professor) --}}
        @if (in_array(Auth::user()->perfil, ['professor']))
            <div class="col-md-4 col-sm-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0 p-2 text-center hover-shadow card-compact">
                    <div class="mb-1">
                        <i class="bi bi-journal-check h3 text-success"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Histórico Presença</h5>
                    <p class="text-muted small mb-2">
                        Visualize seus check-ins!
                    </p>
                    <a href="{{ route('aluno.presenca.relatorio_professor') }}"
                        class="btn btn-outline-success btn-sm mt-auto">
                        <i class="bi bi-clock-history me-1"></i> HISTÓRICO
                    </a>
                </div>
            </div>
        @endif

        {{-- 🤝 CARD DE OUVIDORIA --}}
        @if (in_array(Auth::user()->perfil, ['socio', 'admin']))
            <div class="col-md-4 col-sm-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0 p-2 text-center hover-shadow card-compact">
                    <div class="mb-1">
                        <i class="bi bi-chat-heart h3 text-info"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Ouvidoria</h5>
                    <p class="text-muted small mb-2">
                        Elogios, sugestões e feedbacks.
                    </p>
                    <a href="{{ route('ouvidoria.index') }}" class="btn btn-outline-info btn-sm mt-auto">
                        <i class="bi bi-send me-1"></i> FEEDBACK
                    </a>
                </div>
            </div>
        @endif

        {{-- 💰 CARD FATURAMENTO (Empresa/Alunos) --}}
        @if (in_array(Auth::user()->perfil, ['socio', 'admin']))
            <div class="col-md-4 col-sm-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0 p-2 text-center hover-shadow card-compact border-top-success">
                    <div class="mb-1">
                        <i class="bi bi-cash-coin h3 text-success"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Financeiro</h5>
                    <p class="text-muted small mb-2">Faturamento e recebimentos.</p>
                    <a href="{{ route('faturamento.index') }}" class="btn btn-outline-success btn-sm mt-auto">
                        <i class="bi bi-graph-up-arrow me-1"></i> CAIXA
                    </a>
                </div>
            </div>
        @endif

        {{-- 💳 CARD PAGAMENTO PROFESSORES --}}
        @if (in_array(Auth::user()->perfil, ['socio', 'admin']))
            <div class="col-md-4 col-sm-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0 p-2 text-center hover-shadow card-compact border-top-warning">
                    <div class="mb-1">
                        <i class="bi bi-person-check h3 text-warning"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Pagamentos</h5>
                    <p class="text-muted small mb-2">Contas a pagar.</p>
                    {{-- Ajustado para o novo nome de rota --}}
                    <a href="{{ route('financeiro.prof.pagar') }}" class="btn btn-outline-warning btn-sm mt-auto">
                        <i class="bi bi-credit-card-2-back me-1"></i> PAGAR
                    </a>
                </div>
            </div>
        @endif

        {{-- 📋 CARD RELATÓRIO ALUNOS --}}
        @if (in_array(Auth::user()->perfil, ['socio', 'admin']))
            <div class="col-md-4 col-sm-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0 p-2 text-center hover-shadow card-compact border-top-primary">
                    <div class="mb-1">
                        <i class="bi bi-people-fill h3 text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Relatório Alunos</h5>
                    <p class="text-muted small mb-2">
                        Alunos por empresa.
                    </p>
                    <a href="{{ route('relatorio.alunos.index') }}" class="btn btn-outline-primary btn-sm mt-auto">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i> ACESSAR
                    </a>
                </div>
            </div>
        @endif

    </div>

    <style>
        /* Botão Sair Sutil */
        .btn-logout-sutil {
            font-size: 0.85rem;
            opacity: 0.6;
            transition: all 0.3s ease;
            padding: 5px 10px;
        }

        .btn-logout-sutil:hover {
            opacity: 1;
            color: #dc3545 !important;
            background-color: rgba(220, 53, 69, 0.05) !important;
            border-radius: 20px;
        }

        /* 📱 UX MOBILE */
        @media (max-width: 576px) {
            .btn-lg-mobile {
                padding: 12px;
                width: 100%;
                font-size: 1rem;
            }

            h1.display-5 {
                font-size: 1.8rem;
            }
        }

        .border-top-success {
            border-top: 4px solid #2dce89 !important;
        }

        .border-top-warning {
            border-top: 4px solid #fbc02d !important;
        }

        .border-top-primary {
            border-top: 4px solid #0d6efd !important;
        }

        .hover-shadow {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .antialiased {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
@endsection
