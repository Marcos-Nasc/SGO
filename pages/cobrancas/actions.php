<?php
// actions.php
include('../../includes/db_connect.php');
session_start();
header('Content-Type: application/json');

// Verifica se o usuário é do Setor de Cobrança ou Admin
if ($_SESSION['usuario_nivel'] != 'Setor de Cobrança' && $_SESSION['usuario_nivel'] != 'Administrador') {
    echo json_encode(['status' => 'erro', 'msg' => 'Acesso negado.']);
    exit;
}

$acao = $_POST['acao'] ?? '';

// Ação para aprovar a venda
if ($acao == 'aprovar_cobranca') {
    $venda_id = $_POST['venda_id'] ?? 0;

    if ($venda_id == 0) {
        echo json_encode(['status' => 'erro', 'msg' => 'ID da venda inválido.']);
        exit;
    }

    // Atualiza o status da venda
    $sql = "UPDATE vendas SET status = 'Aprovado para Agendamento' WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $venda_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'sucesso', 'msg' => 'Venda aprovada!']);
    } else {
        echo json_encode(['status' => 'erro', 'msg' => 'Falha ao atualizar o banco de dados.']);
    }
    exit;
}
?>