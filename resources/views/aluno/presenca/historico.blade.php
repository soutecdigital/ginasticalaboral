@extends('layouts.main')

@section('title', 'Histórico de Presenças')

@section('content')
    <div class="container-fluid p-2 p-md-3">
        <!-- 📱 HEADER MOBILE -->
        <div class="row mb-3">
            <div class="col-12">
                <h1 class="h4 fw-bold mb-1">📋 Meu Histórico</h1>
                <p class="text-muted small mb-3">Presenças nas aulas</p>
            </div>
        </div>

        <!-- 🔍 FILTRO DE DATAS (Light) -->
        <div class="card border-light shadow-sm mb-3">
            <div class="card-body p-2 p-md-3">
                <form method="GET" action="{{ route('aluno.presenca.historico', ['aluno_id' => $aluno->id]) }}"
                    class="row g-2">
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-bold">De:</label>
                        <input type="date" name="data_inicio" class="form-control form-control-sm"
                            value="{{ $dataInicio }}" required>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-bold">Até:</label>
                        <input type="date" name="data_fim" class="form-control form-control-sm"
                            value="{{ $dataFim }}" required>
                    </div>

                    <div class="col-12 col-md-4 d-flex align-items-end">
                        <button type="submit" name="filtro" value="1" class="btn btn-sm btn-primary w-100">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 💪 MENSAGEM MOTIVACIONAL (Se não houver faltas) -->
        @if (!$temFaltas && $presenças->count() > 0)
            <div class="alert alert-success border-0 shadow-sm mb-3 animate__animated animate__bounceIn" role="alert">
                <div class="text-center">
                    <h2 class="display-4 mb-2">{{ $mensagemMotivacional['emoji'] }}</h2>
                    <p class="mb-2 h5 fw-bold">{{ $mensagemMotivacional['texto'] }}</p>
                    <p class="mb-0 text-success fw-semibold">{{ $mensagemMotivacional['texto2'] }}</p>
                </div>
            </div>
        @endif

        <!-- 📊 TABELA LEVE (Otimizada para 3G) -->
        @if ($presenças->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover border-light mb-3">
                    <thead class="table-light">
                        <tr>
                            <th class="small fw-bold">User ID</th>
                            <th class="small fw-bold">Data</th>
                            <th class="small fw-bold">Professor</th>
                            <th class="small fw-bold text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($presenças as $presenca)
                            <tr class="{{ $presenca->presenca == '1' ? 'table-success' : 'table-danger' }}">
                                <td class="small">
                                    <strong>{{ $presenca->user_id }}</strong>
                                </td>
                                <td class="small">
                                    <strong>{{ $presenca->created_at->format('d/m/Y') }}</strong>
                                    <br>
                                    <span class="text-muted">{{ $presenca->created_at->format('H:i') }}</span>
                                </td>
                                <td class="small">
                                    {{ $presenca->professor->name ?? '' }}
                                    <br>
                                    <span class="text-muted">{{ $presenca->empresa->nome ?? '' }}</span>
                                </td>
                                <td class="small text-center">
                                    @if ($presenca->presenca == '1')
                                        <span class="badge bg-success">Presente</span>
                                    @else
                                        <span class="badge bg-danger">Falta</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- 📄 PAGINAÇÃO (Light) -->
            <nav class="d-flex justify-content-center mb-3">
                {{ $presenças->links('pagination::bootstrap-4') }}
            </nav>

            <!-- 📈 RESUMO -->
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="card text-center border-success bg-light">
                        <div class="card-body p-2">
                            <h6 class="card-title small text-success">✅ Presenças</h6>
                            <p class="h5 fw-bold text-success mb-0">
                                {{ $presenças->getCollection()->where('presenca', '1')->count() }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card text-center border-danger bg-light">
                        <div class="card-body p-2">
                            <h6 class="card-title small text-danger">❌ Faltas</h6>
                            <p class="h5 fw-bold text-danger mb-0">
                                {{ $presenças->getCollection()->where('presenca', '0')->count() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- ⚠️ MENSAGEM VAZIA -->
            <div class="alert alert-info border-0 shadow-sm text-center">
                <h5 class="mb-2">📭 Nenhum registro encontrado</h5>
                <p class="mb-0 small text-muted">Selecione outro período e tente novamente</p>
            </div>
        @endif

        <!-- 🔙 BOTÃO VOLTAR -->
        <div class="text-center mb-3">
            <a href="{{ route('aluno.presenca.index') }}" class="btn btn-outline-secondary btn-sm">
                🔙 Voltar às Aulas
            </a>
        </div>
    </div>

    <style>
        /* 📱 Otimizações para Mobile */
        @media (max-width: 576px) {
            .container-fluid {
                padding: 0.5rem;
            }

            .table {
                font-size: 0.75rem;
            }

            .card {
                margin-bottom: 0.75rem;
            }

            .btn-sm {
                padding: 0.4rem 0.6rem;
                font-size: 0.75rem;
            }

            .badge {
                font-size: 0.65rem;
                padding: 0.4rem 0.5rem;
            }
        }

        /* 🎨 Animação */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate__animated {
            animation: slideInUp 0.5s ease-out;
        }

        /* 📊 Cores dos badges */
        .table-success {
            background-color: rgba(25, 135, 84, 0.1);
        }

        .table-danger {
            background-color: rgba(220, 53, 69, 0.1);
        }
    </style>
@endsection
