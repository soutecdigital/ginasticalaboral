@extends('layouts.main')

@section('content')
    <div class="container-fluid mt-4 px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold" style="color: #1a2a40;">
                    <i class="bi bi-clock-history me-2 text-success"></i>Histórico: {{ $empresa->nome_fantasia }}
                </h3>
            </div>
            <a href="{{ route('financeiro.index') }}" class="btn btn-outline-secondary fw-bold shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> VOLTAR
            </a>
        </div>

        <div class="row g-4">
            {{-- Timeline de Evolução do Preço --}}
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold m-0 text-primary"><i class="bi bi-pencil-square me-2"></i>Evolução</h5>
                    </div>
                    <div class="card-body p-3" style="max-height: 550px; overflow-y: auto;">
                        <ul class="list-group list-group-flush">
                            @forelse ($empresa->historicos as $h)
                                <li class="list-group-item border-0 ps-0 pb-3">
                                    <div class="d-flex align-items-center">
                                        <span class="text-danger small">R$
                                            {{ number_format($h->valor_anterior, 2, ',', '.') }}</span>
                                        <i class="bi bi-arrow-right mx-2 text-muted small"></i>
                                        <span class="text-success fw-bold">R$
                                            {{ number_format($h->valor_novo, 2, ',', '.') }}</span>
                                    </div>
                                    <small class="text-muted d-block"
                                        style="font-size: 0.7rem;">{{ $h->created_at->format('d/m/Y H:i') }}</small>
                                    <div class="bg-light p-2 rounded mt-1 small fst-italic">"{{ $h->motivo }}"</div>
                                </li>
                            @empty
                                <p class="text-muted small text-center py-4">Sem registros de reajuste.</p>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Log de Pagamentos --}}
            <div class="col-md-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold m-0 text-success"><i class="bi bi-list-check me-2"></i>Mensalidades</h5>
                    </div>
                    <div class="card-body p-0" style="max-height: 550px; overflow-y: auto;">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th class="ps-4">MÊS REF.</th>
                                        <th>VALOR</th>
                                        <th>STATUS</th>
                                        <th>AUDITORIA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($empresa->faturamentos as $f)
                                        <tr>
                                            <td class="ps-4 fw-bold small">
                                                {{ $f->mes_referencia->isoFormat('MMMM / YYYY') }}</td>
                                            <td class="fw-bold text-success">R$
                                                {{ number_format($f->valor_mensalidade, 2, ',', '.') }}</td>
                                            <td><span
                                                    class="badge {{ $f->status == 'pago' ? 'bg-success' : 'bg-warning' }}">{{ strtoupper($f->status) }}</span>
                                            </td>
                                            <td class="small">
                                                @if ($f->observacao_financeira)
                                                    <div class="p-1 bg-light border-start border-info mb-1">
                                                        {{ $f->observacao_financeira }}</div>
                                                @endif
                                                @if ($f->user_baixa_id)
                                                    <small class="text-muted">Por:
                                                        {{ $f->usuarioBaixa->name ?? 'Sistema' }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
