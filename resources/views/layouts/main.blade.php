<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboral Hub - SouTecDigital</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap');

        /* Poka-Yoke: Fundo dinâmico conforme a rota */
        body {
            background-color: {{ Route::is('login') ? '#0f172a' : '#f4f7f6' }};
            font-family: 'Inter', sans-serif;
            margin: 0;
            transition: background-color 0.3s ease;
            {{ Route::is('login') ? 'height: 100vh; display: flex; align-items: center; justify-content: center;' : '' }}
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .auto-close {
            transition: opacity 0.5s ease-out;
        }

        /* Scrollbar Estilo Dark */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 10px;
        }
    </style>
</head>

<body>

    {{-- 1. NAVBAR: Escondida no Login para evitar vazamento visual --}}
    @if (!Route::is('login') && !Route::is('escolha_unidade'))
        @include('layouts.main_nav')
    @endif

    {{-- 2. CONTAINER: No Login ele é neutro, no Sistema ele tem margens --}}
    <div class="{{ Route::is('login') ? '' : 'container-fluid px-lg-5 mt-4' }}">

        <div id="alert-container">
            {{-- Só processa alertas do Layout se NÃO for login --}}
            @if (!Route::is('login'))

                {{-- Homenagem ao Dia do Professor --}}
                @auth
                    @if (now()->format('d/m') == '15/10' && in_array(Auth::user()->perfil, ['professor', 'admin', 'socio']))
                        <div class="alert alert-warning border-0 shadow-lg text-center p-4 rounded-4 mb-4">
                            <h2 class="display-4">🎓</h2>
                            <h3 class="fw-bold">Feliz Dia do Professor, {{ explode(' ', Auth::user()->name)[0] }}!</h3>
                            <p class="mb-0">Seu trabalho transforma vidas. Obrigado por dedicar seu talento!</p>
                        </div>
                    @endif
                @endauth

                {{-- Alertas de Sucesso/Erro Padrão do Sistema --}}
                @if (session('success') || session('error'))
                    <div class="alert alert-{{ session('success') ? 'success' : 'danger' }} alert-dismissible fade show shadow-sm border-0 auto-close"
                        role="alert"
                        style="border-left: 5px solid {{ session('success') ? '#0f5132' : '#842029' }} !important;">
                        <i
                            class="bi bi-{{ session('success') ? 'check-circle-fill' : 'exclamation-triangle-fill' }} me-2"></i>
                        {{ session('success') ?? session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            @endif
        </div>

        {{-- 3. CONTEÚDO DA PÁGINA --}}
        @yield('content')

    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicialização de Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function(el) {
                return new bootstrap.Tooltip(el);
            });

            // Auto-fechar alertas do sistema após 5 segundos
            setTimeout(function() {
                $(".auto-close").fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        });
    </script>

    @stack('scripts')
</body>

</html>
