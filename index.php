<?php
// 1. Nosso "Porteiro"
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';

// 2. Lógica do Roteador
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'view';

// 3. DEFINIR O TÍTULO DA PÁGINA (NOVO!)
$page_title = 'Dashboard'; // Padrão
if ($page == 'usuarios' && $action == 'view') $page_title = 'Usuários';
if ($page == 'vendas' && $action == 'view') $page_title = 'Minhas Vendas';
if ($page == 'vendas' && $action == 'registrar') $page_title = 'Registrar Venda';
// ... adicione outros títulos aqui

// 4. Incluir o topo (HTML, Head, CSS, etc.)
include('includes/header.php');

// 5. Incluir a Sidebar (Menu lateral)
include('includes/sidebar.php');

// 6. Incluir a Navbar (Barra superior) e iniciar o conteúdo
include('includes/navbar.php');

// 7. O "MIOLO" - Onde a mágica acontece
$filePath = "pages/{$page}/{$action}.php";
if ($page == 'dashboard') $filePath = "pages/dashboard.php"; // Caso especial

if (file_exists($filePath)) {
    include($filePath);
} else {
    include('pages/errors/404.php');
}

// 8. Incluir o Rodapé (fechar tags, carregar JS)
include('includes/footer.php');
?>