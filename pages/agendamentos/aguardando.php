<?php
// A sessão e a conexão com $conexao já foram iniciadas no index.php
$gestor_id = $_SESSION['usuario_id'];

// Busca agendamentos enviados para validação
$sql = "
    SELECT 
        a.id AS agendamento_id, 
        a.data_agendada, 
        v.numero_os, 
        m.nome AS mantenedor_nome,
        a.status,
        p.nome AS servico_nome
    FROM agendamentos a
    JOIN vendas v ON a.venda_id = v.id
    JOIN mantenedores m ON v.mantenedor_id = m.id
    JOIN produtos_servicos p ON v.produto_servico_id = p.id
    WHERE 
        a.gestor_id = ? AND
        a.status = 'Concluído - Aguardando Validação' -- <-- SÓ O QUE ESTÁ AGUARDANDO
    ORDER BY 
        a.data_agendada DESC
";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $gestor_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<div class="page-header" style="margin-bottom: 20px;">
    <h2>Serviços Enviados (Aguardando Validação)</h2>
</div>

<div class="content-widget">
    <table class="widget-table" id="tabelaAguardando">
        <thead>
            <tr>
                <th>Data Agendada</th>
                <th>Nº OS</th>
                <th>Mantenedor</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($agendamento = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d/m/Y \à\s H:i', strtotime($agendamento['data_agendada'])); ?></td>
                        <td><?php echo htmlspecialchars($agendamento['numero_os']); ?></td>
                        <td><?php echo htmlspecialchars($agendamento['mantenedor_nome']); ?></td>
                        <td><span class="badge badge-cobranca">Aguardando Validação</span></td>
                        <td class="acoes-buttons">
                            <button class="btn-details" 
                                    onclick="abrirModalFotos(
                                <?php echo $agendamento['agendamento_id']; ?>, 
                                '<?php echo htmlspecialchars($agendamento['numero_os']); ?>',
                                '<?php echo htmlspecialchars($agendamento['mantenedor_nome']); ?>',
                                '<?php echo date('d/m/Y, H:i:s', strtotime($agendamento['data_agendada'])); ?>',
                                '<?php echo htmlspecialchars($agendamento['servico_nome']); ?>' 
                            )">
                                Ver Fotos
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">Nenhum serviço aguardando validação.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
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
                </div>

                <div class="upload-section">
                    <h4>Fotos do "Depois"</h4>
                    <div class="photo-grid" id="grid-fotos-depois">
                        <span class="loading-fotos">Carregando...</span>
                    </div>
                </div>
            </div>

            <div class="form-separator"></div>

            <div class="modal-footer-buttons" style="justify-content: space-between;">
                <button type="button" class="btn-back" onclick="fecharModalFotos()">Fechar</button>
            </div>
        </form>
    </div>
</div>