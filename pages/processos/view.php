<?php
// pages/processos/view.php

// Segurança: Apenas Admin
if ($_SESSION['usuario_nivel'] != 'Administrador') {
    echo '<div class="alert alert-danger">Acesso restrito a Administradores.</div>';
    exit;
}

$busca = $_GET['busca'] ?? '';

// Query Poderosa: Junta Vendas, Agendamentos, Mantenedores e Serviços
$sql = "SELECT 
            v.id as venda_id, 
            v.numero_os, 
            v.status as status_venda, 
            v.criado_em,
            m.nome as cliente_nome,
            ps.nome as servico_nome,
            a.id as agendamento_id,
            a.status as status_agendamento,
            a.data_agendada
        FROM vendas v
        JOIN mantenedores m ON v.mantenedor_id = m.id
        JOIN produtos_servicos ps ON v.produto_servico_id = ps.id
        LEFT JOIN agendamentos a ON a.venda_id = v.id
        WHERE (m.nome LIKE '%$busca%' OR v.numero_os LIKE '%$busca%')
        ORDER BY v.id DESC";

$resultado = $conexao->query($sql);
?>

<div class="page-header" style="margin-bottom: 20px;">
    <h2>Gestão de Processos (Vendas & Agendamentos)</h2>
</div>

<div class="content-widget" style="padding: 15px;">
    <table class="widget-table">
        <thead>
            <tr>
                <th>OS / Data</th>
                <th>Cliente (Mantenedor)</th>
                <th>Serviço</th>
                <th>Status Venda</th>
                <th>Status Agendamento</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($row = $resultado->fetch_assoc()): 
                    
                    // --- LÓGICA DE BADGES (VENDA) ---
                    $statusV = $row['status_venda'];
                    $badgeV = 'badge-pendente'; // Padrão

                    switch ($statusV) {
                        case 'Aguardando Cobrança':       $badgeV = 'badge-cobranca'; break;
                        case 'Aprovado para Agendamento': $badgeV = 'badge-aprovado'; break; // Azul Claro
                        case 'Agendado':                  $badgeV = 'badge-agendado'; break; // Azul
                        case 'Concluído':                 $badgeV = 'badge-concluido'; break; // Verde
                    }

                    // --- LÓGICA DE BADGES (AGENDAMENTO) ---
                    $statusA = $row['status_agendamento'] ?? 'Não Agendado';
                    $badgeA = 'badge-pendente'; // Padrão

                    if (!$row['agendamento_id']) {
                        $badgeA = 'badge-rejeitado'; // Vermelho (Sem agendamento)
                    } else {
                        switch ($statusA) {
                            case 'Pendente de Contato':           $badgeA = 'badge-pendente'; break;
                            case 'Confirmado':                    $badgeA = 'badge-agendado'; break; // Azul
                            case 'Concluído - Aguardando Validação': $badgeA = 'badge-cobranca'; break; // Amarelo (Atenção)
                            case 'Finalizado e Enviado':          $badgeA = 'badge-finalizado'; break; // Verde Sólido
                            case 'Finalizado Internamente':       $badgeA = 'badge-concluido'; break; // Verde
                            case 'Rejeitado':                     $badgeA = 'badge-rejeitado'; break; // Vermelho
                            case 'Reagendamento Solicitado':      $badgeA = 'badge-cobranca'; break; // Amarelo
                        }
                    }
                ?>
                    <tr>
                        <td>
                            <strong>#<?php echo $row['numero_os'] ?: 'N/A'; ?></strong><br>
                            <small style="color:var(--cor-texto-secundario);"><?php echo date('d/m/Y', strtotime($row['criado_em'])); ?></small>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <?php echo htmlspecialchars($row['cliente_nome']); ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($row['servico_nome']); ?></td>
                        
                        <td><span class="badge <?php echo $badgeV; ?>"><?php echo $statusV; ?></span></td>
                        <td><span class="badge <?php echo $badgeA; ?>"><?php echo $statusA; ?></span></td>
                        
                        <td class="acoes-buttons" style="justify-content: flex-end;">
                            <button class="btn-details" onclick="editarProcesso(<?php echo $row['venda_id']; ?>)">
                                <i class="bi bi-pencil-square"></i> Editar
                            </button>
                            <button class="btn-delete" onclick="excluirProcesso(<?php echo $row['venda_id']; ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:30px; color:var(--cor-texto-secundario);">Nenhum processo encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

