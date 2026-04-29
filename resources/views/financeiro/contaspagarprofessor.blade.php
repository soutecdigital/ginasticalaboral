@extends('layouts.main')

@section('content')
    <div class="container-fluid px-4 mt-4">
        {{-- 1. HEADER DO RELATÓRIO --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body d-flex justify-content-between align-items-center ">
                <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i> Contas a Pagar: Professores</h5>
                <div class="badge bg-success fs-6">Período: {{ $mesSel }} / {{ $anoSel }}</div>
            </div>
        </div>

        {{-- 2. LEGENDA POKA-YOKE (INFORMAÇÃO DE PENDÊNCIA) --}}
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
            <div>
                <h6 class="alert-heading fw-bold mb-1">Itens Pendentes de Liquidação</h6>
                <p class="mb-0 small">As aulas listadas abaixo foram <strong>validadas</strong>, mas ainda aguardam o lançamento da Nota Fiscal para o pagamento final. Clique em <strong>Liquidar</strong> para vincular o comprovante e dar baixa.</p>
            </div>
        </div>

        {{-- 3. GRID DE PROFESSORES --}}
        <div class="row">
            @forelse ($relatorioPagamento as $dados)
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center border-bottom">
                            <span class="text-uppercase" style="color: #1a2a40;">
                                <i class="bi bi-person-badge me-1"></i> {{ $dados['nome'] }}
                            </span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-success border border-success fs-6">
                                    R$ {{ number_format($dados['total'], 2, ',', '.') }}
                                </span>
                                @if ($dados['total'] > 0)
                                    <a href="{{ route('financeiro.prof.liquidar', $dados['professor_id']) }}"
                                        class="btn btn-sm btn-primary shadow-sm">
                                        <i class="bi bi-check-circle me-1"></i> Liquidar
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr class="small text-uppercase">
                                            <th class="ps-3">Data</th>
                                            <th>Unidade</th>
                                            <th>Valor</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($dados['itens'] as $aula)
                                            <tr>
                                                <td class="ps-3 small">{{ date('d/m', strtotime($aula['data'])) }}</td>
                                                <td><small class="text-muted">{{ $aula['unidade'] }}</small></td>
                                                <td class="fw-bold">R$ {{ number_format($aula['valor'], 2, ',', '.') }}</td>
                                                <td class="text-center">
                                                    @if ($aula['pago'])
                                                        <span class="badge bg-warning-subtle text-warning border border-warning" style="font-size: 0.7rem;">
                                                            <i class="bi bi-hourglass-split"></i> PENDENTE PGTO
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger border border-danger" style="font-size: 0.7rem;">
                                                            <i class="bi bi-x-circle"></i> SEM PRESENÇA
                                                        </span>
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
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-5 text-center">
                        <i class="bi bi-check2-all display-1 text-muted"></i>
                        <h4 class="text-muted mt-3">Tudo em dia! Nenhuma pendência para este mês.</h4>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
    {{-- CSS/JS do DataTables mantidos conforme sua versão --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializa as tabelas com busca e paginação simplificada para caber nos cards
            $('.table').DataTable({
                language: {
                    "sSearch": "Buscar aula:",
                    "oPaginate": { "sNext": ">>", "sPrevious": "<<" }
                },
                pageLength: 5,
                lengthChange: false,
                info: false,
                dom: 'frtip'
            });
        });
    </script>
@endpush