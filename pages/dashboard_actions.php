<?php
// pages/dashboard_actions.php
// Responde a requisições AJAX do dashboard

include('../includes/db_connect.php');
session_start();
header('Content-Type: application/json');

// Garante que o usuário é um Gestor (ou Admin)
$nivel_usuario = $_SESSION['usuario_nivel'] ?? '';
$gestor_id = $_SESSION['usuario_id'] ?? 0;

if ($nivel_usuario != 'Gestor' && $nivel_usuario != 'Administrador') {
    echo json_encode(['status' => 'erro', 'msg' => 'Acesso negado.']);
    exit;
}

$acao = $_POST['acao'] ?? '';

// --- Ação: Carregar a nota do gestor ---
if ($acao == 'carregar_nota') {
    $stmt = $conexao->prepare("SELECT conteudo FROM gestor_notas WHERE gestor_id = ?");
    $stmt->bind_param("i", $gestor_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $nota = $resultado->fetch_assoc();
        echo json_encode(['status' => 'sucesso', 'conteudo' => $nota['conteudo']]);
    } else {
        // Nenhuma nota encontrada, retorna vazio
        echo json_encode(['status' => 'sucesso', 'conteudo' => '']);
    }
    exit;
}

// --- Ação: Salvar a nota do gestor ---
if ($acao == 'salvar_nota') {
    $conteudo = $_POST['conteudo'] ?? '';

    // Este comando (INSERT ... ON DUPLICATE KEY UPDATE) é muito eficiente:
    // 1. Tenta INSERIR uma nova nota.
    // 2. Se falhar porque o 'gestor_id' (que é UNIQUE) já existe,
    // 3. Ele executa o UPDATE no 'conteudo' da linha existente.

    $sql = "
        INSERT INTO gestor_notas (gestor_id, conteudo) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE conteudo = ?
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("iss", $gestor_id, $conteudo, $conteudo);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'sucesso', 'msg' => 'Nota salva.']);
    } else {
        echo json_encode(['status' => 'erro', 'msg' => 'Falha ao salvar nota.']);
    }
    exit;
}

if ($acao == 'buscar_agendamentos_gestor') {
    $filtro = $_POST['filtro'] ?? 'proximos'; // 'proximos', 'hoje', 'semana', 'mes', 'data'

    // SQL Base
    $sql_base = "
        SELECT 
            a.data_agendada, a.status, v.numero_os, m.nome as cliente_nome
        FROM agendamentos a
        JOIN vendas v ON a.venda_id = v.id
        JOIN mantenedores m ON v.mantenedor_id = m.id
        WHERE 
            a.gestor_id = ? 
            AND a.status IN ('Pendente de Contato', 'Confirmado')
    ";

    // Adiciona o filtro de data
    switch ($filtro) {
        case 'hoje':
            $sql_final = $sql_base . " AND DATE(a.data_agendada) = CURDATE() ORDER BY a.data_agendada ASC";
            $stmt = $conexao->prepare($sql_final);
            $stmt->bind_param("i", $gestor_id);
            break;
        case 'amanha':
            $sql_final = $sql_base . " AND DATE(a.data_agendada) = CURDATE() + INTERVAL 1 DAY ORDER BY a.data_agendada ASC";
            $stmt = $conexao->prepare($sql_final);
            $stmt->bind_param("i", $gestor_id);
            break;
        case 'semana':
            // YEARWEEK com modo 1 (Semana começa na Segunda)
            $sql_final = $sql_base . " AND YEARWEEK(a.data_agendada, 1) = YEARWEEK(CURDATE(), 1) ORDER BY a.data_agendada ASC";
            $stmt = $conexao->prepare($sql_final);
            $stmt->bind_param("i", $gestor_id);
            break;
        case 'mes':
            $sql_final = $sql_base . " AND MONTH(a.data_agendada) = MONTH(CURDATE()) AND YEAR(a.data_agendada) = YEAR(CURDATE()) ORDER BY a.data_agendada ASC";
            $stmt = $conexao->prepare($sql_final);
            $stmt->bind_param("i", $gestor_id);
            break;
        case 'data':
            $data_selecionada = $_POST['data_selecionada'] ?? '';
            $sql_final = $sql_base . " AND DATE(a.data_agendada) = ? ORDER BY a.data_agendada ASC";
            $stmt = $conexao->prepare($sql_final);
            $stmt->bind_param("is", $gestor_id, $data_selecionada);
            break;
        case 'proximos':
        default:
            $sql_final = $sql_base . " AND a.data_agendada >= CURDATE() ORDER BY a.data_agendada ASC LIMIT 5";
            $stmt = $conexao->prepare($sql_final);
            $stmt->bind_param("i", $gestor_id);
            break;
    }

    $stmt->execute();
    $resultado = $stmt->get_result();
    $agendamentos = [];
    while ($row = $resultado->fetch_assoc()) {
        $agendamentos[] = $row;
    }
    $stmt->close();

    echo json_encode(['status' => 'sucesso', 'agendamentos' => $agendamentos]);
    exit;
}
