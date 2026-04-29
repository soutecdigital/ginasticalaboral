@extends('layouts.main')

@section('content')
    <div class="container-fluid py-4">
        {{-- Header --}}
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h2 fw-bold" style="color: #1a2a40;">
                    <i class="bi bi-people-fill me-2 text-primary"></i> Relatório de Alunos
                </h1>
                <p class="text-muted">Visualize todos os alunos cadastrados por empresa</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('relatorio.alunos.exportar', request()->query()) }}" class="btn btn-outline-success">
                    <i class="bi bi-download me-1"></i> Exportar CSV
                </a>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 p-3">
                    <form method="GET" class="row g-3 align-items-end">
                        {{-- Filtro Empresa --}}
                        <div class="col-md-6">
                            <label for="empresa_id" class="form-label fw-bold">Empresa</label>
                            <select name="empresa_id" id="empresa_id" class="form-select">
                                <option value="">-- Todas as Empresas --</option>
                                @foreach ($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" {{ $empresaSel == $empresa->id ? 'selected' : '' }}>
                                        {{ $empresa->nome_fantasia }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filtro Status --}}
                        <div class="col-md-6">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="ativo" {{ $status == 'ativo' ? 'selected' : '' }}>
                                    ✅ Ativos
                                </option>
                                <option value="inativo" {{ $status == 'inativo' ? 'selected' : '' }}>
                                    ❌ Inativos
                                </option>
                                <option value="todos" {{ $status == 'todos' ? 'selected' : '' }}>
                                    📋 Todos
                                </option>
                            </select>
                        </div>

                        {{-- Botões --}}
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i> Filtrar
                            </button>
                            <a href="{{ route('relatorio.alunos.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-1"></i> Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Resumo --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white shadow-sm border-0 p-3 text-center">
                    <h6 class="text-white-50 mb-1">Total de Alunos</h6>
                    <h3 class="fw-bold">{{ $alunos->count() }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white shadow-sm border-0 p-3 text-center">
                    <h6 class="text-white-50 mb-1">Empresas com Alunos</h6>
                    <h3 class="fw-bold">{{ $alunosPorEmpresa->count() }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white shadow-sm border-0 p-3 text-center">
                    <h6 class="text-white-50 mb-1">Alunos Ativos</h6>
                    <h3 class="fw-bold">{{ $alunos->where('status', 'ativo')->count() }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white shadow-sm border-0 p-3 text-center">
                    <h6 class="text-white-50 mb-1">Alunos Inativos</h6>
                    <h3 class="fw-bold">{{ $alunos->where('status', '!=', 'ativo')->count() }}</h3>
                </div>
            </div>
        </div>

        {{-- Relatório por Empresa --}}
        @if ($alunosPorEmpresa->isEmpty())
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i> Nenhum aluno encontrado com os critérios informados.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @else
            @foreach ($alunosPorEmpresa as $empresaId => $alunosEmpresa)
                @php
                    $empresa = $empresas->where('id', $empresaId)->first();
                @endphp

                <div class="card shadow-sm border-0 mb-4">
                    {{-- Header da Empresa --}}
                    <div class="card-header bg-light border-bottom" style="border-left: 4px solid #007bff;">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1 fw-bold">
                                    <i class="bi bi-building me-2 text-primary"></i>
                                    {{ $empresa->nome_fantasia }}
                                </h5>
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt me-1"></i> {{ $empresa->cidade }}, {{ $empresa->estado }}
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-primary rounded-pill">
                                    {{ $alunosEmpresa->count() }} aluno(s)
                                </span>
                                <span class="badge bg-success rounded-pill ms-1">
                                    {{ $alunosEmpresa->where('status', 'ativo')->count() ?: $alunosEmpresa->count() }}
                                    ativo(s)
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Tabela de Alunos --}}
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nome</th>
                                    <th>Matrícula</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Data de Cadastro</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($alunosEmpresa->sortBy('name') as $aluno)
                                    <tr>
                                        <td class="text-muted small">{{ $loop->index + 1 }}</td>
                                        <td class="fw-bold">{{ $aluno->name }}</td>
                                        <td>
                                            <code class="bg-light p-1 rounded small">{{ $aluno->matricula ?? '—' }}</code>
                                        </td>
                                        <td>
                                            <a href="mailto:{{ $aluno->email }}" class="text-decoration-none">
                                                {{ $aluno->email ?? '—' }}
                                            </a>
                                        </td>
                                        {{-- Procure este trecho na sua tabela de alunos --}}
                                        <td>
                                            @if ($aluno->ativo)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i> Ativo
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle me-1"></i> Inativo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">
                                            {{ $aluno->created_at->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">
                                            Nenhum aluno cadastrado nesta empresa
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <style>
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .card {
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
        }
    </style>
@endsection
