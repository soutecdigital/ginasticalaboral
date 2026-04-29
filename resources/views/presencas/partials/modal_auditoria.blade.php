<div class="modal fade" id="modalAuditoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('presencas.store') }}" method="POST" class="w-100">
            @csrf
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 bg-warning text-dark" style="border-radius: 20px 20px 0 0;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock-fill me-2"></i>Ajustar Presença</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="aluno_id" id="modal_aluno_id">

                    <div class="text-center mb-4">
                        <i class="bi bi-person-badge fs-1 text-secondary"></i>
                        <p id="modal_texto_aluno" class="fw-bold mt-2 mb-0"></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Novo Status</label>
                        <select name="status" class="form-select form-select-lg" required style="border-radius: 12px;">
                            <option value="presente">Presente</option>
                            <option value="ausente">Falta</option>
                            <option value="justificado">Justificado</option>
                        </select>
                    </div>

                    <div class="mb-3 px-3">
                        <label class="form-label small fw-bold text-muted">MOTIVO DA ALTERAÇÃO</label>
                        <textarea name="observacao" class="form-control border-0 bg-light" rows="2" placeholder="Por que está mudando?"
                            style="border-radius: 10px; font-size: 0.85rem;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                        style="border-radius: 10px;">Cancelar</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4" style="border-radius: 10px;">Salvar
                        Alteração</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    /**
     * Poka-Yoke: Função para preencher e abrir o modal
     */
    function abrirModalTroca(id, nome) {
        document.getElementById('modal_aluno_id').value = id;
        document.getElementById('modal_texto_aluno').innerText = nome;
        var myModal = new bootstrap.Modal(document.getElementById('modalAuditoria'));
        myModal.show();
    }
</script>
