<?php
$gestor_id = $_SESSION['usuario_id'];

// Busca agendamentos Rejeitados OU Reagendamento Solicitado
$sql = "
    SELECT 
        a.id AS agendamento_id, 
        a.data_agendada, 
        a.status,
        a.observacoes, 
        v.numero_os,
        v.familia_comparecer, -- Precisamos saber se a família vem
        m.nome AS mantenedor_nome,
        p.nome AS servico_nome
    FROM agendamentos a
    JOIN vendas v ON a.venda_id = v.id
    JOIN mantenedores m ON v.mantenedor_id = m.id
    JOIN produtos_servicos p ON v.produto_servico_id = p.id
    WHERE 
        a.gestor_id = ? AND
        a.status IN ('Rejeitado', 'Reagendamento Solicitado') -- <-- FILTRA OS DOIS
    ORDER BY 
        a.data_agendada DESC
";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $gestor_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<div class="page-header" style="margin-bottom: 20px;">
    <h2>Serviços com Pendências (Correção/Reagendamento)</h2>
</div>

<div class="content-widget">
    <table class="widget-table" id="tabelaRejeitados">
        <thead>
            <tr>
                <th>Data Original</th>
                <th>Nº OS</th>
                <th>Mantenedor</th>
                <th>Motivo</th>
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
                        <td><?php echo htmlspecialchars($agendamento['observacoes']); ?></td>
                        <td class="acoes-buttons">
                            
                            <?php if ($agendamento['status'] == 'Rejeitado'): ?>
                                <button class="btn-agendar" style="background-color: #dc3545;" 
                                        onclick="abrirModalFotos(
                                    <?php echo $agendamento['agendamento_id']; ?>, 
                                    '<?php echo htmlspecialchars($agendamento['numero_os']); ?>',
                                    '<?php echo htmlspecialchars($agendamento['mantenedor_nome']); ?>',
                                    '<?php echo date('d/m/Y, H:i:s', strtotime($agendamento['data_agendada'])); ?>',
                                    '<?php echo htmlspecialchars($agendamento['servico_nome']); ?>' 
                                )">
                                    <i class="bi bi-camera-fill"></i> Corrigir Fotos
                                </button>
                            
                            <?php else: ?>
                                <button class="btn-agendar" style="background-color: #ffc107; color: #000;"
                                        onclick="abrirModalReagendamento(
                                            <?php echo $agendamento['agendamento_id']; ?>,
                                            '<?php echo htmlspecialchars($agendamento['numero_os']); ?>',
                                            <?php echo $agendamento['familia_comparecer']; ?>
                                        )">
                                    <i class="bi bi-calendar-range"></i> Reagendar
                                </button>
                            <?php endif; ?>

                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">Nenhuma pendência encontrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'pages/agendamentos/anexar_modal_template.php';?>

<div id="modalReagendamento" class="modal-overlay">
    <div class="modal-content" style="max-width: 550px;">
        <div class="modal-header">
            <h3 id="modalReagendamentoTitulo">Reagendar Serviço</h3>
            <button class="close-modal" onclick="fecharModalReagendamento()">&times;</button>
        </div>
        
        <form id="formReagendamento">
            <input type="hidden" id="reagendamento_id" name="agendamento_id">
            <input type="hidden" id="reagendamento_familia" name="familia_comparecer">
            
            <div class="form-group">
                <label>Nova Data e Hora</label>
                <input type="datetime-local" id="reagendamento_data" name="data_agendada" required>
            </div>

            <div class="form-group">
                <label>Observações</label>
                <textarea id="reagendamento_obs" name="observacoes" rows="3" placeholder="Motivo do reagendamento..."></textarea>
            </div>

            <div class="form-separator"></div>

            <div class="modal-footer-buttons" style="justify-content: flex-end;">
                <button type="button" class="btn-back" onclick="fecharModalReagendamento()">Cancelar</button>
                <button type="button" class="btn-primary" onclick="salvarReagendamento()">Confirmar Nova Data</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/anexar_fotos.js"></script>
<script>
// Script inline rápido para o Reagendamento (ou crie um arquivo separado se preferir)
const modalReagendamento = document.getElementById('modalReagendamento');

function abrirModalReagendamento(id, os, familia) {
    document.getElementById('reagendamento_id').value = id;
    document.getElementById('reagendamento_familia').value = familia;
    document.getElementById('modalReagendamentoTitulo').innerText = "Reagendar OS " + os;
    modalReagendamento.classList.add('active');
}

function fecharModalReagendamento() {
    modalReagendamento.classList.remove('active');
}

function salvarReagendamento() {
    const form = document.getElementById('formReagendamento');
    const formData = new FormData(form);
    formData.append('acao', 'reagendar_servico');

    // O caminho deve ser relativo ao index.php
    fetch('pages/produtos_servicos/actions.php', { 
        method: 'POST', 
        body: formData 
    })
    .then(r => r.json()) // Se der erro aqui, é porque o PHP acima falhou
    .then(data => {
        alert(data.msg);
        if(data.status === 'sucesso') location.reload();
    })
    .catch(err => {
        console.error(err);
        alert('Erro de comunicação ou JSON inválido.');
    });
}
</script>