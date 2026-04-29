@extends('layouts.main')

@section('content')
    @php
        $diasPt = [
            0 => 'Domingo',
            1 => 'Segunda',
            2 => 'Terça',
            3 => 'Quarta',
            4 => 'Quinta',
            5 => 'Sexta',
            6 => 'Sábado',
        ];
    @endphp

    <style>
        body {
            background-color: #f4f6f9;
        }

        .presenca-box {
            background: linear-gradient(135deg, #1a2a40 0%, #2d3e50 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-dia {
            border: none;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .aula-item {
            background: #fff;
            border-left: 6px solid #1a2a40;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }

        .aula-item.confirmada {
            border-left-color: #28a745;
            background-color: #f0fdf4;
        }

        .btn-acao {
            min-height: 48px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .badge-turno {
            background-color: #1a2a40;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
        }
    </style>

    <div class="container-fluid px-3 mt-3">
        {{-- Cabeçalho --}}
        <div class="presenca-box">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-uppercase opacity-75 d-block" style="font-size: 0.6rem;">Aluno</small>
                    <strong>{{ $aluno->name }}</strong>
                </div>
                <div class="text-end">
                    <small class="text-uppercase opacity-75 d-block" style="font-size: 0.6rem;">Hoje</small>
                    <strong>{{ \Carbon\Carbon::parse($hoje)->format('d/m/Y') }}</strong>
                </div>
            </div>
        </div>

        {{-- Botão de Histórico --}}
        <div class="mb-3">
            <a href="{{ route('aluno.presenca.historico', ['aluno_id' => $aluno->id]) }}"
                class="btn btn-outline-primary w-100 btn-acao">
                <i class="bi bi-clock-history me-2"></i> 📋 Meu Histórico de Presenças
            </a>
        </div>

        {{-- Abas de Navegação (Removido) --}}

        <div class="tab-content">
            {{-- TAB 1: MINHAS AULAS (Simplificado para apenas hoje) --}}
            <div class="tab-pane fade show active" id="aulas">
                @if ($escalas->isEmpty())
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle me-2"></i> Nenhuma aula agendada para hoje.
                    </div>
                @else
                    @foreach ($escalasAgrupadas as $data => $aulasDia)
                        @php
                            $dataObj = \Carbon\Carbon::parse($data);
                            $diaNome = $diasPt[$dataObj->dayOfWeek];
                        @endphp

                        <div class="card card-dia border-primary border-2">
                            <div class="card-header bg-secondary text-white py-2">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ strtoupper($diaNome) }}</strong>
                                    <small>{{ $dataObj->format('d/m/Y') }}</small>
                                </div>
                            </div>

                            <div class="card-body p-2">
                                @foreach ($aulasDia as $escala)
                                    @php
                                        $temPresenca = in_array($escala->user_id, $presencasConfirmadas);
                                    @endphp

                                    <div class="aula-item {{ $temPresenca ? 'confirmada' : '' }} mb-2">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <strong class="d-block">{{ $escala->empresa->nome_fantasia }}</strong>
                                                <span class="badge-turno">{{ strtoupper($escala->turno) }}</span>
                                            </div>
                                            <div class="text-end">
                                                @if ($temPresenca)
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle-fill"></i> Confirmada
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-clock"></i> Pendente
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="small text-muted mb-2">
                                            <i class="bi bi-person-fill"></i> Prof.:
                                            <strong>{{ $escala->professor->name }}</strong>
                                        </div>

                                        <div class="mt-3 pt-2 border-top">
                                            @if ($temPresenca)
                                                <button class="btn btn-outline-success w-100 btn-acao" disabled>
                                                    <i class="bi bi-check-circle-fill me-2"></i> PRESENÇA CONFIRMADA
                                                </button>
                                            @else
                                                <form method="POST"
                                                    action="{{ route('aluno.presenca.confirmar', $escala->id) }}"
                                                    style="display: inline-block; width: 100%;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success w-100 btn-acao"
                                                        onclick="return confirm('Confirmar sua presença nesta aula?')">
                                                        <i class="bi bi-check-circle me-2"></i> CONFIRMAR PRESENÇA
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Botão sutil de avaliar professor --}}
                                            <button type="button" class="btn btn-sm btn-link text-muted mt-2 w-100"
                                                data-bs-toggle="modal" data-bs-target="#modalAvaliar"
                                                data-professor-id="{{ $escala->professor->id }}"
                                                data-professor-name="{{ $escala->professor->name }}"
                                                data-empresa-id="{{ $escala->empresa_id }}">
                                                <i class="bi bi-chat-left-text me-1"></i> Avaliar Professor
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- Modal de Avaliação do Professor --}}
    <div class="modal fade" id="modalAvaliar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-star-fill text-warning me-2"></i> Avaliar Professor
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="formAvaliar" method="POST" action="{{ route('ouvidoria.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Professor:</label>
                            <div class="p-2 bg-light rounded">
                                <strong id="professorNome">-</strong>
                                <input type="hidden" id="professorId" name="professor_id">
                                <input type="hidden" id="empresaId" name="empresa_id">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="assunto" class="form-label">Tipo de Avaliação</label>
                            <select id="assunto" name="assunto" class="form-select" required>
                                <option value="">-- Selecione --</option>
                                <option value="Elogio">⭐ Elogio</option>
                                <option value="Sugestão">💡 Sugestão de Melhoria</option>
                                <option value="Dúvida">❓ Dúvida / Esclarecimento</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="mensagem" class="form-label">Mensagem</label>
                            <textarea id="mensagem" name="mensagem" class="form-control" rows="4"
                                placeholder="Compartilhe sua avaliação com o professor..." required></textarea>
                            <small class="text-muted d-block mt-1">Mínimo 5 caracteres</small>
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i> Enviar Avaliação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Preencher dados do professor no modal
        const modalAvaliar = document.getElementById('modalAvaliar');
        modalAvaliar.addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            const professorId = btn.getAttribute('data-professor-id');
            const professorName = btn.getAttribute('data-professor-name');
            const empresaId = btn.getAttribute('data-empresa-id');

            document.getElementById('professorId').value = professorId;
            document.getElementById('empresaId').value = empresaId;
            document.getElementById('professorNome').textContent = professorName;
        });

        // Limpar formulário quando fechar modal
        modalAvaliar.addEventListener('hide.bs.modal', function() {
            document.getElementById('formAvaliar').reset();
        });
    </script>
@endsection
