<?php
// Busca vendas do usuário logado
$vendedor_id = $_SESSION['usuario_id'];
$sql = "SELECT v.*, m.nome as mantenedor_nome, ps.nome as servico_nome 
        FROM vendas v 
        JOIN mantenedores m ON v.mantenedor_id = m.id
        JOIN produtos_servicos ps ON v.produto_servico_id = ps.id
        WHERE v.vendedor_id = ? ORDER BY v.data_venda DESC";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $vendedor_id);
$stmt->execute();
$vendas = $stmt->get_result();

// Busca Produtos/Serviços para o Select
$prod_sql = "SELECT id, nome, tipo FROM produtos_servicos WHERE status = 'Ativo'";
$produtos = $conexao->query($prod_sql);
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Minhas Vendas</h2>
    <button id="btnNovaVenda" class="btn-primary">
        Cadastrar Nova Venda
    </button>
</div>

<div class="content-widget">
    <table class="widget-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Nº OS</th>
                <th>Mantenedor</th>
                <th>Produto/Serviço</th>
                <th>Valor Final</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($venda = $vendas->fetch_assoc()):
                $badgeClass = 'badge-pendente';
                if (strpos($venda['status'], 'Agendado') !== false) $badgeClass = 'badge-agendado';
                if (strpos($venda['status'], 'Cobrança') !== false) $badgeClass = 'badge-cobranca';
                if (strpos($venda['status'], 'Aprovado') !== false) $badgeClass = 'badge-aprovado';
            ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($venda['data_venda'])); ?></td>
                    <td><?php echo $venda['numero_os'] ? $venda['numero_os'] : '-'; ?></td>
                    <td><?php echo htmlspecialchars($venda['mantenedor_nome']); ?></td>
                    <td><?php echo htmlspecialchars($venda['servico_nome']); ?></td>
                    <td>R$ <?php echo number_format($venda['valor_final'], 2, ',', '.'); ?></td>
                    <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $venda['status']; ?></span></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div id="modalVenda" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Cadastrar Nova Venda</h3>
            <button class="close-modal" onclick="fecharModal()">&times;</button>
        </div>

        <div class="stepper">
            <div class="stepper-line-bg"></div>
            <div class="stepper-line-progress" id="stepperProgress"></div>

            <div class="step active" id="step-indicator-1">
                <div class="step-circle">1</div>
            </div>
            <div class="step" id="step-indicator-2">
                <div class="step-circle">2</div>
            </div>
            <div class="step" id="step-indicator-3">
                <div class="step-circle">3</div>
            </div>
        </div>

        <form id="formVenda">
            <input type="hidden" id="selected_mantenedor_id" name="mantenedor_id">
            <input type="hidden" id="selected_contrato_id" name="contrato_id">

            <div class="step-content active" id="step-1-busca-mantenedor">
                <h4>Etapa 1: Dados do Mantenedor</h4>
                <div class="form-group">
                    <label>Pesquisar Mantenedor (por Nome ou Email)</label>
                    <input type="text" id="buscaMantenedor" placeholder="Digite o nome ou email..." autocomplete="off">
                    <div id="listaMantenedores" class="search-results"></div>
                </div>
                <div class="action-link-group">
                    Não encontrou? <a href="#" id="linkCadastrarNovoMantenedor">Cadastrar Novo Mantenedor</a>
                </div>

                <div class="modal-footer-buttons">
                    <button type="button" class="btn-back" onclick="passoAnterior(1)" style="visibility:hidden;">Voltar</button> <button type="button" class="btn-primary" onclick="proximoPasso(2)" disabled id="btnNext1">Avançar</button>
                </div>
            </div>

            <div class="step-content" id="step-1-cadastro-mantenedor">
                <h4>Cadastrar Novo Mantenedor</h4>
                <div class="form-group">
                    <label for="novoMantenedorNome">Nome Completo</label>
                    <input type="text" id="novoMantenedorNome" placeholder="Nome completo do mantenedor">
                </div>
                <div class="form-group">
                    <label for="novoMantenedorEmail">E-mail</label>
                    <input type="email" id="novoMantenedorEmail" placeholder="email@exemplo.com">
                </div>
                <div class="form-group">
                    <label for="novoMantenedorTelefone">Telefone</label>
                    <input type="text" id="novoMantenedorTelefone" placeholder="(XX) XXXX-XXXX">
                </div>

                <div class="form-separator"></div>
                <div class="modal-footer-buttons">
                    <button type="button" class="btn-back" onclick="mostrarBuscaMantenedor()">Cancelar</button>
                    <button type="button" class="btn-primary" id="btnSalvarNovoMantenedor" disabled>Salvar e Continuar</button>
                </div>
            </div>

            <div class="step-content" id="step-2-busca-contrato">
                <h4>Etapa 2: Dados do Contrato (<span id="labelNomeMantenedor">...</span>)</h4>
                <div class="form-group">
                    <label>Selecione o Contrato Existente</label>
                    <div id="listaContratos" class="search-results" style="display:block; max-height:250px;">
                        <p style="padding:10px; color:var(--cor-texto-secundario);">Selecione um mantenedor primeiro.</p>
                    </div>
                </div>
                <div class="action-link-group">
                    Não encontrou? <a href="#" id="linkCadastrarNovoContrato">Cadastrar Novo Contrato</a>
                </div>

                <div class="form-separator"></div>

                <div class="modal-footer-buttons">
                    <button type="button" class="btn-back" onclick="passoAnterior(1)">Voltar</button>
                    <button type="button" class="btn-primary" onclick="proximoPasso(3)" disabled id="btnNext2">Avançar</button>
                </div>
            </div>

            <div class="step-content" id="step-2-cadastro-contrato">
                <h4>Cadastrar Novo Contrato para <span id="labelNomeMantenedorCadastro">...</span></h4>

                <div class="form-group">
                    <label for="novoContratoCemiterio">Filial (Cemitério)</label>
                    <select id="novoContratoCemiterio">
                        <option value="">Selecione...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="novoContratoNumero">Número do Contrato</label>
                    <input type="text" id="novoContratoNumero" placeholder="Digite o número do contrato">
                </div>

                <div class="form-group">
                    <label for="novoContratoJazigo">Jazigo</label>
                    <input type="text" id="novoContratoJazigo" placeholder="Ex: J-01">
                </div>

                <div class="form-group">
                    <label for="novoContratoQuadra">Quadra</label>
                    <input type="text" id="novoContratoQuadra" placeholder="Ex: Q-A">
                </div>

                <div class="form-group">
                    <label for="novoContratoBloco">Bloco</label>
                    <input type="text" id="novoContratoBloco" placeholder="Ex: B-01">
                </div>

                <div class="form-separator"></div>

                <div class="modal-footer-buttons">
                    <button type="button" class="btn-back" onclick="mostrarBuscaContrato()">Cancelar</button>
                    <button type="button" class="btn-primary" id="btnSalvarNovoContrato">Salvar e Continuar</button>
                </div>
            </div>

            <div class="step-content" id="step-3">
                <h4>Etapa 3: Dados da Venda</h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

                    <div class="form-group">
                        <label>Tipo do Produto/Serviço</label>
                        <select id="filtroTipo" onchange="filtrarProdutos()">
                            <option value="">Selecione...</option>
                            <?php
                            // Criar um array para garantir tipos únicos
                            $tipos_unicos = [];
                            if ($produtos->num_rows > 0) {
                                // Loop 1: Apenas para pegar os tipos únicos
                                while ($p = $produtos->fetch_assoc()) {
                                    $tipos_unicos[$p['tipo']] = 1; // Usa a chave do array para evitar duplicatas
                                }

                                // Ordena os tipos alfabeticamente
                                $lista_tipos = array_keys($tipos_unicos);
                                sort($lista_tipos);

                                // Imprime as opções
                                foreach ($lista_tipos as $tipo) {
                                    echo '<option value="' . htmlspecialchars($tipo) . '">' . htmlspecialchars($tipo) . '</option>';
                                }

                                // IMPORTANTE: Resetar o ponteiro do resultado para o começo
                                $produtos->data_seek(0);
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Produto/Serviço</label>
                        <select id="selectProduto" name="produto_servico_id" disabled>
                            <option value="">Selecione o tipo primeiro...</option>
                            <?php
                            // Loop 2: Agora funciona de novo graças ao data_seek(0)
                            // Ele irá imprimir o data-tipo com o nome da categoria correta
                            while ($p = $produtos->fetch_assoc()): ?>
                                <option value="<?php echo $p['id']; ?>" data-tipo="<?php echo htmlspecialchars($p['tipo']); ?>" style="display:none;">
                                    <?php echo htmlspecialchars($p['nome']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                </div>


                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Data da Venda</label>
                        <input type="date" name="data_venda" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Número da OS</label>
                        <input type="text" name="numero_os" id="numero_os" placeholder="Ex: OS-1023">
                    </div>
                </div>

                <div class="form-group">
                    <label>Número da NF</label>
                    <input type="text" name="numero_nf" placeholder="Ex: 000.231">
                </div>

                <div class="form-group" style="margin: 20px 0;">
                    <div class="checkbox-wrapper disabled" id="wrapperFamilia">
                        <label style="display: flex; align-items: center; gap: 10px; user-select: none;">
                            <input type="checkbox" id="checkFamilia" name="familia_comparecer" disabled>
                            Família comparecerá?
                        </label>
                        <small style="color: var(--cor-texto-secundario); font-size: 0.85rem; display:block; margin-top:5px;">
                            Disponível apenas para "A Vista" ou "A Prazo" com entrada ≥ 50%.
                        </small>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Valor Final (R$)</label>
                        <input type="number" step="0.01" id="valorFinal" name="valor_final" placeholder="0,00">
                    </div>
                    <div class="form-group">
                        <label>Condição de Pagamento</label>
                        <select id="condicaoPagamento" name="condicao_pagamento">
                            <option value="A Vista">A Vista</option>
                            <option value="A Prazo">A Prazo</option>
                        </select>
                    </div>
                </div>

                <div id="divCamposPrazo" style="display:none; margin-top: 15px;">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Valor de Entrada (R$)</label>
                            <input type="number" step="0.01" id="valorEntrada" name="valor_entrada" placeholder="0,00">
                        </div>
                        <div class="form-group">
                            <label>Qtd. Parcelas</label>
                            <input type="number" id="qtdeParcelas" name="qtde_parcelas" placeholder="Ex: 5" min="1">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 15px;"> <label>Data da 1ª Parcela (Previsão)</label>
                        <input type="date" name="data_previsao_cobranca" id="dataPrevisao"
                            value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>"
                            onchange="verificarRegraPagamento()">
                    </div>

                    <div id="alertaEntrada" style="display:none; margin-top:10px; padding: 10px; background: rgba(220, 53, 69, 0.1); color: #dc3545; border: 1px solid #dc3545; border-radius: 6px; font-size: 0.9rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span id="textoAlertaEntrada"></span>
                    </div>

                    <div id="resumoParcelas" class="installment-summary">
                        <span>Parcelamento:</span>
                        <span id="textoCalculoParcela">--</span>
                    </div>
                </div>
                <div class="form-separator"></div>
                <div class="modal-footer-buttons">
                    <button type="button" class="btn-back" onclick="passoAnterior(2)">Voltar</button>
                    <button type="button" class="btn-primary" id="btnConcluirVenda" disabled>Concluir Venda</button>
                </div>

            </div>
        </form>
    </div>
</div>