@extends('layouts.main')

@section('content')
    <div class="container-fluid mt-4 px-4">
        {{-- Cabeçalho --}}
        <div class="row align-items-center mb-4">
            <div class="col">
                <h4 class="fw-bold m-0" style="color: #2d3748;">
                    <i class="bi bi-pencil-square me-2"></i>Editar Cadastro e Contrato
                </h4>
                <p class="text-muted small m-0">Alterando informações de: <strong>{{ $empresa->nome_fantasia }}</strong></p>
            </div>
            <div class="col-auto">
                <a href="{{ route('empresas.index') }}" class="btn btn-outline-secondary px-3 fw-bold border-0">
                    <i class="bi bi-arrow-left me-1"></i> VOLTAR
                </a>
            </div>
        </div>

        {{-- Bloco de Erros --}}
        @if ($errors->any())
            <div class="alert alert-danger shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <ul class="mb-0 small fw-bold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-4">
                <form action="{{ route('empresas.update', $empresa->id) }}" method="POST" id="formEditarEmpresa">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        {{-- Cadastro Básico --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">CNPJ</label>
                            <input type="text" name="cnpj" id="cnpj" class="form-control bg-light"
                                value="{{ old('cnpj', $empresa->cnpj) }}" required>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-bold small text-muted">Nome Fantasia</label>
                            <input type="text" name="nome_fantasia" id="nome_fantasia" class="form-control"
                                value="{{ old('nome_fantasia', $empresa->nome_fantasia) }}" required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">Razão Social</label>
                            <input type="text" name="razao_social" class="form-control"
                                value="{{ old('razao_social', $empresa->razao_social) }}">
                        </div>

                        {{-- Endereço Detalhado --}}
                        <div class="col-md-5">
                            <label class="form-label fw-bold small text-muted">Logradouro (Rua)</label>
                            <input type="text" name="logradouro" id="logradouro" class="form-control"
                                value="{{ old('logradouro', $empresa->logradouro) }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-muted">Nº</label>
                            <input type="text" name="numero" id="numero" class="form-control"
                                value="{{ old('numero', $empresa->numero) }}">
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-bold small text-muted">Bairro</label>
                            <input type="text" name="bairro" id="bairro" class="form-control"
                                value="{{ old('bairro', $empresa->bairro) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">Cidade</label>
                            <input type="text" name="cidade" id="cidade" class="form-control"
                                value="{{ old('cidade', $empresa->cidade) }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-muted">UF</label>
                            <input type="text" name="estado" id="estado" class="form-control text-uppercase"
                                maxlength="2" value="{{ old('estado', $empresa->estado) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Celular / WhatsApp</label>
                            <input type="text" name="celular" id="celular" class="form-control"
                                value="{{ old('celular', $empresa->celular) }}">
                        </div>

                        {{-- Geolocalização --}}
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-primary"><i class="bi bi-geo-alt"></i>
                                Latitude</label>
                            <input type="text" name="lat" id="lat" class="form-control bg-light fw-bold"
                                value="{{ old('lat', $empresa->lat) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-primary"><i class="bi bi-geo"></i> Longitude</label>
                            <input type="text" name="lng" id="lng" class="form-control bg-light fw-bold"
                                value="{{ old('lng', $empresa->lng) }}">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-primary btn-sm mb-1"
                                onclick="buscarCoordenadas()">
                                <i class="bi bi-search"></i> Localizar
                            </button>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">Pessoa de Contato</label>
                            <input type="text" name="contato" class="form-control"
                                value="{{ old('contato', $empresa->contato) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">Plano</label>
                            <select name="plano" class="form-select">
                                <option value="basic" {{ $empresa->plano == 'basic' ? 'selected' : '' }}>Basic</option>
                                <option value="pro" {{ $empresa->plano == 'pro' ? 'selected' : '' }}>Pro</option>
                                <option value="premium" {{ $empresa->plano == 'premium' ? 'selected' : '' }}>Premium
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-muted">Status do Cadastro</label>
                            <select name="ativo"
                                class="form-select fw-bold {{ $empresa->ativo ? 'text-success' : 'text-danger' }}">
                                <option value="1" {{ $empresa->ativo == 1 ? 'selected' : '' }}>● ATIVO</option>
                                <option value="0" {{ $empresa->ativo == 0 ? 'selected' : '' }}>● INATIVO</option>
                            </select>
                        </div>

                        {{-- BLOCO FINANCEIRO --}}
                        @if (in_array(Auth::user()->perfil, ['admin', 'socio']))
                            <div class="col-md-12 mt-3">
                                <div class="p-4 rounded-3 border-start border-4 border-primary bg-light shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <label class="form-label fw-bold small text-primary m-0">
                                            <i class="bi bi-graph-up-arrow me-1"></i> GESTÃO DE CONTRATO
                                        </label>
                                        <button type="button" class="btn btn-sm btn-primary fw-bold shadow-sm"
                                            data-bs-toggle="modal" data-bs-target="#modalReajuste">
                                            <i class="bi bi-pencil me-1"></i> REAJUSTAR VALOR
                                        </button>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small text-muted">Mensalidade Atual
                                                (R$)</label>
                                            <div class="input-group">
                                                <span
                                                    class="input-group-text bg-white border-primary text-primary">R$</span>
                                                @php $valorExibir = $ultimaFatura->valor_mensalidade ?? $empresa->valor_contrato; @endphp
                                                <input type="text" id="display_valor"
                                                    class="form-control border-primary fw-bold bg-white"
                                                    value="{{ number_format($valorExibir, 2, ',', '.') }}" readonly>
                                                <input type="hidden" name="valor_contrato" id="valor_contrato_hidden"
                                                    value="{{ $empresa->valor_contrato }}">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small text-muted">Dia Vencimento</label>
                                            <input type="number" name="dia_vencimento" min="1" max="31"
                                                class="form-control border-primary fw-bold"
                                                value="{{ $empresa->dia_vencimento ?? 10 }}" required>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small text-muted">Alunos Atual</label>
                                            <input type="text" class="form-control border-primary bg-light fw-bold"
                                                value="{{ $totalAlunosAtual }}" readonly>
                                            <input type="hidden" name="total_alunos" value="{{ $totalAlunosAtual }}">
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <small class="text-muted">Motivo Pendente: <strong id="resumo_motivo"
                                                class="text-primary">Nenhuma alteração</strong></small>
                                        <input type="hidden" name="motivo_alteracao" id="motivo_hidden">
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Checkboxes de Dias (Compacto) --}}
                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold small text-muted mb-2"><i
                                    class="bi bi-calendar-week me-1"></i> DIAS DE AULA</label>
                            <div class="d-flex gap-2">
                                @foreach (['seg' => 'Segunda', 'ter' => 'Terça', 'qua' => 'Quarta', 'qui' => 'Quinta', 'sex' => 'Sexta', 'sab' => 'Sábado', 'dom' => 'Domingo'] as $dia => $label)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="{{ $dia }}"
                                            value="1" {{ $empresa->$dia ? 'checked' : '' }}
                                            id="check_{{ $dia }}">
                                        <label class="form-check-label small"
                                            for="check_{{ $dia }}">{{ substr($label, 0, 3) }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Observações</label>
                            <textarea name="observacao" class="form-control" rows="3">{{ $empresa->observacao }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('empresas.index') }}"
                            class="btn btn-light px-4 fw-bold text-muted">CANCELAR</a>
                        <button type="submit" class="btn text-white px-5 fw-bold shadow-sm"
                            style="background-color: #1a2a40;">
                            <i class="bi bi-check-lg me-2"></i> ATUALIZAR EMPRESA
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL DE REAJUSTE --}}
    @if (in_array(Auth::user()->perfil, ['admin', 'socio']))
        {{-- Seu modal de reajuste se mantém igual --}}
        <div class="modal fade" id="modalReajuste" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold">Reajuste de Contrato</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Valor Original (Bloqueado)</label>
                            <input type="text" id="valor_original_modal" class="form-control bg-light fw-bold"
                                readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Novo Valor (R$)</label>
                            <input type="number" id="novo_valor_modal" step="0.01"
                                class="form-control border-primary fw-bold">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-danger">Motivo da Alteração *</label>
                            <textarea id="motivo_modal" class="form-control" rows="3" placeholder="Obrigatório para o histórico..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">VOLTAR</button>
                        <button type="button" id="btnAplicarReajuste"
                            class="btn btn-primary fw-bold px-4">APLICAR</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

 @push('scripts')
    <script>
        // Função de busca (fora do ready para ser acessível pelo onclick do botão)
        function buscarCoordenadas() {
            const logradouro = document.getElementById('logradouro').value;
            const numero = document.getElementById('numero').value;
            const cidade = document.getElementById('cidade').value;
            const bairro = document.getElementById('bairro').value;

            if (!logradouro || !cidade) {
                alert("Preencha o logradouro e a cidade para localizar as coordenadas.");
                return;
            }

            const enderecoCompleto = `${logradouro}, ${numero}, ${bairro}, ${cidade}, Brasil`;

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(enderecoCompleto)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        document.getElementById('lat').value = data[0].lat;
                        document.getElementById('lng').value = data[0].lon;
                        alert("Coordenadas localizadas com sucesso!");
                    } else {
                        alert("Não foi possível encontrar as coordenadas. Verifique o endereço.");
                    }
                })
                .catch(error => {
                    console.error("Erro na busca:", error);
                    alert("Erro ao conectar com o serviço de mapas.");
                });
        }

        $(document).ready(function() {
            // Máscaras existentes
            $('#cnpj').mask('00.000.000/0000-00');
            $('#celular').mask('(00) 00000-0000');

            // Lógica do Modal de Reajuste (mantida)
            $('#modalReajuste').on('show.bs.modal', function() {
                let valorFormatado = $('#display_valor').val();
                let valorPuro = $('#valor_contrato_hidden').val();
                setTimeout(function() {
                    $('#valor_original_modal').val(valorFormatado);
                    $('#novo_valor_modal').val(parseFloat(valorPuro).toFixed(2));
                }, 150);
            });

            $('#btnAplicarReajuste').on('click', function() {
                let novo = $('#novo_valor_modal').val();
                let motivo = $('#motivo_modal').val();
                if (!novo || !motivo) {
                    alert('Motivo obrigatório!');
                    return;
                }
                $('#valor_contrato_hidden').val(novo);
                $('#motivo_hidden').val(motivo);
                $('#display_valor').val(parseFloat(novo).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));
                $('#resumo_motivo').text(motivo);
                $('#modalReajuste').modal('hide');
            });
        });
    </script>
@endpush
@endsection
