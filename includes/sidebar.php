<?php
// Pega o nível do usuário da sessão para decidir qual menu mostrar
$nivel_usuario = $_SESSION['usuario_nivel'] ?? 'Vendedor';
?>
<aside class="sidebar">

    <div class="sidebar-header">
        <a href="index.php" class="logo-link">
            <img src="" alt="Logo_SGO">
        </a>
    </div>

    <nav class="sidebar-nav">
        <ul>

            <?php // --- MENU ADMINISTRADOR --- (Baseado na Imagem 1)
            if ($nivel_usuario == 'Administrador'): ?>
                <li class="nav-item active">
                    <a href="index.php?page=dashboard"><i class="bi bi-grid-fill"></i> Dashboard Geral</a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=monitor"><i class="bi bi-display"></i> Monitor de Processos</a>
                </li>

                <li class="nav-item has-submenu">
                    <a href="#"><i class="bi bi-briefcase-fill"></i> Gerenciamento <i class="bi bi-chevron-down arrow"></i></a>
                    <ul class="submenu">
                        <li><a href="index.php?page=mantenedores&action=view"><i class="bi bi-person-vcard-fill"></i> Mantenedores</a></li>
                        <li><a href="index.php?page=produtos_servicos&action=view"><i class="bi bi-shield-check"></i> Serviços</a></li>
                    </ul>
                </li>

                <li class="nav-item has-submenu">
                    <a href="#"><i class="bi bi-gear-fill"></i> Sistema <i class="bi bi-chevron-down arrow"></i></a>
                    <ul class="submenu">
                        <li><a href="index.php?page=logs"><i class="bi bi-file-earmark-text-fill"></i> Logs do Sistema</a></li>
                        <li><a href="index.php?page=configuracoes"><i class="bi bi-sliders"></i> Configurações</a></li>
                    </ul>
                </li>

            <?php // --- MENU GESTOR --- (Atualizado conforme a imagem)
            elseif ($nivel_usuario == 'Gestor'): ?>
                <li class="nav-item">
                    <a href="index.php?page=dashboard"><i class="bi bi-grid-fill"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=produtos_servicos&action=view"><i class="bi bi-shield-check"></i> Serviços (Agendar)</a>
                </li>

                <li class="nav-item has-submenu open"> <a href="#"><i class="bi bi-calendar-check"></i> Agendamentos <i class="bi bi-chevron-down arrow"></i></a>
                    <ul class="submenu" style="max-height: 500px;">
                        <li><a href="index.php?page=agendamentos&action=anexar">
                                <i class="bi bi-camera-fill"></i> Anexar Fotos (Fazer)
                            </a></li>

                        <li><a href="index.php?page=agendamentos&action=aguardando">
                                <i class="bi bi-hourglass-split"></i> Aguardando Validação
                            </a></li>

                        <li><a href="index.php?page=agendamentos&action=rejeitados">
                                <i class="bi bi-x-circle-fill"></i> Serviços Rejeitados
                            </a></li>

                    </ul>
                </li>

            <?php // --- MENU SUCESSO DO CLIENTE --- (Baseado na Imagem 4)
            elseif ($nivel_usuario == 'Sucesso do Cliente'): ?>
                <li class="nav-item">
                    <a href="index.php?page=dashboard"><i class="bi bi-grid-fill"></i> Dashboard</a>
                </li>

                <li class="nav-item has-submenu open"> <a href="#"><i class="bi bi-calendar-check"></i> Agendamentos <i class="bi bi-chevron-down arrow"></i></a>
                    <ul class="submenu" style="max-height: 500px;">
                        <li><a href="index.php?page=agendamentos&action=confirmar">
                                <i class="bi bi-check-circle-fill"></i> Confirmar Agendamentos
                            </a></li>

                        <li><a href="index.php?page=agendamentos&action=validar">
                                <i class="bi bi-image-fill"></i> Validação de Serviços
                            </a></li>

                        <li><a href="index.php?page=agendamentos&action=enviar_email">
                                <i class="bi bi-send-fill"></i> Enviar E-mails
                            </a></li>

                    </ul>
                </li>


            <?php // --- MENU SETOR DE COBRANÇA --- (Baseado na Imagem 5)
            elseif ($nivel_usuario == 'Setor de Cobrança'): ?>
                <li class="nav-item active">
                    <a href="index.php?page=dashboard"><i class="bi bi-grid-fill"></i> Dashboard</a>
                </li>
                <li class="nav-item has-submenu">
                    <a href="#"><i class="bi bi-cash-coin"></i> Cobranças <i class="bi bi-chevron-down arrow"></i></a>
                    <ul class="submenu">
                        <li><a href="index.php?page=cobrancas&action=pendentes"><i class="bi bi-hourglass-split"></i> Cobranças Pendentes</a></li>
                    </ul>
                </li>

            <?php // --- MENU VENDEDOR --- (Baseado na Imagem 3)
            elseif ($nivel_usuario == 'Vendedor'): ?>
                <li class="nav-item active">
                    <a href="index.php?page=dashboard"><i class="bi bi-grid-fill"></i> Dashboard</a>
                </li>
                <li class="nav-item has-submenu">
                    <a href="#"><i class="bi bi-journal-check"></i> Vendas <i class="bi bi-chevron-down arrow"></i></a>
                    <ul class="submenu">
                        <li><a href="index.php?page=vendas&action=view"><i class="bi bi-list-task"></i> Minhas Vendas</a></li>
                    </ul>
                </li>
            <?php endif; ?>

        </ul>
    </nav>

    <div class="sidebar-footer">
        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item">
                    <a href="logout.php"><i class="bi bi-box-arrow-left"></i> Sair</a>
                </li>
            </ul>
        </nav>
    </div>
</aside>