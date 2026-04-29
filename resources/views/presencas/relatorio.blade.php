@extends('layouts.main')

@section('content')
    <div class="container-fluid px-3 mt-3">
        {{-- Topo: Identificação --}}
        <div class="d-flex align-items-center mb-4 bg-white p-3 shadow-sm rounded-3">
            <div class="bg-info text-white p-3 rounded-circle me-3">
                <i class="bi bi-graph-up fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0">Relatório de Presenças</h5>
                <small class="text-muted">Consolidado por período</small>
            </div>
        </div>

        {{-- Filtro de Datas --}}
        <form action="{{ route('presencas.relatorio') }}" method="GET" class="row g-2 mb-4">
            <div class="col-md-5">
                <label class="small fw-bold">DATA INICIAL:</label>
                <input type="date" name="data_inicio" class="form-control" value="{{ $dataInicio }}" required>
            </div>
            <div class="col-md-5">
                <label class="small fw-bold">DATA FINAL:</label>
                <input type="date" name="data_fim" class="form-control" value="{{ $dataFim }}" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-info w-100 fw-bold">
                    <i class="bi bi-funnel me-1"></i> GERAR
                </button>
            </div>
        </form>

        {{-- Cards de Resumo --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-3 bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="opacity-75">TOTAL DE PRESENÇAS</small>
                                <h3 class="fw-bold mb-0">{{ $totalPresencas }}</h3>
                            </div>
                            <i class="bi bi-calendar-check fs-1 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-3 bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="opacity-75">EMPRESAS ATENDIDAS</small>
                                <h3 class="fw-bold mb-0">{{ $porEmpresa->count() }}</h3>
                            </div>
                            <i class="bi bi-building fs-1 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-3 bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="opacity-75">PERÍODO</small>
                                <h5 class="fw-bold mb-0">
                                    {{ date('d/m', strtotime($dataInicio)) }} a
                                    {{ date('d/m/Y', strtotime($dataFim)) }}
                                </h5>
                            </div>
                            <i class="bi bi-calendar-range fs-1 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Presença por Empresa --}}
        @if ($porEmpresa->count() > 0)
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-light border-0 rounded-top-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart me-2"></i> Resumo por Empresa</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Empresa</th>
                                    <th class="text-end">Presenças</th>
                                    <th class="text-end">Percentual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($porEmpresa as $empresa => $count)
                                    <tr>
                                        <td class="fw-bold">{{ $empresa }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-primary">{{ $count }}</span>
                                        </td>
                                        <td class="text-end">
                                            {{ round(($count / $totalPresencas) * 100, 1) }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Tabela Detalhada --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-light border-0 rounded-top-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-list-check me-2"></i> Detalhamento</h6>
            </div>
            <div class="card-body p-0">
                @if ($presencas->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Data</th>
                                    <th>Hora</th>
                                    <th>Empresa</th>
                                    <th>Observações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($presencas as $p)
                                    <tr>
                                        <td class="fw-bold">{{ date('d/m/Y', strtotime($p->data_presenca)) }}</td>
                                        <td>
                                            <span class="badge bg-primary rounded-pill">
                                                {{ date('H:i', strtotime($p->hora_presenca)) }}
                                            </span>
                                        </td>
                                        <td>{{ $p->empresa->nome_fantasia ?? 'N/A' }}</td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $p->observacoes ?? '-' }}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 opacity-50">
                        <i class="bi bi-cloud-slash fs-1"></i>
                        <p class="mt-2">Nenhuma presença encontrada neste período.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Botão de Ação --}}
        <div class="mt-4 pb-5 d-flex gap-2">
            <button class="btn btn-outline-primary fw-bold" onclick="window.print()">
                <i class="bi bi-printer me-2"></i> Imprimir
            </button>
            <a href="{{ route('presencas.index') }}" class="btn btn-outline-secondary fw-bold">
                <i class="bi bi-arrow-left me-2"></i> Voltar
            </a>
        </div>
    </div>
@endsection
