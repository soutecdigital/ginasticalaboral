@extends('layouts.main')

@section('content')
    <div class="container-fluid mt-4">
        {{-- Cabeçalho Fluid --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold" style="color: #1a2a40;">
                    <i class="bi bi-people-fill me-2"></i>Gestão de Usuários
                </h3>
                <p class="text-muted small mb-0">Administre professores, alunos, sócios e administradores do Laboral App.</p>
            </div>
            <a href="{{ route('usuarios.create') }}" class="btn text-white px-4 fw-bold shadow-sm"
                style="background-color: #1a2a40;">
                <i class="bi bi-person-plus-fill me-1"></i> NOVO USUÁRIO
            </a>
        </div>

        {{-- Tabela Fluid com DataTable --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4"> {{-- Ajustado padding para o DataTable --}}
                <div class="table-responsive">
                    <table id="tabelaUsuarios" class="table table-hover mb-0 align-middle">
                        <thead style="background-color: #f8f9fa; border-bottom: 2px solid #1a2a40;">
                            <tr>
                                <th class="ps-4 py-3" style="color: #1a2a40; width: 25%;">NOME / MATRÍCULA</th>
                                <th style="color: #1a2a40; width: 20%;">E-MAIL (LOGIN)</th>
                                <th style="color: #1a2a40; width: 15%;">PERFIL</th>
                                <th style="color: #1a2a40; width: 30%;">EMPRESAS VINCULADAS</th>
                                <th class="text-end pe-4" style="color: #1a2a40; width: 10%;">AÇÕES</th>

                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            {{-- Indicador de Status com efeito de brilho --}}
                                            <div class="position-relative me-3">
                                                <span
                                                    class="rounded-circle d-block {{ $user->ativo ? 'bg-success' : 'bg-danger' }} shadow-sm"
                                                    style="width: 12px; height: 12px;" data-bs-toggle="tooltip"
                                                    title="{{ $user->ativo ? 'Usuário Ativo' : 'Usuário Inativo' }}">
                                                </span>
                                                {{-- Poka-Yoke Visual: Se estiver ativo, adiciona um leve brilho --}}
                                                @if ($user->ativo)
                                                    <span
                                                        class="position-absolute top-0 start-0 translate-middle p-1 bg-success border border-light rounded-circle opacity-50 animate-ping"
                                                        style="width: 12px; height: 12px; z-index: -1;"></span>
                                                @endif
                                            </div>

                                            <div>
                                                <div class="fw-bold text-dark lh-1 mb-1">{{ $user->name }}</div>
                                                <small class="text-muted" style="font-size: 0.75rem;">
                                                    <i class="bi bi-hash"></i>{{ $user->matricula }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @php
                                            $config = [
                                                'admin' => [
                                                    'cor' => 'bg-danger',
                                                    'label' => 'ADMIN',
                                                    'icon' => 'bi-shield-lock-fill',
                                                ],
                                                'professor' => [
                                                    'cor' => 'bg-warning text-dark',
                                                    'label' => 'PROFESSOR',
                                                    'icon' => 'bi-person-workspace',
                                                ],
                                                'aluno' => [
                                                    'cor' => 'bg-success',
                                                    'label' => 'ALUNO',
                                                    'icon' => 'bi-mortarboard-fill',
                                                ],
                                                'socio' => [
                                                    'cor' => 'bg-primary',
                                                    'label' => 'SÓCIO',
                                                    'icon' => 'bi-briefcase-fill',
                                                ],
                                            ];

                                            $perfil = $config[$user->perfil] ?? [
                                                'cor' => 'bg-secondary',
                                                'label' => 'OUTRO',
                                                'icon' => 'bi-person-fill',
                                            ];
                                        @endphp
                                        <span class="badge {{ $perfil['cor'] }} shadow-sm px-3 py-2 rounded-pill">
                                            <i class="bi {{ $perfil['icon'] }} me-1"></i> {{ $perfil['label'] }}
                                        </span>
                                    </td>
                                    <td>
                                        @forelse($user->empresas as $empresa)
                                            <span class="badge border text-dark bg-white shadow-sm mb-1 p-2">
                                                <i class="bi bi-building text-primary me-1"></i>
                                                {{ $empresa->nome_fantasia }}
                                            </span>
                                        @empty
                                            <span class="text-danger small fw-bold">
                                                <i class="bi bi-exclamation-triangle"></i> Sem vínculo ativo
                                            </span>
                                        @endforelse
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm">
                                            <a href="{{ route('usuarios.edit', $user->id) }}"
                                                class="btn btn-sm btn-outline-secondary" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger" title="Desativar">
                                                <i class="bi bi-person-x"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                {{-- O DataTable cuidará da mensagem de 'vazio' via JS --}}
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <footer class="mt-4 text-center text-muted small">
            © {{ date('Y') }} SouTecDigital - Sistema de Ginástica Laboral
        </footer>
    </div>
@endsection

@push('scripts')
    {{-- Dependências do DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#tabelaUsuarios').DataTable({
                language: {
                    "sEmptyTable": "Nenhum usuário encontrado",
                    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ usuários",
                    "sInfoEmpty": "Mostrando 0 até 0 de 0 usuários",
                    "sInfoFiltered": "(Filtrado de _MAX_ registros no total)",
                    "sSearch": "Pesquisar:",
                    "oPaginate": {
                        "sNext": "Próximo",
                        "sPrevious": "Anterior"
                    }
                },
                pageLength: 25,
                responsive: true,
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel me-1"></i> Exportar Lista',
                    className: 'btn btn-success btn-sm mb-3',
                    title: 'Gestao_de_Usuarios_LaboralApp',
                    exportOptions: {
                        columns: [0, 1, 2]
                    }
                }]
            });

            // POKA-YOKE: Injeta a legenda dinamicamente ao lado dos botões
            $("div.dt-buttons").append(`
            <div class="d-inline-flex gap-3 ms-3 mb-3 p-2 bg-light rounded border shadow-sm align-items-center" style="height: 31px;">
                <div class="d-flex align-items-center" title="Usuário Ativo">
                    <span class="rounded-circle bg-success me-2" style="width: 8px; height: 8px;"></span>
                    <small class="text-muted fw-bold" style="font-size: 0.65rem;">ATIVO</small>
                </div>
                <div class="d-flex align-items-center border-start ps-2" title="Usuário Inativo">
                    <span class="rounded-circle bg-danger me-2" style="width: 8px; height: 8px;"></span>
                    <small class="text-muted fw-bold" style="font-size: 0.65rem;">INATIVO</small>
                </div>
            </div>
        `);
        });
    </script>
@endpush
