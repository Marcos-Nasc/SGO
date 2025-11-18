<?php
include('../../includes/db_connect.php');
header('Content-Type: application/json');

$acao = $_POST['acao'] ?? '';

if ($acao == 'buscar_detalhes_completo') {
    $venda_id = $_POST['venda_id'];

    // 1. Dados da Venda
    $sql = "SELECT v.*, m.nome as cliente_nome, p.nome as servico_nome, c.numero as contrato_numero 
            FROM vendas v 
            JOIN mantenedores m ON v.mantenedor_id = m.id
            JOIN produtos_servicos p ON v.produto_servico_id = p.id
            JOIN contratos c ON v.contrato_id = c.id
            WHERE v.id = $venda_id";
    $venda = $conexao->query($sql)->fetch_assoc();

    if (!$venda) {
        echo json_encode(['status' => 'erro', 'msg' => 'Venda não encontrada']);
        exit;
    }

    // 2. Dados do Agendamento (se houver)
    $sql_ag = "SELECT * FROM agendamentos WHERE venda_id = $venda_id LIMIT 1";
    $res_ag = $conexao->query($sql_ag);
    $agendamento_data = null;
    $agendamento_id = 0;

    if ($res_ag->num_rows > 0) {
        $ag = $res_ag->fetch_assoc();
        $agendamento_id = $ag['id'];
        $agendamento_data = [
            'data' => date('d/m/Y H:i', strtotime($ag['data_agendada'])),
            'status' => $ag['status'],
            'obs' => $ag['observacoes']
        ];
    }

    // 3. Fotos (se houver agendamento)
    $fotos_info = ['tem_fotos' => false, 'status_validacao' => 'Pendente'];
    $fotos_lista = ['antes' => [], 'depois' => []];
    
    if ($agendamento_id > 0) {
        $sql_fotos = "SELECT tipo, caminho_arquivo, status_validacao FROM fotos_servico WHERE agendamento_id = $agendamento_id";
        $res_fotos = $conexao->query($sql_fotos);
        if ($res_fotos->num_rows > 0) {
            $fotos_info['tem_fotos'] = true;
            while ($f = $res_fotos->fetch_assoc()) {
                $fotos_lista[$f['tipo']][] = ['caminho' => $f['caminho_arquivo']];
                // Pega o status da última foto (assumindo que todas têm o mesmo status)
                $fotos_info['status_validacao'] = $f['status_validacao'];
            }
        }
    }

    // 4. Log de E-mail (se foi enviado)
    $email_info = null;
    $sql_email = "SELECT * FROM log_emails_clientes WHERE venda_id = $venda_id ORDER BY data_envio DESC LIMIT 1";
    $res_email = $conexao->query($sql_email);
    if ($res_email->num_rows > 0) {
        $email = $res_email->fetch_assoc();
        $email_info = [
            'enviado' => true,
            'data' => date('d/m/Y H:i', strtotime($email['data_envio'])),
            'destino' => $email['email_destino'],
            'status' => $email['status_envio']
        ];
    } else {
        $email_info = ['enviado' => false];
    }

    // Monta o objeto final
    $dados = [
        'id' => $venda['id'],
        'numero_os' => $venda['numero_os'],
        'numero_nf' => $venda['numero_nf'] ?? 'N/A',
        'contrato' => $venda['contrato_numero'],
        'status_venda' => $venda['status'],
        'cliente_nome' => $venda['cliente_nome'],
        'servico_nome' => $venda['servico_nome'],
        'data_venda' => date('d/m/Y', strtotime($venda['data_venda'])),
        
        // Dados Financeiros Formatados
        'valor_final' => number_format($venda['valor_final'], 2, ',', '.'),
        'valor_entrada' => $venda['valor_entrada'] ? number_format($venda['valor_entrada'], 2, ',', '.') : '0,00',
        'valor_parcela' => $venda['valor_parcela'] ? number_format($venda['valor_parcela'], 2, ',', '.') : '0,00',
        
        // Dados Financeiros Brutos (Para cálculos no JS se precisar)
        'valor_final_raw' => $venda['valor_final'],
        'valor_entrada_raw' => $venda['valor_entrada'],
        'condicao_pagamento' => $venda['condicao_pagamento'],
        'qtde_parcelas' => $venda['qtde_parcelas'],
        
        // Datas Importantes
        'data_previsao_cobranca' => $venda['data_previsao_cobranca'] ? date('d/m/Y', strtotime($venda['data_previsao_cobranca'])) : null,
        'data_venda_raw' => $venda['data_venda'], // YYYY-MM-DD
        
        'familia_comparecer' => $venda['familia_comparecer'],
        'agendamento' => $agendamento_data,
        'fotos_info' => $fotos_info,
        'fotos' => $fotos_lista,
        'email_info' => $email_info
    ];

    echo json_encode(['status' => 'sucesso', 'dados' => $dados]);
    exit;
}
?>