<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #1a2a40;">
    <div class="container">
        {{-- LOGO --}}
        <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ url('/') }}">
            <i class="bi bi-person-walking text-warning me-2 fs-3"></i>
            <span class="tracking-tight">LABORAL APP</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                @auth
                    {{-- GESTÃO (Admin e Sócio) --}}
                    @if (Auth::user()->perfil === 'admin' || Auth::user()->perfil === 'socio')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#"
                                id="dropGestao" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear-fill me-1"></i> Gestão
                            </a>
                            <ul class="dropdown-menu shadow border-0 mt-2">
                                <li><a class="dropdown-item" href="{{ route('empresas.index') }}"><i
                                            class="bi bi-building me-2 text-primary"></i>Empresas</a></li>
                                <li><a class="dropdown-item" href="{{ route('usuarios.index') }}"><i
                                            class="bi bi-people-fill me-2 text-success"></i>Usuários</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item fw-bold text-success" href="{{ route('faturamento.index') }}">
                                        <i class="bi bi-cash-coin me-2"></i>Financeiro / Faturamento
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- LINK PRIVADO: GESTÃO DE OUVIDORIA (Só quem resolve vê) --}}
                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('ouvidoria.index') ? 'active fw-bold' : '' }}"
                                href="{{ route('ouvidoria.index') }}">
                                <i class="bi bi-megaphone-fill me-1"></i> Ouvidoria
                                @php $pendentes = \App\Models\Ouvidoria::where('status', 'pendente')->count(); @endphp
                                @if ($pendentes > 0)
                                    <span class="badge rounded-pill bg-danger"
                                        style="font-size: 0.6rem;">{{ $pendentes }}</span>
                                @endif
                            </a>
                        </li>
                    @endif

                    {{-- OPERAÇÃO (Admin, Sócio e Professor) --}}
                    @if (in_array(Auth::user()->perfil, ['admin', 'professor', 'socio']))
                        {{-- Dropdown de Agendas --}}
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ Request::is('agenda*') ? 'active' : '' }} text-white d-flex align-items-center"
                                href="#" id="navbarDropdownAgenda" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-calendar3 me-1"></i> Agendas
                            </a>
                            <ul class="dropdown-menu shadow border-0 mt-2" aria-labelledby="navbarDropdownAgenda">

                                {{-- VISÃO DO PROFESSOR (Minha Agenda) --}}
                                @if (auth()->user()->perfil == 'professor')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('agenda.index') }}">
                                            <i class="bi bi-calendar-check me-2 text-success"></i> Minha Agenda
                                        </a>
                                    </li>
                                @endif

                                {{-- VISÃO DO SÓCIO/ADMIN (Gestão Completa) --}}
                                @if (auth()->user()->perfil == 'admin' || auth()->user()->perfil == 'socio')
                                    <li>
                                        <hr class="dropdown-divider opacity-25">
                                    </li>
                                    <li>
                                        <a class="dropdown-item small fw-bold text-primary"
                                            href="{{ route('agenda_socio.index') }}">
                                            <i class="bi bi-calendar-plus me-2"></i> Planejamento de Escalas
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item small" href="{{ route('financeiro.prof.pagar') }}">
                                            <i class="bi bi-currency-dollar me-2 text-success"></i> Fechamento Financeiro
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item small" href="{{ route('financeiro.prof.liquidar.index') }}">
                                            <i class="bi bi-file-earmark-text me-2 text-warning"></i> Histórico de Liquidações
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    {{-- LINK PÚBLICO: DESTAQUES (Visível para Aluno, Professor, Sócio e Admin) --}}
                    <li class="nav-item">
                        {{-- <a class="nav-link {{ Route::is('ouvidoria.relatorio') ? 'active fw-bold' : '' }} text-warning"
                            href="{{ route('ouvidoria.relatorio') }}">
                            <i class="bi bi-star-fill me-1"></i> Destaques do Mês
                        </a> --}}
                    </li>


                    {{-- MENU EXCLUSIVO ALUNO --}}
                    @if (Auth::user()->perfil === 'aluno')
                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('aluno.presenca.index') ? 'active' : '' }}"
                                href="{{ route('aluno.presenca.index') }}">
                                <i class="bi bi-calendar2-check me-1"></i> Minhas Aulas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('ouvidoria.aluno') ? 'active fw-bold' : '' }}"
                                href="{{ route('ouvidoria.aluno') }}">
                                <i class="bi bi-chat-left-text-fill me-1"></i> Mensagens
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>

            {{-- LADO DIREITO: PERFIL --}}
            <ul class="navbar-nav ms-auto">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#"
                            id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="text-end me-2 d-none d-lg-block">
                                <div class="lh-1 small fw-bold">{{ explode(' ', Auth::user()->name)[0] }}</div>
                                <small class="opacity-75" style="font-size: 0.7rem;">
                                    <i class="bi bi-geo-alt-fill"></i> {{ session('empresa_nome', 'S/ Unidade') }}
                                </small>
                            </div>
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                style="width: 35px; height: 35px;">
                                <i class="bi bi-person text-dark"></i>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li class="px-3 py-2 text-center bg-light mb-2 small fw-bold text-uppercase">
                                {{ Auth::user()->perfil }}
                            </li>
                            <li><a class="dropdown-item py-2" href="{{ route('escolha_unidade') }}"><i
                                        class="bi bi-arrow-left-right me-2 text-primary"></i>Trocar Unidade</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="{{ url('/logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Sair do Sistema
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
