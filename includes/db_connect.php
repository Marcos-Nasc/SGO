<?php
/*
 * Arquivo de Conexão com o Banco de Dados (sgo_db)
 */

// Configurações do banco local
$servidor = 'localhost';
$usuario_db = 'root';
$senha_db = ''; // Padrão do XAMPP/WAMP é vazio
$banco = 'sgo_db';

// Criar a conexão
$conexao = new mysqli($servidor, $usuario_db, $senha_db, $banco);

// Verificar a conexão
if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}

// Definir o charset para UTF-8 (para suportar acentos)
if (!$conexao->set_charset("utf8mb4")) {
    printf("Erro ao definir o charset utf8mb4: %s\n", $conexao->error);
    exit();
}

// Não feche o '?>