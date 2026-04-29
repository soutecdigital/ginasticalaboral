@extends('layouts.main')

@section('content')
    @php
        // Respostas padrão profissionais conforme o assunto
        $respostasPadrao = [
            'Elogio' => [
                'Agradecemos enormemente pelo feedback positivo. Seus comentários nos motivam a continuar oferecendo o melhor atendimento e qualidade. Continuaremos trabalhando para superar suas expectativas!',
                'Muito obrigado por reconhecer nosso trabalho. Estamos comprometidos em manter a excelência nos serviços prestados. Seu apoio é fundamental para nosso sucesso!',
            ],
            'Sugestão' => [
                'Obrigado pela sugestão valiosa! Levaremos sua contribuição em consideração para melhorias futuras. Continuamos abertos a feedbacks que nos ajudem a evoluir.',
                'Apreciamos sua sugestão construtiva. A equipe de gestão analisará a proposta e entrará em contato caso necessite de maiores informações para implementação.',
            ],
            'Dúvida' => [
                'Agradecemos o contato! A equipe responsável fará contato direto para esclarecer suas dúvidas de forma clara e objetiva. Estamos à disposição!',
                'Sua dúvida foi anotada e será respondida com precisão. Entraremos em contato no menor prazo possível para ajudá-lo adequadamente.',
            ],
            'Reclamação' => [
                'Desculpamos por qualquer inconveniente ocasionado. Tomaremos as medidas necessárias para que a situação não se repita. Sua satisfação é nossa prioridade!',
                'Lamentamos o ocorrido. A situação será analisada pela gestão e as providências cabíveis serão tomadas. Obrigado pela oportunidade de melhorarmos.',
            ],
        ];
    @endphp

    <div class="container-fluid mt-3 px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold"><i class="bi bi-shield-lock me-2"></i>Gestão de Ouvidoria / RH</h4>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="ps-4">DATA</th>
                            <th>ALUNO / UNIDADE</th>
                            <th>ASSUNTO</th>
                            <th style="width: 40%;">MENSAGEM E PROVIDÊNCIAS</th>
                            <th class="text-center">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($feedbacks as $f)
                            <tr class="{{ $f->status == 'pendente' ? 'table-warning' : '' }}">
                                <td class="ps-4 small">{{ $f->created_at->format('d/m/y H:i') }}</td>
                                <td>
                                    <span class="d-block fw-bold small">{{ $f->usuario->name }}</span>
                                    <small class="text-muted small">{{ $f->empresa->nome_fantasia }}</small>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $f->assunto }}</span></td>
                                <td>
                                    {{-- Mensagem Original --}}
                                    <div class="mb-2 small text-dark"><strong>Relato:</strong> {{ $f->mensagem }}</div>

                                    {{-- Lógica de Resposta --}}
                                    @if ($f->resposta_coordenacao)
                                        <div
                                            class="p-2 rounded bg-success bg-opacity-10 border-start border-4 border-success small">
                                            <strong class="text-success small">MEDIDA TOMADA:</strong><br>
                                            {{ $f->resposta_coordenacao }}
                                            <div class="text-muted mt-1" style="font-size: 0.65rem;">
                                                Em: {{ \Carbon\Carbon::parse($f->respondido_em)->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    @else
                                        <form action="{{ route('ouvidoria.responder', $f->id) }}" method="POST">
                                            @csrf
                                            <div class="mb-2">
                                                {{-- Dropdown de Sugestões --}}
                                                @if (isset($respostasPadrao[$f->assunto]))
                                                    <div class="btn-group btn-group-sm mb-2 w-100" role="group">
                                                        <button type="button" class="btn btn-outline-info btn-sm"
                                                            data-bs-toggle="dropdown"
                                                            title="Carregar sugestões de resposta">
                                                            <i class="bi bi-lightbulb me-1"></i> Sugestões Prontas
                                                        </button>
                                                        <ul class="dropdown-menu" style="max-width: 400px;">
                                                            @foreach ($respostasPadrao[$f->assunto] as $idx => $sugestao)
                                                                <li>
                                                                    <button type="button"
                                                                        class="dropdown-item small text-wrap text-start"
                                                                        onclick="preencherResposta(this, '{{ addslashes($sugestao) }}')"
                                                                        title="Clique para usar esta sugestão">
                                                                        <i class="bi bi-check2 text-success me-1"></i>
                                                                        {{ substr($sugestao, 0, 60) }}...
                                                                    </button>
                                                                </li>
                                                                @if ($idx < count($respostasPadrao[$f->assunto]) - 1)
                                                                    <li>
                                                                        <hr class="dropdown-divider">
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="input-group">
                                                <textarea name="resposta" class="form-control textarea-resposta" placeholder="Descreva as medidas tomadas pelo RH..."
                                                    rows="5" required></textarea>
                                                <button class="btn btn-success" type="submit" title="Gravar Resposta"><i
                                                        class="bi bi-send-fill"></i></button>
                                            </div>
                                        </form>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $f->status == 'pendente' ? 'bg-danger' : 'bg-success' }} p-2">
                                        {{ strtoupper($f->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function preencherResposta(btn, sugestao) {
            // Encontra a textarea mais próxima
            const textarea = btn.closest('form').querySelector('.textarea-resposta');
            textarea.value = sugestao;
            textarea.focus();

            // Feedback visual
            btn.classList.add('active');
            setTimeout(() => btn.classList.remove('active'), 200);
        }
    </script>
@endsection
