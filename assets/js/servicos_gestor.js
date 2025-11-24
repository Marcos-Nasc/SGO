// assets/js/servicos_gestor.js

const modalAgendamento = document.getElementById('modalAgendamento');
const formAgendamento = document.getElementById('formAgendamento');
const btnConfirmarAgendamento = document.getElementById('btnConfirmarAgendamento');

// Campos do formulário
const hiddenVendaId = document.getElementById('agendamento_venda_id');
const hiddenFamilia = document.getElementById('agendamento_familia');
const inputData = document.getElementById('agendamento_data');
const inputObs = document.getElementById('agendamento_obs');
const modalTitulo = document.getElementById('modalAgendamentoTitulo');
const alertaFamiliaSim = document.getElementById('alertaFamiliaComparece');
const alertaFamiliaNao = document.getElementById('alertaFamiliaNaoComparece');

let isSubmittingAgendamento = false; // Trava de segurança

function abrirModalAgendamento(vendaId, os, familiaFlag) {
    // 1. Preenche os dados no formulário
    hiddenVendaId.value = vendaId;
    hiddenFamilia.value = familiaFlag;
    modalTitulo.innerText = "Agendar Serviço - OS " + os;

    // Define o valor padrão do datetime-local (ex: 3 dias a partir de agora)
    const dataDefault = new Date(Date.now() + 3 * 24 * 60 * 60 * 1000);
    // Formata para 'YYYY-MM-DDTHH:MM' (removendo segundos e timezone)
    inputData.value = dataDefault.toISOString().slice(0, 16);

    inputObs.value = '';

    // 2. Mostra o alerta correto
    if (familiaFlag == 1) {
        alertaFamiliaSim.style.display = 'block';
        alertaFamiliaNao.style.display = 'none';
    } else {
        alertaFamiliaSim.style.display = 'none';
        alertaFamiliaNao.style.display = 'block';
    }

    // 3. Reseta o botão e a trava
    btnConfirmarAgendamento.disabled = false;
    btnConfirmarAgendamento.innerHTML = 'Confirmar Agendamento';
    isSubmittingAgendamento = false;

    // 4. Abre o modal
    modalAgendamento.classList.add('active');
}

function fecharModalAgendamento() {
    modalAgendamento.classList.remove('active');
}

function salvarAgendamento() {
    // 1. Verifica a trava de segurança
    if (isSubmittingAgendamento) return;

    // 2. Validação simples (Substituindo alert)
    if (inputData.value === '') {
        showNotification('warning', 'Por favor, selecione uma data e hora para o agendamento.', 4000);
        return;
    }

    // 3. Ativa a trava
    isSubmittingAgendamento = true;
    btnConfirmarAgendamento.disabled = true;
    btnConfirmarAgendamento.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Salvando...';

    // 4. Prepara e envia os dados
    const formData = new FormData(formAgendamento);
    formData.append('acao', 'agendar_servico');

    fetch('pages/produtos_servicos/actions.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'sucesso') {
                // CORREÇÃO [1]: Usar 'sucesso' (status do PHP/mapeamento)
                // CORREÇÃO [2]: Tempo de duração alterado para 2000ms
                showNotification('sucesso', data.msg, 2000);

                // CORREÇÃO [3]: Ajustar delay para garantir a exibição da notificação antes do reload
                setTimeout(() => {
                    location.reload();
                }, 2000); // 500ms é um delay seguro para iniciar o reload após a notificação aparecer.

            } else {
                showNotification('erro', 'Falha ao agendar: ' + data.msg, 5000); // Alterado 'error' para 'erro' para consistência

                // Destrava em caso de erro
                isSubmittingAgendamento = false;
                btnConfirmarAgendamento.disabled = false;
                btnConfirmarAgendamento.innerHTML = 'Confirmar Agendamento';
            }
        })
        .catch(err => {
            console.error('Fetch Error:', err);
            showNotification('error', 'Erro de comunicação com o servidor. Tente novamente.', 5000); // Notificação de Erro Geral

            isSubmittingAgendamento = false;
            btnConfirmarAgendamento.disabled = false;
            btnConfirmarAgendamento.innerHTML = 'Confirmar Agendamento';
        });
}