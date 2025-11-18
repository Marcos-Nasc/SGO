<div id="modalFotos" class="modal-overlay">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3 id="modalFotosTitulo">Anexar Fotos - OS...</h3>
            <button class="close-modal" onclick="fecharModalFotos()">&times;</button>
        </div>

        <div class="modal-summary">
            <span id="modal-resumo-servico" style="font-weight: 600; color: var(--cor-texto-primario);">Serviço: ...</span>
            <span id="modal-resumo-cliente">Cliente: ...</span>
            <span id="modal-resumo-data">Data Agendada: ...</span>
            <div class="form-separator" style="margin-top: 15px; margin-bottom: 0;"></div>
        </div>
        
        <form id="formFotos">
            <input type="hidden" id="fotos_agendamento_id">

            <div class="upload-sections-container">
                <div class="upload-section">
                    <h4>Fotos do "Antes"</h4>
                    <div class="photo-grid" id="grid-fotos-antes">
                        <span class="loading-fotos">Carregando...</span>
                    </div>
                    <label class="btn-upload-label">
                        <i class="bi bi-plus-circle-fill"></i> Adicionar Foto "Antes"
                        <input type="file" class="input-file-upload" accept="image/jpeg, image/png"
                               onchange="handleFotoUpload(this, 'antes')">
                    </label>
                </div>

                <div class="upload-section">
                    <h4>Fotos do "Depois"</h4>
                    <div class="photo-grid" id="grid-fotos-depois">
                        <span class="loading-fotos">Carregando...</span>
                    </div>
                    <label class="btn-upload-label">
                        <i class="bi bi-plus-circle-fill"></i> Adicionar Foto "Depois"
                        <input type="file" class="input-file-upload" accept="image/jpeg, image/png"
                               onchange="handleFotoUpload(this, 'depois')">
                    </label>
                </div>
            </div>

            <div class="form-separator"></div>

            <div class="modal-footer-buttons" style="justify-content: space-between;">
                <button type="button" class="btn-back" onclick="fecharModalFotos()">Fechar</button>
                <button type="button" class="btn-approve" id="btnMarcarConcluido" onclick="marcarServicoConcluido()">
                    <i class="bi bi-check-all"></i> Marcar Serviço como Concluído
                </button>
            </div>
        </form>
    </div>
</div>