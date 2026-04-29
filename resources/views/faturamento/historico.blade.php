@extends('layouts.main')

@section('content')
    <div class="container-fluid py-4 px-4">
        {{-- Header Compacto --}}
        <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-3">
            <div>
                <h4 class="fw-bold m-0" style="color: #1a2a40;">
                    <i class="bi bi-clock-history me-2 text-primary"></i>Histórico Contratual
                </h4>
                <small class="text-muted">{{ $empresa->nome_fantasia }} • Parceria há {{ $tempoContrato }}</small>
            </div>
            <a href="{{ route('faturamento.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
        </div>

        <div class="row">
            <div class="col-md-9 mx-auto">
                {{-- Timeline Compacta --}}
                <div class="position-relative ps-4 border-start border-2 border-primary ms-2">
                    @forelse($empresa->historicos as $h)
                        <div class="mb-4 position-relative">
                            {{-- Marcador Simples --}}
                            <div class="position-absolute bg-primary rounded-circle"
                                style="width: 12px; height: 12px; left: -31px; top: 6px; border: 2px solid #fff;"></div>

                            <div class="d-flex align-items-center gap-3 mb-1">
                                <span class="fw-bold text-dark small">{{ $h->created_at->format('d/m/Y') }}</span>
                                <span class="badge bg-light text-primary border border-primary-subtle"
                                    style="font-size: 0.7rem;">
                                    {{ $h->total_alunos_momento }} Alunos
                                </span>
                            </div>

                            <div class="p-3 bg-white border rounded shadow-sm">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center gap-2">X
                                            <span class="text-muted small text-decoration-line-through">R$
                                                {{ number_format($h->valor_anterior, 2, ',', '.') }}</span>
                                            <i class="bi bi-arrow-right text-muted"></i>
                                            <span class="fw-bold text-success">R$
                                                {{ number_format($h->valor_novo, 2, ',', '.') }} <i
                                                    class="bi bi-check"></i></span>
                                        </div>
                                    </div>
                                    <div class="col-md-5 border-start">
                                        <small class="text-dark d-block" style="font-size: 0.85rem;">
                                            <strong>Motivo:</strong> {{ $h->motivo }}
                                        </small>
                                    </div>
                                    <div class="col-md-3 text-end border-start">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <div class="text-end me-2">
                                                <div style="font-size: 0.65rem;"
                                                    class="text-uppercase text-muted fw-bold lh-1">Ajustado por:</div>
                                                <span
                                                    class="fw-bold text-dark small">{{ Str::before($h->usuario->name ?? 'Sistema', ' ') }}</span>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-light border small text-muted">
                            <i class="bi bi-info-circle me-2"></i> Sem registros de reajuste.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Ajuste para o Breadcrumb se quiser manter, ou use o Header acima */
        .breadcrumb-item+.breadcrumb-item::before {
            content: "•";
        }
    </style>
@endsection
