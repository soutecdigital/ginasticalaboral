@extends('layouts.main')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header p-3 text-white" style="background-color: #1a2a40;">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus-fill me-2"></i>NOVO USUÁRIO</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('usuarios.store') }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                {{-- Dados Básicos --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-secondary">NOME</label>
                                    <input type="text" name="name" id="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        placeholder="Ex: Nome Completo" value="{{ old('name') }}" required>
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-secondary">CPF</label>
                                    <input type="text" name="cpf" id="cpf"
                                        class="form-control @error('cpf') is-invalid @enderror"
                                        placeholder="Ex: 123.456.789-00" value="{{ old('cpf') }}" required>
                                    @error('cpf')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-secondary">E-MAIL (LOGIN)</label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        placeholder="email@exemplo.com" value="{{ old('email') }}" required>
                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-secondary">MATRÍCULA</label>
                                    <input type="text" name="matricula" id="matricula"
                                        class="form-control @error('matricula') is-invalid @enderror"
                                        placeholder="Digite a matrícula (opcional)" value="{{ old('matricula') }}">
                                    <small class="text-muted">Campo opcional</small>
                                    @error('matricula')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-secondary">PERFIL</label>
                                    <select name="perfil" class="form-select @error('perfil') is-invalid @enderror">
                                        <option disabled selected value="">Selecione um perfil</option>
                                        <option value="admin" {{ old('perfil') == 'admin' ? 'selected' : '' }}>
                                            Administrador</option>
                                        <option value="aluno" {{ old('perfil') == 'aluno' ? 'selected' : '' }}>Aluno
                                        </option>
                                        <option value="socio" {{ old('perfil') == 'socio' ? 'selected' : '' }}>Sócio
                                        </option>
                                        <option value="professor" {{ old('perfil') == 'professor' ? 'selected' : '' }}>
                                            Professor</option>
                                    </select>
                                    @error('perfil')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-6 mt-3">
                                    <label class="form-label fw-bold small text-secondary">SENHA</label>
                                    <input type="password" name="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror" placeholder="*******"
                                        required>
                                    @error('password')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-6 mt-3">
                                    <label class="form-label fw-bold small text-secondary">CONFIRMAR SENHA</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control @error('password_confirmation') is-invalid @enderror"
                                        placeholder="*******" required>
                                    @error('password_confirmation')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- SELEÇÃO DE EMPRESAS (OBRIGATÓRIO) --}}
                                <div class="col-12 mt-4">
                                    <label class="form-label fw-bold small text-primary">
                                        <i class="bi bi-building-check me-1"></i> VINCULAR ÀS EMPRESAS <span
                                            class="text-danger">*</span>
                                    </label>
                                    <select name="empresas[]" id="select-empresas"
                                        class="form-control select2-multiple @error('empresas') is-invalid @enderror"
                                        multiple="multiple" required>
                                        @foreach ($empresas as $empresa)
                                            <option value="{{ $empresa->id }}"
                                                {{ isset($empresasVinculadas) && in_array($empresa->id, $empresasVinculadas) ? 'selected' : '' }}>
                                                {{ $empresa->nome_fantasia }}
                                                ({{ $empresa->cidade }}/{{ $empresa->estado }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Selecione uma ou mais empresas para este usuário
                                        (obrigatório).</small>
                                    @error('empresas')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                    @enderror
                                </div>


                            </div>

                            <hr class="my-4 opacity-25">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('usuarios.index') }}"
                                    class="btn btn-light px-4 fw-bold text-secondary">CANCELAR</a>
                                <button type="submit" class="btn text-white px-5 shadow"
                                    style="background-color: #1a2a40;">
                                    <i class="bi bi-save me-1"></i> FINALIZAR CADASTRO
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts para o Select2 funcionar --}}
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
                // Inicializa o Select2 com o tema do Bootstrap 5 que você já importou no head
                $('#select-empresas').select2({
                    theme: 'bootstrap-5',
                    placeholder: "Digite o nome da empresa...",
                    allowClear: true,
                    width: '100%',
                    language: {
                        noResults: function() {
                            return "Nenhuma empresa encontrada";
                        }
                    }
                });

                // Máscara de CPF (XXX.XXX.XXX-XX)
                $('#cpf').mask('000.000.000-00', {
                    placeholder: "000.000.000-00"
                });

                // [POKA-YOKE] Capitaliza valores pré-preenchidos (old values)
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

                // [POKA-YOKE] Capitaliza inputs ao vivo - apenas a última palavra digitada
                $('#name, #matricula').on('input', function() {
                    let valor = $(this).val();
                    if (valor && valor.length > 0) {
                        $(this).val(capitalizarUltimaPalavra(valor));
                    }
                });

                // Email automaticamente em minúsculas enquanto digita
                $('input[type="email"]').on('input', function() {
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
                    $('input[type="email"]').val($('input[type="email"]').val().trim());

                    // [IMPORTANTE] Remove espaços em branco dos valores ANTES de enviar
                    // Isso evita que o backend receba valores com espaços que causam erro
                    $('#name').val($('#name').val().trim());
                    $('input[type="email"]').val($('input[type="email"]').val().trim());
                    $('#matricula').val($('#matricula').val().trim() || ''); // Vazio se só espaços
                });
            });
        </script>
    @endpush
@endsection
