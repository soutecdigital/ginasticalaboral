@extends('layouts.main')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header p-3 text-white" style="background-color: #1a2a40;">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-geo-alt-fill me-2"></i>AUDITORIA DE LOCALIZAÇÃO DO PROFESSOR
                        </h5>
                    </div>

                    <div class="card-body p-4">
                        {{-- FILTROS --}}
                        <form method="GET" action="{{ route('auditoria.localizacoes.index') }}" class="row g-3 mb-4">
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Data Início</label>
                                <input type="date" name="data_inicio" class="form-control form-control-sm"
                                    value="{{ request('data_inicio') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Data Fim</label>
                                <input type="date" name="data_fim" class="form-control form-control-sm"
                                    value="{{ request('data_fim') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Professor</label>
                                <select name="professor_id" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    @foreach ($professores as $prof)
                                        <option value="{{ $prof->id }}"
                                            {{ request('professor_id') == $prof->id ? 'selected' : '' }}>
                                            {{ $prof->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Empresa</label>
                                <select name="empresa_id" class="form-select form-select-sm">
                                    <option value="">Todas</option>
                                    @foreach ($empresas as $emp)
                                        <option value="{{ $emp->id }}"
                                            {{ request('empresa_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->nome_fantasia }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Tipo Confirmação</label>
                                <select name="tipo_confirmacao" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="gps" {{ request('tipo_confirmacao') == 'gps' ? 'selected' : '' }}>
                                        GPS</option>
                                    <option value="horario"
                                        {{ request('tipo_confirmacao') == 'horario' ? 'selected' : '' }}>
                                        Horário</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Status Localização</label>
                                <select name="dentro_raio" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="dentro" {{ request('dentro_raio') == 'dentro' ? 'selected' : '' }}>
                                        Dentro do Raio</option>
                                    <option value="fora" {{ request('dentro_raio') == 'fora' ? 'selected' : '' }}>
                                        FORA DO RAIO</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search me-1"></i>FILTRAR
                                </button>
                                <a href="{{ route('auditoria.localizacoes.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-clockwise me-1"></i>LIMPAR
                                </a>
                                <a href="{{ route('auditoria.localizacoes.relatorio') }}" class="btn btn-info btn-sm">
                                    <i class="bi bi-bar-chart me-1"></i>RELATÓRIO
                                </a>
                                <a href="{{ route('auditoria.localizacoes.exportar', request()->query()) }}"
                                    class="btn btn-success btn-sm">
                                    <i class="bi bi-download me-1"></i>EXPORTAR CSV
                                </a>
                            </div>
                        </form>

                        {{-- TABELA DE LOCALIZAÇÕES --}}
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Data/Hora</th>
                                        <th>Professor</th>
                                        <th>Empresa</th>
                                        <th>Tipo</th>
                                        <th>Distância (m)</th>
                                        <th>Status</th>
                                        <th>IP</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($localizacoes as $loc)
                                        <tr>
                                            <td class="small">{{ $loc->confirmado_em->format('d/m/Y H:i:s') }}</td>
                                            <td class="small fw-bold">{{ $loc->professor->name }}</td>
                                            <td class="small">{{ $loc->empresa->nome_fantasia }}</td>
                                            <td>
                                                @if ($loc->tipo_confirmacao === 'gps')
                                                    <span class="badge bg-success"><i class="bi bi-geo-alt-fill"></i>
                                                        GPS</span>
                                                @else
                                                    <span class="badge bg-warning"><i class="bi bi-clock"></i>
                                                        Horário</span>
                                                @endif
                                            </td>
                                            <td class="small">
                                                @if ($loc->distancia_metros !== null)
                                                    <strong>{{ number_format($loc->distancia_metros, 0) }}</strong>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($loc->dentro_raio)
                                                    <span class="badge bg-success">✓ Dentro</span>
                                                @else
                                                    <span class="badge bg-danger">✗ FORA</span>
                                                @endif
                                            </td>
                                            <td class="small text-muted">{{ $loc->ip_address }}</td>
                                            <td>
                                                <a href="{{ route('auditoria.localizacoes.show', $loc->id) }}"
                                                    class="btn btn-primary btn-xs">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Nenhuma localização
                                                registrada.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- PAGINAÇÃO --}}
                        <div class="mt-3">
                            {{ $localizacoes->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
