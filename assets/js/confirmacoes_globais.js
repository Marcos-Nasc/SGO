// assets/js/confirmacoes_globais.js

const globalConfirmModal = document.getElementById('globalConfirmModal');
const btnGlobalConfirmAction = document.getElementById('btnGlobalConfirmAction');

let confirmCallback = null; // Função a ser executada na confirmação

/**
 * Abre o modal de confirmação global.
 * @param {string} message - Mensagem principal de confirmação.
 * @param {string} actionLabel - Texto do botão de ação (ex: "Aprovar").
 * @param {function} callback - Função que será executada se o usuário confirmar.
 * @param {string} [title='Confirmar Ação'] - Título do modal.
 */
window.abrirGlobalConfirm = function(message, actionLabel, callback, title = 'Confirmar Ação') {
    if (!globalConfirmModal || !btnGlobalConfirmAction) return;

    // 1. Define os conteúdos
    document.getElementById('globalConfirmTitle').innerText = title;
    document.getElementById('globalConfirmMessage').innerHTML = message;
    btnGlobalConfirmAction.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${actionLabel}`;

    // 2. Armazena a função de callback e reativa o botão
    confirmCallback = callback;
    btnGlobalConfirmAction.disabled = false;
    
    // 3. Abre o modal
    globalConfirmModal.classList.add('active');
}

/**
 * Fecha o modal.
 */
window.fecharGlobalConfirm = function() {
    if (globalConfirmModal) {
        globalConfirmModal.classList.remove('active');
        confirmCallback = null; // Limpa o callback para segurança
    }
}

// Listener para o botão de Ação
if (btnGlobalConfirmAction) {
    btnGlobalConfirmAction.addEventListener('click', function() {
        // Verificamos se existe callback
        if (confirmCallback) {
            // 1. Travamos o botão para evitar duplo clique
            this.disabled = true; 
            
            // 2. IMPORTANTE: Salvamos a função em uma variável local
            // pois o fecharGlobalConfirm() vai limpar a variável global 'confirmCallback'
            const acaoParaExecutar = confirmCallback;

            // 3. Fecha o modal (isso seta confirmCallback = null)
            fecharGlobalConfirm(); 
            
            // 4. Executa a função que salvamos localmente
            if (typeof acaoParaExecutar === 'function') {
                acaoParaExecutar();
            }
        }
    });
}