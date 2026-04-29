@extends('layouts.main')

@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Poppins:wght@600&display=swap');

        /* Poka-Yoke: Remove a Navbar padrão */
        nav {
            display: none !important;
        }

        /* Centralização Total e Fundo Dark */
        body {
            background-color: #0f172a;
            font-family: 'Inter', sans-serif;
            margin: 0;
            height: 100vh;
            width: 100vw;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Container de Alertas (Largura controlada) */
        .alert-container {
            width: 100%;
            max-width: 400px;
            margin-bottom: 1rem;
            z-index: 10;
        }

        .custom-alert {
            background: rgba(16, 185, 129, 0.2) !important;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(16, 185, 129, 0.4) !important;
            color: #10b981 !important;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4);
        }

        /* Card com Efeito Glassmorphism */
        .login-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2.5rem;
            border-radius: 24px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            z-index: 2;
        }

        /* Resto dos seus estilos permanecem iguais... */
        .icon-circle {
            width: 55px;
            height: 55px;
            background: linear-gradient(135px, #3b82f6 0%, #2563eb 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.2rem;
            font-size: 1.4rem;
            box-shadow: 0 8px 15px rgba(59, 130, 246, 0.3);
        }

        .form-label {
            color: #94a3b8;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .input-group-text {
            background-color: #1e293b !important;
            border: 1px solid #334155;
            border-right: none;
            color: #64748b !important;
            border-radius: 12px 0 0 12px !important;
            padding-left: 1rem;
        }

        .form-control {
            background: #1e293b !important;
            border: 1px solid #334155;
            color: #ffffff !important;
            padding: 0.75rem 1rem;
            border-radius: 0 12px 12px 0 !important;
            transition: all 0.3s ease;
        }

        .form-control::placeholder {
            color: #475569 !important;
        }

        .input-group:focus-within .input-group-text {
            border-color: #3b82f6;
            color: #3b82f6 !important;
        }

        .input-group:focus-within .form-control {
            border-color: #3b82f6;
            box-shadow: none;
            outline: none;
        }

        .btn-primary {
            background: #3b82f6;
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
            letter-spacing: 1px;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
        }

        .footer-fixed {
            position: fixed;
            bottom: 25px;
            left: 0;
            right: 0;
            text-align: center;
            z-index: 1;
        }

        .wa-link {
            text-decoration: none;
            color: rgba(148, 163, 184, 0.5);
            display: inline-block;
        }

        .wa-link h6 {
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            color: #cbd5e1;
            margin: 0;
            letter-spacing: 2px;
        }

        .footer-subtext {
            font-size: 0.6rem;
            text-transform: uppercase;
            display: block;
            letter-spacing: 1px;
        }

        .wa-link:hover h6 {
            color: #22c55e;
            text-shadow: 0 0 10px rgba(34, 197, 94, 0.8);
        }

        .motivation-phrase {
            height: 25px;
            /* Reserva o espaço fixo */
            font-size: 0.8rem;
            color: #60a5fa;
            /* Um azul um pouco mais claro para legibilidade */
            font-weight: 500;
            text-align: center;
            margin-top: -10px;
            /* Puxa para cima para ficar perto do título */
            margin-bottom: 15px;
            opacity: 0;
            transition: all 0.3s ease;
            transform: translateY(5px);
        }

        .motivation-phrase.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>

    {{-- Bloco Adicionado para exibir a Mensagem de Sucesso (Logout) --}}
    @if (session('success'))
        <div class="alert-container">
            <div class="alert alert-success custom-alert alert-dismissible fade show border-0" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <div class="login-card">
        <div class="icon-circle" id="motivation-trigger">
            <span style="filter: drop-shadow(0 0 3px white);">🧘‍♂️</span>
        </div>


        <h5 class="text-center text-white fw-bold mb-4">Laboral Hub</h5>
        <div id="motivation-text" class="motivation-phrase"></div>
        <form action="{{ url('/login') }}" method="POST" id="login-form">
            @csrf
            <div class="mb-3">
                <label class="form-label">Identificação</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-plus-fill"></i></span>
                    <input type="text" name="login" class="form-control" placeholder="E-mail, CPF ou Matrícula"
                        value="{{ old('login') }}" required autocomplete="off">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Senha</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                @if ($errors->has('login_error'))
                    <div class="alert alert-danger border-0 bg-transparent text-danger small mt-2 fw-bold text-center p-0"
                        style="font-size: 0.75rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i> {{ $errors->first('login_error') }}
                    </div>
                @endif
            </div>

            <button type="submit" class="btn btn-primary w-100 shadow">ENTRAR</button>
        </form>
    </div>

    <footer class="footer-fixed">
        <a href="https://soutecdigital.com.br" target="_blank" class="wa-link" data-bs-toggle="tooltip"
            data-bs-placement="top" title="Visite nosso site: www.soutecdigital.com.br">

            <span class="footer-subtext">Distribuído por</span>
            <h6>SOUTECDIGITAL</h6>
            <span class="footer-subtext" style="font-size: 0.5rem; opacity: 0.7;">Modernizando sua empresa</span>
        </a>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const phrases = [
                "Um corpo ativo, uma mente brilhante! 🚀",
                "Pequenas pausas, grandes resultados. ✨",
                "Sua postura hoje define sua saúde amanhã. 🧘‍♂️",
                "Movimente-se! Seu corpo agradece. ❤️",
                "A produtividade começa com o bem-estar. 📈",
                "Respire fundo e estique-se. Você merece!",
                "Ginástica Laboral: o combustível do seu dia. 🔋"
            ];

            const textTarget = document.getElementById('motivation-text');
            const inputs = document.querySelectorAll('#login-form input');

            // Função para mostrar frase aleatória
            const showMotivation = () => {
                if (!textTarget.classList.contains('show')) {
                    const randomPhrase = phrases[Math.floor(Math.random() * phrases.length)];
                    textTarget.innerText = randomPhrase;
                    textTarget.classList.add('show');
                }
            };

            // Função para esconder
            const hideMotivation = () => {
                textTarget.classList.remove('show');
            };

            // Poka-Yoke: Aplica nos inputs de Login e Senha
            inputs.forEach(input => {
                input.addEventListener('focus', showMotivation);
                input.addEventListener('blur', hideMotivation);
            });

            // Mantém o efeito do mouse no ícone também, por garantia
            const trigger = document.getElementById('motivation-trigger');
            trigger.addEventListener('mouseenter', showMotivation);
            trigger.addEventListener('mouseleave', hideMotivation);
        });

        setTimeout(function() {
            let alert = document.querySelector('.custom-alert');
            if (alert) {
                // Poka-Yoke: Verifica se o Bootstrap está carregado para fechar
                if (typeof bootstrap !== 'undefined') {
                    let bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                } else {
                    alert.style.display = 'none';
                }
            }
        }, 4000);
    </script>
@endsection
