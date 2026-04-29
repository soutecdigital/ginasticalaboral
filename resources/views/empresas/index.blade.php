@extends('layouts.main')

@section('content')
    <div class="container-fluid mt-4 px-4">
        {{-- Cabeçalho --}}
        <div class="row align-items-center mb-4">
            <div class="col">
                <h4 class="fw-bold m-0" style="color: #2d3748;">Gestão de Empresas</h4>
                <p class="text-muted small m-0">Visualize e gerencie as organizações cadastradas</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('empresas.create') }}" class="btn btn-primary px-4 fw-bold shadow-sm"
                    style="background-color: #1a2a40; border: none; border-radius: 8px;">
                    <i class="bi bi-plus-lg me-1"></i> NOVA EMPRESA
                </a>
            </div>
        </div>

        {{-- Tabela dentro do Card --}}
        <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body p-4">
                <div class="table-responsive">
                    {{-- Usando a classe global 'datatable-laravel' --}}
                    <table class="table table-hover mb-0 align-middle datatable-laravel" data-title="Lista de Empresas">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Empresa</th>
                                <th>Documento</th>
                                <th>Localização</th>
                                <th>Celular</th>
                                <th>Status</th>
                                {{-- Adicionado 'no-export' para limpar o Excel --}}
                                <th class="text-end pe-4 no-export">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($empresas as $empresa)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $empresa->nome_fantasia }}</div>
                                        <div class="text-muted small">{{ $empresa->razao_social }}</div>
                                    </td>
                                    <td class="text-secondary small">{{ $empresa->cnpj }}</td>
                                    <td class="text-secondary small">{{ $empresa->cidade }} - {{ $empresa->estado }}</td>
                                    <td>
                                        @if ($empresa->celular)
                                            <a href="https://wa.me/55{{ preg_replace('/\D/', '', $empresa->celular) }}"
                                                target="_blank" class="text-success text-decoration-none">
                                                <i class="bi bi-whatsapp"></i> {{ $empresa->celular }}
                                            </a>
                                        @else
                                            <span class="text-muted small">Não informado</span>
                                        @endif
                                    </td>

                                    <td>
                                        <span
                                            class="badge rounded-pill px-3 {{ $empresa->ativo ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                            ● {{ $empresa->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="{{ route('empresas.edit', $empresa->id) }}"
                                                class="btn btn-sm btn-outline-primary border-0" title="Editar">
                                                <i class="bi bi-pencil fs-5"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-info border-0 btn-visualizar"
                                                title="Visualizar" data-bs-toggle="modal" data-bs-target="#modalVisualizar"
                                                data-nome="{{ $empresa->nome_fantasia }}"
                                                data-razao="{{ $empresa->razao_social }}" data-cnpj="{{ $empresa->cnpj }}"
                                                data-contato="{{ $empresa->contato }}"
                                                data-celular="{{ $empresa->celular }}"
                                                data-local="{{ $empresa->cidade }} - {{ $empresa->estado }}, {{ $empresa->numero }}"
                                                data-obs="{{ $empresa->observacao }}"
                                                data-plano="{{ strtoupper($empresa->plano) }}">
                                                <i class="bi bi-eye fs-5"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                {{-- O DataTables cuida da mensagem de "vazio", mas mantemos o forelse por segurança --}}
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Visualização --}}
    <div class="modal fade" id="modalVisualizar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h5 class="fw-bold m-0" id="v-nome"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="small text-muted d-block">CNPJ</label>
                            <span class="fw-bold" id="v-cnpj"></span>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted d-block">Plano</label>
                            <span class="badge bg-primary-subtle text-primary border" id="v-plano"></span>
                        </div>
                        <div class="col-12">
                            <label class="small text-muted d-block">Razão Social</label>
                            <span id="v-razao"></span>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted d-block">Contato</label>
                            <span id="v-contato"></span>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted d-block">Celular</label>
                            <span id="v-celular"></span>
                        </div>
                        <div class="col-12 border-top pt-2">
                            <label class="small text-muted d-block">Localização</label>
                            <span id="v-local"></span>
                        </div>
                        <div class="col-12 bg-light p-3 rounded mt-3">
                            <label class="small text-muted d-block fw-bold text-uppercase"
                                style="font-size: 0.7rem;">Observações Internas</label>
                            <p class="m-0 small text-dark" id="v-obs"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            // Usamos $(document).on para que o clique funcione mesmo se a tabela for filtrada ou paginada
            $(document).on('click', '.btn-visualizar', function() {
                const btn = $(this);

                // Preenche os campos do modal
                $('#v-nome').text(btn.data('nome'));
                $('#v-cnpj').text(btn.data('cnpj'));
                $('#v-razao').text(btn.data('razao') || 'Não informada');
                $('#v-contato').text(btn.data('contato') || 'Não informado');
                $('#v-celular').text(btn.data('celular') || 'Não informado');
                $('#v-local').text(btn.data('local'));
                $('#v-obs').text(btn.data('obs') || 'Sem observações.');
                $('#v-plano').text(btn.data('plano'));
            });
        });
    </script>
@endpush
