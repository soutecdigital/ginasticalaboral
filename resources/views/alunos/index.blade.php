@extends('layouts.main')

@section('content')
    <div class="container py-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 antialiased">Relatório de Alunos</h5>
                        <small class="text-muted">Unidade: {{ session('empresa_nome') }}</small>
                    </div>
                    <span class="badge bg-success-subtle text-success rounded-pill px-3">
                        {{ $alunos->count() }} Alunos Ativos
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Matrícula</th>
                            <th>Nome Completo</th>
                            <th>E-mail</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alunos as $aluno)
                            <tr>
                                <td class="ps-4 fw-medium text-primary">#{{ $aluno->matricula ?? '---' }}</td>
                                <td class="fw-bold text-dark">{{ $aluno->name }}</td>
                                <td class="text-muted">{{ $aluno->email }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success" style="font-size: 0.7rem;">ATIVO</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <i class="bi bi-person-exclamation display-4 text-muted"></i>
                                    <p class="mt-2 text-muted">Nenhum aluno vinculado a esta unidade.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
