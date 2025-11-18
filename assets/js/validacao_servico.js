// assets/js/validacao_servico.js

let isSubmittingValidation = false; // Trava de segurança

/**
 * Ação de Invalidar (Botão Vermelho)
 */
function invalidarServico(agendamentoId) {
    if (isSubmittingValidation) return;
    
    const obs = document.getElementById('obsValidacao').value;
    if (obs.trim() === '') {
        alert('Para invalidar, a observação é obrigatória. (O Gestor precisa saber o motivo).');
        return;
    }

    if (!confirm('Tem certeza que deseja INVALIDAR este serviço? Ele voltará para o Gestor corrigir.')) {
        return;
    }

    const btn = document.getElementById('btnInvalidar');
    setLoading(true, btn, 'Invalidando...');

    const formData = new FormData();
    formData.append('acao', 'invalidar_servico');
    formData.append('agendamento_id', agendamentoId);
    formData.append('observacao', obs);

    fetch('pages/agendamentos/actions_validacao.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => handleResponse(data, btn, 'Invalidar', 'invalidar_servico'))
    .catch(err => handleError(err, btn, 'Invalidar'));
}

/**
 * Ação de Validar (Botão Azul)
 */
function validarFotos(agendamentoId) {
    if (isSubmittingValidation) return;
    if (!confirm('Validar as fotos internamente? (O e-mail para o cliente NÃO será enviado agora).')) {
        return;
    }

    const btn = document.getElementById('btnValidar');
    setLoading(true, btn, 'Validando...');

    const formData = new FormData();
    formData.append('acao', 'validar_servico');
    formData.append('agendamento_id', agendamentoId);
    formData.append('observacao', document.getElementById('obsValidacao').value);

    fetch('pages/agendamentos/actions_validacao.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => handleResponse(data, btn, 'Validar Fotos', 'validar_servico')) // Passa a ação
    .catch(err => handleError(err, btn, 'Validar Fotos'));
}

/**
 * Ação de Enviar E-mail (Chamada pelo Modal)
 */
function executarEnvioEmail(botao) {
    if (isSubmittingValidation) return;
    
    // Pega o ID que armazenamos no atributo data-
    const agendamentoId = botao.getAttribute('data-agendamento-id');
    
    setLoading(true, botao, 'Enviando...'); // Usa a função de loading

    const formData = new FormData();
    formData.append('acao', 'enviar_email_cliente');
    formData.append('agendamento_id', agendamentoId);

    fetch('pages/agendamentos/actions_validacao.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        fecharModalConfirmarEmail(); // Fecha o modal primeiro
        handleResponse(data, botao, 'Confirmar Envio', 'enviar_email_cliente');
    })
    .catch(err => {
        fecharModalConfirmarEmail();
        handleError(err, botao, 'Confirmar Envio');
    });
}


// --- Funções Auxiliares ---

function setLoading(isLoading, button, text) {
    isSubmittingValidation = isLoading;
    // Desabilita todos os botões na área de validação
    document.querySelectorAll('.btn-validation').forEach(btn => {
        btn.disabled = isLoading;
    });

    if (isLoading) {
        button.innerHTML = `<i class="bi bi-arrow-clockwise"></i> ${text}`;
    }
}

// Lógica de resposta
function handleResponse(data, button, originalText, acao) {
    alert(data.msg);
    
    if (data.status === 'sucesso') {
        // Se deu certo (Validou ou Invalidou), recarrega a página.
        // Como o SQL agora filtra o status antigo, este item vai sumir 
        // e o próximo da fila vai aparecer automaticamente.
        location.reload(); 
    } else {
        // Se deu erro, reabilita os botões
        isSubmittingValidation = false;
        document.querySelectorAll('.validation-buttons button').forEach(btn => {
            btn.disabled = false;
        });
        button.innerHTML = originalText; 
    }
}

function handleError(err, button, originalText) {
    console.error(err);
    alert('Erro de comunicação. Verifique o console para detalhes.');
    isSubmittingValidation = false;
    // Reabilita tudo
    document.querySelectorAll('.btn-validation').forEach(btn => {
        btn.disabled = false;
    });
    button.innerHTML = originalText;
}

// --- Funções do Modal de Confirmação de E-mail ---
const modalConfirmarEmail = document.getElementById('modalConfirmarEmail');
const btnConfirmarEnvioFinal = document.getElementById('btnConfirmarEnvioFinal');

function abrirModalConfirmarEmail(agendamentoId, clienteNome, clienteEmail) {
    if (isSubmittingValidation) return;

    // Só abre o modal se ele existir nesta página
    if (modalConfirmarEmail) { 
        document.getElementById('email-confirm-cliente').innerText = clienteNome;
        document.getElementById('email-confirm-email').innerText = clienteEmail;
        btnConfirmarEnvioFinal.setAttribute('data-agendamento-id', agendamentoId);
        modalConfirmarEmail.classList.add('active');
    }
}

function fecharModalConfirmarEmail() {
    if (modalConfirmarEmail) {
        modalConfirmarEmail.classList.remove('active');
    }
}