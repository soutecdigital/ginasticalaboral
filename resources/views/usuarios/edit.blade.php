@extends('layouts.main')

@section('content')
    <div class="container-fluid px-lg-5 mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    {{-- Cabeçalho Estilo Industrial --}}
                    <div class="card-header p-3 text-white d-flex align-items-center justify-content-between"
                        style="background-color: #1a2a40;">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-pencil-square me-2"></i>EDITAR USUÁRIO: {{ strtoupper($usuario->name) }}
                        </h5>
                        <span class="badge bg-light text-dark shadow-sm">ID: {{ $usuario->matricula }}</span>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                {{-- Dados Pessoais --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-secondary">NOME COMPLETO</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        value="{{ $usuario->name }}" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-secondary">E-MAIL (LOGIN)</label>
                                    <input type="email" name="email" id="email" class="form-control"
                                        value="{{ $usuario->email }}" required>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-bold small text-secondary">MATRÍCULA</label>
                                    <input type="text" name="matricula" id="matricula" class="form-control"
                                        value="{{ old('matricula', $usuario->matricula) }}" placeholder="Opcional">
                                    <small class="text-muted">Campo opcional</small>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-bold small text-secondary">CPF</label>
                                    <input type="text" name="cpf" id="cpf"
                                        class="form-control @error('cpf') is-invalid @enderror"
                                        value="{{ old('cpf', $usuario->cpf) }}" placeholder="000.000.000-00">
                                    @error('cpf')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Perfil e Reajuste --}}
                                <div class="col-md-2">
                                    <label class="form-label fw-bold small text-secondary">PERFIL</label>
                                    <select name="perfil" class="form-select">
                                        <option value="admin" {{ $usuario->perfil == 'admin' ? 'selected' : '' }}>
                                            Administrador</option>
                                        <option value="aluno" {{ $usuario->perfil == 'aluno' ? 'selected' : '' }}>Aluno
                                        </option>
                                        <option value="socio" {{ $usuario->perfil == 'socio' ? 'selected' : '' }}>Sócio
                                        </option>
                                        <option value="professor" {{ $usuario->perfil == 'professor' ? 'selected' : '' }}>
                                            Professor</option>
                                    </select>

                                    {{-- POKA-YOKE: Botão de Reajuste visível apenas para Professores --}}
                                    @if ($usuario->perfil == 'professor')
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-warning btn-sm w-100 fw-bold shadow-sm"
                                                data-bs-toggle="modal" data-bs-target="#modalReajuste">
                                                <i class="bi bi-graph-up-arrow me-1"></i> PREÇO & AULAS
                                            </button>
                                            <div class="text-center mt-1">
                                                <small class="text-muted" style="font-size: 0.7rem;">
                                                    AULA NORMAL (Presencial)
                                                    <strong>R$
                                                        {{ number_format($usuario->configuracaoAtual->valor_aula ?? 0, 2, ',', '.') }}</strong>
                                                </small>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Gestão de Vínculos --}}
                                <div class="col-12 mt-4">
                                    <label class="form-label fw-bold small text-primary">
                                        <i class="bi bi-building-check me-1"></i> GESTÃO DE VÍNCULOS (EMPRESAS) <span
                                            class="text-danger">*</span>
                                    </label>
                                    <select name="empresas[]" id="select-empresas-edit"
                                        class="form-select @error('empresas') is-invalid @enderror" multiple="multiple"
                                        required>
                                        @foreach ($empresas as $empresa)
                                            <option value="{{ $empresa->id }}"
                                                {{ in_array($empresa->id, $empresasVinculadas) ? 'selected' : '' }}>
                                                {{ $empresa->nome_fantasia }} -
                                                {{ $empresa->cidade }}/{{ $empresa->estado }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-1">Atualize as unidades que este usuário pode
                                        acessar (obrigatório).</small>
                                    @error('empresas')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Senha --}}
                                <div class="col-md-4 mt-3">
                                    <label class="form-label fw-bold small text-secondary">ALTERAR SENHA</label>
                                    <input type="password" name="password" class="form-control"
                                        placeholder="Deixe em branco para manter a atual">
                                </div>
                            </div>

                            <hr class="my-4 opacity-25">

                            {{-- Botões de Ação --}}
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary px-4 fw-bold">
                                    <i class="bi bi-arrow-left me-1"></i> VOLTAR
                                </a>
                                <button type="submit" class="btn text-white px-5 shadow-sm fw-bold"
                                    style="background-color: #1a2a40;">
                                    <i class="bi bi-check-lg me-1"></i> SALVAR ALTERAÇÕES
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DE REAJUSTE HISTÓRICO --}}

    <div class="modal fade" id="modalReajuste" tabindex="-1" aria-labelledby="modalReajusteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg"> {{-- mudei para modal-lg para caber os 3 campos lado a lado --}}
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark border-0">
                    <h5 class="modal-title fw-bold" id="modalReajusteLabel">
                        <i class="bi bi-currency-dollar"></i> CONFIGURAR VALORES: {{ strtoupper($usuario->name) }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('professores.reajuste', $usuario->id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            {{-- Valor Normal --}}
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">AULA NORMAL (Presencial)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">R$</span>
                                    <input type="number" step="0.01" name="valor_aula" class="form-control" required
                                        value="{{ $usuario->configuracaoAtual->valor_aula ?? '' }}">
                                </div>
                            </div>

                            {{-- Valor Online --}}
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-info">AULA ONLINE</label>
                                <div class="input-group border-info">
                                    <span class="input-group-text bg-info text-white">R$</span>
                                    <input type="number" step="0.01" name="valor_aula_online" class="form-control"
                                        required value="{{ $usuario->configuracaoAtual->valor_aula_online ?? '' }}">
                                </div>
                            </div>

                            {{-- Valor Avulso --}}
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-danger">AULA AVULSA / EVENTO</label>
                                <div class="input-group border-danger">
                                    <span class="input-group-text bg-danger text-white">R$</span>
                                    <input type="number" step="0.01" name="valor_aula_avulso" class="form-control"
                                        required value="{{ $usuario->configuracaoAtual->valor_aula_avulso ?? '' }}">
                                </div>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label class="form-label fw-bold small">INÍCIO DA VIGÊNCIA</label>
                                <input type="date" name="data_inicio_vigencia" class="form-control" required
                                    value="{{ date('Y-m-d') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small">MOTIVO DO REAJUSTE / OBSERVAÇÃO</label>
                                <textarea name="observacao" class="form-control" rows="2"
                                    placeholder="Ex: Reajuste anual ou nova modalidade..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-secondary px-4 fw-bold"
                            data-bs-dismiss="modal">CANCELAR</button>
                        <button type="submit" class="btn btn-warning px-4 fw-bold">SALVAR CONFIGURAÇÃO</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Função helper para capitalizar apenas a última palavra (sem perder espaços)
            function capitalizarUltimaPalavra(texto) {
                if (!texto || texto.length === 0) {
                    return texto;
                }

                // Encontra a última palavra
                let ultimoEspacoIndex = texto.lastIndexOf(' ');

                if (ultimoEspacoIndex === -1) {
                    // Não tem espaço, é a primeira palavra
                    return texto.charAt(0).toUpperCase() + texto.slice(1).toLowerCase();
                } else {
                    // Tem espaço, capitaliza só a parte depois do último espaço
                    let parteAnterior = texto.substring(0, ultimoEspacoIndex + 1);
                    let ultimaPalavra = texto.substring(ultimoEspacoIndex + 1);
                    return parteAnterior + ultimaPalavra.charAt(0).toUpperCase() + ultimaPalavra.slice(1).toLowerCase();
                }
            }

            $(document).ready(function() {
                // Poka-Yoke: Inicialização do Select2 para as Empresas
                $('#select-empresas-edit').select2({
                    theme: 'bootstrap-5',
                    placeholder: "Selecione as empresas...",
                    allowClear: true,
                    width: '100%'
                });

                // Máscara de CPF (XXX.XXX.XXX-XX)
                $('#cpf').mask('000.000.000-00', {
                    placeholder: "000.000.000-00"
                });

                // [POKA-YOKE] Capitaliza valores pré-preenchidos (valores do banco de dados)
                $('#name, #matricula').each(function() {
                    let valor = $(this).val();
                    if (valor && valor.trim().length > 0) {
                        // Capitaliza cada palavra
                        let palavras = valor.trim().split(/\s+/);
                        let capitalizado = palavras.map(p => p.charAt(0).toUpperCase() + p.slice(1)
                            .toLowerCase()).join(' ');
                        $(this).val(capitalizado);
                    }
                });

                // [POKA-YOKE] Email pré-preenchido em minúsculas
                $('#email').each(function() {
                    let valor = $(this).val();
                    if (valor && valor.trim().length > 0) {
                        $(this).val(valor.toLowerCase());
                    }
                });

                // [POKA-YOKE] Capitaliza inputs ao vivo - apenas a última palavra digitada
                $('#name, #matricula').on('input', function() {
                    let valor = $(this).val();
                    if (valor && valor.length > 0) {
                        $(this).val(capitalizarUltimaPalavra(valor));
                    }
                });

                // Email automaticamente em minúsculas enquanto digita
                $('#email').on('input', function() {
                    $(this).val($(this).val().toLowerCase());
                });

                // [POKA-YOKE] Ao enviar o form, limpa espaços em branco nos campos opcionais
                $('form').on('submit', function(e) {
                    // Limpa espaços em branco da matrícula (campo opcional)
                    let matriculaField = $('#matricula');
                    if (matriculaField.val().trim() === '') {
                        matriculaField.val(''); // Deixa vazio se só tem espaços
                    }
                    // Trim em name e email
                    $('#name').val($('#name').val().trim());
                    $('#email').val($('#email').val().trim());
                });
            });
        </script>
    @endpush
@endsection
