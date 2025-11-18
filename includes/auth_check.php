<?php
// Inicia a sessão se ainda não houver uma
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se a sessão 'usuario_id' NÃO existe
if (!isset($_SESSION['usuario_id'])) {
    
    // Se não existir, destrói qualquer sessão antiga
    session_unset();
    session_destroy();
    
    // Redireciona o usuário para a página de login
    header("Location: login.php");
    exit(); // Garante que o script pare de ser executado
}

// Se a sessão existe, o script continua e a página protegida é carregada.
?>