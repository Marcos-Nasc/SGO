// assets/js/cobranca.js

// --- 1. VARIÁVEIS DE CONTROLE E MODAIS DE DETALHES ---
// Mantemos apenas as referências ao modal de detalhes (modalCobranca)
const modalCobranca = document.getElementById('modalCobranca');
const btnAprovarModal = document.getElementById('btnAprovarModal');

// As variáveis modalConfirmacaoAprovacao e btnConfirmarAprovacaoFinal foram removidas
// pois serão gerenciadas pelo script global.

// ... (Restante das funções abrirModalDetalhes e fecharModalDetalhes) ...


// --- 2. FUNÇÃO DE APROVAR VENDA (GENÉRICA) ---
// Função de execução que será passada ao modal global
function aprovarCobranca(vendaId) {
    
    // 1. Identifica o botão original da tabela para feedback visual (pode ser o btn-approve ou o btn-details)
    const botaoOriginal = document.querySelector(`button.btn-approve[data-venda-id="${vendaId}"]`);
    
    // Se o modal de detalhes estava ativo, feche-o (pode ser necessário se o usuário aprovar direto do modal de detalhes)
    if (modalCobranca && modalCobranca.classList.contains('active')) {
        fecharModalDetalhes();
    }
    
    // 2. Desabilita o botão original e mostra feedback (caso exista)
    if (botaoOriginal) {
        botaoOriginal.disabled = true;
        botaoOriginal.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Aprovando...';
    }

    const formData = new FormData();
    formData.append('acao', 'aprovar_cobranca');
    formData.append('venda_id', vendaId);

    fetch('pages/cobrancas/actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        
        if (data.status === 'sucesso') {
            // CORREÇÃO: Tempo de notificação para 2000ms
            showNotification(data.status, data.msg, 2000); 
            
            // Sucesso: Remove a linha da tabela com uma animação
            const tr = botaoOriginal ? botaoOriginal.closest('tr') : document.querySelector(`#tabelaCobrancas tr[data-venda-id="${vendaId}"]`);
            
            if (tr) { 
                tr.style.transition = 'opacity 0.5s ease';
                tr.style.opacity = '0';
                
                // CORREÇÃO: Ajuste do delay para 500ms antes de iniciar o reload/remoção
                setTimeout(() => {
                    tr.remove();
                    // Recarrega se não houver mais linhas (para mostrar "tabela vazia")
                    if(document.querySelectorAll('#tabelaCobrancas tbody tr').length <= 0) {
                        location.reload(); 
                    }
                }, 2000); 
            }
        } else {
            // Erro: Reabilita o botão
            showNotification(data.status, 'Falha na Aprovação: ' + data.msg, 4000); 
            
            if (botaoOriginal) {
                botaoOriginal.disabled = false;
                botaoOriginal.innerHTML = '<i class="bi bi-check-circle-fill"></i> Aprovar';
            }
        }
    })
    .catch(err => {
        showNotification('erro', 'Erro de comunicação. O servidor não respondeu.', 4000); 
        
        if (botaoOriginal) {
            botaoOriginal.disabled = false;
            botaoOriginal.innerHTML = '<i class="bi bi-check-circle-fill"></i> Aprovar';
        }
    });
}


// --- 3. FUNÇÃO DE FLUXO (ABERTURA DO MODAL GLOBAL) ---

// Chamada pelo botão "Aprovar Venda" dentro do Modal de Detalhes
function aprovarDoModal(botao) {
    // Pega os dados do botão de detalhes
    const vendaId = botao.getAttribute('data-venda-id');
    const os = document.getElementById('modalTituloOS').innerText.replace('Detalhes da Venda - OS ', '');

    // 1. Fecha o modal de detalhes
    fecharModalDetalhes(); 
    
    // 2. Abre o modal de confirmação global
    abrirConfirmacaoAprovacao(vendaId, os);
}

// Chamada pelo botão "Aprovar" direto da Tabela (onclick)
function abrirConfirmacaoAprovacao(vendaId, os) {
    
    const mensagem = `Você tem certeza que deseja aprovar a cobrança da OS <strong>${os}</strong>?`;
    const acaoLabel = "Sim, Aprovar Cobrança";
    const titulo = `Aprovar Cobrança OS ${os}`;
    
    // Chamamos a função global com o callback
    // O callback é a função aprovarCobranca(vendaId)
    abrirGlobalConfirm(
        mensagem,
        acaoLabel,
        () => aprovarCobranca(vendaId), // Função a ser executada na confirmação
        titulo
    );
}

// --- 4. ADICIONANDO LISTENERS ---
document.addEventListener('DOMContentLoaded', () => {
    // 1. Altera a função de clique dos botões de detalhes/aprovar na tabela
    // O botão de detalhes chama abrirModalDetalhes, que por sua vez chama aprovarDoModal.
    
    // 2. Garante que os botões de Aprovar da Tabela chamem a função correta
    document.querySelectorAll('.widget-table .btn-approve').forEach(btn => {
        const vendaId = btn.getAttribute('data-venda-id');
        const os = btn.getAttribute('data-os');
        
        // Se o botão da tabela for clicado, ele abre diretamente o modal global.
        btn.setAttribute('onclick', `abrirConfirmacaoAprovacao(${vendaId}, '${os}')`);
    });
});