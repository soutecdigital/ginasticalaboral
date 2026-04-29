@extends('layouts.main')

@section('content')
    <div class="container-fluid py-4 bg-light">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-danger mb-0">
                    <i class="bi bi-clock-history me-2"></i>Histórico de Cancelamentos
                </h4>
                <span class="text-muted small">Listagem de escalas com status: <strong>cancelado</strong></span>
            </div>

            <div class="d-flex gap-2">

            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-white border-bottom">
                        <tr>
                            <th class="py-3 ps-4 text-muted small fw-bold">ESCALA</th>
                            <th class="text-muted small fw-bold">PROFESSOR</th>
                            <th class="text-muted small fw-bold">DATA OPERAÇÃO</th>
                            <th class="text-muted small fw-bold">Horario:</th>
                            <th class="text-muted small fw-bold">Tipo de Aula</th>
                            <th class="text-muted small fw-bold">Motivo do Cancelamento</th>
                            <th class="text-muted small fw-bold">Usuário de Cancelamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cancelamentos as $escala)
                            <tr>
                                <td class="fw-bold text-dark">
                                    {{ $escala->empresa->nome_fantasia ?? 'Empresa não encontrada' }}
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-badge me-2 text-muted"></i>
                                        {{ $escala->professor->name ?? 'Não atribuído' }}
                                    </div>
                                </td>

                                <td>{{ \Carbon\Carbon::parse($escala->data)->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ strtoupper($escala->turno) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $escala->tipo_aula == 'online' ? 'bg-info' : 'bg-primary' }}">
                                        {{ ucfirst($escala->tipo_aula) }}
                                    </span>
                                </td>

                                <td class="text-danger small">
                                    {{ $escala->observacao_cancelamento ?? 'Sem justificativa' }}
                                </td>
                                <td class="small">
                                    <div class="text-danger fw-bold">
                                        {{ $escala->usuarioCancelamento->name ?? 'Sistema' }}
                                    </div>

                                </td>



                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($cancelamentos->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $cancelamentos->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
