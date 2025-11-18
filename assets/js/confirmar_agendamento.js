// assets/js/confirmar_agendamento.js

const modalConfirmacao = document.getElementById('modalConfirmacao');
const btnConfirmarFinal = document.getElementById('btnConfirmarFinal');
const btnReagendar = document.getElementById('btnReagendar');
let currentAgendamentoId = null;
let isSubmittingConfirm = false;

function abrirModalConfirmacao(id, os, cliente, telefone, servico, data) {
    currentAgendamentoId = id;
    
    // Preenche os dados visuais
    document.getElementById('modal-cliente').innerText = cliente;
    document.getElementById('modal-telefone').innerText = telefone;
    document.getElementById('modal-servico').innerText = servico;
    document.getElementById('modal-data').innerText = data;
    document.getElementById('obsConfirmacao').value = ''; // Limpa obs
    
    // Reseta botões
    btnConfirmarFinal.disabled = false;
    btnConfirmarFinal.innerHTML = '<i class="bi bi-check-lg"></i> Confirmar Presença';
    btnReagendar.disabled = false;
    
    isSubmittingConfirm = false;
    modalConfirmacao.classList.add('active');
}

function fecharModalConfirmacao() {
    modalConfirmacao.classList.remove('active');
}

function confirmarAgendamento() {
    if (isSubmittingConfirm) return;
    isSubmittingConfirm = true;
    
    const obs = document.getElementById('obsConfirmacao').value;
    btnConfirmarFinal.disabled = true;
    btnConfirmarFinal.innerHTML = 'Salvando...';
    
    enviarAcao('confirmar', obs);
}

function rejeitarAgendamento() {
    if (isSubmittingConfirm) return;
    
    const obs = document.getElementById('obsConfirmacao').value;
    if(obs.trim() === '') {
        alert("Por favor, escreva na observação o motivo do cancelamento.");
        return;
    }
    
    if(!confirm("Tem certeza que o cliente cancelou? Isso voltará para o Gestor.")) return;

    isSubmittingConfirm = true;
    btnReagendar.disabled = true;
    btnReagendar.innerHTML = 'Salvando...';
    
    enviarAcao('rejeitar', obs);
}

function enviarAcao(acao, obs) {
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
        alert(data.msg);
        if (data.status === 'sucesso') {
            // Remove a linha da tabela sem recarregar
            const row = document.getElementById('row-' + currentAgendamentoId);
            if(row) row.remove();
            fecharModalConfirmacao();
            
            // Se tabela ficar vazia, recarrega para mostrar msg "Nenhum agendamento"
            if(document.querySelectorAll('#tabelaConfirmar tbody tr').length === 0) {
                location.reload();
            }
        } else {
            isSubmittingConfirm = false;
            btnConfirmarFinal.disabled = false;
            btnReagendar.disabled = false;
        }
    })
    .catch(err => {
        alert('Erro de comunicação.');
        isSubmittingConfirm = false;
        btnConfirmarFinal.disabled = false;
        btnReagendar.disabled = false;
    });
}