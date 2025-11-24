<?php
// pages/processos/actions.php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

include('../../includes/db_connect.php');
session_start();

function enviarJSON($array) {
    ob_clean(); 
    header('Content-Type: application/json');
    echo json_encode($array);
    exit;
}

try {
    // Verifica permissão
    if (($_SESSION['usuario_nivel'] ?? '') !== 'Administrador') {
        throw new Exception('Acesso negado.');
    }

    $acao = $_POST['acao'] ?? '';

    // --- 0. LISTAR PRODUTOS E TIPOS (Agrupados para o Select) ---
    if ($acao == 'listar_produtos_tipos') {
        // Busca apenas os tipos distintos primeiro
        $sqlTipos = "SELECT DISTINCT tipo FROM produtos_servicos WHERE status = 'Ativo' ORDER BY tipo ASC";
        $resTipos = $conexao->query($sqlTipos);
        $tipos = [];
        while($row = $resTipos->fetch_assoc()) { $tipos[] = $row['tipo']; }

        // Busca todos os itens
        $sqlItens = "SELECT id, nome, tipo FROM produtos_servicos WHERE status = 'Ativo' ORDER BY nome ASC";
        $resItens = $conexao->query($sqlItens);
        $itens = [];
        while($row = $resItens->fetch_assoc()) { $itens[] = $row; }

        enviarJSON(['status' => 'sucesso', 'tipos' => $tipos, 'itens' => $itens]);
    }

    // --- 1. BUSCAR DADOS COMPLETOS ---
    if ($acao == 'buscar_dados_processo') {
        $venda_id = $_POST['venda_id'];

        $sql = "SELECT 
                    v.id as venda_id, v.mantenedor_id, v.contrato_id, v.produto_servico_id,
                    v.numero_os, v.numero_nf, v.status as status_venda, v.observacao as obs_venda,
                    v.valor_final, v.valor_entrada, v.qtde_parcelas, v.condicao_pagamento, v.data_venda,
                    
                    m.nome as mantenedor_nome, m.email as mantenedor_email, m.telefone as mantenedor_telefone,
                    
                    c.numero as contrato_numero, c.jazigo, c.quadra, c.bloco,
                    
                    ps.nome as servico_nome, ps.tipo as servico_tipo, 

                    a.id as agendamento_id, a.data_agendada, a.status as status_agendamento, a.observacoes as obs_agendamento

                FROM vendas v
                JOIN mantenedores m ON v.mantenedor_id = m.id
                JOIN contratos c ON v.contrato_id = c.id
                JOIN produtos_servicos ps ON v.produto_servico_id = ps.id
                LEFT JOIN agendamentos a ON a.venda_id = v.id
                WHERE v.id = ?";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $venda_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $dados = $result->fetch_assoc();

        if ($dados) {
            if (!empty($dados['data_agendada'])) {
                $dados['data_agendada_formatada'] = date('Y-m-d\TH:i', strtotime($dados['data_agendada']));
            } else {
                $dados['data_agendada_formatada'] = '';
            }

            // Busca fotos (AGORA Trazendo o ID)
            $dados['fotos'] = ['antes' => [], 'depois' => []];
            if (!empty($dados['agendamento_id'])) {
                // MUDANÇA AQUI: Adicionado 'id' no select
                $sqlFotos = "SELECT id, caminho_arquivo, tipo FROM fotos_servico WHERE agendamento_id = ?";
                $stmtFotos = $conexao->prepare($sqlFotos);
                if ($stmtFotos) {
                    $stmtFotos->bind_param("i", $dados['agendamento_id']);
                    $stmtFotos->execute();
                    $resFotos = $stmtFotos->get_result();
                    while ($f = $resFotos->fetch_assoc()) {
                        // Adiciona o ID no array de retorno
                        if ($f['tipo'] == 'antes') $dados['fotos']['antes'][] = ['id' => $f['id'], 'caminho' => $f['caminho_arquivo']];
                        if ($f['tipo'] == 'depois') $dados['fotos']['depois'][] = ['id' => $f['id'], 'caminho' => $f['caminho_arquivo']];
                    }
                }
            }
            enviarJSON(['status' => 'sucesso', 'dados' => $dados]);
        } else {
            enviarJSON(['status' => 'erro', 'msg' => 'Processo não encontrado.']);
        }
    }

    // --- 2. SALVAR EDICAO (CONTRATO REMOVIDO) ---
    if ($acao == 'salvar_edicao_completa') {
        
        $venda_id = $_POST['venda_id'];
        $contrato_id = $_POST['contrato_id'];
        $agendamento_id = $_POST['agendamento_id'];

        // Dados
        $produto_id = $_POST['v_produto_id'];
        // Nota: Dados do Contrato e Cliente não são atualizados aqui (Readonly), apenas IDs de relação se necessário
        
        $v_os = $_POST['v_os']; $v_nf = $_POST['v_nf']; $v_status = $_POST['v_status']; $v_obs = $_POST['v_obs'];
        $v_valor = $_POST['v_valor']; $v_condicao = $_POST['v_condicao']; 
        
        // Tratamento Financeiro
        if ($v_condicao == 'A Vista') {
            $v_entrada = NULL;
            $v_parcelas = NULL;
            $v_valor_parcela = NULL;
            $v_data_previsao = $_POST['v_data_venda']; // Se é a vista, 50% já foi (data da venda)
        } else {
            $v_entrada = !empty($_POST['v_entrada']) ? $_POST['v_entrada'] : 0; 
            $v_parcelas = !empty($_POST['v_parcelas']) ? $_POST['v_parcelas'] : 1;
            
            // Recebe os valores calculados pelo JS (ou recalcula aqui por segurança)
            $v_valor_parcela = $_POST['v_valor_parcela_calc']; 
            $v_data_previsao = $_POST['v_data_previsao_calc'];
        }

        $a_data = !empty($_POST['a_data']) ? $_POST['a_data'] : NULL;
        $a_status = $_POST['a_status']; $a_obs = $_POST['a_obs'];

        $conexao->begin_transaction();

        // 1. Atualizar Venda (Adicionado valor_parcela e data_previsao_cobranca)
        $sqlV = "UPDATE vendas SET 
                    produto_servico_id=?, numero_os=?, numero_nf=?, status=?, observacao=?, 
                    valor_final=?, condicao_pagamento=?, valor_entrada=?, qtde_parcelas=?, 
                    valor_parcela=?, data_previsao_cobranca=? 
                 WHERE id=?";
                 
        $stmtV = $conexao->prepare($sqlV);
        $stmtV->bind_param("issssdsdidsi", 
            $produto_id, $v_os, $v_nf, $v_status, $v_obs, 
            $v_valor, $v_condicao, $v_entrada, $v_parcelas, 
            $v_valor_parcela, $v_data_previsao, $venda_id
        );
        $stmtV->execute();

        // 2. Atualizar/Criar Agendamento (Mantido igual)
        if (!empty($agendamento_id)) {
            $sqlA = "UPDATE agendamentos SET data_agendada=?, status=?, observacoes=? WHERE id=?";
            $stmtA = $conexao->prepare($sqlA);
            $stmtA->bind_param("sssi", $a_data, $a_status, $a_obs, $agendamento_id);
            $stmtA->execute();
        } elseif (!empty($a_data)) {
            $gestor_id = $_SESSION['usuario_id'];
            $sqlA = "INSERT INTO agendamentos (venda_id, gestor_id, data_agendada, status, observacoes) VALUES (?, ?, ?, ?, ?)";
            $stmtA = $conexao->prepare($sqlA);
            $stmtA->bind_param("iisss", $venda_id, $gestor_id, $a_data, $a_status, $a_obs);
            $stmtA->execute();
        }

        $conexao->commit();
        enviarJSON(['status' => 'sucesso', 'msg' => 'Processo atualizado com sucesso!']);
    }

    // --- 3. NOVO: EXCLUIR FOTO INDIVIDUAL ---
    if ($acao == 'excluir_foto_processo') {
        $foto_id = $_POST['foto_id'];
        $caminho_relativo = $_POST['caminho']; // O caminho que vem do banco (ex: uploads/fotos_servicos/...)

        // 1. Busca o caminho completo no servidor para deletar o arquivo
        // Ajuste o caminho base conforme a estrutura real do seu servidor.
        // Assumindo que 'pages/processos/actions.php' está 2 níveis abaixo da raiz 'sgo/' e a pasta uploads está na raiz 'sgo/'
        $caminho_fisico = realpath('../../' . $caminho_relativo);

        $conexao->begin_transaction();

        // 2. Deleta do Banco
        $sql = "DELETE FROM fotos_servico WHERE id = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $foto_id);
        
        if ($stmt->execute()) {
            // 3. Tenta deletar o arquivo físico se ele existir
            if ($caminho_fisico && file_exists($caminho_fisico)) {
                unlink($caminho_fisico);
            }
            
            $conexao->commit();
            enviarJSON(['status' => 'sucesso', 'msg' => 'Foto excluída.']);
        } else {
            $conexao->rollback();
            throw new Exception($stmt->error);
        }
    }

    // --- EXCLUIR PROCESSO GERAL ---
    if ($acao == 'excluir_processo') {
        $venda_id = $_POST['venda_id'];
        
        $conexao->begin_transaction(); // Inicia transação para garantir segurança

        try {
            // 1. Apagar Logs de Email primeiro (Para evitar o erro de Foreign Key)
            $stmtLog = $conexao->prepare("DELETE FROM log_emails_clientes WHERE venda_id = ?");
            $stmtLog->bind_param("i", $venda_id);
            $stmtLog->execute();

            // 2. Agora sim, apagar a Venda (O Agendamento apaga sozinho por causa do Cascade do banco)
            $sql = "DELETE FROM vendas WHERE id = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("i", $venda_id);
            
            if ($stmt->execute()) {
                $conexao->commit(); // Confirma
                enviarJSON(['status' => 'sucesso', 'msg' => 'Processo excluído.']);
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            $conexao->rollback(); // Cancela se der erro
            enviarJSON(['status' => 'erro', 'msg' => 'Erro ao excluir: ' . $e->getMessage()]);
        }
    }

} catch (Exception $e) {
    if (isset($conexao) && $conexao->errno) $conexao->rollback();
    enviarJSON(['status' => 'erro', 'msg' => 'Erro no servidor: ' . $e->getMessage()]);
}
enviarJSON(['status' => 'erro', 'msg' => 'Ação inválida.']);
?>