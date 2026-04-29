@extends('layouts.main')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header p-3 text-white" style="background-color: #1a2a40;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-bar-chart me-2"></i>RELATÓRIO DE CONFORMIDADE - LOCALIZAÇÃO
                            </h5>
                            <a href="{{ route('auditoria.localizacoes.index') }}" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>VOLTAR
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        {{-- FILTROS --}}
                        <form method="GET" action="{{ route('auditoria.localizacoes.relatorio') }}" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Data Início</label>
                                <input type="date" name="data_inicio" class="form-control form-control-sm"
                                    value="{{ $dataInicio->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Data Fim</label>
                                <input type="date" name="data_fim" class="form-control form-control-sm"
                                    value="{{ $dataFim->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search me-1"></i>GERAR RELATÓRIO
                                </button>
                            </div>
                        </form>

                        {{-- RESUMO DE PERÍODO --}}
                        <h6 class="fw-bold text-primary mb-3">Período: {{ $dataInicio->format('d/m/Y') }} a
                            {{ $dataFim->format('d/m/Y') }}</h6>

                        {{-- ESTATÍSTICAS --}}
                        <div class="row mb-4">
                            {{-- Confirmações por Tipo --}}
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <h6 class="fw-bold text-success mb-3">
                                            <i class="bi bi-check-circle me-2"></i>CONFIRMAÇÕES POR TIPO
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-borderless">
                                                @php $totalConfirmacoes = 0; @endphp
                                                @forelse ($confirmacoesPorTipo as $item)
                                                    @php $totalConfirmacoes += $item->total; @endphp
                                                    <tr>
                                                        <td>
                                                            @if ($item->tipo_confirmacao === 'gps')
                                                                <span class="badge bg-success"><i
                                                                        class="bi bi-geo-alt-fill"></i> GPS</span>
                                                            @else
                                                                <span class="badge bg-warning"><i class="bi bi-clock"></i>
                                                                    Horário</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            <strong>{{ $item->total }} confirmações</strong>
                                                        </td>
                                                        <td class="text-end">
                                                            <small class="text-muted">
                                                                {{ round(($item->total / ($confirmacoesPorTipo->sum('total') ?: 1)) * 100, 1) }}%
                                                            </small>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">Sem dados</td>
                                                    </tr>
                                                @endforelse
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Conformidade de Raio --}}
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <h6 class="fw-bold text-info mb-3">
                                            <i class="bi bi-shield-check me-2"></i>CONFORMIDADE DE RAIO GPS
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-borderless">
                                                @php $totalConf = 0; @endphp
                                                @forelse ($conformidade as $item)
                                                    @php $totalConf += $item->total; @endphp
                                                    <tr>
                                                        <td>
                                                            @if ($item->dentro_raio)
                                                                <span class="badge bg-success">✓ Dentro do Raio</span>
                                                            @else
                                                                <span class="badge bg-danger">✗ Fora do Raio</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            <strong>{{ $item->total }} ocorrências</strong>
                                                        </td>
                                                        <td class="text-end">
                                                            <small class="text-muted">
                                                                {{ round(($item->total / ($conformidade->sum('total') ?: 1)) * 100, 1) }}%
                                                            </small>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">Sem dados</td>
                                                    </tr>
                                                @endforelse
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- PROFESSORES COM ALERTAS --}}
                        <h6 class="fw-bold text-danger mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>PROFESSORES COM CONFIRMAÇÕES FORA DO RAIO
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-danger">
                                    <tr>
                                        <th>Professor</th>
                                        <th class="text-end">Confirmações Fora do Raio</th>
                                        <th class="text-end">% de Não-Conformidade</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($professoresAlerta as $prof)
                                        @php
                                            $totalProf = \App\Models\LocalizacaoProfEmp::where(
                                                'professor_id',
                                                $prof->professor_id,
                                            )
                                                ->whereBetween('confirmado_em', [
                                                    $dataInicio->startOfDay(),
                                                    $dataFim->endOfDay(),
                                                ])
                                                ->count();
                                            $percentualFora =
                                                $totalProf > 0
                                                    ? round(($prof->total_fora_raio / $totalProf) * 100, 1)
                                                    : 0;
                                        @endphp
                                        <tr class="{{ $percentualFora > 50 ? 'table-danger' : '' }}">
                                            <td class="fw-bold">{{ $prof->professor->name ?? 'N/A' }}</td>
                                            <td class="text-end">
                                                <span class="badge bg-danger">{{ $prof->total_fora_raio }}</span>
                                            </td>
                                            <td class="text-end">
                                                <strong class="{{ $percentualFora > 50 ? 'text-danger' : '' }}">
                                                    {{ $percentualFora }}%
                                                </strong>
                                            </td>
                                            <td>
                                                <a href="{{ route('auditoria.localizacoes.index', ['professor_id' => $prof->professor_id, 'dentro_raio' => 'fora']) }}"
                                                    class="btn btn-danger btn-xs">
                                                    <i class="bi bi-eye"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                ✓ Excelente! Nenhum professor com confirmações fora do raio.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
