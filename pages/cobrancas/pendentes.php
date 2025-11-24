<?php
// A sessão e a conexão com $conexao já foram iniciadas no index.php
$sql = "
    SELECT 
        v.*, 
        m.nome AS mantenedor_nome, 
        m.email AS mantenedor_email, 
        m.telefone AS mantenedor_telefone,
        c.numero AS contrato_numero, 
        p.nome AS servico_nome,
        CASE 
            WHEN v.data_previsao_cobranca = CURDATE() THEN 1
            ELSE 2 
        END AS prioridade
    FROM vendas v
    JOIN mantenedores m ON v.mantenedor_id = m.id
    JOIN contratos c ON v.contrato_id = c.id
    JOIN produtos_servicos p ON v.produto_servico_id = p.id
    WHERE v.status = 'Aguardando Cobrança'
    ORDER BY 
        prioridade ASC, 
        v.data_previsao_cobranca ASC
";

$resultado = $conexao->query($sql);
$hoje = date('Y-m-d');
?>

<div class="page-header" style="margin-bottom: 20px;">
    <h2>Cobranças Pendentes</h2>
</div>

<div class="content-widget">
    <table class="widget-table" id="tabelaCobrancas">
        <thead>
            <tr>
                <th>Vencimento</th>
                <th>Nº OS</th>
                <th>Mantenedor</th>
                <th>Contrato</th>
                <th>Valor Final</th>
                <th>Entrada</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($venda = $resultado->fetch_assoc()):
                    $is_vencido = $venda['data_previsao_cobranca'] < $hoje;
                    $is_hoje = $venda['prioridade'] == 1;
                ?>
                    <tr <?php if ($is_hoje) echo 'class="priority-today"'; ?>>
                        <td>
                            <strong style="<?php echo ($is_vencido || $is_hoje) ? 'color: #dc3545;' : ''; ?>">
                                <?php echo date('d/m/Y', strtotime($venda['data_previsao_cobranca'])); ?>
                            </strong>
                            <?php if ($is_hoje): ?>
                                <span class="badge-hoje">HOJE</span>
                            <?php elseif ($is_vencido): ?>
                                <span class="badge-hoje" style="background-color: #ffc107;">VENCIDO</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($venda['numero_os']); ?></td>
                        <td><?php echo htmlspecialchars($venda['mantenedor_nome']); ?></td>
                        <td><?php echo htmlspecialchars($venda['contrato_numero']); ?></td>
                        <td>R$ <?php echo number_format($venda['valor_final'], 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($venda['valor_entrada'], 2, ',', '.'); ?></td>
                        <td class="acoes-buttons">
                            <button class="btn-approve"
                                data-venda-id="<?php echo $venda['id']; ?>"
                                data-os="<?php echo htmlspecialchars($venda['numero_os']); ?>"
                                onclick="aprovarCobranca(this, <?php echo $venda['id']; ?>)"> <i class="bi bi-check-circle-fill"></i> Aprovar
                            </button>
                            <button class="btn-details"
                                onclick="abrirModalDetalhes(this)"
                                data-venda-id="<?php echo $venda['id']; ?>"
                                data-os="<?php echo htmlspecialchars($venda['numero_os']); ?>"
                                data-mantenedor="<?php echo htmlspecialchars($venda['mantenedor_nome']); ?>"
                                data-telefone="<?php echo htmlspecialchars($venda['mantenedor_telefone']); ?>"
                                data-email="<?php echo htmlspecialchars($venda['mantenedor_email']); ?>"
                                data-contrato="<?php echo htmlspecialchars($venda['contrato_numero']); ?>"
                                data-servico="<?php echo htmlspecialchars($venda['servico_nome']); ?>"
                                data-data-venda="<?php echo date('d/m/Y', strtotime($venda['data_venda'])); ?>"
                                data-nf="<?php echo htmlspecialchars($venda['numero_nf']); ?>"
                                data-familia="<?php echo $venda['familia_comparecer'] ? 'Sim' : 'Não'; ?>"
                                data-valor-final="R$ <?php echo number_format($venda['valor_final'], 2, ',', '.'); ?>"
                                data-condicao="<?php echo htmlspecialchars($venda['condicao_pagamento']); ?>"
                                data-valor-entrada="R$ <?php echo number_format($venda['valor_entrada'], 2, ',', '.'); ?>"
                                data-parcelas="<?php echo htmlspecialchars($venda['qtde_parcelas']); ?>"
                                data-valor-parcela="R$ <?php echo number_format($venda['valor_parcela'], 2, ',', '.'); ?>"
                                data-previsao="<?php echo date('d/m/Y', strtotime($venda['data_previsao_cobranca'])); ?>"
                                data-is-hoje="<?php echo $is_hoje ? '1' : '0'; ?>">
                                Detalhes
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">Nenhuma cobrança pendente no momento.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<div id="modalCobranca" class="modal-overlay">
    <div class="modal-content modal-detalhes" style="max-width: 700px;">
        <div class="modal-header">
            <h3 id="modalTituloOS">Detalhes da Venda - OS 0024</h3>
            <button class="close-modal" onclick="fecharModalDetalhes()">&times;</button>
        </div>

        <div class="modal-card">
            <h4><i class="bi bi-person-fill"></i> Informações do Cliente</h4>
            <div class="detalhes-grid">
                <div class="detalhe-item">
                    <label>Mantenedor</label>
                    <span id="modalMantenedor"></span>
                </div>
                <div class="detalhe-item">
                    <label>Telefone</label>
                    <span id="modalTelefone"></span>
                </div>
                <div class="detalhe-item">
                    <label>Email</label>
                    <span id="modalEmail"></span>
                </div>
                <div class="detalhe-item">
                    <label>Contrato</label>
                    <span id="modalContrato"></span>
                </div>
            </div>
        </div>

        <div class="modal-card">
            <h4><i class="bi bi-info-circle-fill"></i> Informações da Venda</h4>
            <div class="detalhes-grid">
                <div class="detalhe-item">
                    <label>Produto/Serviço</label>
                    <span id="modalServico"></span>
                </div>
                <div class="detalhe-item">
                    <label>Nº OS / Nº NF</label>
                    <span id="modalOsNf"></span>
                </div>
                <div class="detalhe-item">
                    <label>Data da Venda</label>
                    <span id="modalDataVenda"></span>
                </div>
                <div class="detalhe-item" id="itemDataPrevisao">
                    <label>Data Vencimento (Previsão)</label>
                    <span id="modalDataPrevisao" style="font-weight: 700;"></span>
                </div>
            </div>
        </div>

        <div class="modal-card financeiro">
            <h4><i class="bi bi-cash-coin"></i> Resumo Financeiro</h4>
            <div class="detalhes-grid" style="grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px;">
                <div class="detalhe-item">
                    <label>Valor Final</label>
                    <strong id="modalValorFinal"></strong>
                </div>
                <div class="detalhe-item">
                    <label>Condição</label>
                    <span id="modalCondicao"></span>
                </div>
                <div class="detalhe-item">
                    <label>Valor Entrada</label>
                    <span id="modalValorEntrada"></span>
                </div>
                <div class="detalhe-item">
                    <label>Família?</label>
                    <span id="modalFamilia"></span>
                </div>
            </div>

            <div class="form-separator" style="margin: 15px 0;"></div>

            <div class="detalhes-grid" style="grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="detalhe-item">
                    <label>Nº Parcelas</label>
                    <span id="modalParcelas" style="font-size: 1.2rem;"></span>
                </div>
                <div class="detalhe-item">
                    <label>Valor da Parcela</label>
                    <strong id="modalValorParcela" style="font-size: 1.5rem;"></strong>
                </div>
            </div>
        </div>

        <div class="modal-footer-buttons">
            <button type="button" class="btn-back" onclick="fecharModalDetalhes()">Fechar</button>
            <button type="button" class="btn-approve" id="btnAprovarModal" onclick="aprovarDoModal(this)">
                <i class="bi bi-check-circle-fill"></i> Aprovar Venda
            </button>
        </div>
    </div>
</div>