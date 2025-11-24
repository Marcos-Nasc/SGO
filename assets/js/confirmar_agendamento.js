// assets/js/confirmar_agendamento.js

// Variáveis de estado
let currentAgendamentoId = null;
let isSubmittingConfirm = false;

// --- FUNÇÕES DE INTERFACE ---

// Definimos explicitamente no window para garantir que o HTML encontre a função
window.abrirModalConfirmacao = function(id, os, cliente, telefone, servico, data) {
    // 1. Busca o modal e os elementos
    const modal = document.getElementById('modalConfirmacao');
    if (!modal) {
        console.error("ERRO: Modal 'modalConfirmacao' não encontrado no HTML.");
        alert("Erro interno: Modal de confirmação não foi carregado.");
        return;
    }

    currentAgendamentoId = id;
    
    // 2. Atualiza os textos
    // Tenta achar o título pelo H3 dentro do header
    const tituloModal = modal.querySelector('.modal-header h3');
    if (tituloModal) {
        tituloModal.innerText = (os && os !== 'null') ? `Confirmar Agendamento - OS ${os}` : "Confirmar Agendamento";
    }

    setElementText('modal-cliente', cliente);
    setElementText('modal-telefone', telefone);
    setElementText('modal-servico', servico);
    setElementText('modal-data', data);
    
    // Limpa observação
    const obsInput = document.getElementById('obsConfirmacao');
    if(obsInput) obsInput.value = ''; 
    
    // 3. Reseta botões
    isSubmittingConfirm = false;
    const btnConfirmar = document.getElementById('btnConfirmarFinal');
    const btnReagendar = document.getElementById('btnReagendar');

    if(btnConfirmar) {
        btnConfirmar.disabled = false;
        btnConfirmar.innerHTML = '<i class="bi bi-check-lg"></i> Confirmar Presença';
    }
    if(btnReagendar) {
        btnReagendar.disabled = false;
        btnReagendar.innerHTML = '<i class="bi bi-x-circle"></i> Cliente Cancelou';
    }
    
    // 4. Abre o modal
    modal.classList.add('active');
    console.log("Modal aberto para o ID: " + id); // Debug no console
}

window.fecharModalConfirmacao = function() {
    const modal = document.getElementById('modalConfirmacao');
    if(modal) modal.classList.remove('active');
}

// --- AÇÕES DE ENVIO (Chamadas pelos botões do Modal) ---

window.confirmarAgendamento = function() {
    if (isSubmittingConfirm) return;
    
    const btn = document.getElementById('btnConfirmarFinal');
    const obs = document.getElementById('obsConfirmacao').value;
    
    isSubmittingConfirm = true;
    setLoading(btn, 'Salvando...', true);
    
    enviarAcao('confirmar', obs, btn, '<i class="bi bi-check-lg"></i> Confirmar Presença');
}

window.rejeitarAgendamento = function() {
    if (isSubmittingConfirm) return;
    
    const btn = document.getElementById('btnReagendar');
    const obs = document.getElementById('obsConfirmacao').value;
    
    if(obs.trim() === '') {
        showNotification('warning', "Motivo de cancelamento obrigatório para rejeitar.", 4000);
        return;
    }
    
    // Verifica se o modal global existe, senão usa confirm nativo
    if (typeof abrirGlobalConfirm === 'function') {
        abrirGlobalConfirm(
            "Tem certeza que o cliente cancelou? Isso voltará o status da OS para o Gestor.",
            "Sim, Cancelar Agendamento",
            () => {
                isSubmittingConfirm = true;
                setLoading(btn, 'Salvando...', true);
                enviarAcao('rejeitar', obs, btn, '<i class="bi bi-x-circle"></i> Cliente Cancelou');
            },
            "Confirmar Cancelamento"
        );
    } else {
        // Fallback caso o modal global falhe
        if(confirm("O cliente cancelou?")) {
            isSubmittingConfirm = true;
            setLoading(btn, 'Salvando...', true);
            enviarAcao('rejeitar', obs, btn, '<i class="bi bi-x-circle"></i> Cliente Cancelou');
        }
    }
}

// --- FUNÇÕES AUXILIARES ---

function enviarAcao(acao, obs, button, originalText) {
    const formData = new FormData();
    formData.append('acao', acao);
    formData.append('agendamento_id', currentAgendamentoId);
    formData.append('observacao', obs);
    
    fetch('pages/agendamentos/actions_confirmar.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'sucesso') {
            showNotification('sucesso', data.msg, 2500);
            const row = document.getElementById('row-' + currentAgendamentoId);
            if(row) row.remove();
            
            fecharModalConfirmacao();
            
            if(document.querySelectorAll('#tabelaConfirmar tbody tr').length <= 1) {
                 setTimeout(() => { location.reload(); }, 2000);
            }
        } else {
            showNotification('erro', 'Falha: ' + data.msg, 5000);
            isSubmittingConfirm = false;
            setLoading(button, originalText, false);
        }
    })
    .catch(err => {
        console.error(err);
        showNotification('erro', 'Erro de comunicação.', 5000);
        isSubmittingConfirm = false;
        setLoading(button, originalText, false);
    });
}

function setLoading(button, text, isLoading) {
    if(!button) return;
    button.disabled = isLoading;
    button.innerHTML = isLoading ? `<i class="bi bi-arrow-clockwise"></i> ${text}` : text;
}

function setElementText(id, text) {
    const el = document.getElementById(id);
    if(el) el.innerText = text;
}