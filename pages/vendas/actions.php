<?php
// Este arquivo responde apenas a chamadas AJAX (JSON)
include('../../includes/db_connect.php');
session_start();

header('Content-Type: application/json');

$acao = $_POST['acao'] ?? '';

// 1. Buscar Mantenedor
if ($acao == 'buscar_mantenedor') {
    $termo = "%" . $_POST['termo'] . "%";
    $stmt = $conexao->prepare("SELECT id, nome, email FROM mantenedores WHERE nome LIKE ? OR email LIKE ? LIMIT 5");
    $stmt->bind_param("ss", $termo, $termo);
    $stmt->execute();
    $res = $stmt->get_result();
    $dados = [];
    while ($row = $res->fetch_assoc()) { $dados[] = $row; }
    echo json_encode($dados);
    exit;
}

// 2. Buscar Contratos do Mantenedor
if ($acao == 'buscar_contratos') {
    $mantenedor_id = $_POST['mantenedor_id'];
    $stmt = $conexao->prepare("SELECT id, numero, jazigo, quadra, bloco FROM contratos WHERE mantenedor_id = ?");
    $stmt->bind_param("i", $mantenedor_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $dados = [];
    while ($row = $res->fetch_assoc()) { $dados[] = $row; }
    echo json_encode($dados);
    exit;
}

// 3. Cadastrar Venda
if ($acao == 'salvar_venda') {
    try {
        $vendedor_id = $_SESSION['usuario_id'];
        $mantenedor_id = $_POST['mantenedor_id'];
        $contrato_id = $_POST['contrato_id'];
        $prod_serv_id = $_POST['produto_servico_id'];
        $data_venda = $_POST['data_venda'];
        $numero_os = $_POST['numero_os'];
        $numero_nf = $_POST['numero_nf'];
        $valor_final = str_replace(',', '.', $_POST['valor_final']);
        $condicao = $_POST['condicao_pagamento'];
        $familia = isset($_POST['familia_comparecer']) ? 1 : 0;
        $valor_entrada = null;
        $qtde_parcelas = null;
        $valor_parcela = null;
        $data_cobranca = null;

        if ($condicao == 'A Prazo') {
            $valor_entrada = isset($_POST['valor_entrada']) && $_POST['valor_entrada'] !== '' ? str_replace(',', '.', $_POST['valor_entrada']) : 0;
            $qtde_parcelas = $_POST['qtde_parcelas'];
            $data_cobranca = $_POST['data_previsao_cobranca'];

            // CÁLCULO NO BACKEND (Segurança para o setor de cobrança)
            if ($qtde_parcelas > 0) {
                $restante = $valor_final - $valor_entrada;
                $valor_parcela = $restante / $qtde_parcelas;
            }
        }

        // Status inicial
        if ($condicao == 'A Prazo' && $valor_entrada < ($valor_final / 2)) {
            $status = 'Aguardando Cobrança';
        } else {
            $status = 'Aprovado para Agendamento';
        }

        // SQL Atualizado
        $sql = "INSERT INTO vendas (vendedor_id, mantenedor_id, contrato_id, produto_servico_id, data_venda, numero_os, numero_nf, valor_final, valor_entrada, qtde_parcelas, valor_parcela, data_previsao_cobranca, condicao_pagamento, familia_comparecer, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexao->prepare($sql);
        // Tipos: d=double, s=string, i=int
        $stmt->bind_param("iiiisssddidssis", $vendedor_id, $mantenedor_id, $contrato_id, $prod_serv_id, $data_venda, $numero_os, $numero_nf, $valor_final, $valor_entrada, $qtde_parcelas, $valor_parcela, $data_cobranca, $condicao, $familia, $status);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'sucesso', 'msg' => 'Venda realizada com sucesso!']);
        } else {
            echo json_encode(['status' => 'erro', 'msg' => 'Erro SQL: ' . $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'erro', 'msg' => $e->getMessage()]);
    }
    exit;
}

// 4. Salvar Novo Mantenedor
if ($acao == 'salvar_mantenedor') {
    try {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $telefone = $_POST['telefone'];

        // Validação básica (pode ser expandida)
        if (empty($nome) || empty($email)) {
            echo json_encode(['status' => 'erro', 'msg' => 'Nome e e-mail são obrigatórios.']);
            exit;
        }

        $sql = "INSERT INTO mantenedores (nome, email, telefone, status) VALUES (?, ?, ?, 'Ativo')";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("sss", $nome, $email, $telefone);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'sucesso', 'msg' => 'Mantenedor cadastrado com sucesso!', 'id' => $stmt->insert_id, 'nome' => $nome]);
        } else {
            echo json_encode(['status' => 'erro', 'msg' => 'Erro ao cadastrar mantenedor: ' . $stmt->error]);
        }

    } catch (Exception $e) {
        echo json_encode(['status' => 'erro', 'msg' => $e->getMessage()]);
    }
    exit;
}
// 5. Buscar Lista de Cemitérios (Para o Select)
if ($acao == 'buscar_cemiterios') {
    // Busca ID e Nome da tabela 'cemiterios'
    $sql = "SELECT id, nome FROM cemiterios WHERE localizacao IS NOT NULL ORDER BY nome ASC"; 
    // (Ajuste o WHERE conforme sua necessidade, ou remova para trazer todos)
    $sql = "SELECT id, nome FROM cemiterios ORDER BY nome ASC";
    
    $res = $conexao->query($sql);
    $dados = [];
    while ($row = $res->fetch_assoc()) {
        $dados[] = $row;
    }
    echo json_encode($dados);
    exit;
}

// 6. Salvar Novo Contrato
if ($acao == 'salvar_contrato') {
    try {
        $mantenedor_id = $_POST['mantenedor_id'];
        $cemiterio_id = $_POST['cemiterio_id'];
        $numero = $_POST['numero'];
        $jazigo = $_POST['jazigo'];
        $quadra = $_POST['quadra'];
        $bloco = $_POST['bloco'];

        if (empty($mantenedor_id) || empty($cemiterio_id) || empty($numero)) {
            echo json_encode(['status' => 'erro', 'msg' => 'Filial e Número do contrato são obrigatórios.']);
            exit;
        }

        $sql = "INSERT INTO contratos (mantenedor_id, cemiterio_id, numero, jazigo, quadra, bloco) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("iissss", $mantenedor_id, $cemiterio_id, $numero, $jazigo, $quadra, $bloco);
        
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'sucesso', 
                'msg' => 'Contrato cadastrado!', 
                'id' => $stmt->insert_id, 
                'numero' => $numero,
                'detalhes' => "Jazigo: $jazigo, Q: $quadra, B: $bloco"
            ]);
        } else {
            echo json_encode(['status' => 'erro', 'msg' => 'Erro ao salvar contrato: ' . $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'erro', 'msg' => $e->getMessage()]);
    }
    exit;
}
?>