document.addEventListener('DOMContentLoaded', function() {
    
    // --- LÓGICA DO TEMA (ESCURO/CLARO) ---
    
    const themeToggle = document.getElementById('themeToggle');
    const htmlElement = document.documentElement; // O <html>

    // 1. Verifica preferência salva no localStorage
    if (localStorage.getItem('theme') === 'dark') {
        htmlElement.classList.add('dark-mode');
    } 
    // 2. Se não houver, verifica preferência do Sistema Operacional
    else if (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        htmlElement.classList.add('dark-mode');
    }

    // 3. Adiciona evento ao botão de trocar tema
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            htmlElement.classList.toggle('dark-mode');
            
            // Salva a preferência no localStorage
            if (htmlElement.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
        });
    }

    // --- LÓGICA DO MENU MOBILE ---

    const sidebarToggle = document.getElementById('sidebarToggle');
    const bodyElement = document.body;

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            bodyElement.classList.toggle('sidebar-open');
        });
    }
    
    // --- LÓGICA DOS SUBMENUS (DROPDOWN) ---
    
    const submenuLinks = document.querySelectorAll('.sidebar-nav .has-submenu > a');
    
    submenuLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            // Previne a navegação se for só para abrir o menu
            // (idealmente, o link '#' não navegaria, mas garantimos)
            event.preventDefault(); 
            
            const parentLi = this.parentElement;
            parentLi.classList.toggle('open');
            
            // Fecha outros submenus abertos
            document.querySelectorAll('.sidebar-nav .nav-item.open').forEach(openItem => {
                if (openItem !== parentLi) {
                    openItem.classList.remove('open');
                }
            });
        });
    });

});