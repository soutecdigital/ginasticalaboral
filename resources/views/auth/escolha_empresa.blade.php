@extends('layouts.main')

@section('content')
    <style>
        /* Reset total para centralização absoluta */
        body {
            background-color: #f4f7f6 !important;
            margin: 0;
            padding: 0;
            height: 100vh;
            width: 100vw;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Container que evita o "vazamento" da navbar no mobile */
        .mobile-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 15px;
            /* Margem de segurança para telas pequenas */
        }

        .escolha-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 2rem 1.5rem;
            width: 100%;
            max-width: 420px;
            /* Largura ideal para desktop e mobile */
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .unidades-list {
            margin-top: 1.5rem;
            max-height: 60vh;
            /* Scroll se houver muitas empresas */
            overflow-y: auto;
            padding-right: 5px;
        }

        .btn-unidade {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #334155;
            padding: 1rem;
            border-radius: 16px;
            margin-bottom: 0.8rem;
            transition: all 0.2s ease;
            text-align: left;
            display: flex;
            align-items: center;
            text-decoration: none;
            -webkit-tap-highlight-color: transparent;
            /* Remove brilho azul ao tocar no mobile */
        }

        .btn-unidade:active {
            transform: scale(0.97);
            /* Efeito de clique no touch */
            background: #eff6ff;
        }

        .unidade-icon {
            width: 42px;
            height: 42px;
            background: #3b82f6;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: white;
            flex-shrink: 0;
            /* Impede o ícone de amassar no mobile */
        }

        .info-unidade h6 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
        }

        .info-unidade small {
            color: #64748b;
            font-size: 0.75rem;
        }

        /* Ajuste para telas muito pequenas (iPhone SE) */
        @media (max-width: 370px) {
            .escolha-card {
                padding: 1.5rem 1rem;
            }

            .info-unidade h6 {
                font-size: 0.85rem;
            }
        }
    </style>

    <div class="mobile-wrapper">
        <div class="escolha-card shadow">
            <div class="mb-4">
                <i class="bi bi-person-walking text-primary fs-1"></i>
                <h4 class="fw-bold text-dark mt-2">Olá, {{ explode(' ', Auth::user()->name)[0] }}!</h4>
                <p class="text-muted small mb-2">Selecione uma unidade para acessar o sistema.</p>

                <div class="d-inline-flex align-items-center p-2 rounded-3"
                    style="background-color: #f1f5f9; border: 1px dashed #cbd5e1;">
                    <i class="bi bi-info-circle-fill text-primary me-2" style="font-size: 0.8rem;"></i>
                    <span style="font-size: 0.72rem; color: #475569; line-height: 1.2;">
                        As unidades abaixo são exibidas conforme sua <strong>permissão de acesso</strong> vinculada ao
                        cadastro definido pela empresa.
                    </span>
                </div>
            </div>

            <div class="unidades-list">
                @forelse($unidades as $unidade)
                    <a href="{{ route('selecionar_empresa', $unidade->id) }}" class="btn-unidade shadow-sm">
                        <div class="unidade-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div class="info-unidade">
                            <h6>{{ $unidade->nome_fantasia }}</h6>
                            <small>{{ $unidade->cidade }} - {{ $unidade->estado }}</small>
                        </div>
                        <i class="bi bi-chevron-right ms-auto opacity-25"></i>
                    </a>
                @empty
                    <div class="alert alert-warning border-0 small">
                        Nenhuma unidade vinculada ao seu perfil. Procure o suporte.
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="text-muted small text-decoration-none">
                    <i class="bi bi-box-arrow-left"></i> Sair do sistema
                </a>
            </div>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        </div>
    </div>
@endsection
