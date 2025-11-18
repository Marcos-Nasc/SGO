<?php
// A sessão e a conexão com $conexao já foram iniciadas no index.php
$gestor_id = $_SESSION['usuario_id'];

// -- 1. LÓGICA DE BUSCA --
// Busca todas as vendas que o Setor de Cobrança aprovou
$sql = "
    SELECT 
        v.id AS venda_id, 
        v.data_venda, 
        v.numero_os, 
        v.familia_comparecer,
        m.nome AS mantenedor_nome, 
        p.nome AS servico_nome
    FROM vendas v
    JOIN mantenedores m ON v.mantenedor_id = m.id
    JOIN produtos_servicos p ON v.produto_servico_id = p.id
    WHERE v.status = 'Aprovado para Agendamento'
    ORDER BY 
        v.data_venda ASC
";

$resultado = $conexao->query($sql);
?>

<div class="page-header" style="margin-bottom: 20px;">
    <h2>Serviços Aprovados para Agendamento</h2>
</div>

<div class="content-widget">
    <table class="widget-table" id="tabelaServicosAgendar">
        <thead>
            <tr>
                <th>Data da Venda</th>
                <th>Nº OS</th>
                <th>Mantenedor</th>
                <th>Produto/Serviço</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while($venda = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($venda['data_venda'])); ?></td>
                        <td><?php echo htmlspecialchars($venda['numero_os']); ?></td>
                        <td><?php echo htmlspecialchars($venda['mantenedor_nome']); ?></td>
                        <td><?php echo htmlspecialchars($venda['servico_nome']); ?></td>
                        <td><span class="badge badge-aprovado">Aprovado para Agendamento</span></td>
                        <td class="acoes-buttons">
                            <button class="btn-agendar" 
                                    onclick="abrirModalAgendamento(
                                        <?php echo $venda['venda_id']; ?>, 
                                        '<?php echo $venda['numero_os']; ?>',
                                        <?php echo $venda['familia_comparecer']; ?>
                                    )">
                                <i class="bi bi-calendar-plus-fill"></i> Agendar
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">Nenhum serviço pronto para agendar.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<div id="modalAgendamento" class="modal-overlay">
    <div class="modal-content" style="max-width: 550px;">
        <div class="modal-header">
            <h3 id="modalAgendamentoTitulo">Agendar Serviço - OS...</h3>
            <button class="close-modal" onclick="fecharModalAgendamento()">&times;</button>
        </div>
        
        <form id="formAgendamento">
            <input type="hidden" id="agendamento_venda_id" name="venda_id">
            <input type="hidden" id="agendamento_familia" name="familia_comparecer">
            
            <div class="form-group">
                <label for="agendamento_data">Data e Hora do Agendamento</label>
                <input type="datetime-local" id="agendamento_data" name="data_agendada" required>
                <small class="text-muted" style="margin-top: 5px; display:block;">Escolha a data e o horário para a execução do serviço.</small>
            </div>

            <div class="form-group">
                <label for="agendamento_obs">Observações (Opcional)</label>
                <textarea id="agendamento_obs" name="observacoes" rows="3" placeholder="Detalhes internos, ex: levar ferramenta X..."></textarea>
            </div>

            <div id="alertaFamiliaComparece" class="alert-info" style="display:none;">
                <i class="bi bi-info-circle-fill"></i>
                A família irá comparecer. Este agendamento irá para a aprovação do "Sucesso do Cliente".
            </div>
            
            <div id="alertaFamiliaNaoComparece" class="alert-info success" style="display:none;">
                <i class="bi bi-check-circle-fill"></i>
                A família não irá comparecer. O agendamento será confirmado e aprovado diretamente.
            </div>

            <div class="form-separator"></div>

            <div class="modal-footer-buttons" style="justify-content: flex-end;">
                <button type="button" class="btn-back" onclick="fecharModalAgendamento()">Cancelar</button>
                <button type="button" class="btn-primary" id="btnConfirmarAgendamento" onclick="salvarAgendamento()">
                    Confirmar Agendamento
                </button>
            </div>
        </form>
    </div>
</div>