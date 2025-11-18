<?php
// 1. Iniciar a sessão
session_start();

// 2. Verificar se o usuário JÁ ESTÁ logado
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php"); // Redireciona para o dashboard
    exit();
}

// 3. Incluir a conexão com o banco
require_once 'includes/db_connect.php';

$error_message = '';

// 4. Verificar se o formulário foi enviado (método POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = $_POST['email'] ?? '';
    $senha_post = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha_post)) {
        $error_message = "Por favor, preencha o e-mail e a senha.";
    } else {
        
        // 5. Preparar a consulta (PREPARED STATEMENT contra SQL Injection)
        $sql = "SELECT id, nome, email, senha, nivel, status FROM usuarios WHERE email = ?";
        
        if ($stmt = $conexao->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows == 1) {
                $usuario = $resultado->fetch_assoc();

                // 6. Verificar a SENHA (HASH) e o STATUS
                if (password_verify($senha_post, $usuario['senha'])) {
                    
                    if ($usuario['status'] == 'Ativo') {
                        // 7. SUCESSO: Login válido e usuário Ativo
                        
                        // Regenerar ID da sessão por segurança
                        session_regenerate_id(true);

                        // Armazenar dados do usuário na sessão
                        $_SESSION['usuario_id'] = $usuario['id'];
                        $_SESSION['usuario_nome'] = $usuario['nome'];
                        $_SESSION['usuario_nivel'] = $usuario['nivel'];

                        // 8. Redirecionar para a index (roteador)
                        header("Location: index.php");
                        exit();
                        
                    } else {
                        $error_message = "Esta conta está inativa.";
                    }
                } else {
                    $error_message = "E-mail ou senha inválidos.";
                }
            } else {
                $error_message = "E-mail ou senha inválidos.";
            }
            $stmt->close();
        } else {
            $error_message = "Erro ao preparar a consulta.";
        }
    }
    $conexao->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SGO</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

    <div class="login-card">
        <h1>SGO</h1>
        <p>Sistema de Gerenciamento Operacional</p>

        <form action="login.php" method="POST">
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>

            <button type="submit" class="btn-login">Entrar</button>
        </form>
    </div>

</body>
</html>