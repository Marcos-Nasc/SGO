<?php
// pages/agendamentos/actions_confirmar.php
include('../../includes/db_connect.php');
session_start();
header('Content-Type: application/json');

// Permissão: Sucesso do Cliente ou Admin
$nivel_usuario = $_SESSION['usuario_nivel'] ?? '';
if ($nivel_usuario != 'Sucesso do Cliente' && $nivel_usuario != 'Administrador') {
    echo json_encode(['status' => 'erro', 'msg' => 'Acesso negado.']);
    exit;
}

$acao = $_POST['acao'] ?? '';

// --- AÇÃO: CONFIRMAR (Muda status para 'Confirmado') ---
if ($acao == 'confirmar') {
    $id = $_POST['agendamento_id'];
    $obs = $_POST['observacao'];
    
    // Atualiza para 'Confirmado'. Agora aparecerá para o Gestor executar.
    $sql = "UPDATE agendamentos SET status = 'Confirmado', observacoes = ? WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("si", $obs, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'sucesso', 'msg' => 'Agendamento confirmado! O Gestor foi notificado.']);
    } else {
        echo json_encode(['status' => 'erro', 'msg' => 'Erro ao atualizar: ' . $stmt->error]);
    }
    exit;
}

// --- AÇÃO: CANCELAR/REJEITAR ---
if ($acao == 'rejeitar') {
    $id = $_POST['agendamento_id'];
    $obs = $_POST['observacao'];
    
    // MUDANÇA AQUI: Agora usamos 'Reagendamento Solicitado'
    $sql = "UPDATE agendamentos SET status = 'Reagendamento Solicitado', observacoes = ? WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("si", $obs, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'sucesso', 'msg' => 'Solicitação de reagendamento enviada ao Gestor.']);
    } else {
        echo json_encode(['status' => 'erro', 'msg' => 'Erro ao atualizar: ' . $stmt->error]);
    }
    exit;
}
?>