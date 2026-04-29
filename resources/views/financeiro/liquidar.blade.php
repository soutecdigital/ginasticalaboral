@extends('layouts.main')

@section('content')
    <div class="container-fluid px-4 mt-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex justify-content-between align-items-center bg-dark text-white rounded">
                <h5 class="mb-0"><i class="bi bi-receipt"></i> Liquidação de Pagamentos</h5>
                <div class="badge bg-success">Professor: {{ $professor->name }}</div>
            </div>
        </div>

        <form action="{{ route('financeiro.prof.liquidar.store') }}" method="POST">
            @csrf
            <input type="hidden" name="professor_id" value="{{ $professor->id }}">
            @if($pagamentosPendentes->isEmpty())
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> Não há pagamentos pendentes para liquidar neste professor.
                </div>
            @else
            <div class="row">
                {{-- Lado Esquerdo: Lista de Aulas --}}
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header bg-dark text-white">Aulas Confirmadas (Pendentes de Pagamento)</div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all"></th>
                                        <th>Data Ref.</th>
                                        <th>Valor</th>
                                        <th>Unidade</th>
                                        <th>Escala</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pagamentosPendentes as $pg)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="pagamentos_ids[]" value="{{ $pg->id }}"
                                                    data-valor="{{ $pg->valor_pago }}" class="pg-check">
                                            </td>
                                            <td>{{ date('d/m/Y', strtotime($pg->data_referencia)) }}</td>
                                            <td>R$ {{ number_format($pg->valor_pago, 2, ',', '.') }}</td>
                                            <td>{{ $pg->escala->empresa->nome_fantasia ?? 'N/A' }}</td>
                                            <td>#{{ $pg->escala_id }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Lado Direito: Dados da NF e Auditoria --}}
                <div class="col-md-4">
                    <div class="card shadow border-primary">
                        <div class="card-body">
                            <h5 class="card-title">Resumo da Liquidação</h5>
                            <hr>
                            <div class="mb-3">
                                <label>Total Selecionado:</label>
                                <h3 id="total-display">R$ 0,00</h3>
                                <input type="hidden" name="valor_total" id="total-input">
                            </div>

                            <div class="mb-3">
                                <label>Empresa</label>
                                <select name="empresa_id" class="form-select" required>
                                    <option value="" disabled selected>Selecione a empresa</option>
                                    @foreach ($empresas as $empresa)
                                        <option value="{{ $empresa->id }}">{{ $empresa->nome_fantasia }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Mês de Referência</label>
                                <input type="month" name="mes_referencia" class="form-control" required
                                    value="{{ date('Y-m') }}">
                            </div>

                            <div class="mb-3">
                                <label>Número da NF</label>
                                <input type="text" name="numero_nf" class="form-control" required placeholder="Ex: 2024/001">
                            </div>

                            <div class="mb-3">
                                <label>Data do Pagamento</label>
                                <input type="date" name="data_pagamento" class="form-control" required
                                    value="{{ date('Y-m-d') }}">
                            </div>

                            <div class="mb-3">
                                <label>Forma de Pagamento</label>
                                <select name="forma_pagamento" class="form-select">
                                    <option value="banco">Transferência/Banco</option>
                                    <option value="pix">PIX</option>
                                    <option value="dinheiro">Dinheiro</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Observação</label>
                                <textarea name="observacao" class="form-control" rows="3" placeholder="Observações opcionais"></textarea>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-all"></i> Confirmar Pagamento e Vincular NF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
            @endif
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // JavaScript para somar os valores conforme marca os checkboxes
        $('.pg-check').on('change', function() {
            let total = 0;
            $('.pg-check:checked').each(function() {
                total += parseFloat($(this).data('valor'));
            });
            $('#total-display').text(total.toLocaleString('pt-br', {
                style: 'currency',
                currency: 'BRL'
            }));
            $('#total-input').val(total);
        });

        // Selecionar todos
        $('#select-all').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.pg-check').prop('checked', isChecked).trigger('change');
        });
    });
</script>
@endpush