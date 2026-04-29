@extends('layouts.main')

@section('content')
    <div class="container mt-4">
        <h4 class="fw-bold mb-4"><i class="bi bi-chat-square-dots me-2 text-primary"></i>Minhas Mensagens</h4>

        @foreach ($mensagens as $m)
            <div class="card shadow-sm mb-4 border-0 rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-light text-dark border mb-2">{{ $m->assunto }}</span>
                            <h6 class="text-muted small mb-0">Enviado em: {{ $m->created_at->format('d/m/Y H:i') }}</h6>
                        </div>
                        <span class="badge {{ $m->status == 'pendente' ? 'bg-warning text-dark' : 'bg-success' }} px-3 py-2">
                            {{ strtoupper($m->status) }}
                        </span>
                    </div>

                    {{-- Relato do Aluno --}}
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <p class="mb-0 text-dark">{{ $m->mensagem }}</p>
                    </div>

                    {{-- RESPOSTA DO RH / SÓCIO --}}
                    @if ($m->resposta_coordenacao)
                        <div class="mt-3 p-3 bg-success bg-opacity-10 border-start border-4 border-success rounded-end">
                            <h6 class="fw-bold text-success mb-2">
                                <i class="bi bi-reply-all-fill me-1"></i> Retorno da Unidade:
                            </h6>
                            <p class="mb-2 text-dark italic" style="font-size: 0.95rem;">
                                "{{ $m->resposta_coordenacao }}"
                            </p>
                            <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                                <i class="bi bi-calendar-check me-1"></i>
                                Respondido em: {{ \Carbon\Carbon::parse($m->respondido_em)->format('d/m/Y H:i') }}
                            </small>
                        </div>
                    @else
                        <div class="mt-3 p-2 text-center border rounded-3 border-dashed">
                            <small class="text-muted italic">
                                <i class="bi bi-clock-history me-1"></i> Aguardando análise da coordenação...
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        @if ($mensagens->isEmpty())
            <div class="text-center mt-5">
                <i class="bi bi-chat-dots fs-1 text-muted"></i>
                <p class="text-muted mt-3">Você ainda não enviou nenhuma mensagem para a ouvidoria.</p>
            </div>
        @endif
    </div>
@endsection
