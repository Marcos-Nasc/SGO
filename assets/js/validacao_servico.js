// assets/js/validacao_servico.js

let isSubmittingValidation = false; // Trava de segurança

/**
 * Ação de Invalidar (Botão Vermelho)
 */
function invalidarServico(agendamentoId) {
    if (isSubmittingValidation) return;
    
    const obs = document.getElementById('obsValidacao').value;
    
    // 1. Validação de campo
    if (obs.trim() === '') {
        showNotification('warning', 'Para invalidar, a observação é obrigatória.', 4000);
        return;
    }

    // 2. SUBSTITUIÇÃO: confirm() nativo -> abrirGlobalConfirm()
    if (typeof abrirGlobalConfirm === 'function') {
        abrirGlobalConfirm(
            "Tem certeza que deseja <strong>INVALIDAR</strong> este serviço?<br>Ele voltará para o Gestor corrigir.",
            "Sim, Invalidar",
            () => processarInvalidacao(agendamentoId, obs), // Callback
            "Invalidar Serviço"
        );
    } else {
        // Fallback caso o modal não carregue
        if (confirm('Tem certeza que deseja INVALIDAR este serviço?')) {
            processarInvalidacao(agendamentoId, obs);
        }
    }
}

// Função auxiliar para executar a lógica após confirmação
function processarInvalidacao(agendamentoId, obs) {
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
    
    const obs = document.getElementById('obsValidacao').value;

    // SUBSTITUIÇÃO: confirm() nativo -> abrirGlobalConfirm()
    if (typeof abrirGlobalConfirm === 'function') {
        abrirGlobalConfirm(
            "Deseja validar as fotos internamente?<br><small>(O e-mail para o cliente NÃO será enviado agora).</small>",
            "Sim, Validar",
            () => processarValidacao(agendamentoId, obs), // Callback
            "Validar Fotos"
        );
    } else {
        if (confirm('Validar as fotos internamente?')) {
            processarValidacao(agendamentoId, obs);
        }
    }
}

// Função auxiliar para executar a lógica após confirmação
function processarValidacao(agendamentoId, obs) {
    const btn = document.getElementById('btnValidar');
    setLoading(true, btn, 'Validando...');

    const formData = new FormData();
    formData.append('acao', 'validar_servico');
    formData.append('agendamento_id', agendamentoId);
    formData.append('observacao', obs);

    fetch('pages/agendamentos/actions_validacao.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => handleResponse(data, btn, 'Validar Fotos', 'validar_servico'))
    .catch(err => handleError(err, btn, 'Validar Fotos'));
}

/**
 * Ação de Enviar E-mail (Chamada pelo Modal Específico de Email)
 */
function executarEnvioEmail(botao) {
    if (isSubmittingValidation) return;
    
    // Pega o ID que armazenamos no atributo data-
    const agendamentoId = botao.getAttribute('data-agendamento-id');
    
    setLoading(true, botao, 'Enviando...'); 

    const formData = new FormData();
    formData.append('acao', 'enviar_email_cliente');
    formData.append('agendamento_id', agendamentoId);

    fetch('pages/agendamentos/actions_validacao.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        fecharModalConfirmarEmail(); 
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
    // Exibe notificação
    showNotification(data.status, data.msg, 2000); 
    
    if (data.status === 'sucesso') {
        
        if (acao === 'validar_servico') {
            // --- SUCESSO AO VALIDAR (NÃO RECARREGA, ATUALIZA UI) ---
            isSubmittingValidation = false; 
            
            // 1. Desabilita botões de ação
            const btnInv = document.getElementById('btnInvalidar');
            if(btnInv) btnInv.disabled = true;
            
            const btnVal = document.getElementById('btnValidar');
            if(btnVal) {
                btnVal.disabled = true;
                btnVal.innerHTML = '<i class="bi bi-check-lg"></i> Validado';
            }
            
            // 2. Habilita e destaca o botão de e-mail
            const btnEmail = document.getElementById('btnEnviarEmail');
            if(btnEmail) {
                btnEmail.disabled = false;
                btnEmail.classList.add('highlight-next-step');
            }
            
            // 3. Atualiza o badge de status
            const badge = document.querySelector('.badge');
            if (badge) {
                badge.textContent = 'Fotos Validadas';
                badge.classList.remove('badge-cobranca');
                badge.classList.add('badge-aprovado');
            }
            
        } else {
            // Ação foi 'invalidar' ou 'enviar_email' -> Recarrega
            setTimeout(() => { location.reload(); }, 2000); 
        }
    } else {
        // --- FALHA ---
        isSubmittingValidation = false;
        
        // Reabilita os botões
        document.querySelectorAll('.validation-buttons button').forEach(btn => {
            // Lógica para não reabilitar botão já validado
            if (document.getElementById('btnValidar') && document.getElementById('btnValidar').textContent.includes('Validado')) {
                const btnEmail = document.getElementById('btnEnviarEmail');
                if(btnEmail) btnEmail.disabled = false;
            } else {
                btn.disabled = false;
            }
        });
        button.innerHTML = originalText; 
    }
}

function handleError(err, button, originalText) {
    console.error(err);
    // Padronizado para 'erro' (vermelho)
    showNotification('erro', 'Erro de comunicação com o servidor.', 5000);
    
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

    if (modalConfirmarEmail) { 
        const elNome = document.getElementById('email-confirm-cliente');
        const elEmail = document.getElementById('email-confirm-email');
        
        if(elNome) elNome.innerText = clienteNome;
        if(elEmail) elEmail.innerText = clienteEmail;
        if(btnConfirmarEnvioFinal) btnConfirmarEnvioFinal.setAttribute('data-agendamento-id', agendamentoId);
        
        modalConfirmarEmail.classList.add('active');
    }
}

function fecharModalConfirmarEmail() {
    if (modalConfirmarEmail) {
        modalConfirmarEmail.classList.remove('active');
    }
}