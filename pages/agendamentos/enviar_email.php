<?php
// A sessão e a conexão com $conexao já foram iniciadas no index.php

// -- 1. BUSCAR A PRÓXIMA TAREFA NA FILA --
// Mostra APENAS o que foi validado internamente
$sql = "
    SELECT 
        a.id AS agendamento_id, 
        a.status,
        v.numero_os, 
        m.nome AS mantenedor_nome, 
        m.email AS mantenedor_email,
        p.nome AS servico_nome
    FROM agendamentos a
    JOIN vendas v ON a.venda_id = v.id
    JOIN mantenedores m ON v.mantenedor_id = m.id
    JOIN produtos_servicos p ON v.produto_servico_id = p.id
    WHERE 
        a.status = 'Finalizado Internamente'
    ORDER BY 
        a.atualizado_em ASC
";
$resultado = $conexao->query($sql);
?>

<div class="page-header" style="margin-bottom: 20px;">
    <h2>Fila de Envio de E-mails</h2>
</div>

<div class="content-widget">
    <table class="widget-table" id="tabelaEnviarEmail">
        <thead>
            <tr>
                <th>Nº OS</th>
                <th>Serviço</th>
                <th>Cliente</th>
                <th>E-mail</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($agendamento = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($agendamento['numero_os']); ?></td>
                        <td><?php echo htmlspecialchars($agendamento['servico_nome']); ?></td>
                        <td><?php echo htmlspecialchars($agendamento['mantenedor_nome']); ?></td>
                        <td><?php echo htmlspecialchars($agendamento['mantenedor_email']); ?></td>
                        <td class="acoes-buttons">
                            <button class="btn-validation email" 
                                    onclick="abrirModalConfirmarEmail(
                                        <?php echo $agendamento['agendamento_id']; ?>,
                                        '<?php echo htmlspecialchars($agendamento['mantenedor_nome']); ?>',
                                        '<?php echo htmlspecialchars($agendamento['mantenedor_email']); ?>'
                                    )">
                                <i class="bi bi-send-fill"></i> Enviar E-mail
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">Nenhum e-mail pendente para envio.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="modalConfirmarEmail" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Confirmar Envio de E-mail</h3>
            <button class="close-modal" onclick="fecharModalConfirmarEmail()">&times;</button>
        </div>
        
        <p style="font-size: 1rem; color: var(--cor-texto-secundario); margin-bottom: 20px;">
            Você está prestes a enviar o e-mail de conclusão do serviço (com as fotos em anexo) para:
        </p>
        
        <div class="detalhe-item" style="margin-top: 20px;">
            <label>Cliente</label>
            <span id="email-confirm-cliente" style="font-size: 1.1rem; font-weight: 500;">...</span>
        </div>
        <div class="detalhe-item" style="margin-top: 15px;">
            <label>E-mail</label>
            <span id="email-confirm-email" style="font-size: 1.1rem; font-weight: 500;">...</span>
        </div>
        
        <div class="form-separator"></div>
        
        <div class="modal-footer-buttons" style="justify-content: flex-end;">
            <button type="button" class="btn-back" onclick="fecharModalConfirmarEmail()">Cancelar</button>
            <button type="button" class="btn-validation email" id="btnConfirmarEnvioFinal" onclick="executarEnvioEmail(this)">
                <i class="bi bi-send-fill"></i> Confirmar Envio
            </button>
        </div>
    </div>
</div>