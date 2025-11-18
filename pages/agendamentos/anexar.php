<?php
// A sessão e a conexão com $conexao já foram iniciadas no index.php
$gestor_id = $_SESSION['usuario_id'];

// -- 1. LÓGICA DE BUSCA (SQL CORRETO) --
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
        a.status IN ('Confirmado') -- <-- MUDANÇA AQUI
    ORDER BY 
        a.data_agendada ASC
";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $gestor_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<div class="page-header" style="margin-bottom: 20px;">
    <h2>Agendamentos Confirmados para Execução</h2>
</div>

<div class="content-widget">
    <table class="widget-table" id="tabelaAgendamentos">
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
                    <tr id="agendamento-row-<?php echo $agendamento['agendamento_id']; ?>">
                        
                        <td><?php echo date('d/m/Y \à\s H:i', strtotime($agendamento['data_agendada'])); ?></td>
                        <td><?php echo htmlspecialchars($agendamento['numero_os']); ?></td>
                        <td><?php echo htmlspecialchars($agendamento['mantenedor_nome']); ?></td>
                        <td>
                            <?php if($agendamento['status'] == 'Confirmado'): ?>
                                <span class="badge badge-aprovado">Confirmado</span>
                            <?php else: ?>
                                <span class="badge badge-cobranca"><?php echo $agendamento['status']; ?></span>
                            <?php endif; ?>
                        </td>
                        
                        <td class="acoes-buttons">
                            <button class="btn-agendar"
                                    onclick="abrirModalFotos(
                                <?php echo $agendamento['agendamento_id']; ?>, 
                                '<?php echo htmlspecialchars($agendamento['numero_os']); ?>',
                                '<?php echo htmlspecialchars($agendamento['mantenedor_nome']); ?>',
                                '<?php echo date('d/m/Y, H:i:s', strtotime($agendamento['data_agendada'])); ?>',
                                '<?php echo htmlspecialchars($agendamento['servico_nome']); ?>' 
                            )">
                                <i class="bi bi-camera-fill"></i> Anexar Fotos
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">Nenhum agendamento confirmado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<?php include 'pages/agendamentos/anexar_modal_template.php'; ?>