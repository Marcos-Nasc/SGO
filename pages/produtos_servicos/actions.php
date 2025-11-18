<?php
// pages/produtos_servicos/actions.php
include('../../includes/db_connect.php');
session_start();
header('Content-Type: application/json');

// Garante que é um Gestor ou Admin
$nivel_usuario = $_SESSION['usuario_nivel'] ?? '';
$gestor_id = $_SESSION['usuario_id'] ?? 0;

// (Permitimos acesso se for Gestor OU se for uma ação de reagendamento que pode vir de outras telas)
if (!$nivel_usuario) {
    echo json_encode(['status' => 'erro', 'msg' => 'Acesso negado.']);
    exit;
}

$acao = $_POST['acao'] ?? '';

// --- AÇÃO: AGENDAR SERVIÇO (Primeiro Agendamento) ---
if ($acao == 'agendar_servico') {
    $venda_id = $_POST['venda_id'] ?? 0;
    $data_agendada = $_POST['data_agendada'] ?? null;
    $observacoes = $_POST['observacoes'] ?? null;
    $familia_flag = $_POST['familia_comparecer'] ?? 0;

    if ($venda_id == 0 || empty($data_agendada)) {
        echo json_encode(['status' => 'erro', 'msg' => 'Dados inválidos.']);
        exit;
    }

    $novo_status_agendamento = ($familia_flag == 1) ? 'Pendente de Contato' : 'Confirmado';
    $novo_status_venda = 'Agendado';

    $conexao->begin_transaction();
    try {
        $sql_insert = "INSERT INTO agendamentos (venda_id, gestor_id, data_agendada, status, observacoes) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conexao->prepare($sql_insert);
        $stmt_insert->bind_param("iisss", $venda_id, $gestor_id, $data_agendada, $novo_status_agendamento, $observacoes);
        $stmt_insert->execute();

        $sql_update = "UPDATE vendas SET status = ? WHERE id = ?";
        $stmt_update = $conexao->prepare($sql_update);
        $stmt_update->bind_param("si", $novo_status_venda, $venda_id);
        $stmt_update->execute();
        
        $conexao->commit();
        echo json_encode(['status' => 'sucesso', 'msg' => 'Serviço agendado com sucesso!']);
    } catch (Exception $e) {
        $conexao->rollback();
        echo json_encode(['status' => 'erro', 'msg' => 'Falha ao salvar: ' . $e->getMessage()]);
    }
    exit;
}

// --- AÇÃO: REAGENDAR SERVIÇO (Correção de Data) ---
if ($acao == 'reagendar_servico') {
    $agendamento_id = $_POST['agendamento_id'] ?? 0;
    $data_agendada = $_POST['data_agendada'] ?? null;
    $observacoes = $_POST['observacoes'] ?? null;
    $familia_flag = $_POST['familia_comparecer'] ?? 0;

    if ($agendamento_id == 0 || empty($data_agendada)) {
        echo json_encode(['status' => 'erro', 'msg' => 'Data inválida ou ID não fornecido.']);
        exit;
    }

    // Lógica: 
    // Se Família VEM (1) -> Vai para 'Pendente de Contato' (CS confirma a nova data)
    // Se Família NÃO VEM (0) -> Vai direto para 'Confirmado' (Gestor executa na nova data)
    $novo_status = ($familia_flag == 1) ? 'Pendente de Contato' : 'Confirmado';

    $sql = "UPDATE agendamentos SET data_agendada = ?, observacoes = ?, status = ? WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sssi", $data_agendada, $observacoes, $novo_status, $agendamento_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'sucesso', 'msg' => 'Serviço reagendado com sucesso!']);
    } else {
        echo json_encode(['status' => 'erro', 'msg' => 'Erro ao reagendar: ' . $stmt->error]);
    }
    exit;
}

// Se nenhuma ação
echo json_encode(['status' => 'erro', 'msg' => 'Nenhuma ação válida.']);
?>