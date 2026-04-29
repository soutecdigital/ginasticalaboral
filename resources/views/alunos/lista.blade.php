@extends('layouts.main')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h2 class="fw-bold text-dark"><i class="bi bi-list-check"></i> Lista de Presença</h2>
            <p class="text-muted">Gerencie os alunos ativos e acompanhe as presenças.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('alunos.registrar') }}" class="btn btn-primary fw-bold shadow-sm">
                <i class="bi bi-person-plus-fill me-2"></i>Novo Aluno
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4 py-3">Matrícula</th>
                            <th>Nome Completo</th>
                            <th>E-mail</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alunos as $aluno)
                        <tr>
                            <td class="ps-4 fw-bold text-primary">{{ $aluno->matricula }}</td>
                            <td>{{ $aluno->name }}</td>
                            <td>{{ $aluno->email }}</td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                    Ativo
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-secondary" title="Ver Histórico">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-info-circle fs-2 d-block mb-3"></i>
                                Nenhum aluno cadastrado no momento.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection