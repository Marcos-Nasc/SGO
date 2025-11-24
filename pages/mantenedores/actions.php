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

if ($acao == 'excluir_mantenedor') {
    $id = $_POST['id'];
    
    $stmt = $conexao->prepare("DELETE FROM mantenedores WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'sucesso', 'msg' => 'Cliente excluído com sucesso.']);
    } else {
        // Erro 1451: Código MySQL para erro de Foreign Key (Chave Estrangeira)
        if ($conexao->errno == 1451) {
            echo json_encode(['status' => 'erro', 'msg' => 'Não é possível excluir: Este cliente possui vendas ou contratos vinculados.']);
        } else {
            echo json_encode(['status' => 'erro', 'msg' => 'Erro no banco de dados ao excluir: ' . $stmt->error]);
        }
    }
    exit;
}

// --- EXCLUIR CONTRATO ---
if ($acao == 'excluir_contrato') {
    $id = $_POST['id'];
    
    $stmt = $conexao->prepare("DELETE FROM contratos WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'sucesso', 'msg' => 'Contrato excluído.']);
    } else {
        echo json_encode(['status' => 'erro', 'msg' => 'Erro ao excluir contrato.']);
    }
    exit;
}

if ($acao == 'buscar_cemiterios') {
    $sql = "SELECT id, nome FROM cemiterios ORDER BY nome ASC";
    $res = $conexao->query($sql);
    $cemiterios = [];
    while ($row = $res->fetch_assoc()) {
        $cemiterios[] = $row;
    }
    echo json_encode(['status' => 'sucesso', 'cemiterios' => $cemiterios]);
    exit;
}

// --- AÇÃO: BUSCAR CONTRATO POR ID (Para Edição) ---
if ($acao == 'buscar_contrato_id') {
    $id = $_POST['id'];
    
    $sql = "SELECT c.*, cem.nome as cemiterio_nome 
            FROM contratos c 
            JOIN cemiterios cem ON c.cemiterio_id = cem.id 
            WHERE c.id = ?";
            
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $contrato = $res->fetch_assoc();
    
    if ($contrato) {
        echo json_encode(['status' => 'sucesso', 'contrato' => $contrato]);
    } else {
        echo json_encode(['status' => 'erro', 'msg' => 'Contrato não encontrado.']);
    }
    exit;
}

// --- AÇÃO: SALVAR EDIÇÃO DE CONTRATO ---
if ($acao == 'editar_contrato') {
    $id = $_POST['id'];
    $cemiterio_id = $_POST['cemiterio_id'];
    $numero = $_POST['numero'];
    $jazigo = $_POST['jazigo'];
    $quadra = $_POST['quadra'];
    $bloco = $_POST['bloco'];
    
    if (empty($cemiterio_id) || empty($numero)) {
        echo json_encode(['status' => 'erro', 'msg' => 'Filial e Número são obrigatórios.']);
        exit;
    }

    $sql = "UPDATE contratos SET cemiterio_id=?, numero=?, jazigo=?, quadra=?, bloco=? WHERE id=?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("issssi", $cemiterio_id, $numero, $jazigo, $quadra, $bloco, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'sucesso', 'msg' => 'Contrato editado com sucesso!']);
    } else {
        echo json_encode(['status' => 'erro', 'msg' => 'Erro ao editar contrato.']);
    }
    exit;
}

?>