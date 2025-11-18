<?php
include('../../includes/db_connect.php');
session_start();
header('Content-Type: application/json');

$acao = $_POST['acao'] ?? '';

// --- SALVAR MANTENEDOR ---
if ($acao == 'salvar_mantenedor') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $status = $_POST['status'];

    if (empty($nome)) {
        echo json_encode(['status' => 'erro', 'msg' => 'Nome é obrigatório.']);
        exit;
    }

    if (empty($id)) {
        $sql = "INSERT INTO mantenedores (nome, email, telefone, status) VALUES (?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ssss", $nome, $email, $telefone, $status);
    } else {
        $sql = "UPDATE mantenedores SET nome=?, email=?, telefone=?, status=? WHERE id=?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ssssi", $nome, $email, $telefone, $status, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'sucesso', 'msg' => 'Salvo com sucesso!']);
    } else {
        echo json_encode(['status' => 'erro', 'msg' => 'Erro ao salvar: ' . $stmt->error]);
    }
    exit;
}

// --- LISTAR CONTRATOS ---
if ($acao == 'listar_contratos') {
    $mantenedor_id = $_POST['mantenedor_id'];
    
    $sql = "SELECT c.*, cem.nome as cemiterio_nome 
            FROM contratos c 
            JOIN cemiterios cem ON c.cemiterio_id = cem.id 
            WHERE c.mantenedor_id = ?";
            
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $mantenedor_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $contratos = [];
    while($row = $res->fetch_assoc()) {
        $contratos[] = $row;
    }
    
    echo json_encode(['status' => 'sucesso', 'contratos' => $contratos]);
    exit;
}
?>