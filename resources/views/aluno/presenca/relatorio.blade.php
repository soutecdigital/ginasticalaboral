@extends('layouts.main')

@section('content')
    <div class="container-fluid px-3 mt-3">
        {{-- Cabeçalho --}}
        <div class="card mb-4"
            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><i class="bi bi-graph-up"></i> Histórico de Presenças</h4>
                        <small>{{ auth()->user()->name }}</small>
                    </div>
                    <a href="{{ route('aluno.presenca.index') }}" class="btn btn-light">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>

        {{-- Estatísticas --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success"><i class="bi bi-check-circle"></i> {{ $presencas->total() }}</h3>
                        <small class="text-muted">Total de Aulas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><i class="bi bi-building"></i>
                            {{ $presencas->total() > 0 ? $presencas->pluck('empresa_id')->unique()->count() : 0 }}</h3>
                        <small class="text-muted">Empresas Vinculadas</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabela de Presenças --}}
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-table"></i> Detalhe de Presenças</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><i class="bi bi-calendar"></i> Data</th>
                                <th><i class="bi bi-building"></i> Empresa</th>
                                <th><i class="bi bi-person"></i> Professor</th>
                                <th><i class="bi bi-clock"></i> Hora</th>
                                <th><i class="bi bi-sticky"></i> Observações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($presencas as $presenca)
                                <tr>
                                    <td>
                                        <strong>
                                            {{ \Carbon\Carbon::parse($presenca->data_presenca)->format('d/m/Y') }}
                                            <br>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($presenca->data_presenca)->format('l') }}
                                            </small>
                                        </strong>
                                    </td>
                                    <td>
                                        <i class="bi bi-building me-2"></i>
                                        {{ $presenca->empresa->nome_fantasia }}
                                    </td>
                                    <td>
                                        <i class="bi bi-person-circle me-2"></i>
                                        {{ $presenca->professor->name }}
                                    </td>
                                    <td>
                                        <i class="bi bi-clock me-2"></i>
                                        {{ $presenca->hora_presenca ? \Carbon\Carbon::parse($presenca->hora_presenca)->format('H:i') : '—' }}
                                    </td>
                                    <td>
                                        @if ($presenca->observacoes)
                                            <small class="text-muted">
                                                {{ Str::limit($presenca->observacoes, 60) }}
                                            </small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox"></i> Nenhuma presença registrada ainda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Paginação --}}
        @if ($presencas->hasPages())
            <div class="d-flex justify-content-center mt-4 mb-4">
                {{ $presencas->links() }}
            </div>
        @endif
    </div>
@endsection
