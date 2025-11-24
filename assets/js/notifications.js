// assets/js/notifications.js

/**
 * Exibe uma notificação Toast personalizada no canto superior direito.
 * @param {string} status - O status retornado pelo PHP ('sucesso', 'erro', ou 'warning').
 * @param {string} message - A mensagem a ser exibida.
 * @param {number} duration - Duração em milissegundos. (Aumentada para 5000ms padrão)
 */
function showNotification(status, message, duration = 5000) {
    const container = document.getElementById('notification-container');
    if (!container) return; 

    // 1. Mapeamento de Status (Converte o vocabulário PHP para a classe CSS/JS)
    const statusMapping = {
        'sucesso': 'success',
        'erro': 'error',
        'warning': 'warning',
        'default': 'error' // Fallback
    };

    const classStatus = statusMapping[status] || statusMapping['default'];

    const titleMap = {
        'success': 'Sucesso!',
        'error': 'Erro na Operação!',
        'warning': 'Atenção'
    };

    const title = titleMap[classStatus] || 'Notificação';

    const toast = document.createElement('div');
    toast.className = `custom-toast ${classStatus}`; 
    toast.innerHTML = `
        <div class="toast-title">${title}</div>
        <div class="toast-message">${message}</div>
    `;

    container.prepend(toast); // Adiciona ao topo

    // Força o reflow para aplicar a transição
    setTimeout(() => {
        toast.classList.add('show');
    }, 10); 

    // Remove o toast após a duração
    setTimeout(() => {
        toast.classList.remove('show');
        toast.classList.add('hide');
        // Remove completamente do DOM após a transição
        toast.addEventListener('transitionend', () => toast.remove());
    }, duration);
}