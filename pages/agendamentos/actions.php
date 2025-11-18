<?php
// pages/agendamentos/actions.php

// 1. Configurações de Debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Inclusão do Banco de Dados
// Verifica o caminho para evitar erros fatais silenciosos
$db_path = '../../includes/db_connect.php';
if (file_exists($db_path)) {
    include($db_path);
} else {
    // Se não achar o arquivo, para tudo e avisa
    header('Content-Type: application/json');
    echo json_encode(['status' => 'erro', 'msg' => 'Erro Crítico: Arquivo de conexão (db_connect.php) não encontrado.']);
    exit;
}

// 3. Iniciar Sessão
session_start();
header('Content-Type: application/json');

// 4. Verifica se a conexão existe
if (!isset($conexao)) {
    echo json_encode(['status' => 'erro', 'msg' => 'Erro Crítico: Falha ao conectar ao banco de dados (Variável $conexao inexistente).']);
    exit;
}

// --- INÍCIO DA LÓGICA ---

// Garante que é um Gestor ou Admin
$nivel_usuario = $_SESSION['usuario_nivel'] ?? '';
$gestor_id = $_SESSION['usuario_id'] ?? 0;
if ($nivel_usuario != 'Gestor' && $nivel_usuario != 'Administrador') {
    echo json_encode(['status' => 'erro', 'msg' => 'Acesso negado.']);
    exit;
}

$acao = $_REQUEST['acao'] ?? ''; // Pode ser GET ou POST

// --- Ação 1: Buscar fotos existentes ---
if ($acao == 'buscar_fotos') {
    $agendamento_id = $_GET['agendamento_id'] ?? 0;
    $stmt = $conexao->prepare("SELECT id, tipo, caminho_arquivo FROM fotos_servico WHERE agendamento_id = ? ORDER BY data_envio ASC");
    $stmt->bind_param("i", $agendamento_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fotos = ['antes' => [], 'depois' => []];
    
    while($row = $resultado->fetch_assoc()) {
        $fotos[ $row['tipo'] ][] = $row;
    }
    echo json_encode(['status' => 'sucesso', 'fotos' => $fotos]);
    exit;
}

// --- Ação 2: Upload de nova foto ---
if ($acao == 'upload_foto') {
    $agendamento_id = $_POST['agendamento_id'] ?? 0;
    $tipo_foto = $_POST['tipo_foto'] ?? ''; // 'antes' ou 'depois'
    
    if ($agendamento_id == 0 || empty($tipo_foto) || !isset($_FILES['foto'])) {
        echo json_encode(['status' => 'erro', 'msg' => 'Dados incompletos.']);
        exit;
    }

    $file = $_FILES['foto'];

    // 1. Validação básica
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'erro', 'msg' => 'Erro no upload.']);
        exit;
    }
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['status' => 'erro', 'msg' => 'Apenas imagens JPG ou PNG.']);
        exit;
    }

    // 2. REMOVER FOTO ANTIGA (Lógica de Substituição)
    // Busca se já existe foto desse tipo para esse agendamento
    $sql_check = "SELECT id, caminho_arquivo FROM fotos_servico WHERE agendamento_id = ? AND tipo = ?";
    $stmt_check = $conexao->prepare($sql_check);
    $stmt_check->bind_param("is", $agendamento_id, $tipo_foto);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    while ($old_photo = $res_check->fetch_assoc()) {
        // Apaga o arquivo físico da pasta
        $arquivo_fisico = '../../' . $old_photo['caminho_arquivo'];
        if (file_exists($arquivo_fisico)) {
            unlink($arquivo_fisico);
        }
        // Apaga o registro do banco
        $conexao->query("DELETE FROM fotos_servico WHERE id = " . $old_photo['id']);
    }
    $stmt_check->close();

    // 3. SALVAR NOVA FOTO
    $upload_dir = '../../uploads/fotos_servicos/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "ag-{$agendamento_id}_{$tipo_foto}_" . time() . "." . $ext;
    $caminho_final = $upload_dir . $filename;
    $caminho_db = 'uploads/fotos_servicos/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $caminho_final)) {
        $sql = "INSERT INTO fotos_servico (agendamento_id, enviado_por, tipo, caminho_arquivo, status_validacao) 
                VALUES (?, ?, ?, ?, 'Pendente')";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("iiss", $agendamento_id, $gestor_id, $tipo_foto, $caminho_db);
        
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'sucesso', 
                'msg' => 'Foto atualizada com sucesso!', 
                'caminho_arquivo' => $caminho_db, 
                'tipo' => $tipo_foto // Retorna o tipo para o JS saber qual atualizar
            ]);
        } else {
            echo json_encode(['status' => 'erro', 'msg' => 'Erro ao salvar no banco.']);
        }
    } else {
        echo json_encode(['status' => 'erro', 'msg' => 'Falha ao mover arquivo.']);
    }
    exit;
}

// --- Ação 3: Marcar serviço como concluído ---
if ($acao == 'marcar_concluido') {
    $agendamento_id = $_POST['agendamento_id'] ?? 0;
    
    // Atualiza o status do agendamento
    $sql = "UPDATE agendamentos SET status = 'Concluído - Aguardando Validação' 
            WHERE id = ? AND gestor_id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ii", $agendamento_id, $gestor_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'sucesso', 'msg' => 'Serviço marcado como concluído para validação!']);
    } else {
        echo json_encode(['status' => 'erro', 'msg' => 'Falha ao atualizar o status.']);
    }
    exit;
}

// Se nenhuma ação foi encontrada
echo json_encode(['status' => 'erro', 'msg' => 'Nenhuma ação válida recebida.']);
?>