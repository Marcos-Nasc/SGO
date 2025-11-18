// --- Funções do Modal de Detalhes ---
const modalCobranca = document.getElementById('modalCobranca');
const btnAprovarModal = document.getElementById('btnAprovarModal');

function abrirModalDetalhes(botao) {
    // Pega todos os dados dos atributos data-*
    const os = botao.getAttribute('data-os');
    const nf = botao.getAttribute('data-nf') || 'N/A';
    const previsao = botao.getAttribute('data-previsao');
    const isHoje = botao.getAttribute('data-is-hoje') === '1';

    // Armazena o ID no botão "Aprovar" do modal
    const vendaId = botao.getAttribute('data-venda-id');
    btnAprovarModal.setAttribute('onclick', `aprovarDoModal(this, ${vendaId})`);

    // Cartão 1: Cliente
    document.getElementById('modalTituloOS').innerText = "Detalhes da Venda - OS " + os;
    document.getElementById('modalMantenedor').innerText = botao.getAttribute('data-mantenedor');
    document.getElementById('modalTelefone').innerText = botao.getAttribute('data-telefone');
    document.getElementById('modalEmail').innerText = botao.getAttribute('data-email');
    document.getElementById('modalContrato').innerText = botao.getAttribute('data-contrato');

    // Cartão 2: Serviço e Datas
    document.getElementById('modalServico').innerText = botao.getAttribute('data-servico');
    document.getElementById('modalOsNf').innerText = `OS: ${os} / NF: ${nf}`;
    document.getElementById('modalDataVenda').innerText = botao.getAttribute('data-data-venda');
    document.getElementById('modalDataPrevisao').innerText = previsao;
    
    // Destaque visual para data de hoje/vencido no modal
    const itemPrevisao = document.getElementById('itemDataPrevisao');
    if (isHoje) {
        itemPrevisao.classList.add('vencimento-hoje');
    } else {
        itemPrevisao.classList.remove('vencimento-hoje');
    }

    // Cartão 3: Financeiro
    document.getElementById('modalValorFinal').innerText = botao.getAttribute('data-valor-final');
    document.getElementById('modalCondicao').innerText = botao.getAttribute('data-condicao');
    document.getElementById('modalValorEntrada').innerText = botao.getAttribute('data-valor-entrada');
    document.getElementById('modalFamilia').innerText = botao.getAttribute('data-familia');
    document.getElementById('modalParcelas').innerText = botao.getAttribute('data-parcelas') + 'x';
    document.getElementById('modalValorParcela').innerText = botao.getAttribute('data-valor-parcela');
    
    // Abre o modal
    modalCobranca.classList.add('active');
}

function fecharModalDetalhes() {
    modalCobranca.classList.remove('active');
    // Garante que o botão do modal seja reativado ao fechar
    btnAprovarModal.disabled = false;
    btnAprovarModal.innerHTML = '<i class="bi bi-check-circle-fill"></i> Aprovar Venda';
}

// --- Função de Aprovar Venda (Genérica) ---
function aprovarCobranca(botao, vendaId) {
    if (!confirm('Tem certeza que deseja aprovar esta cobrança? A venda seguirá para o agendamento.')) {
        return;
    }

    // Desabilita o botão e mostra feedback
    botao.disabled = true;
    botao.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Aprovando...';

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
            // Sucesso: Remove a linha da tabela com uma animação
            const tr = botao.closest('tr');
            if (tr) { // Se o botão estava na tabela
                tr.style.transition = 'opacity 0.5s ease';
                tr.style.opacity = '0';
                setTimeout(() => {
                    tr.remove();
                }, 500);
            }
            return true;
        } else {
            // Erro: Reabilita o botão
            alert('Erro: ' + data.msg);
            botao.disabled = false;
            botao.innerHTML = '<i class="bi bi-check-circle-fill"></i> Aprovar';
            return false;
        }
    })
    .catch(err => {
        alert('Erro de comunicação. Tente novamente.');
        botao.disabled = false;
        botao.innerHTML = '<i class="bi bi-check-circle-fill"></i> Aprovar';
        return false;
    });
}

// --- Função Específica para o Botão do Modal ---
async function aprovarDoModal(botao, vendaId) {
    // Chama a função genérica e espera a resposta (true/false)
    const sucesso = await aprovarCobranca(botao, vendaId);
    
    if (sucesso) {
        fecharModalDetalhes();
        // A linha da tabela já foi removida pela 'aprovarCobranca'
        // Mas precisamos encontrar o botão na tabela e removê-lo se o modal fechar
        // (A forma mais fácil é recarregar a página, ou deixar a 'aprovarCobranca' cuidar disso)
        
        // Vamos apenas recarregar a tabela para garantir
        location.reload(); 
    }
    // Se falhar, a 'aprovarCobranca' já reabilitou o botão
}