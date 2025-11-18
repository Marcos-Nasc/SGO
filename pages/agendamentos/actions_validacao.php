<?php
// FORÇAR EXIBIÇÃO DE ERROS
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- IMPORTAÇÃO E INCLUDES ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
include('../../includes/db_connect.php');
session_start();
header('Content-Type: application/json');
require '../../vendor/phpmailer/phpmailer/src/Exception.php';
require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../vendor/phpmailer/phpmailer/src/SMTP.php';
// -----------------------------------------------------

// Nível de Usuário e Ação
$nivel_usuario = $_SESSION['usuario_nivel'] ?? '';
$sc_id = $_SESSION['usuario_id'] ?? 0;
if ($nivel_usuario != 'Sucesso do Cliente' && $nivel_usuario != 'Administrador') {
    echo json_encode(['status' => 'erro', 'msg' => 'Acesso negado.']);
    exit;
}

$acao = $_POST['acao'] ?? '';

// --- AÇÃO 1: Invalidar Serviço ---
if ($acao == 'invalidar_servico') {
    // ... (código da Ação 1 - Invalidar) ...
    $agendamento_id = $_POST['agendamento_id'] ?? 0;
    $observacao = $_POST['observacao'] ?? '';
    if (empty($observacao)) {
        echo json_encode(['status' => 'erro', 'msg' => 'A observação é obrigatória para invalidar.']);
        exit;
    }
    $conexao->begin_transaction();
    try {
        $sql_ag = "UPDATE agendamentos SET status = 'Rejeitado', observacoes = ? WHERE id = ?";
        $stmt_ag = $conexao->prepare($sql_ag);
        $stmt_ag->bind_param("si", $observacao, $agendamento_id);
        $stmt_ag->execute();
        $sql_fotos = "UPDATE fotos_servico SET status_validacao = 'Invalidado' WHERE agendamento_id = ?";
        $stmt_fotos = $conexao->prepare($sql_fotos);
        $stmt_fotos->bind_param("i", $agendamento_id);
        $stmt_fotos->execute();
        $conexao->commit();
        echo json_encode(['status' => 'sucesso', 'msg' => 'Serviço invalidado e devolvido ao Gestor.']);
    } catch (Exception $e) {
        $conexao->rollback();
        echo json_encode(['status' => 'erro', 'msg' => 'Falha no banco de dados.']);
    }
    exit;
}

// --- AÇÃO 2: Validar Fotos ---
if ($acao == 'validar_servico') {
    // ... (código da Ação 2 - Validar) ...
    $agendamento_id = $_POST['agendamento_id'] ?? 0;
    $observacao = $_POST['observacao'] ?? null;
    $conexao->begin_transaction();
    try {
        $sql_ag = "UPDATE agendamentos SET status = 'Finalizado Internamente', observacoes = ? WHERE id = ?";
        $stmt_ag = $conexao->prepare($sql_ag);
        $stmt_ag->bind_param("si", $observacao, $agendamento_id);
        $stmt_ag->execute();
        $sql_fotos = "UPDATE fotos_servico SET status_validacao = 'Validado' WHERE agendamento_id = ?";
        $stmt_fotos = $conexao->prepare($sql_fotos);
        $stmt_fotos->bind_param("i", $agendamento_id);
        $stmt_fotos->execute();
        $conexao->commit();
        echo json_encode(['status' => 'sucesso', 'msg' => 'Fotos validadas! Próximo passo é enviar o e-mail.']);
    } catch (Exception $e) {
        $conexao->rollback();
        echo json_encode(['status' => 'erro', 'msg' => 'Falha no banco de dados.']);
    }
    exit;
}

// --- AÇÃO 3: Enviar E-mail ao Cliente (COM NOVAS CREDENCIAIS) ---
if ($acao == 'enviar_email_cliente') {
    $agendamento_id = $_POST['agendamento_id'] ?? 0;

    // 1. Busca dados (Já testado - OK)
    $sql = "SELECT m.nome as cliente_nome, m.email as cliente_email, p.nome as servico_nome, v.numero_os FROM agendamentos a JOIN vendas v ON a.venda_id = v.id JOIN mantenedores m ON v.mantenedor_id = m.id JOIN produtos_servicos p ON v.produto_servico_id = p.id WHERE a.id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $agendamento_id);
    $stmt->execute();
    $stmt->bind_result($cliente_nome, $cliente_email, $servico_nome, $numero_os);
    $stmt->fetch();
    $venda_info = ['cliente_nome' => $cliente_nome, 'cliente_email' => $cliente_email, 'servico_nome' => $servico_nome, 'numero_os' => $numero_os];
    $stmt->close();

    // 2. Busca fotos (Já testado - OK)
    $sql_fotos = "SELECT tipo, caminho_arquivo FROM fotos_servico WHERE agendamento_id = ? AND status_validacao = 'Validado'";
    $stmt_fotos = $conexao->prepare($sql_fotos);
    $stmt_fotos->bind_param("i", $agendamento_id);
    $stmt_fotos->execute();
    $stmt_fotos->bind_result($tipo, $caminho_arquivo);
    $fotos = [];
    while ($stmt_fotos->fetch()) { $fotos[] = ['tipo' => $tipo, 'caminho_arquivo' => $caminho_arquivo]; }
    $stmt_fotos->close();

    if (empty($venda_info['cliente_email']) || empty($fotos)) {
        echo json_encode(['status' => 'erro', 'msg' => 'Não foi possível enviar. Verifique se as fotos já foram validadas.']);
        exit;
    }

    // 3. Montagem e Envio do E-mail
    $mail = new PHPMailer(true);
    $debug_output = ''; // Variável para capturar o debug

    try {
        // --- ATIVANDO O DEBUG ---
        // $mail->SMTPDebug = 2; // (Deixe comentado por enquanto)
        // $mail->Debugoutput = function($str, $level) use (&$debug_output) {
        //     $debug_output .= "$str\n";
        // };
        // -------------------------

        $mail->IsSMTP(); 
        
        // --- SUAS NOVAS CONFIGURAÇÕES DO GMAIL (ATHENA) ---
        $mail->Host = "email-ssl.com.br"; 
        $mail->SMTPAuth = true; 
        $mail->Username = 'marcos.correia@reviver.srv.br'; 
        $mail->Password = 'Ma@20504177729';
        $mail->Port = 587; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // (tls)
        $mail->CharSet = 'UTF-8';
        // ------------------------------------------

        // Remetente (DEVE ser o mesmo do Username)
        $mail->setFrom('marcos.correia@reviver.srv.br', 'SGO - Serviços Reviver');
        $mail->addAddress($venda_info['cliente_email'], $venda_info['cliente_nome']);
        // O "ReplyTo" (Para onde o cliente responde) pode ser o e-mail de contato
        $mail->addReplyTo('contato@concessionariareviver.com.br', 'SGO');

        foreach ($fotos as $foto) {
            $caminho_completo = '../../' . $foto['caminho_arquivo']; 
            $nome_arquivo = $foto['tipo'] . '_' . basename($foto['caminho_arquivo']);
            if(file_exists($caminho_completo)){
                $mail->addAttachment($caminho_completo, $nome_arquivo);
            }
        }

        $mail->isHTML(true);
        $mail->Subject = 'Seu serviço SGO foi concluído! (OS: ' . $venda_info['numero_os'] . ')';
        $mail->Body    = "Prezado(a) " . $venda_info['cliente_nome'] . ",<br><br>"
                       . "Temos o prazer de informar que seu serviço de <strong>" . $venda_info['servico_nome'] . "</strong> (referente à OS: " . $venda_info['numero_os'] . ") foi concluído com sucesso.<br><br>"
                       . "<b>Confira as imagens do antes e depois do serviço realizado, no anexo deste e-mail.</b><br/><br/>"
                       . "Agradecemos a confiança,<br>Equipe Reviver";
        $mail->AltBody = "Olá, " . $venda_info['cliente_nome'] . ". O seu serviço de " . $venda_info['servico_nome'] . " (OS: " . $venda_info['numero_os'] . ") foi concluído. As fotos estão em anexo.";

        $mail->send();
        
        // 4. Atualiza o status final
        $sql_final = "UPDATE agendamentos SET status = 'Finalizado e Enviado' WHERE id = ?";
        $stmt_final = $conexao->prepare($sql_final);
        $stmt_final->bind_param("i", $agendamento_id);
        $stmt_final->execute();
        
        // 5. Log
        $log_sql = "INSERT INTO log_emails_clientes (venda_id, email_destino, assunto, mensagem, status_envio) 
                    VALUES (?, ?, ?, ?, 'Enviado')";
        $venda_id_log = $conexao->query("SELECT venda_id FROM agendamentos WHERE id = $agendamento_id")->fetch_assoc()['venda_id'];
        $stmt_log = $conexao->prepare($log_sql);
        $stmt_log->bind_param("isss", $venda_id_log, $venda_info['cliente_email'], $mail->Subject, $mail->Body);
        $stmt_log->execute();

        echo json_encode(['status' => 'sucesso', 'msg' => 'E-mail enviado com sucesso para ' . $venda_info['cliente_email']]);

    } catch (Exception $e) {
        // Se o PHPMailer falhar
        echo json_encode([
            'status' => 'erro', 
            'msg' => 'E-mail não pôde ser enviado. Erro do Servidor: ' . $mail->ErrorInfo,
            'debug' => $debug_output
        ]);
    }
    exit;
}

// Se nenhuma ação foi encontrada (fallback)
echo json_encode(['status' => 'erro', 'msg' => 'Nenhuma ação válida foi recebida pelo PHP.']);
exit;
?>