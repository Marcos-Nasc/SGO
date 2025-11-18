<main class="main-content">
    <header class="navbar">
        <div class="navbar-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            
            <h1 class="page-title"><?php echo $page_title ?? 'Dashboard'; ?></h1>
        </div>
        
        <div class="navbar-right">
            <button class="theme-toggle" id="themeToggle">
                <i class="bi bi-moon-fill"></i> <i class="bi bi-sun-fill"></i> </button>
            
            <div class="user-profile">
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                    <span class="user-role"><?php echo htmlspecialchars($_SESSION['usuario_nivel']); ?></span>
                </div>
            </div>
        </div>
    </header>

    <div class="page-content">