</div> </main> </div>

<div id="modalListaContratos" class="modal-overlay">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h4>
                <i class="bi bi-file-earmark-text-fill"></i>
                <span id="tituloModalContratos">Contratos</span>
            </h4>
            <button type="button" class="close-modal" onclick="fecharModalContratos()">
                <i class="bi bi-x"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="listaContratosConteudo"></div>
        </div>
        <div class="modal-footer-buttons">
            <button type="button" class="btn-back" onclick="fecharModalContratos()">Fechar</button>
        </div>
    </div>
</div>

<div id="modalEditarContrato" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h4>
                <i class="bi bi-pencil-square"></i>
                <span id="tituloEditarContrato">Editar Contrato</span>
            </h4>
            <button type="button" class="close-modal" onclick="fecharModalEditarContrato()">
                <i class="bi bi-x"></i>
            </button>
        </div>
        <form id="formEditarContrato">
            <input type="hidden" name="acao" value="editar_contrato">
            <input type="hidden" id="editContratoId" name="id">
            
            <div class="modal-body">
                <div class="form-group">
                    <label>Filial</label>
                    <select name="cemiterio_id" id="editContratoCemiterio" class="form-control" required></select>
                </div>
                <div class="form-group">
                    <label>Número do Contrato</label>
                    <input type="text" id="editContratoNumero" name="numero" class="form-control" required>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px;">
                    <div class="form-group"><label>Jazigo</label><input type="text" name="jazigo" id="editContratoJazigo" class="form-control"></div>
                    <div class="form-group"><label>Quadra</label><input type="text" name="quadra" id="editContratoQuadra" class="form-control"></div>
                    <div class="form-group"><label>Bloco</label><input type="text" name="bloco" id="editContratoBloco" class="form-control"></div>
                </div>
            </div>
            
            <div class="modal-footer-buttons">
                <button type="button" class="btn-back" onclick="fecharModalEditarContrato()">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar</button>
            </div>
        </form>
    </div>
</div>

<div id="globalConfirmModal">
    <div class="modal-content small-modal" style="max-width: 500px; margin: auto;">
        <div class="modal-header">
            <h4>
                <i class="bi bi-info-circle-fill"></i>
                <span id="globalConfirmTitle">Confirmação</span>
            </h4>
            <button type="button" class="close-modal" onclick="fecharGlobalConfirm()">
                <i class="bi bi-x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="globalConfirmMessage"></p>
        </div>
        <div class="modal-footer-buttons">
            <button type="button" class="btn-back" onclick="fecharGlobalConfirm()">Cancelar</button>
            <button type="button" id="btnGlobalConfirmAction" class="btn btn-primary">Confirmar</button>
        </div>
    </div>
</div>

<div id="modalConfirmarEmail" class="modal-overlay">
    <div class="modal-content small-modal" style="max-width: 500px; margin: auto;">
        <div class="modal-header">
            <h4>
                <i class="bi bi-envelope-check-fill"></i>
                <span>Confirmar Envio</span>
            </h4>
            <button type="button" class="close-modal" onclick="fecharModalConfirmarEmail()">
                <i class="bi bi-x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Tem certeza que deseja enviar o e-mail de validação para o cliente?</p>
            <div style="background: var(--cor-fundo); padding: 15px; border-radius: 8px; border: 1px solid var(--cor-borda); margin-top: 10px;">
                <p style="margin-bottom: 5px;"><strong>Cliente:</strong> <span id="email-confirm-cliente">...</span></p>
                <p style="margin-bottom: 0;"><strong>E-mail:</strong> <span id="email-confirm-email">...</span></p>
            </div>
        </div>
        <div class="modal-footer-buttons">
            <button type="button" class="btn-back" onclick="fecharModalConfirmarEmail()">Cancelar</button>
            <button type="button" id="btnConfirmarEnvioFinal" class="btn btn-primary" onclick="executarEnvioEmail(this)">
                <i class="bi bi-send-fill"></i> Enviar
            </button>
        </div>
    </div>
</div>

<div id="modalVenda" class="modal-overlay">
    <div class="modal-content" style="max-width: 800px; width: 95%;">
        <div class="modal-header">
            <h4><i class="bi bi-cart-plus-fill"></i> Nova Venda</h4>
            <button type="button" class="close-modal" onclick="fecharModal()"><i class="bi bi-x"></i></button>
        </div>
        <div class="modal-body" style="padding-top: 0;">
             <div class="stepper-container" style="position: relative; margin: 0 20px 20px 20px;">
                <div class="progress-bg" style="background: #e9ecef; height: 4px; width: 100%; position: absolute; top: 14px; z-index: 1;"></div>
                <div id="stepperProgress" class="progress-bar" style="background: var(--cor-primaria); height: 4px; width: 0%; position: absolute; top: 14px; z-index: 2; transition: 0.3s;"></div>
                <div style="display: flex; justify-content: space-between; position: relative; z-index: 3;">
                    <div id="step-indicator-1" class="step active"><div class="step-circle">1</div><span>Cliente</span></div>
                    <div id="step-indicator-2" class="step"><div class="step-circle">2</div><span>Contrato</span></div>
                    <div id="step-indicator-3" class="step"><div class="step-circle">3</div><span>Pagamento</span></div>
                </div>
            </div>
            <form id="formVenda" onsubmit="return false;">
                <input type="hidden" id="selected_mantenedor_id" name="mantenedor_id">
                <input type="hidden" id="selected_contrato_id" name="contrato_id">
                
                <div id="step-1-busca-mantenedor" class="step-content active">
                    <h5 style="margin-bottom:15px;">Buscar Cliente</h5>
                    <input type="text" id="buscaMantenedor" class="form-control" placeholder="Nome, CPF ou E-mail...">
                    <div id="listaMantenedores" class="search-results"></div>
                    <div style="text-align:center; margin-top:15px;"><button id="linkCadastrarNovoMantenedor" class="btn-outline">Novo Cliente</button></div>
                    <div class="modal-footer-buttons"><button type="button" class="btn-back" onclick="fecharModal()">Cancelar</button><button type="button" id="btnNext1" class="btn btn-primary" disabled>Próximo</button></div>
                </div>
                <div id="step-1-cadastro-mantenedor" class="step-content" style="display:none;">
                     <div class="form-group"><label>Nome</label><input type="text" id="novoMantenedorNome" class="form-control"></div>
                     <div class="form-group"><label>Email</label><input type="text" id="novoMantenedorEmail" class="form-control"></div>
                     <div class="form-group"><label>Tel</label><input type="text" id="novoMantenedorTelefone" class="form-control"></div>
                     <div class="modal-footer-buttons"><button class="btn-back">Voltar</button><button id="btnSalvarNovoMantenedor" class="btn btn-primary">Salvar</button></div>
                 </div>
                 <div id="step-2-busca-contrato" class="step-content" style="display:none;">
                     <div id="listaContratos"></div>
                     <div style="text-align:center;"><button id="linkCadastrarNovoContrato" class="btn-outline">Novo Contrato</button></div>
                     <div class="modal-footer-buttons"><button class="btn-back">Voltar</button><button id="btnNext2" class="btn btn-primary" disabled>Próximo</button></div>
                 </div>
                 <div id="step-2-cadastro-contrato" class="step-content" style="display:none;">
                     <select id="novoContratoCemiterio" class="form-control"></select>
                     <input type="text" id="novoContratoNumero" class="form-control" placeholder="Número">
                     <input type="text" id="novoContratoJazigo" class="form-control" placeholder="Jazigo">
                     <input type="text" id="novoContratoQuadra" class="form-control" placeholder="Quadra">
                     <input type="text" id="novoContratoBloco" class="form-control" placeholder="Bloco">
                     <div class="modal-footer-buttons"><button class="btn-back">Voltar</button><button id="btnSalvarNovoContrato" class="btn btn-primary">Salvar</button></div>
                 </div>
                 <div id="step-3" class="step-content" style="display:none;">
                     <select id="filtroTipo" class="form-control"><option value="Serviço">Serviço</option><option value="Produto">Produto</option></select>
                     <select id="selectProduto" class="form-control"></select>
                     <input type="text" id="numero_os" class="form-control" placeholder="OS">
                     <input type="number" id="valorFinal" class="form-control" placeholder="Valor">
                     <select id="condicaoPagamento" class="form-control"><option>A Vista</option><option>A Prazo</option></select>
                     <div id="divCamposPrazo" style="display:none;">
                         <input type="number" id="valorEntrada" class="form-control">
                         <input type="number" id="qtdeParcelas" class="form-control">
                         <input type="date" id="dataPrevisao" class="form-control">
                         <div id="alertaEntrada" style="display:none;"></div>
                     </div>
                     <div id="wrapperFamilia" class="disabled"><input type="checkbox" id="checkFamilia"> Família Presente</div>
                     <div class="modal-footer-buttons"><button class="btn-back">Voltar</button><button id="btnConcluirVenda" class="btn btn-primary">Concluir</button></div>
                 </div>
            </form>
        </div>
    </div>
</div>

<div id="modalEditarProcesso" class="modal-overlay">
    <div class="modal-content">
        
        <div class="modal-header">
            <h4 style="margin: 0; display:flex; gap:10px; align-items:center;">
                <i class="bi bi-pencil-square"></i> <span>Editar Processo Completo</span>
            </h4>
            <button type="button" class="close-modal" onclick="fecharModalProcesso()" style="color: #adb5bd;">&times;</button>
        </div>

        <form id="formEditarProcesso" style="display: flex; flex-direction: column; flex-grow: 1; overflow: hidden;">
            <input type="hidden" name="acao" value="salvar_edicao_completa">
            <input type="hidden" name="venda_id" id="editVendaId">
            <input type="hidden" name="contrato_id" id="editContratoId">
            <input type="hidden" name="agendamento_id" id="editAgendamentoId">

            <div class="modal-body">
                
                <h5 style="font-size: 0.8rem; color: #adb5bd; text-transform: uppercase; margin-bottom: 10px;">
                    <i class="bi bi-person-vcard"></i> Dados do Cliente e Contrato (Leitura)
                </h5>
                <div class="monitor-top-grid" style="margin-bottom: 10px;">
                    <div style="grid-column: span 2;">
                        <span class="monitor-label">Cliente</span>
                        <input type="text" id="editClienteNome" class="input-dark-integrated" readonly style="opacity: 0.5;">
                    </div>
                    <div>
                        <span class="monitor-label">Telefone</span>
                        <input type="text" id="editClienteTelefone" class="input-dark-integrated" readonly style="opacity: 0.5;">
                    </div>
                    <div>
                        <span class="monitor-label">E-mail</span>
                        <input type="text" id="editClienteEmail" class="input-dark-integrated" readonly style="opacity: 0.5;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 10px; margin-bottom: 15px; background: #212529; padding: 10px; border-radius: 6px; border: 1px solid #343a40;">
                    <div>
                        <span class="monitor-label" style="color: #6ea8fe;">Contrato Nº</span>
                        <input type="text" id="editContratoNum" class="input-dark-integrated" readonly style="border:none !important; background:transparent !important; padding:0; font-weight:bold;">
                    </div>
                    <div>
                        <span class="monitor-label" style="color: #6ea8fe;">Jazigo</span>
                        <input type="text" id="editJazigo" class="input-dark-integrated" readonly style="border:none !important; background:transparent !important; padding:0;">
                    </div>
                    <div>
                        <span class="monitor-label" style="color: #6ea8fe;">Quadra</span>
                        <input type="text" id="editQuadra" class="input-dark-integrated" readonly style="border:none !important; background:transparent !important; padding:0;">
                    </div>
                    <div>
                        <span class="monitor-label" style="color: #6ea8fe;">Bloco</span>
                        <input type="text" id="editBloco" class="input-dark-integrated" readonly style="border:none !important; background:transparent !important; padding:0;">
                    </div>
                </div>

                <div class="modal-divider"></div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h5 style="font-size: 0.8rem; color: #6ea8fe; text-transform: uppercase; margin-bottom: 10px;">
                            <i class="bi bi-cart"></i> Dados da Venda
                        </h5>
                        <div class="form-group">
                            <span class="monitor-label">Data da Venda (Base p/ Previsão)</span>
                            <input type="date" name="v_data_venda" id="editDataVenda" class="input-dark-integrated">
                        </div>
                        <div class="form-group">
                            <span class="monitor-label">Nº OS / NF</span>
                            <div style="display: flex; gap: 5px;">
                                <input type="text" name="v_os" id="editOS" class="input-dark-integrated" placeholder="OS">
                                <input type="text" name="v_nf" id="editNF" class="input-dark-integrated" placeholder="NF">
                            </div>
                        </div>
                        <div class="form-group">
                            <span class="monitor-label">Tipo Serviço</span>
                            <select id="editTipoProdutoSelect" class="select-dark" style="margin-bottom:5px;"></select>
                            <select name="v_produto_id" id="editProdutoIdSelect" class="select-dark"></select>
                        </div>
                    </div>

                    <div class="card-financeiro" style="margin-bottom: 0;">
                        <div class="card-financeiro-header"><i class="bi bi-calculator"></i> Financeiro (Automático)</div>
                        <div class="financeiro-grid-row" style="grid-template-columns: 1fr 1fr;">
                            <div class="financeiro-input-group">
                                <label>TOTAL (R$)</label>
                                <input type="number" step="0.01" name="v_valor" id="editValorFinal" class="input-blue-integrated highlight-value">
                            </div>
                            <div class="financeiro-input-group">
                                <label>CONDIÇÃO</label>
                                <select name="v_condicao" id="editCondicao" class="input-blue-integrated" onchange="toggleParcelamento()">
                                    <option value="A Vista">A Vista</option>
                                    <option value="A Prazo">A Prazo</option>
                                </select>
                            </div>
                        </div>
                        <div id="divCamposParcelado">
                            <div class="financeiro-grid-row" style="grid-template-columns: 1fr 1fr; margin-top: 10px;">
                                <div class="financeiro-input-group">
                                    <label>ENTRADA (R$)</label>
                                    <input type="number" step="0.01" name="v_entrada" id="editValorEntrada" class="input-blue-integrated">
                                </div>
                                <div class="financeiro-input-group">
                                    <label>PARCELAS</label>
                                    <input type="number" name="v_parcelas" id="editQtdParcelas" class="input-blue-integrated">
                                </div>
                            </div>
                            <div style="margin-top: 15px; padding-top: 10px; border-top: 1px dashed rgba(13, 110, 253, 0.3);">
                                <div class="financeiro-grid-row" style="grid-template-columns: 1fr 1fr;">
                                    <div class="financeiro-input-group">
                                        <label style="color: #ffc107;">VALOR PARCELA</label>
                                        <input type="text" id="editValorParcelaDisplay" class="input-blue-integrated" readonly style="font-weight: bold;">
                                        <input type="hidden" name="v_valor_parcela_calc" id="editValorParcelaCalc">
                                    </div>
                                    <div class="financeiro-input-group">
                                        <label style="color: #ffc107;">PREVISÃO 50%</label>
                                        <input type="date" name="v_data_previsao_calc" id="editDataPrevisaoCalc" class="input-blue-integrated" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-divider"></div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <span class="monitor-label">Status Venda</span>
                        <select name="v_status" id="editStatusVenda" class="select-dark">
                            <option value="Pendente">Pendente</option>
                            <option value="Aguardando Cobrança">Aguardando Cobrança</option>
                            <option value="Aprovado para Agendamento">Aprovado p/ Agendamento</option>
                            <option value="Agendado">Agendado</option>
                            <option value="Concluído">Concluído</option>
                        </select>
                    </div>
                    <div>
                        <span class="monitor-label">Status Processo</span>
                        <select name="a_status" id="editStatusAgendamento" class="select-dark">
                            <option value="Pendente de Contato">Pendente de Contato</option>
                            <option value="Confirmado">Confirmado</option>
                            <option value="Concluído - Aguardando Validação">Concluído - Aguardando Validação</option>
                            <option value="Reagendamento Solicitado">Reagendamento Solicitado</option>
                            <option value="Finalizado e Enviado">Finalizado e Enviado</option>
                        </select>
                    </div>
                </div>
                
                <div style="margin-top: 15px;">
                    <span class="monitor-label">Data Agendada</span>
                    <input type="datetime-local" name="a_data" id="editDataAgendada" class="input-dark-integrated">
                </div>

                <div style="margin-top: 15px;">
                    <span class="monitor-label">Observações Técnicas</span>
                    <textarea name="a_obs" id="editObsAgendamento" class="input-dark-integrated" rows="2"></textarea>
                </div>
                <input type="hidden" name="v_obs" id="editObsVenda">

                <div id="editorFotosArea" style="margin-top: 25px;"></div>
                </div>

            <div class="modal-footer-buttons" style="justify-content: space-between;">
                <button type="button" class="btn-delete" onclick="acaoExcluirDoModal()" style="background: transparent; border: 1px solid #dc3545; color: #dc3545;">
                    <i class="bi bi-trash"></i> Excluir
                </button>
                
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn-back" onclick="fecharModalProcesso()" style="background: transparent; border: 1px solid #495057; color: #fff;">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar Alterações</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="modalMonitor" class="modal-overlay">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h4 id="monitorTitulo">Detalhes</h4>
            <button class="close-modal" onclick="fecharModalMonitor()">&times;</button>
        </div>
        <div class="modal-body">
             <div class="monitor-top-grid">
                <div><span class="monitor-label">Cliente</span><span class="monitor-value" id="monitorCliente"></span></div>
                <div><span class="monitor-label">Serviço</span><span class="monitor-value" id="monitorServico"></span></div>
                <div><span class="monitor-label">Nº OS</span><span class="monitor-value" id="monitorOS"></span></div>
                <div><span class="monitor-label">Data</span><span class="monitor-value" id="monitorData"></span></div>
            </div>
            <div id="monitorContainerFinanceiro"></div>
            <div class="status-section">
                <h5 style="font-size:0.9rem; color:#adb5bd;">Status</h5>
                <strong id="monitorStatus"></strong>
                <span id="monitorAgendamento"></span>
                <div id="monitorObs" class="obs-box"></div>
            </div>
            <div id="monitorFotosArea"></div>
        </div>
        <div class="modal-footer-buttons">
            <button class="btn-back" onclick="fecharModalMonitor()">Fechar</button>
        </div>
    </div>
</div>

<script src="assets/js/script.js"></script>
<script src="assets/js/cobranca.js"></script>
<script src="assets/js/dashboard_gestor.js"></script>
<script src="assets/js/vendas_wizard.js"></script>
<script src="assets/js/servicos_gestor.js"></script>
<script src="assets/js/anexar_fotos.js"></script>
<script src="assets/js/validacao_servico.js"></script>
<script src="assets/js/confirmar_agendamento.js"></script>
<script src="assets/js/monitor.js"></script>
<script src="assets/js/notifications.js"></script>
<script src="assets/js/confirmacoes_globais.js"></script>
<script src="assets/js/mantenedores.js"></script>
<script src="assets/js/processos.js"></script>
</body>
</html>