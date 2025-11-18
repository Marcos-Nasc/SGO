<?php
// pages/monitor/view.php

// --- 1. CAPTURA DE FILTROS ---
// MUDANÇA: Data inicial agora é o primeiro dia do ANO, para não esconder vendas antigas
$filtro_inicio = $_GET['data_inicio'] ?? date('Y-01-01'); 
$filtro_fim    = $_GET['data_fim'] ?? date('Y-12-31');
$filtro_busca  = $_GET['busca'] ?? '';
$filtro_status = $_GET['status'] ?? '';

// --- 2. CONSTRUÇÃO DA CONSULTA ---
// Buscamos TUDO: Venda, Cliente, Serviço e Agendamento
$sql = "
    SELECT 
        v.id AS venda_id, 
        v.data_venda, 
        v.numero_os, 
        v.numero_nf,
        v.valor_final,
        v.condicao_pagamento,
        v.qtde_parcelas,
        v.status AS status_venda,
        m.nome AS cliente_nome,
        p.nome AS servico_nome,
        a.data_agendada,
        a.status AS status_agendamento,
        a.observacoes AS obs_agendamento
    FROM vendas v
    JOIN mantenedores m ON v.mantenedor_id = m.id
    JOIN produtos_servicos p ON v.produto_servico_id = p.id
    LEFT JOIN agendamentos a ON a.venda_id = v.id
    WHERE v.data_venda BETWEEN '$filtro_inicio' AND '$filtro_fim'
";

if (!empty($filtro_busca)) {
    $sql .= " AND (m.nome LIKE '%$filtro_busca%' OR v.numero_os LIKE '%$filtro_busca%')";
}
if (!empty($filtro_status)) {
    // Filtra tanto pelo status da venda quanto do agendamento
    $sql .= " AND (v.status = '$filtro_status' OR a.status = '$filtro_status')";
}

$sql .= " ORDER BY v.data_venda DESC";
$resultado = $conexao->query($sql);
?>

<div class="page-header">
    <h2><i class="bi bi-display"></i> Monitor de Processos</h2>
    </div>

<div class="content-widget" style="margin-bottom: 20px; padding: 20px;">
    <form method="GET" action="index.php" class="filter-form">
        <input type="hidden" name="page" value="monitor">
        <input type="hidden" name="action" value="view">
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Data Início</label>
                <input type="date" name="data_inicio" value="<?php echo $filtro_inicio; ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Data Fim</label>
                <input type="date" name="data_fim" value="<?php echo $filtro_fim; ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0; flex-grow: 2;">
                <label>Buscar (Cliente ou OS)</label>
                <input type="text" name="busca" placeholder="Digite para buscar..." value="<?php echo htmlspecialchars($filtro_busca); ?>">
            </div>
            <button type="submit" class="btn-primary" style="height: 42px;">
                <i class="bi bi-filter"></i> Filtrar
            </button>
        </div>
    </form>
</div>

<div class="content-widget">
    <table class="widget-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>OS</th>
                <th>Cliente</th>
                <th>Serviço</th>
                <th>Faturamento</th> <th>Status Atual</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($row = $resultado->fetch_assoc()): 
                    // Define qual status mostrar (o do agendamento tem prioridade se existir)
                    $status_exibicao = $row['status_agendamento'] ? $row['status_agendamento'] : $row['status_venda'];
                    
                    // Cor da Badge
                    $badgeClass = 'badge-pendente';
                    if (strpos($status_exibicao, 'Aprovado') !== false) $badgeClass = 'badge-aprovado';
                    if (strpos($status_exibicao, 'Agendado') !== false || strpos($status_exibicao, 'Confirmado') !== false) $badgeClass = 'badge-agendado';
                    if (strpos($status_exibicao, 'Concluído') !== false) $badgeClass = 'badge-cobranca'; // Amarelo/Laranja
                    if (strpos($status_exibicao, 'Finalizado') !== false) $badgeClass = 'badge-finalizado'; // Verde forte
                    if (strpos($status_exibicao, 'Rejeitado') !== false) $badgeClass = 'badge-rejeitado';
                ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($row['data_venda'])); ?></td>
                        <td><?php echo htmlspecialchars($row['numero_os']); ?></td>
                        <td><?php echo htmlspecialchars($row['cliente_nome']); ?></td>
                        <td><?php echo htmlspecialchars($row['servico_nome']); ?></td>
                        <td>
                            <small style="display:block; color:var(--cor-texto-secundario)"><?php echo $row['condicao_pagamento']; ?></small>
                            R$ <?php echo number_format($row['valor_final'], 2, ',', '.'); ?>
                        </td>
                        <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $status_exibicao; ?></span></td>
                        <td>
                            <button class="btn-details" onclick='abrirModalMonitor(<?php echo json_encode($row); ?>)'>
                                <i class="bi bi-eye-fill"></i> Detalhes
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding: 20px;">Nenhum processo encontrado. Verifique as datas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="modalMonitor" class="modal-overlay">
    <div class="modal-content" style="max-width: 750px;">
        <div class="modal-header">
            <h3 id="monitorTitulo">Detalhes do Processo</h3>
            <button class="close-modal" onclick="fecharModalMonitor()">&times;</button>
        </div>

        <div class="detalhes-grid">
            <div class="detalhe-item"><label>Cliente</label><span id="monitorCliente"></span></div>
            <div class="detalhe-item"><label>Serviço</label><span id="monitorServico"></span></div>
            <div class="detalhe-item"><label>Nº OS</label><span id="monitorOS"></span></div>
            <div class="detalhe-item"><label>Data Venda</label><span id="monitorData"></span></div>
        </div>

        <div class="form-separator"></div>
        <div class="modal-card financeiro">
                <h4 style="color: var(--cor-texto-secundario); font-size: 1rem; margin-bottom: 10px;">
            <i class="bi bi-currency-dollar"></i> Faturamento e Pagamento
        </h4>
        
        <div id="monitorContainerFinanceiro"></div>

        </div>

        

        <div class="form-separator"></div>

        <h4 style="color: var(--cor-texto-secundario); font-size: 1rem; margin-bottom: 10px;">
            <i class="bi bi-activity"></i> Status e Execução
        </h4>
        <div class="detalhes-grid">
             <div class="detalhe-item">
                <label>Status Atual</label>
                <strong id="monitorStatus" style="font-size: 1.1rem;"></strong>
            </div>
            <div class="detalhe-item">
                <label>Data Agendada</label>
                <span id="monitorAgendamento"></span>
            </div>
            <div class="detalhe-item" style="grid-column: span 2;">
                <label>Observações do Agendamento</label>
                <p id="monitorObs" style="background: var(--cor-fundo); padding: 10px; border-radius: 6px; color: var(--cor-texto-secundario); margin: 5px 0 0 0;"></p>
            </div>
        </div>
        
        <div id="monitorFotosArea"></div>

        <div class="form-separator"></div>

        <div class="modal-footer-buttons" style="justify-content: flex-end;">
            <button type="button" class="btn-back" onclick="fecharModalMonitor()">Fechar</button>
        </div>
    </div>
</div>

<script src="assets/js/monitor.js"></script>