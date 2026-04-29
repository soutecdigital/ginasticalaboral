<div class="modal fade" id="modalEscala" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('agenda_socio.agendar') }}" method="POST" id="formEscala"
            class="modal-content border-0 shadow-lg">
            @csrf
            {{-- IDs de Controle --}}
            <input type="hidden" name="id" id="inputEscalaId">
            <input type="hidden" name="empresa_id" id="inputEmpresaId">
            <input type="hidden" name="data" id="inputData">

            <div class="modal-header bg-primary text-white border-0">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-calendar-check me-2"></i>
                    <span id="labelNome"></span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                {{-- Alertas Dinâmicos (Admin / Esquecimento) --}}
                <div id="alertaModal" class="alert d-none mb-3 shadow-sm border-start border-4"></div>

                <div class="row g-3">
                    {{-- Seleção de Professor --}}
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">PROFESSOR RESPONSÁVEL</label>
                        <select name="user_id" id="selectProf" class="form-select shadow-sm" required>
                            <option value="" data-valor="0" data-online="0" data-avulso="0">Selecione o
                                profissional...</option>
                            @foreach ($professores as $prof)
                                <option value="{{ $prof->id }}"
                                    data-valor="{{ $prof->configuracaoAtual->valor_aula ?? 0 }}"
                                    data-online="{{ $prof->configuracaoAtual->valor_aula_online ?? 0 }}"
                                    data-avulso="{{ $prof->configuracaoAtual->valor_aula_avulso ?? 0 }}">
                                    {{ $prof->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Turno --}}
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">TURNO DA ESCALA</label>
                        <select name="turno" id="selectTurno" class="form-select shadow-sm">
                            <option value="manha">Manhã</option>
                            <option value="tarde">Tarde</option>
                            <option value="noite">Noite</option>
                        </select>
                    </div>

                    {{-- Modalidade da Aula (Poka-Yoke Financeiro) --}}
                    <div class="col-12 mt-2">
                        <label class="form-label small fw-bold text-primary">TIPO DE ATENDIMENTO</label>
                        {{-- Legenda de Instrução UX --}}
                        <div class="p-2 rounded bg-light border shadow-sm">
                            <p class="mb-0 text-muted" style="font-size: 0.7rem; line-height: 1.2;">
                                <i class="bi bi-shield-lock-fill text-primary me-1"></i>
                                <strong>GESTÃO DE VALORES:</strong> Os preços de atendimento são vinculados ao contrato
                                do professor.
                                Para reajustes ou correções, acesse o <strong>Cadastro de Usuários</strong>.
                            </p>
                        </div>
                        <br>
                        <div class="d-flex gap-2">
                            <input type="radio" class="btn-check" name="tipo_aula" id="tNormal" value="normal"
                                checked>
                            <label class="btn btn-outline-primary btn-sm flex-fill fw-bold"
                                for="tNormal">NORMAL</label>

                            <input type="radio" class="btn-check" name="tipo_aula" id="tOnline" value="online">
                            <label class="btn btn-outline-info btn-sm flex-fill fw-bold" for="tOnline">ONLINE</label>

                            <input type="radio" class="btn-check" name="tipo_aula" id="tAvulso" value="avulso">
                            <label class="btn btn-outline-danger btn-sm flex-fill fw-bold" for="tAvulso">AVULSO /
                                EVENTO</label>
                        </div>
                    </div>

                    {{-- Valor Extra --}}
                    <div class="col-12">
                        <label class="form-label small fw-bold text-danger">VALOR EXTRA / EVENTO (R$)</label>
                        <input type="number" step="0.01" name="valor_venda_avulso" id="inputValor"
                            class="form-control border-danger-subtle shadow-sm" placeholder="0.00">
                        <br>


                        {{-- Legenda de Instrução UX --}}
                        <div class="p-2 rounded bg-light border shadow-sm">
                            <p class="mb-0 text-muted" style="font-size: 0.7rem; line-height: 1.2;">
                                <i class="bi bi-pencil text-primary me-1"></i>
                                <strong>VALOR EXTRA / EVENTO (R$):</strong> <strong>Digitar o valor
                                    correspondente</strong>.
                            </p>
                        </div>
                    </div>
                    {{-- Observações --}}
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">OBSERVAÇÕES DA ESCALA</label>
                        <textarea name="observacao" id="inputObs" class="form-control shadow-sm" rows="2"
                            placeholder="Detalhes internos para o professor..."></textarea>
                    </div>

                    {{-- ÁREA DE CANCELAMENTO (AUDITORIA OBRIGATÓRIA) --}}
                    <div class="col-12 mt-3 pt-3 border-top">
                        <div class="form-check form-switch p-3 border rounded bg-light border-danger-subtle shadow-sm">
                            <input class="form-check-input ms-0 me-2" type="checkbox" role="switch"
                                id="swCancelado" name="status_cancelamento" value="cancelado">
                            <label class="form-check-label fw-bold text-danger" for="swCancelado">
                                <i class="bi bi-x-octagon me-1"></i> CANCELAR ESTA ESCALA
                            </label>
                        </div>

                        <div id="divMotivo" class="mt-2 d-none animate__animated animate__fadeIn">
                            <label class="form-label small fw-bold text-danger">JUSTIFICATIVA OBRIGATÓRIA</label>
                            <textarea name="observacao_cancelamento" id="inputMotivo" class="form-control border-danger shadow-sm"
                                rows="2" placeholder="Por que esta escala está sendo cancelada?"></textarea>
                            <small class="text-muted" style="font-size: 0.6rem;">* Necessário para reagendamento e
                                auditoria.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-0 rounded-bottom">
                <button type="button" class="btn btn-sm btn-secondary fw-bold px-4"
                    data-bs-dismiss="modal">VOLTAR</button>
                <button type="submit" id="btnSalvar" class="btn btn-sm btn-primary fw-bold px-4 shadow">
                    SALVAR ESCALA
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Lógica para mostrar/esconder motivo de cancelamento
    document.getElementById('swCancelado').addEventListener('change', function() {
        const div = document.getElementById('divMotivo');
        const txt = document.getElementById('inputMotivo');

        if (this.checked) {
            div.classList.remove('d-none');
            txt.setAttribute('required', 'required'); // Poka-Yoke: Torna obrigatório no cancelamento
        } else {
            div.classList.add('d-none');
            txt.removeAttribute('required');
        }
    });

    // Poka-Yoke Visual: Se trocar para Online, sugere atenção ao valor
    document.querySelectorAll('input[name="tipo_aula"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'online') {
                // Aqui você poderia resetar o campo de valor ou aplicar o preço padrão online via JS se quiser
                console.log('Mudança para Modalidade Online');
            }
        });

        // Função para validar se o prof tem preço configurado
        function validarPrecoProf() {
            const select = document.getElementById('selectProf');
            const option = select.options[select.selectedIndex];
            const alerta = document.getElementById('alertaModal');
            const btn = document.getElementById('btnSalvar');

            // Pega a modalidade selecionada (rádio buttons)
            const modalidade = document.querySelector('input[name="tipo_aula"]:checked').value;

            // Mapeia qual valor buscar baseado no data-attribute
            let valorRef = 0;
            if (modalidade === 'normal') valorRef = option.getAttribute('data-valor');
            if (modalidade === 'online') valorRef = option.getAttribute('data-online');
            if (modalidade === 'avulso') valorRef = option.getAttribute('data-avulso');

            if (parseFloat(valorRef) <= 0 && select.value !== "") {
                alerta.innerHTML = `
            <div class="d-flex align-items-start">
                <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
                <div>
                    <strong class="d-block">BLOQUEIO FINANCEIRO</strong>
                    O professor <b>${option.text}</b> não possui valor para aula <b>${modalidade.toUpperCase()}</b>.<br>
                    <small class="mt-2 d-block text-dark">
                        <b>COMO RESOLVER:</b> Vá em <i>Menu > Gestão > Usuários > Escolha o Prof°> Editar >  Preço de Aula</i> e atualize os dados deste profissional.
                    </small>
                </div>
            </div>
        `;
                alerta.className = "alert alert-danger p-2 small d-block animate__animated animate__shakeX";
                btn.disabled = true; // Impede o clique no salvar
                btn.classList.add('opacity-50');
            } else {
                // Se estiver tudo OK ou for edição admin, o prepararAgendamento já cuida do alerta
                // Aqui apenas limpamos o erro de valor zerado
                if (alerta.classList.contains('alert-danger')) {
                    alerta.classList.add('d-none');
                    btn.disabled = false;
                    btn.classList.remove('opacity-50');
                }
            }
        }

        // Escutadores de Eventos
        document.getElementById('selectProf').addEventListener('change', validarPrecoProf);
        document.querySelectorAll('input[name="tipo_aula"]').forEach(radio => {
            radio.addEventListener('change', validarPrecoProf);
        });
    });

    /**
     * FUNÇÃO: Preparar Agendamento
     * Poka-Yoke: Identifica auditoria do sócio e bloqueia edição retroativa.
     */
    function prepararAgendamento(empresaId, empresaNome, data, userId = null, valor = null, turno = 'manha', obs = '',
        statusCancelamento = 'ativo', obsCancelamento = '', aulaRealizada = false, ajuste = false, escalaId = null,
        tipoAula = 'normal') {

        const form = document.getElementById('formEscala');
        const btnSalvar = document.getElementById('btnSalvar');
        const alerta = document.getElementById('alertaModal');

        // 1. Reset padrão e reabilitação (Garante que o modal não abra travado de uma vez anterior)
        form.reset();
        form.querySelectorAll('input, select, textarea').forEach(el => el.disabled = false);
        btnSalvar.classList.remove('d-none');
        btnSalvar.textContent = 'SALVAR ESCALA';

        // 2. IDs e Cabeçalho
        document.getElementById('inputEscalaId').value = escalaId || '';
        document.getElementById('inputEmpresaId').value = empresaId;
        document.getElementById('inputData').value = data;
        document.getElementById('labelNome').textContent = `${empresaNome} - ${data}`;

        // 3. 🛡️ POKA-YOKE: Identifica Auditoria do Sócio (String match)
        const foiValidadoPeloSocio = obs && obs.includes('[Validado manualmente por Sócio');

        if (userId) {
            // Popula campos
            document.getElementById('selectProf').value = userId;
            document.getElementById('selectTurno').value = turno || 'manha';
            const rawValor = parseFloat(valor) || 0;
            document.getElementById('inputValor').value = tipoAula === 'avulso' ? rawValor : 0;
            document.getElementById('inputObs').value = obs || '';

            // Modalidade
            if (tipoAula === 'online') {
                document.getElementById('tOnline').checked = true;
            } else if (tipoAula === 'avulso') {
                document.getElementById('tAvulso').checked = true;
            } else {
                document.getElementById('tNormal').checked = true;
            }
            // --- LÓGICA DE BLOQUEIO / ALERTAS ---

            if (foiValidadoPeloSocio) {
                // BLOQUEIO TOTAL: Auditoria Finalizada pelo Sócio
                alerta.innerHTML = `
                <div class="d-flex align-items-start">
                    <i class="bi bi-shield-lock-fill me-2 fs-5 text-success"></i>
                    <div>
                        <strong class="d-block text-success"> AUDITORIA CONCLUÍDA</strong>
                        Esta escala foi validada manualmente por um Sócio. O registro está bloqueado para alterações.
                    </div>
                </div>`;
                alerta.className =
                    "alert alert-success p-2 small d-flex animate__animated animate__fadeIn border-start border-success border-4";

                // Trava total do formulário
                form.querySelectorAll('input, select, textarea').forEach(el => el.disabled = true);
                btnSalvar.classList.add('d-none');

            } else if (aulaRealizada) {
                // Alerta de Aula Confirmada pelo Prof
                alerta.innerHTML = `
                <div class="d-flex align-items-start">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-warning"></i>
                    <div>
                        <strong class="d-block text-dark">⚠️ AULA REALIZADA</strong>
                        Esta aula foi confirmada pelo professor. Alterações exigem perfil administrativo.
                    </div>
                </div>`;
                alerta.className =
                    "alert alert-warning p-2 small d-flex animate__animated animate__fadeIn border-start border-warning border-4";

                // Se não for admin (Sócio), bloqueia edição de aula já confirmada
                if ({{ auth()->user()->perfil !== 'admin' ? 'true' : 'false' }}) {
                    form.querySelectorAll('input, select, textarea').forEach(el => el.disabled = true);
                    btnSalvar.classList.add('d-none');
                }
            } else {
                // Escala pendente ou futura
                alerta.classList.add('d-none');
            }

            // Caso de Cancelamento
            if (statusCancelamento === 'cancelado') {
                document.getElementById('swCancelado').checked = true;
                document.getElementById('divMotivo').classList.remove('d-none');
                document.getElementById('inputMotivo').value = obsCancelamento || '';
                btnSalvar.textContent = 'REAGENDAR ESCALA';
            } else {
                document.getElementById('swCancelado').checked = false;
                document.getElementById('divMotivo').classList.add('d-none');
            }

        } else {
            // Nova escala: Limpa alertas e foca no profissional
            alerta.classList.add('d-none');
            setTimeout(() => document.getElementById('selectProf').focus(), 400);
        }

        // 4. Dispara validação de preço (Atualiza o R$ conforme o professor selecionado)
        const event = new Event('change', {
            bubbles: true
        });
        document.getElementById('selectProf').dispatchEvent(event);

        // 5. Exibe o Modal
        const modal = new bootstrap.Modal(document.getElementById('modalEscala'));
        modal.show();
    }
</script>
