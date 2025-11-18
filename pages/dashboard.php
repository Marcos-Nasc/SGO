<?php
// -- 1. CONFIGURAÇÃO INICIAL E CONEXÃO --
// A sessão e o DB já vêm do index.php, mas garantimos as variáveis principais
$nivel_usuario = $_SESSION['usuario_nivel'];
$id_usuario    = $_SESSION['usuario_id'];
$nome_usuario  = $_SESSION['usuario_nome'];

// Variáveis de data para filtros (Mês Atual)
$mes_atual = date('m');
$ano_atual = date('Y');

// Array para armazenar dias com eventos no calendário (será preenchido pelas queries)
$dias_com_eventos = [];

// -- 2. LÓGICA DE DADOS POR NÍVEL --

// Inicializa contadores com 0 para evitar erros
$kpi1 = $kpi2 = $kpi3 = $kpi4 = $kpi5 = 0;
$lista_dados = []; // Para tabelas

try {
    // ==========================================================================
    // CASO 1: ADMINISTRADOR
    // ==========================================================================
    if ($nivel_usuario == 'Administrador') {
        // KPI 1: Vendas Aguardando Cobrança
        $sql = "SELECT COUNT(*) as total FROM vendas WHERE status = 'Aguardando Cobrança'";
        $kpi1 = $conexao->query($sql)->fetch_assoc()['total'];

        // KPI 2: Vendas Aguardando Agendamento (Status 'Aprovado para Agendamento')
        $sql = "SELECT COUNT(*) as total FROM vendas WHERE status = 'Aprovado para Agendamento'";
        $kpi2 = $conexao->query($sql)->fetch_assoc()['total'];

        // KPI 3: Aguardando Confirmação SC (Agendamentos 'Pendente de Contato')
        $sql = "SELECT COUNT(*) as total FROM agendamentos WHERE status = 'Pendente de Contato'";
        $kpi3 = $conexao->query($sql)->fetch_assoc()['total'];

        // KPI 4: Aguardando Execução (Agendamentos 'Confirmado' - ainda não feitos)
        $sql = "SELECT COUNT(*) as total FROM agendamentos WHERE status = 'Confirmado' AND data_agendada >= CURDATE()";
        $kpi4 = $conexao->query($sql)->fetch_assoc()['total'];

        // KPI 5: Aguardando Validação SC (Agendamentos 'Concluído - Aguardando Validação')
        $sql = "SELECT COUNT(*) as total FROM agendamentos WHERE status = 'Concluído - Aguardando Validação'";
        $kpi5 = $conexao->query($sql)->fetch_assoc()['total'];

        // CALENDÁRIO: Pega TODOS os agendamentos do mês
        $sql_cal = "SELECT data_agendada FROM agendamentos WHERE MONTH(data_agendada) = '$mes_atual' AND YEAR(data_agendada) = '$ano_atual'";
        $res_cal = $conexao->query($sql_cal);
        while ($row = $res_cal->fetch_assoc()) {
            // Guarda apenas o dia (ex: '11')
            $dias_com_eventos[] = date('j', strtotime($row['data_agendada']));
        }
    }

    // ==========================================================================
    // CASO 2: VENDEDOR
    // ==========================================================================
    elseif ($nivel_usuario == 'Vendedor') {
        // KPI 1: Minhas Vendas (Mês)
        $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM vendas WHERE vendedor_id = ? AND MONTH(data_venda) = ? AND YEAR(data_venda) = ?");
        $stmt->bind_param("iss", $id_usuario, $mes_atual, $ano_atual);
        $stmt->execute();
        $kpi1 = $stmt->get_result()->fetch_assoc()['total'];

        // KPI 2: Valor Vendido (Mês)
        $stmt = $conexao->prepare("SELECT SUM(valor_final) as total FROM vendas WHERE vendedor_id = ? AND MONTH(data_venda) = ? AND YEAR(data_venda) = ?");
        $stmt->bind_param("iss", $id_usuario, $mes_atual, $ano_atual);
        $stmt->execute();
        $valor = $stmt->get_result()->fetch_assoc()['total'];
        $kpi2 = $valor ? "R$ " . number_format($valor, 2, ',', '.') : "R$ 0,00";

        // KPI 3: Aguardando Cobrança (Minhas)
        $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM vendas WHERE vendedor_id = ? AND status = 'Aguardando Cobrança'");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $kpi3 = $stmt->get_result()->fetch_assoc()['total'];

        // KPI 4: Aprovadas p/ Agendar (Minhas)
        $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM vendas WHERE vendedor_id = ? AND status = 'Aprovado para Agendamento'");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $kpi4 = $stmt->get_result()->fetch_assoc()['total'];

        // TABELA: Últimas 5 Vendas (Join com Mantenedores para pegar o nome do cliente)
        $query_lista = "SELECT v.numero_os, m.nome as cliente, v.valor_final, v.status 
                        FROM vendas v 
                        JOIN mantenedores m ON v.mantenedor_id = m.id 
                        WHERE v.vendedor_id = ? 
                        ORDER BY v.data_venda DESC LIMIT 5";
        $stmt = $conexao->prepare($query_lista);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $lista_dados = $stmt->get_result();
    }

    // ==========================================================================
    // CASO 3: GESTOR (Operacional)
    // ==========================================================================
    elseif ($nivel_usuario == 'Gestor') {
        $gestor_id = $_SESSION['usuario_id'];
        $kpi1 = $kpi2 = $kpi3 = 0; // Inicializa KPIs

        // KPI 1: Aguardando Agendamento (Essa consulta já estava correta)
        $sql_kpi1 = "SELECT COUNT(*) as total FROM vendas WHERE status = 'Aprovado para Agendamento'";
        $kpi1_res = $conexao->query($sql_kpi1);
        if ($kpi1_res) $kpi1 = $kpi1_res->fetch_assoc()['total'];

        // KPI 2: Serviços a Executar Hoje (Sintaxe corrigida)
        $sql_kpi2 = "SELECT COUNT(*) FROM agendamentos WHERE gestor_id = ? AND status = 'Confirmado' AND DATE(data_agendada) = CURDATE()";
        $stmt_kpi2 = $conexao->prepare($sql_kpi2);
        $stmt_kpi2->bind_param("i", $gestor_id);
        $stmt_kpi2->execute();
        $stmt_kpi2->bind_result($kpi2_total); // Associa o resultado a uma variável
        $stmt_kpi2->fetch(); // Busca o resultado
        $kpi2 = $kpi2_total;
        $stmt_kpi2->close();

        // KPI 3: Serviços Rejeitados (Meus) (Sintaxe corrigida)
        $sql_kpi3 = "SELECT COUNT(*) FROM agendamentos WHERE gestor_id = ? AND status = 'Rejeitado'";
        $stmt_kpi3 = $conexao->prepare($sql_kpi3);
        $stmt_kpi3->bind_param("i", $gestor_id);
        $stmt_kpi3->execute();
        $stmt_kpi3->bind_result($kpi3_total);
        $stmt_kpi3->fetch();
        $kpi3 = $kpi3_total;
        $stmt_kpi3->close();

        // CALENDÁRIO: Pega agendamentos deste Gestor (Sintaxe corrigida)
        $dias_com_eventos = [];
        $sql_cal = "SELECT data_agendada FROM agendamentos WHERE gestor_id = ? AND MONTH(data_agendada) = ? AND YEAR(data_agendada) = ?";
        $stmt_cal = $conexao->prepare($sql_cal);
        $stmt_cal->bind_param("iss", $gestor_id, $mes_atual, $ano_atual);
        $stmt_cal->execute();
        $stmt_cal->bind_result($data_evento);
        while ($stmt_cal->fetch()) {
            $dias_com_eventos[] = date('j', strtotime($data_evento));
        }
        $stmt_cal->close();

        // NOVA AGENDA: Busca 5 próximos agendamentos (Sintaxe corrigida)
        $sql_agenda = "
        SELECT 
            a.data_agendada, 
            a.status, 
            v.numero_os, 
            m.nome as cliente_nome
        FROM agendamentos a
        JOIN vendas v ON a.venda_id = v.id
        JOIN mantenedores m ON v.mantenedor_id = m.id
        WHERE 
            a.gestor_id = ? AND
            a.status IN ('Pendente de Contato', 'Confirmado') AND
            a.data_agendada >= CURDATE() -- (Adicionado: Apenas futuros)
        ORDER BY a.data_agendada ASC
        LIMIT 5";
        $stmt_agenda = $conexao->prepare($sql_agenda);
        $stmt_agenda->bind_param("i", $gestor_id);
        $stmt_agenda->execute();
        $lista_agenda_resultado = $stmt_agenda->get_result(); // get_result() é necessário aqui para o loop
    }

    // ==========================================================================
    // CASO 4: SETOR DE COBRANÇA
    // ==========================================================================
    elseif ($nivel_usuario == 'Setor de Cobrança') {
        // KPI 1: Cobranças Pendentes (Total)
        $sql = "SELECT COUNT(*) as total FROM vendas WHERE status = 'Aguardando Cobrança'";
        $kpi1 = $conexao->query($sql)->fetch_assoc()['total'];

        // KPI 2: Vendas Aprovadas Hoje (Que você aprovou hoje)
        $sql_kpi2 = "SELECT COUNT(*) as total FROM vendas WHERE status = 'Aprovado para Agendamento' AND DATE(atualizado_em) = CURDATE()";
        $kpi2 = $conexao->query($sql_kpi2)->fetch_assoc()['total'];

        // KPI 3: VENCIMENTOS DE HOJE (O card vermelho que já fizemos)
        $sql_kpi3 = "SELECT COUNT(*) as total FROM vendas WHERE status = 'Aguardando Cobrança' AND data_previsao_cobranca = CURDATE()";
        $kpi3 = $conexao->query($sql_kpi3)->fetch_assoc()['total'];

        // --- NOVA LÓGICA DE TABELAS ---

        // TABELA 1: Para Aprovar Hoje
        $query_hoje = "
        SELECT v.data_venda, v.numero_os, m.nome as cliente, v.valor_final 
        FROM vendas v 
        JOIN mantenedores m ON v.mantenedor_id = m.id 
        WHERE v.status = 'Aguardando Cobrança' AND v.data_previsao_cobranca = CURDATE()
        ORDER BY v.data_venda ASC";
        $lista_hoje = $conexao->query($query_hoje);

        // TABELA 2: Próximos Vencimentos (TOP 5)
        $query_geral = "
        SELECT v.data_venda, v.numero_os, m.nome as cliente, v.valor_final, v.data_previsao_cobranca
        FROM vendas v 
        JOIN mantenedores m ON v.mantenedor_id = m.id 
        WHERE v.status = 'Aguardando Cobrança' AND v.data_previsao_cobranca > CURDATE()
        ORDER BY v.data_previsao_cobranca ASC 
        LIMIT 5";
        $lista_geral = $conexao->query($query_geral);
    }

    // ==========================================================================
    // CASO 5: SUCESSO DO CLIENTE
    // ==========================================================================
    elseif ($nivel_usuario == 'Sucesso do Cliente') {
        // KPI 1: Confirmações Pendentes (Agendamentos criados mas não confirmados)
        $sql = "SELECT COUNT(*) as total FROM agendamentos WHERE status = 'Pendente de Contato'";
        $kpi1 = $conexao->query($sql)->fetch_assoc()['total'];

        // KPI 2: Validações Pendentes (Fotos enviadas)
        $sql = "SELECT COUNT(*) as total FROM fotos_servico WHERE status_validacao = 'Pendente'";
        $kpi2 = $conexao->query($sql)->fetch_assoc()['total'];

        // Listas (Para "Minha Agenda de Tarefas")
        // 1. Agendamentos para confirmar
        $sql_conf = "SELECT a.data_agendada, m.nome as cliente 
                     FROM agendamentos a
                     JOIN vendas v ON a.venda_id = v.id
                     JOIN mantenedores m ON v.mantenedor_id = m.id
                     WHERE a.status = 'Pendente de Contato' LIMIT 5";
        $lista_confirmar = $conexao->query($sql_conf);

        // 2. Serviços para validar
        $sql_valid = "SELECT v.numero_os 
                      FROM fotos_servico f
                      JOIN agendamentos a ON f.agendamento_id = a.id
                      JOIN vendas v ON a.venda_id = v.id
                      WHERE f.status_validacao = 'Pendente' GROUP BY v.numero_os LIMIT 5";
        $lista_validar = $conexao->query($sql_valid);
    }
} catch (Exception $e) {
    echo "<div class='error-message'>Erro ao carregar dados: " . $e->getMessage() . "</div>";
}
?>

<?php if ($nivel_usuario == 'Administrador'): ?>
    <div class="dashboard-grid kpi-grid-5">
        <div class="kpi-card" style="--kpi-color: #ffc107;">
            <span>Vendas Aguardando Cobrança</span>
            <strong><?php echo $kpi1; ?></strong>
        </div>
        <div class="kpi-card" style="--kpi-color: #ffc107;">
            <span>Vendas Aguardando Agendamento</span>
            <strong><?php echo $kpi2; ?></strong>
        </div>
        <div class="kpi-card" style="--kpi-color: #adb5bd;">
            <span>Aguardando Confirmação SC</span>
            <strong><?php echo $kpi3; ?></strong>
        </div>
        <div class="kpi-card" style="--kpi-color: #0d6efd;">
            <span>Aguardando Execução</span>
            <strong><?php echo $kpi4; ?></strong>
        </div>
        <div class="kpi-card" style="--kpi-color: #ff8f00;">
            <span>Aguardando Validação SC</span>
            <strong><?php echo $kpi5; ?></strong>
        </div>
    </div>

    <div class="dashboard-grid main-grid">
        <div class="grid-col-8">
            <div class="content-widget">
                <h3>Funil de Status de Processos</h3>
                <div class="placeholder-chart">(Gráfico em desenvolvimento)</div>
            </div>
        </div>
        <div class="grid-col-4">
            <?php include('widgets/calendar_widget.php'); ?>
        </div>
    </div>

<?php elseif ($nivel_usuario == 'Gestor'): ?>
    <div class="dashboard-grid kpi-grid-3">
        <div class="kpi-card" style="--kpi-color: #0d6efd;">
            <span>Aguardando Agendamento</span>
            <strong><?php echo $kpi1; ?></strong>
        </div>
        <div class="kpi-card" style="--kpi-color: #198754;">
            <span>Serviços a Executar (Hoje)</span>
            <strong><?php echo $kpi2; ?></strong>
        </div>
        <div class="kpi-card" style="--kpi-color: #dc3545;">
            <span>Serviços Rejeitados</span>
            <strong><?php echo $kpi3; ?></strong>
        </div>
    </div>

    <div class="dashboard-grid main-grid">
        <div class="grid-col-7">
            <div class="content-widget">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h3><i class="bi bi-journal-text"></i> Próximos Agendamentos</h3>
                    <div class="filter-group">
                        <button id="btn-agenda-proximos" class="btn-filter active" onclick="carregarAgenda('proximos', this)">Próximos</button>
                        <button id="btn-agenda-hoje" class="btn-filter" onclick="carregarAgenda('hoje', this)">Hoje</button>
                        <button id="btn-agenda-amanha" class="btn-filter" onclick="carregarAgenda('amanha', this)">Amanhã</button>
                        <button id="btn-agenda-semana" class="btn-filter" onclick="carregarAgenda('semana', this)">Semana</button>
                        <button id="btn-agenda-mes" class="btn-filter" onclick="carregarAgenda('mes', this)">Mês</button>
                    </div>
                </div>
                <div class="content-widget">

                    <ul class="agenda-list" id="lista-agenda-gestor">
                        <?php if ($lista_agenda_resultado && $lista_agenda_resultado->num_rows > 0): ?>
                            <?php while ($item = $lista_agenda_resultado->fetch_assoc()):
                                $data_ag = new DateTime($item['data_agendada']);
                            ?>
                                <li class="agenda-item">
                                    <div class="agenda-data">
                                        <span class="dia"><?php echo $data_ag->format('d'); ?></span>
                                        <span class="mes"><?php echo strtoupper($data_ag->format('M')); ?></span>
                                    </div>
                                    <div class="agenda-info">
                                        <span class="titulo">OS <?php echo htmlspecialchars($item['numero_os']); ?> - <?php echo htmlspecialchars($item['cliente_nome']); ?></span>
                                        <span class="subtitulo"><?php echo htmlspecialchars($item['status']); ?> às <?php echo $data_ag->format('H:i'); ?></span>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li class="agenda-item-vazio">
                                <span>Nenhum agendamento pendente.</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <ul class="agenda-list">
                    <?php if ($lista_agenda_resultado && $lista_agenda_resultado->num_rows > 0): ?>
                        <?php while ($item = $lista_agenda_resultado->fetch_assoc()):
                            $data_ag = new DateTime($item['data_agendada']);
                        ?>
                            <li class="agenda-item">
                                <div class="agenda-data">
                                    <span class="dia"><?php echo $data_ag->format('d'); ?></span>
                                    <span class="mes"><?php echo strtoupper($data_ag->format('M')); ?></span>
                                </div>
                                <div class="agenda-info">
                                    <span class="titulo">OS <?php echo htmlspecialchars($item['numero_os']); ?> - <?php echo htmlspecialchars($item['cliente_nome']); ?></span>
                                    <span class="subtitulo"><?php echo htmlspecialchars($item['status']); ?> às <?php echo $data_ag->format('H:i'); ?></span>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="agenda-item-vazio">
                            <span>Nenhum agendamento pendente.</span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="content-widget">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3><i class="bi bi-pencil-square"></i> Bloco de Anotações</h3>
                    <small id="notasFeedback" style="transition: color 0.3s;"></small>
                </div>
                <textarea id="blocoNotasGestor" class="notes-widget" placeholder="Suas anotações rápidas..."></textarea>
                <small class="text-muted" style="margin-top: 5px; display:block;">
                    Suas notas são salvas automaticamente.
                </small>
            </div>
        </div>

        <div class="grid-col-5">

            <div class="content-widget">
                <?php include('widgets/calendar_widget.php'); ?>
            </div>

            <div class="content-widget" id="agenda-diaria-calendario" style="display: none;">
                <h3 id="agenda-diaria-titulo">Agendamentos para...</h3>
                <ul class="agenda-list" id="agenda-diaria-lista">
                </ul>
            </div>
        </div>
    </div>

<?php elseif ($nivel_usuario == 'Vendedor'): ?>
    <div class="dashboard-grid kpi-grid-4">
        <div class="kpi-card" style="--kpi-color: #0d6efd;">
            <span>Minhas Vendas (Mês)</span>
            <strong><?php echo $kpi1; ?></strong>
        </div>
        <div class="kpi-card" style="--kpi-color: #198754;">
            <span>Valor Vendido (Mês)</span>
            <strong><?php echo $kpi2; ?></strong>
        </div>
        <div class="kpi-card" style="--kpi-color: #ffc107;">
            <span>Aguardando Cobrança</span>
            <strong><?php echo $kpi3; ?></strong>
        </div>
        <div class="kpi-card" style="--kpi-color: #7e00ff;">
            <span>Aprovadas p/ Agendar</span>
            <strong><?php echo $kpi4; ?></strong>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="content-widget">
            <h3>Minhas Últimas 5 Vendas</h3>
            <table class="widget-table">
                <thead>
                    <tr>
                        <th>OS</th>
                        <th>Cliente</th>
                        <th>Valor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($lista_dados && $lista_dados->num_rows > 0): ?>
                        <?php while ($row = $lista_dados->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['numero_os'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                                <td>R$ <?php echo number_format($row['valor_final'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center;">Nenhuma venda recente.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($nivel_usuario == 'Setor de Cobrança'): ?>
    <div class="dashboard-grid kpi-grid-3">
        <div class="kpi-card" style="--kpi-color: #dc3545;">
            <span>Total Pendente</span>
            <strong><?php echo $kpi1; ?></strong>
        </div>

        <div class="kpi-card" style="--kpi-color: #ffc107;"> <span>Vencimentos Hoje</span>
            <strong><?php echo $kpi3; ?></strong>
        </div>

        <div class="kpi-card" style="--kpi-color: #198754;">
            <span>Aprovadas por mim (Hoje)</span>
            <strong><?php echo $kpi2; ?></strong>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="content-widget">
            <h3>Possíveis Agendamentos Pra Aprovados (<?php echo $lista_hoje->num_rows; ?>)</h3>
            <table class="widget-table">
                <thead>
                    <tr>
                        <th>Data Venda</th>
                        <th>OS</th>
                        <th>Cliente</th>
                        <th class="text-right">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($lista_hoje && $lista_hoje->num_rows > 0): ?>
                        <?php while ($row = $lista_hoje->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['data_venda'])); ?></td>
                                <td><?php echo htmlspecialchars($row['numero_os'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                                <td class="text-right">R$ <?php echo number_format($row['valor_final'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 10px;">Nenhuma cobrança com vencimento para hoje.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="content-widget">
            <h3>Próximos Vencimentos (Aguardando)</h3>
            <table class="widget-table">
                <thead>
                    <tr>
                        <th>Vencimento</th>
                        <th>OS</th>
                        <th>Cliente</th>
                        <th class="text-right">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($lista_geral && $lista_geral->num_rows > 0): ?>
                        <?php while ($row = $lista_geral->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['data_previsao_cobranca'])); ?></td>
                                <td><?php echo htmlspecialchars($row['numero_os'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                                <td class="text-right">R$ <?php echo number_format($row['valor_final'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 10px;">Nenhuma outra cobrança pendente.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($nivel_usuario == 'Sucesso do Cliente'): ?>
    <div class="dashboard-grid kpi-grid-2">
        <div class="kpi-card" style="--kpi-color: #0d6efd;">
            <span>Confirmações Pendentes</span>
            <strong><?php echo $kpi1; ?></strong>
        </div>
        <div class="kpi-card" style="--kpi-color: #ffc107;">
            <span>Validações Pendentes</span>
            <strong><?php echo $kpi2; ?></strong>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="content-widget">
            <h3>Minha Agenda de Tarefas</h3>
            <div class="task-grid">
                <div class="task-column">
                    <h4>1. Agendamentos para Confirmar</h4>
                    <?php if ($lista_confirmar && $lista_confirmar->num_rows > 0): ?>
                        <?php while ($row = $lista_confirmar->fetch_assoc()): ?>
                            <div class="task-item">
                                <span><?php echo htmlspecialchars($row['cliente']); ?> - <?php echo date('d/m', strtotime($row['data_agendada'])); ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted">Nada pendente.</p>
                    <?php endif; ?>
                </div>
                <div class="task-column">
                    <h4>2. Serviços para Validar Fotos</h4>
                    <?php if ($lista_validar && $lista_validar->num_rows > 0): ?>
                        <?php while ($row = $lista_validar->fetch_assoc()): ?>
                            <div class="task-item">
                                <span>OS: <?php echo htmlspecialchars($row['numero_os']); ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted">Nada pendente.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="content-widget">
        <h2>Bem-vindo ao SGO</h2>
        <p>Seu perfil não possui um dashboard configurado.</p>
    </div>
<?php endif; ?>