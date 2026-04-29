@extends('layouts.main')

@section('content')
    <div class="container-fluid mt-4">
        {{-- Header com Automação --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold m-0" style="color: #1a2a40;">
                    <i class="bi bi-cash-coin me-2 text-success"></i>Financeiro - Faturamento
                </h3>
                <div class="d-flex align-items-center mt-2 p-2 bg-light rounded-3 border" style="max-width: fit-content;">
                    <span class="badge bg-success me-2 shadow-sm" style="font-size: 0.65rem;">
                        <i class="bi bi-gear-fill spin-slow me-1"></i> AUTOMAÇÃO ATIVA
                    </span>
                    <div class="small fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                        <span class="text-secondary">1. Baixa:</span>
                        <i class="bi bi-wallet2 text-success"></i>
                        <i class="bi bi-arrow-right text-muted"></i>
                        <span class="text-secondary">2. Cria Próximo:</span>
                        <i class="bi bi-plus-circle text-primary"></i>
                        <i class="bi bi-arrow-right text-muted"></i>
                        <span class="text-secondary">3. Valor Contrato:</span>
                        <i class="bi bi-file-earmark-text text-primary"></i>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <span class="badge bg-dark px-3 py-2 fs-6 shadow-sm text-capitalize">
                    <i class="bi bi-calendar3 me-1"></i>
                    @if ($mesSel == 'all')
                        Ano Inteiro de {{ $anoSel }}
                    @else
                        {{ \Carbon\Carbon::create($anoSel, $mesSel, 1)->isoFormat('MMMM / YYYY') }}
                    @endif
                </span>
            </div>
        </div>

        {{-- Barra de Filtros --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                {{-- Barra de Filtros Atualizada --}}
                <form action="{{ route('faturamento.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Empresa</label>
                        <select name="empresa_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Todas as Empresas</option>
                            @foreach ($empresas as $emp)
                                <option value="{{ $emp->id }}" {{ $empresaSel == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->nome_fantasia }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Mês de Referência</label>
                        <select name="mes" class="form-select" onchange="this.form.submit()">
                            <option value="all" {{ $mesSel == 'all' ? 'selected' : '' }}>📅 ANO INTEIRO</option>
                            @foreach (range(1, 12) as $m)
                                <option value="{{ sprintf('%02d', $m) }}" {{ $mesSel == $m ? 'selected' : '' }}>
                                    {{ ucfirst(\Carbon\Carbon::create()->month($m)->locale('pt_BR')->isoFormat('MMMM')) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Ano</label>
                        <select name="ano" class="form-select" onchange="this.form.submit()">
                            @for ($i = date('Y'); $i >= 2024; $i--)
                                <option value="{{ $i }}" {{ $anoSel == $i ? 'selected' : '' }}>
                                    {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        {{-- O botão filtrar agora é opcional, mas bom manter por acessibilidade --}}
                        <button type="submit" class="btn btn-primary w-100 fw-bold">BUSCAR</button>
                        <a href="{{ route('faturamento.index') }}" class="btn btn-outline-secondary px-3"
                            title="Limpar Filtros">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </form>
            </div>
</div>


            {{-- Tabela de Lançamentos --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">EMPRESA / CONTRATO</th>
                                    <th>VLR. CONTRATO</th>
                                    <th>VLR. COBRADO</th>
                                    <th>STATUS / REF</th>
                                    <th class="text-end pe-4">AÇÕES</th>
                                    <th class="text-center">Histórico</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($faturamentos as $f)
                                    @php
                                        $valorContrato = (float) $f->empresa->valor_contrato;
                                        $valorFatura = (float) $f->valor_mensalidade;
                                        $diferenca = $valorFatura - $valorContrato;
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="fw-bold text-dark me-2">{{ $f->empresa->nome_fantasia }}</div>

                                            </div>
                                            @if ($diferenca < 0)
                                                <span class="badge bg-danger" style="font-size: 0.6rem;">DESC.
                                                    NEGOCIADO</span>
                                            @elseif($diferenca > 0)
                                                <span class="badge bg-success"
                                                    style="font-size: 0.6rem;">REAJUSTE/EXTRA</span>
                                            @endif
                                        </td>
                                        <td>R$ {{ number_format($valorContrato, 2, ',', '.') }}</td>
                                        <td
                                            class="fw-bold {{ $diferenca < 0 ? 'text-danger' : ($diferenca > 0 ? 'text-success' : '') }}">
                                            R$ {{ number_format($valorFatura, 2, ',', '.') }}
                                        </td>
                                        <td>
                                            <span
                                                class="badge {{ $f->status == 'pago' ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-2 rounded-pill shadow-sm">
                                                <i
                                                    class="bi {{ $f->status == 'pago' ? 'bi-check-all' : 'bi-clock' }} me-1"></i>
                                                {{ strtoupper($f->status) }}
                                            </span>
                                            <div class="text-muted mt-1 ms-1" style="font-size: 0.65rem; font-weight: 600;">
                                                REF: {{ $f->mes_referencia->locale('pt_BR')->isoFormat('MMM/YYYY') }}
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            @if ($f->status != 'pago')
                                                <button class="btn btn-sm btn-dark px-3 shadow-sm"
                                                    onclick="abrirModalBaixa({{ $f->id }}, '{{ $f->empresa->nome_fantasia }}', '{{ $valorFatura }}')">
                                                    <i class="bi bi-cash-stack me-1"></i> BAIXA
                                                </button>
                                            @else
                                                <div class="text-success small fw-bold">
                                                    <i class="bi bi-calendar-check me-1"></i> PAGO EM
                                                    {{ $f->data_pagamento->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <a href="{{ route('faturamento.historico', $f->empresa_id) }}"
                                                    title="Histórico do Cliente" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-clock-history"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">Nenhum registro encontrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL DE BAIXA --}}
        <div class="modal fade" id="modalBaixa" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title"><i class="bi bi-cash-coin me-2"></i>Confirmar Recebimento</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formBaixa" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="text-center mb-4">
                                <h4 class="fw-bold" id="modal_nome_empresa">---</h4>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-muted">Valor Recebido</label>
                                    <div class="input-group border border-success rounded">
                                        <span class="input-group-text bg-success text-white border-0">R$</span>
                                        <input type="number" name="valor_recebido" id="modal_valor_input"
                                            step="0.01" class="form-control fw-bold text-success fs-4 border-0">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-muted">Data do Pagamento</label>
                                    <input type="date" name="data_pagamento" class="form-control"
                                        value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-muted">Observação / Motivo Ajuste</label>
                                    <textarea name="observacao_financeira" id="modal_obs" class="form-control" rows="2"
                                        placeholder="Opcional..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-secondary px-4"
                                data-bs-dismiss="modal">CANCELAR</button>
                            <button type="submit" class="btn btn-success fw-bold px-4">CONFIRMAR PAGAMENTO</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endsection

    @push('scripts')
        <script>
            function abrirModalBaixa(id, nome, valor) {
                document.getElementById('modal_nome_empresa').innerText = nome;
                document.getElementById('modal_valor_input').value = valor;
                document.getElementById('modal_obs').value = "";
                let url = "{{ route('faturamento.baixa', ':id') }}";
                document.getElementById('formBaixa').action = url.replace(':id', id);
                new bootstrap.Modal(document.getElementById('modalBaixa')).show();
            }
        </script>
        <style>
            @keyframes spinner-border {
                to {
                    transform: rotate(360deg);
                }
            }

            .spin-slow {
                display: inline-block;
                animation: spinner-border 3s linear infinite;
            }
        </style>
    @endpush
