@extends('layouts.main')

@section('content')
    <div class="container-fluid mt-4 px-4">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h4 class="fw-bold m-0" style="color: #2d3748;">
                    <i class="bi bi-building-plus me-2"></i>Nova Empresa
                </h4>
                <p class="text-muted small m-0">Preencha os dados para cadastrar uma nova organização e gerar o faturamento.
                </p>
            </div>
        </div>

        {{-- Bloco de Erros (Poka-Yoke para Debug) --}}
        @if ($errors->any())
            <div class="alert alert-danger shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Atenção:</h6>
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-4">
                <form action="{{ route('empresas.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        {{-- CNPJ com Busca Automática --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">CNPJ (Busca Automática)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" name="cnpj" id="cnpj"
                                    class="form-control border-start-0 @error('cnpj') is-invalid @enderror"
                                    value="{{ old('cnpj') }}" placeholder="00.000.000/0000-00" required>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-bold small text-muted">Nome Fantasia</label>
                            <input type="text" name="nome_fantasia" id="nome_fantasia" class="form-control"
                                value="{{ old('nome_fantasia') }}" required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">Razão Social</label>
                            <input type="text" name="razao_social" id="razao_social" class="form-control"
                                value="{{ old('razao_social') }}">
                        </div>

                        {{-- Novos Campos de Endereço --}}
                        <div class="col-md-5">
                            <label class="form-label fw-bold small text-muted">Logradouro (Rua)</label>
                            <input type="text" name="logradouro" id="logradouro" class="form-control"
                                value="{{ old('logradouro') }}" placeholder="Ex: Av. Paulista">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-muted">Nº</label>
                            <input type="text" name="numero" id="numero" class="form-control"
                                value="{{ old('numero') }}">
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-bold small text-muted">Bairro</label>
                            <input type="text" name="bairro" id="bairro" class="form-control"
                                value="{{ old('bairro') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">Cidade</label>
                            <input type="text" name="cidade" id="cidade" class="form-control"
                                value="{{ old('cidade') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-muted">UF</label>
                            <input type="text" name="estado" id="estado" class="form-control text-uppercase"
                                maxlength="2" value="{{ old('estado') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Celular / WhatsApp</label>
                            <input type="text" name="celular" id="celular" class="form-control"
                                placeholder="(00) 00000-0000" value="{{ old('celular') }}">
                        </div>

                        {{-- Campos de Geolocalização (Ocultos ou Somente Leitura) --}}
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-primary"><i class="bi bi-geo-alt"></i>
                                Latitude</label>
                            <input type="text" name="lat" id="lat" class="form-control bg-light"
                                value="{{ old('lat') }}" placeholder="-0.000000">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-primary"><i class="bi bi-geo"></i> Longitude</label>
                            <input type="text" name="lng" id="lng" class="form-control bg-light"
                                value="{{ old('lng') }}" placeholder="-0.000000">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Pessoa de Contato</label>
                            <input type="text" name="contato" class="form-control" placeholder="Ex: João da Silva"
                                value="{{ old('contato') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-primary"><i class="bi bi-geo-alt"></i>
                                Latitude</label>
                            <div class="input-group">
                                <input type="text" name="lat" id="lat" class="form-control bg-light"
                                    value="{{ old('lat') }}" placeholder="-0.000000">
                                <button class="btn btn-outline-primary" type="button" id="btn-gps"
                                    title="Buscar Coordenadas">
                                    <i class="bi bi-geo"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Dias da Semana (Compacto) --}}
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted d-block mb-2">Dias de Atendimento</label>
                            <div class="d-flex gap-2">
                                @foreach (['seg' => 'Seg', 'ter' => 'Ter', 'qua' => 'Qua', 'qui' => 'Qui', 'sex' => 'Sex', 'sab' => 'Sáb', 'dom' => 'Dom'] as $key => $label)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="{{ $key }}"
                                            id="check_{{ $key }}" value="1"
                                            {{ old($key) ? 'checked' : '' }}>
                                        <label class="form-check-label small"
                                            for="check_{{ $key }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Plano e Valor Financeiro --}}
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-muted">Plano</label>
                            <select name="plano" class="form-select">
                                <option value="basic" {{ old('plano') == 'basic' ? 'selected' : '' }}>Basic</option>
                                <option value="pro" {{ old('plano') == 'pro' ? 'selected' : '' }}>Pro</option>
                                <option value="premium" {{ old('plano') == 'premium' ? 'selected' : '' }}>Premium</option>
                            </select>
                        </div>

                        @if (in_array(Auth::user()->perfil, ['admin', 'socio']))
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-success">
                                    <i class="bi bi-currency-dollar"></i> Valor Mensal (Contrato)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white border-0">R$</span>
                                    <input type="number" name="valor_contrato" step="0.01"
                                        class="form-control border-success" placeholder="0.00"
                                        value="{{ old('valor_contrato') }}" required>
                                </div>
                            </div>
                        @else
                            <input type="hidden" name="valor_contrato" value="0">
                        @endif

                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Observações Internas</label>
                            <textarea name="observacao" class="form-control" rows="3">{{ old('observacao') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('empresas.index') }}" class="btn btn-light px-4 fw-bold">CANCELAR</a>
                        <button type="submit" class="btn text-white px-5 fw-bold" style="background-color: #1a2a40;">
                            <i class="bi bi-save me-2"></i> SALVAR EMPRESA
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@push('scripts')
<script>
    $(document).ready(function() {
        // FUNÇÃO 1: Busca de dados do CNPJ (Prioridade Total)
        $('#cnpj').blur(function() {
            let cnpj = $(this).val().replace(/\D/g, '');
            if (cnpj.length === 14) {
                $('#nome_fantasia').val('Buscando dados...').addClass('bg-light');
                let btnSalvar = $('button[type="submit"]');
                
                // Trava o botão para evitar envio sem os dados básicos
                btnSalvar.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

                $.getJSON(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`, function(data) {
                    if (!("error" in data)) {
                        $('#nome_fantasia').val(data.nome_fantasia || data.razao_social).removeClass('bg-light');
                        $('#razao_social').val(data.razao_social);
                        $('#logradouro').val(data.logradouro);
                        $('#bairro').val(data.bairro);
                        $('#cidade').val(data.municipio);
                        $('#estado').val(data.uf);
                        $('#numero').val(data.numero);

                        // LIBERA O BOTÃO IMEDIATAMENTE após os dados de faturamento chegarem
                        btnSalvar.prop('disabled', false).html('<i class="bi bi-save me-2"></i> SALVAR EMPRESA');
                    }
                }).fail(function() {
                    btnSalvar.prop('disabled', false).html('<i class="bi bi-save me-2"></i> SALVAR EMPRESA');
                    $('#nome_fantasia').removeClass('bg-light').val('');
                });
            }
        });

        // FUNÇÃO 2: Busca de GPS (Apenas se o usuário clicar)
        $('#btn-gps').click(function() {
            let logradouro = $('#logradouro').val();
            let numero = $('#numero').val();
            let cidade = $('#cidade').val();
            let uf = $('#estado').val();

            if (!logradouro || !cidade) {
                alert("Preencha o endereço primeiro para buscar as coordenadas.");
                return;
            }

            let enderecoCompleto = `${logradouro}, ${numero}, ${cidade}, ${uf}, Brasil`;
            $(this).html('<span class="spinner-border spinner-border-sm"></span>');

            $.getJSON(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURI(enderecoCompleto)}`, (geo) => {
                if (geo.length > 0) {
                    $('#lat').val(geo[0].lat);
                    $('#lng').val(geo[0].lon);
                    $(this).html('<i class="bi bi-check-lg"></i>').addClass('btn-success').removeClass('btn-outline-primary');
                } else {
                    alert("Coordenadas não encontradas para este endereço.");
                    $(this).html('<i class="bi bi-geo"></i>');
                }
            });
        });
    });
</script>
@endpush
