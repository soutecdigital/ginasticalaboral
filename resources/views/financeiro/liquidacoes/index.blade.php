@extends('layouts.main')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h2 class="fw-bold" style="color: #1a2a40;">
                    <i class="bi bi-shield-check text-success me-2"></i>Histórico de Liquidações
                </h2>
                <p class="text-muted">Auditoria de pagamentos e Notas Fiscais</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="card d-inline-block border-0 shadow-sm px-4 py-2 bg-success text-white">
                    <small class="d-block text-white-50">Total Pago no Período</small>
                    <h4 class="mb-0 fw-bold">R$ {{ number_format($totalPago, 2, ',', '.') }}</h4>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('financeiro.prof.liquidar.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Unidade</label>
                        <select name="empresa_id" class="form-select">
                            <option value="">Todas</option>
                            @foreach ($empresas as $emp)
                                <option value="{{ $emp->id }}"
                                    {{ request('empresa_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->nome_fantasia }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Data Início</label>
                        <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Data Fim</label>
                        <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter me-1"></i>Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabela de Auditoria --}}
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead 
                        class="bg-light sticky-top">
                        <tr>
                            <th class="text-center">Data Pgto</th>
                            <th>Professor</th>
                            <th>Unidade</th>
                            <th class="text-center">Ref.</th>
                            <th>NF / Documento</th>
                            <th class="text-end">Valor Total</th>
                            <th>Baixa por</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($liquidacoes as $liq)
                            <tr>
                                <td class="text-center small">{{ date('d/m/Y', strtotime($liq->data_pagamento)) }}</td>
                                <td class="fw-bold">{{ $liq->professor->name }}</td>
                                <td><small>{{ $liq->empresa->nome_fantasia }}</small></td>
                                <td class="text-center text-muted small">{{ $liq->mes_referencia }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-receipt me-1"></i>{{ $liq->numero_nf }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    R$ {{ number_format($liq->valor_total_pago, 2, ',', '.') }}
                                </td>
                                <td class="small text-muted">
                                    {{-- Aplicando o nome curto que discutimos --}}
                                    {{ Str::before($liq->usuarioBaixa->name ?? 'Sistema', ' ') }}
                                </td>
                                <td class="text-center">
                                    {{-- Botão para ver detalhes/aulas que compõem essa NF --}}
                                    <button class="btn btn-sm btn-outline-primary"
                                        onclick="verDetalhes({{ $liq->id }})">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Nenhuma liquidação encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0">
                {{ $liquidacoes->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection


<div class="modal fade" id="modalDetalhesLiquitacao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detalhes do Pagamento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body" id="conteudo-modal-liquidacao">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    function verDetalhes(id) {
        // Busca os elementos do DOM
        const modalElement = document.getElementById('modalDetalhesLiquitacao');
        const container = document.getElementById('conteudo-modal-liquidacao');

        // Inicializa o modal do Bootstrap
        const modal = new bootstrap.Modal(modalElement);

        // Feedback visual de carregamento
        container.innerHTML =
            '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Buscando dados da auditoria...</p></div>';
        modal.show();

        // Rota corrigida conforme o seu prefixo 'financeiro.prof'
        fetch(`/financeiro/professores/liquidar/${id}/detalhes`) // Verifique se esta URL bate com seu route:list
            .then(response => {
                if (!response.ok) throw new Error('Erro na rede ou rota não encontrada');
                return response.json();
            })
            .then(data => {
                // POKA-YOKE: Formatação de moeda e data
                const formatMoeda = (valor) => parseFloat(valor).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });
                const formatData = (dataSql) => new Date(dataSql).toLocaleDateString('pt-BR');

                let html = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-0 small text-muted text-uppercase fw-bold">Professor</p>
                        <h6 class="fw-bold text-dark">${data.professor.name}</h6>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-0 small text-muted text-uppercase fw-bold">Destino</p>
                        <h6 class="fw-bold text-dark">${data.empresa.nome_fantasia}</h6>
                    </div>
                </div>
                <div class="row mb-3 border-top pt-3 bg-light rounded p-2 mx-0">
                    <div class="col-md-3">
                        <p class="mb-0 small text-muted fw-bold">NF / DOC</p>
                        <span class="badge bg-white text-primary border border-primary">${data.numero_nf}</span>
                    </div>                   


                    <div class="col-md-3">
                        <p class="mb-0 small text-muted fw-bold">DATA PGTO</p>
                        <h6 class="mb-0">${formatData(data.data_pagamento)}</h6>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-0 small text-muted fw-bold">TOTAL PAGO</p>
                        <h6 class="mb-0 text-success fw-bold">${formatMoeda(data.valor_total_pago)}</h6>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-0 small text-muted fw-bold">OPERADOR</p>
                        <h6 class="mb-0">${data.usuario_baixa ? data.usuario_baixa.name : 'Sistema'}</h6>
                    </div>
                </div>
                <div class="border-top pt-3">
                    <p class="mb-2 small text-muted fw-bold text-uppercase"><i class="bi bi-list-check me-1"></i>Aulas Conciliadas</p>
                    <table class="table table-sm table-hover border small">
                        <thead class="table-light">
                            <tr>
                                <th>Data Aula</th>
                                <th>Turno</th>
                                <th>Tipo</th>
                                <th class="text-end">Valor</th>
                                 <th class="text-end">Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.pagamentos.map(pg => `
                                <tr>
                                    <td>${formatData(pg.data_referencia)}</td>
                                    <td><span class="badge bg-secondary-subtle text-secondary border">${pg.escala.turno.toUpperCase()}</span></td>
                                    <td>${pg.escala.tipo_aula.toUpperCase()}</td>
                                    <td class="text-end fw-bold">${formatMoeda(pg.valor_pago)}</td>
                                    <td class="text-end"> ${data.forma_pagamento === 'pix' ? '<i class="bi bi-qr-code text-primary"></i> PIX' : 
          data.forma_pagamento === 'dinheiro' ? '<i class="bi bi-cash-stack text-success"></i> Dinheiro' : 
          '<i class="bi bi-bank text-secondary"></i> ' + data.forma_pagamento.toUpperCase()}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                ${data.observacao ? `<div class="alert alert-warning border-0 shadow-sm small mt-2 mb-0"><strong>Observação:</strong> ${data.observacao}</div>` : ''}
            `;
                container.innerHTML = html;
            })
            .catch(error => {
                container.innerHTML =
                    `<div class="alert alert-danger"><strong>Erro 500:</strong> Não foi possível carregar os detalhes. Verifique se o relacionamento 'pagamentos' existe no Model.</div>`;
                console.error('Erro:', error);
            });
    }
</script>


