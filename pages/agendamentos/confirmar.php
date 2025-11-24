<?php
// A sessão e a conexão com $conexao já foram iniciadas no index.php
$sc_id = $_SESSION['usuario_id'];

// -- 1. BUSCAR AGENDAMENTOS PENDENTES DE CONTATO --
$sql = "
    SELECT 
        a.id AS agendamento_id, 
        a.data_agendada, 
        v.numero_os, 
        m.nome AS mantenedor_nome, 
        m.telefone AS mantenedor_telefone,
        m.email AS mantenedor_email,
        p.nome AS servico_nome,
        a.observacoes
    FROM agendamentos a
    JOIN vendas v ON a.venda_id = v.id
    JOIN mantenedores m ON v.mantenedor_id = m.id
    JOIN produtos_servicos p ON v.produto_servico_id = p.id
    WHERE 
        a.status = 'Pendente de Contato'
    ORDER BY 
        a.data_agendada ASC
";
$resultado = $conexao->query($sql);
?>

<div class="page-header" style="margin-bottom: 20px;">
    <h2>Confirmar Agendamentos (Família)</h2>
</div>

<div class="content-widget">
    <table class="widget-table" id="tabelaConfirmar">
        <thead>
            <tr>
                <th>Data Prevista</th>
                <th>Nº OS</th>
                <th>Cliente</th>
                <th>Telefone</th>
                <th>Serviço</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($row = $resultado->fetch_assoc()): ?>
                    <tr id="row-<?php echo $row['agendamento_id']; ?>">
                        <td>
                            <strong style="color: var(--cor-link-ativo-texto);">
                                <?php echo date('d/m/Y H:i', strtotime($row['data_agendada'])); ?>
                            </strong>
                        </td>
                        <td><?php echo htmlspecialchars($row['numero_os']); ?></td>
                        <td><?php echo htmlspecialchars($row['mantenedor_nome']); ?></td>
                        <td><?php echo htmlspecialchars($row['mantenedor_telefone']); ?></td>
                        <td><?php echo htmlspecialchars($row['servico_nome']); ?></td>
                        <td class="acoes-buttons">
                            <button class="btn-confirm"
                                style="background-color: #28a745; color: white;"
                                onclick="abrirModalConfirmacao(<?php echo $row['agendamento_id']; ?>, '<?php echo htmlspecialchars($row['numero_os'] ?? ''); ?>', '<?php echo htmlspecialchars($row['mantenedor_nome'] ?? ''); ?>', '<?php echo htmlspecialchars($row['mantenedor_telefone'] ?? ''); ?>', '<?php echo htmlspecialchars($row['servico_nome'] ?? ''); ?>', '<?php echo date('d/m/Y', strtotime($row['data_agendada'])); ?>')">
                                <i class="bi bi-check2-square"></i> Confirmar
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">Nenhum agendamento pendente de contato.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="modalConfirmacao" class="modal-overlay">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Confirmar com Cliente</h3>
            <button class="close-modal" onclick="fecharModalConfirmacao()">&times;</button>
        </div>

        <div class="modal-card financeiro" style="margin-bottom: 20px;">
            <h4><i class="bi bi-person-lines-fill"></i> Dados para Contato</h4>
            <div class="detalhes-grid">
                <div class="detalhe-item">
                    <label>Cliente</label>
                    <span id="modal-cliente" style="font-weight:bold;">...</span>
                </div>
                <div class="detalhe-item">
                    <label>Telefone</label>
                    <span id="modal-telefone" style="font-size: 1.2rem; color: var(--cor-link-ativo-texto);">...</span>
                </div>
            </div>
            <div class="form-separator" style="margin: 10px 0;"></div>
            <div class="detalhe-item">
                <label>Script Sugerido:</label>
                <p style="font-size: 0.9rem; color: var(--cor-texto-secundario); font-style: italic;">
                    "Olá, estamos ligando para confirmar o serviço de <strong id="modal-servico">...</strong> agendado para o dia <strong id="modal-data">...</strong>. A família poderá comparecer?"
                </p>
            </div>
        </div>

        <div class="form-group">
            <label>Observações do Contato (Opcional)</label>
            <textarea id="obsConfirmacao" class="form-control" rows="2" placeholder="Ex: Confirmado com Sr. João, pediu para chegar 10min antes..."></textarea>
        </div>

        <div class="form-separator"></div>

        <div class="modal-footer-buttons" style="justify-content: space-between;">
            <button type="button" class="btn-back" onclick="fecharModalConfirmacao()">Cancelar</button>

            <div style="display:flex; gap:10px;">
                <button type="button" class="btn-validation reject" id="btnReagendar" onclick="rejeitarAgendamento()">
                    <i class="bi bi-x-circle"></i> Cliente Cancelou
                </button>
                <button type="button" class="btn-approve" id="btnConfirmarFinal" onclick="confirmarAgendamento()">
                    <i class="bi bi-check-lg"></i> Confirmar Presença
                </button>
            </div>
        </div>
    </div>
</div>