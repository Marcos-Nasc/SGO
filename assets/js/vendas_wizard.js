// --- 1. VARIÁVEIS DE CONTROLE E ELEMENTOS (COMPLETO) ---
let isSubmitting = false;
let dataLiberacaoCalculada = null;

const modal = document.getElementById('modalVenda');
const btnNovaVenda = document.getElementById('btnNovaVenda');
let currentStep = 1;
let currentSubStep1 = 'busca';
let currentSubStep2 = 'busca';

// Elementos da Etapa 1
const inputBusca = document.getElementById('buscaMantenedor');
const listaResultados = document.getElementById('listaMantenedores');
const linkCadastrarNovoMantenedor = document.getElementById('linkCadastrarNovoMantenedor');
const btnNext1 = document.getElementById('btnNext1');
const novoMantenedorNome = document.getElementById('novoMantenedorNome');
const novoMantenedorEmail = document.getElementById('novoMantenedorEmail');
const novoMantenedorTelefone = document.getElementById('novoMantenedorTelefone');
const btnSalvarNovoMantenedor = document.getElementById('btnSalvarNovoMantenedor');

// Elementos da Etapa 2
const linkCadastrarNovoContrato = document.getElementById('linkCadastrarNovoContrato');
const btnNext2 = document.getElementById('btnNext2');
const btnSalvarNovoContrato = document.getElementById('btnSalvarNovoContrato');
const novoContratoCemiterio = document.getElementById('novoContratoCemiterio');
const novoContratoNumero = document.getElementById('novoContratoNumero');
const listaContratos = document.getElementById('listaContratos');

// Elementos da Etapa 3
const condicaoPagamento = document.getElementById('condicaoPagamento');
const valorFinal = document.getElementById('valorFinal');
const valorEntrada = document.getElementById('valorEntrada');
const qtdeParcelas = document.getElementById('qtdeParcelas');
const checkFamilia = document.getElementById('checkFamilia');
const wrapperFamilia = document.getElementById('wrapperFamilia');
const divCamposPrazo = document.getElementById('divCamposPrazo');
const resumoParcelas = document.getElementById('resumoParcelas');
const textoCalculoParcela = document.getElementById('textoCalculoParcela');
const alertaEntrada = document.getElementById('alertaEntrada');
const textoAlertaEntrada = document.getElementById('textoAlertaEntrada');
const dataPrevisaoInput = document.getElementById('dataPrevisao');
const btnConcluirVenda = document.getElementById('btnConcluirVenda');
const filtroTipo = document.getElementById('filtroTipo');
const selectProduto = document.getElementById('selectProduto');
const inputNumeroOS = document.getElementById('numero_os');
const stepperProgress = document.getElementById('stepperProgress');


// --- 2. FUNÇÕES PRINCIPAIS DO WIZARD ---

function proximoPasso(step) {
    currentStep = step;
    atualizarUI();
}

function passoAnterior(step) {
    currentStep = step;
    currentSubStep1 = 'busca';
    currentSubStep2 = 'busca';
    atualizarUI();
}

function atualizarUI() {
    // 1. Esconde todos os conteúdos
    document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.stepper .step').forEach(el => el.classList.remove('active', 'completed'));

    // 2. Controle da Barra de Progresso
    if (stepperProgress) {
        if (currentStep === 1) {
            stepperProgress.style.width = '0%';
        } else if (currentStep === 2) {
            stepperProgress.style.width = '50%';
        } else if (currentStep === 3) {
            stepperProgress.style.width = '100%';
        }
    }

    // 3. Lógica dos Círculos e Ícones
    for (let i = 1; i <= 3; i++) {
        const stepEl = document.getElementById(`step-indicator-${i}`);
        if (stepEl) { 
            const circleEl = stepEl.querySelector('.step-circle');
            if (circleEl) {
                circleEl.innerHTML = i; // Reseta para número

                if (i < currentStep) {
                    stepEl.classList.add('completed');
                    circleEl.innerHTML = '<i class="bi bi-check-lg"></i>';
                }
                if (i === currentStep) {
                    stepEl.classList.add('active');
                }
            }
        }
    }

    // 4. Exibir o Conteúdo da Etapa Correta
    if (currentStep === 1) {
        if (currentSubStep1 === 'busca') {
            document.getElementById('step-1-busca-mantenedor').classList.add('active');
        } else {
            document.getElementById('step-1-cadastro-mantenedor').classList.add('active');
        }
    } else if (currentStep === 2) {
        if (currentSubStep2 === 'busca') {
            document.getElementById('step-2-busca-contrato').classList.add('active');
        } else {
            document.getElementById('step-2-cadastro-contrato').classList.add('active');
        }
    } else if (currentStep === 3) {
        document.getElementById(`step-${currentStep}`).classList.add('active');
        validarEtapa3(); // Valida a Etapa 3 assim que ela é exibida
    }
}

function fecharModal() {
    if (modal) modal.classList.remove('active');
    
    // ... (Resto da lógica de reset) ...
    document.getElementById('formVenda').reset();
    inputBusca.value = '';
    listaResultados.innerHTML = '';
    if (listaResultados) listaResultados.style.display = 'none';
    if (listaContratos) listaContratos.innerHTML = '';
    
    document.getElementById('selected_mantenedor_id').value = '';
    document.getElementById('selected_contrato_id').value = '';
    
    if (btnNext1) btnNext1.disabled = true;
    if (btnNext2) btnNext2.disabled = true;
    if (btnSalvarNovoMantenedor) btnSalvarNovoMantenedor.disabled = true;
    if (btnSalvarNovoContrato) btnSalvarNovoContrato.disabled = true;
    if (btnConcluirVenda) btnConcluirVenda.disabled = true;
    if (btnConcluirVenda) btnConcluirVenda.innerHTML = 'Concluir Venda';

    isSubmitting = false; 

    if (novoMantenedorNome) novoMantenedorNome.value = '';
    if (novoMantenedorEmail) novoMantenedorEmail.value = '';
    if (novoMantenedorTelefone) novoMantenedorTelefone.value = '';

    if (checkFamilia) checkFamilia.disabled = true;
    if (checkFamilia) checkFamilia.checked = false;
    if (wrapperFamilia) wrapperFamilia.classList.add('disabled');
    if (divCamposPrazo) divCamposPrazo.style.display = 'none';
    if (alertaEntrada) alertaEntrada.style.display = 'none';
    if (resumoParcelas) resumoParcelas.style.display = 'none';
    
    // Reseta para a visão de busca
    toggleMantenedorView('busca');
    toggleContratoView('busca');
}


// --- 3. FUNÇÕES DAS ETAPAS ---

// ETAPA 1: MANTENEDOR
function mostrarCadastroMantenedor() {
    currentSubStep1 = 'cadastro';
    validarFormNovoMantenedor();
    atualizarUI();
}
function mostrarBuscaMantenedor() {
    currentSubStep1 = 'busca';
    if (btnNext1) btnNext1.disabled = true;
    if (document.getElementById('selected_mantenedor_id')) document.getElementById('selected_mantenedor_id').value = '';
    atualizarUI();
}
function selecionarMantenedor(id, nome) {
    document.getElementById('selected_mantenedor_id').value = id;
    inputBusca.value = nome;
    document.getElementById('labelNomeMantenedor').innerText = nome;
    if (btnNext1) btnNext1.disabled = false;
    if (listaResultados) listaResultados.style.display = 'none';
    carregarContratos(id);
}
function validarFormNovoMantenedor() {
    const nomeValido = novoMantenedorNome?.value.trim().length > 2;
    const emailValido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(novoMantenedorEmail?.value.trim());
    if (btnSalvarNovoMantenedor) btnSalvarNovoMantenedor.disabled = !(nomeValido && emailValido);
}
function salvarNovoMantenedor() {
    if (isSubmitting) return;
    isSubmitting = true;
    if (btnSalvarNovoMantenedor) btnSalvarNovoMantenedor.disabled = true;

    const formData = new FormData();
    formData.append('acao', 'salvar_mantenedor');
    formData.append('nome', novoMantenedorNome?.value);
    formData.append('email', novoMantenedorEmail?.value);
    formData.append('telefone', novoMantenedorTelefone?.value);

    fetch('pages/vendas/actions.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        isSubmitting = false; 
        if (data.status === 'sucesso') {
            showNotification('sucesso', data.msg, 2500); // NOTIFICADO
            selecionarMantenedor(data.id, data.nome);
            proximoPasso(2);
        } else {
            showNotification('erro', 'Erro ao cadastrar: ' + data.msg, 5000); // NOTIFICADO
            if (btnSalvarNovoMantenedor) btnSalvarNovoMantenedor.disabled = false;
        }
    })
    .catch(err => {
        isSubmitting = false; 
        if (btnSalvarNovoMantenedor) btnSalvarNovoMantenedor.disabled = false;
        showNotification('erro', 'Erro na comunicação com o servidor.', 5000); // NOTIFICADO
    });
}

// ETAPA 2: CONTRATO
function mostrarCadastroContrato() {
    currentSubStep2 = 'cadastro';
    const nomeMantenedor = document.getElementById('labelNomeMantenedor')?.innerText;
    if (document.getElementById('labelNomeMantenedorCadastro')) document.getElementById('labelNomeMantenedorCadastro').innerText = nomeMantenedor || '';
    carregarCemiterios();
    validarFormNovoContrato();
    atualizarUI();
}
function mostrarBuscaContrato() {
    currentSubStep2 = 'busca';
    if (btnNext2) btnNext2.disabled = true;
    if (document.getElementById('selected_contrato_id')) document.getElementById('selected_contrato_id').value = '';
    atualizarUI();
}
function carregarContratos(mantenedorId) {
    const formData = new FormData();
    formData.append('acao', 'buscar_contratos');
    formData.append('mantenedor_id', mantenedorId);
    
    if (document.getElementById('selected_contrato_id')) document.getElementById('selected_contrato_id').value = '';
    if (btnNext2) btnNext2.disabled = true;

    fetch('pages/vendas/actions.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (listaContratos) listaContratos.innerHTML = '';
        if (data.length > 0) {
            data.forEach(contrato => {
                const div = document.createElement('div');
                div.className = 'result-item';
                div.innerHTML = `<strong>Contrato: ${contrato.numero}</strong><br>
                                 <small>Jazigo: ${contrato.jazigo || '-'}, Quadra: ${contrato.quadra || '-'}, Bloco: ${contrato.bloco || '-'}</small>`;
                div.onclick = function() {
                    document.querySelectorAll('#listaContratos .result-item').forEach(el => el.classList.remove('selected'));
                    this.classList.add('selected');
                    if (document.getElementById('selected_contrato_id')) document.getElementById('selected_contrato_id').value = contrato.id;
                    if (btnNext2) btnNext2.disabled = false;
                };
                if (listaContratos) listaContratos.appendChild(div);
            });
        } else {
            if (listaContratos) listaContratos.innerHTML = '<p style="padding:10px; color:var(--cor-texto-secundario);">Nenhum contrato encontrado. Cadastre um novo.</p>';
            if (btnNext2) btnNext2.disabled = true;
        }
    });
}
function carregarCemiterios() {
    const selectCemiterio = document.getElementById('novoContratoCemiterio');
    if (!selectCemiterio || selectCemiterio.options.length > 1) return;
    
    const formData = new FormData();
    formData.append('acao', 'buscar_cemiterios');
    fetch('pages/vendas/actions.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        selectCemiterio.innerHTML = '<option value="">Selecione...</option>';
        data.forEach(cem => {
            const opt = document.createElement('option');
            opt.value = cem.id;
            opt.textContent = cem.nome;
            selectCemiterio.appendChild(opt);
        });
    });
}
function validarFormNovoContrato() {
    const filialValida = novoContratoCemiterio?.value !== '';
    const numeroValido = novoContratoNumero?.value.trim().length > 0;
    if (btnSalvarNovoContrato) btnSalvarNovoContrato.disabled = !(filialValida && numeroValido);
}
function salvarNovoContrato() {
    if (isSubmitting) return;
    isSubmitting = true;
    if (btnSalvarNovoContrato) btnSalvarNovoContrato.disabled = true;

    const formData = new FormData();
    formData.append('acao', 'salvar_contrato');
    formData.append('mantenedor_id', document.getElementById('selected_mantenedor_id')?.value);
    formData.append('cemiterio_id', novoContratoCemiterio?.value);
    formData.append('numero', novoContratoNumero?.value);
    formData.append('jazigo', document.getElementById('novoContratoJazigo')?.value);
    formData.append('quadra', document.getElementById('novoContratoQuadra')?.value);
    formData.append('bloco', document.getElementById('novoContratoBloco')?.value);

    fetch('pages/vendas/actions.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        isSubmitting = false; 
        if (data.status === 'sucesso') {
            showNotification('sucesso', data.msg, 2500); // NOTIFICADO
            document.getElementById('selected_contrato_id').value = data.id;
            proximoPasso(3);
        } else {
            showNotification('erro', 'Erro ao salvar contrato: ' + data.msg, 5000); // NOTIFICADO
            if (btnSalvarNovoContrato) btnSalvarNovoContrato.disabled = false;
        }
    })
    .catch(err => {
        isSubmitting = false; 
        if (btnSalvarNovoContrato) btnSalvarNovoContrato.disabled = false;
        showNotification('erro', 'Erro na comunicação com o servidor.', 5000); // NOTIFICADO
    });
}

// ETAPA 3: DADOS DA VENDA
function filtrarProdutos() {
    const tipo = filtroTipo?.value;
    if (selectProduto) selectProduto.value = "";
    if (selectProduto) selectProduto.disabled = (tipo === "");
    
    selectProduto?.querySelectorAll('option').forEach(opt => {
        if (opt.value === "") return;
        if (opt.getAttribute('data-tipo') === tipo) {
            opt.style.display = 'block';
        } else {
            opt.style.display = 'none';
        }
    });
    validarEtapa3();
}
function formatarMoeda(valor) {
    if (isNaN(valor) || valor === null) return "R$ 0,00";
    return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}
function adicionarMeses(dataOriginal, meses) {
    if (!dataOriginal) return "(defina a data)";
    const d = new Date(dataOriginal + "T00:00:00"); 
    d.setMonth(d.getMonth() + meses);
    return d.toLocaleDateString('pt-BR');
}

// *** FUNÇÃO CALCULAR PARCELAS ***
function calcularParcelas() {
    const total = parseFloat(valorFinal?.value) || 0;
    const entrada = parseFloat(valorEntrada?.value) || 0;
    const parcelas = parseInt(qtdeParcelas?.value) || 0;

    const entradaReal = (entrada > total) ? total : entrada;
    const restante = total - entradaReal;

    if (parcelas > 0 && total > 0) {
        if (restante > 0) {
            const valorParcela = restante / parcelas;
            if (resumoParcelas) resumoParcelas.style.display = 'flex';
            if (textoCalculoParcela) textoCalculoParcela.innerHTML = `Restante <strong>${formatarMoeda(restante)}</strong> em <strong>${parcelas}x</strong> de <strong>${formatarMoeda(valorParcela)}</strong>`;
        } else {
            if (resumoParcelas) resumoParcelas.style.display = 'flex';
            if (textoCalculoParcela) textoCalculoParcela.innerHTML = "Entrada cobre o valor total (Venda quitada).";
        }
    } else {
        if (resumoParcelas) resumoParcelas.style.display = 'none';
    }
}

// *** FUNÇÃO VERIFICAR REGRAS (LÓGICA DA FAMÍLIA) ***
function verificarRegraPagamento() {
    const condicao = condicaoPagamento?.value;
    const total = parseFloat(valorFinal?.value) || 0;
    const entrada = parseFloat(valorEntrada?.value) || 0;
    const qtdParcelas = parseInt(qtdeParcelas?.value) || 0;
    
    const metadeValor = total / 2;
    let podeMarcarFamilia = false;
    
    if (alertaEntrada) alertaEntrada.style.display = 'none';
    if (textoAlertaEntrada) textoAlertaEntrada.innerHTML = '';
    dataLiberacaoCalculada = null; // Reseta a data calculada

    if (condicao === 'A Vista') {
        podeMarcarFamilia = true;
        if (divCamposPrazo) divCamposPrazo.style.display = 'none';
        if (valorEntrada) valorEntrada.value = '';
        if (qtdeParcelas) qtdeParcelas.value = '';
        dataLiberacaoCalculada = dataPrevisaoInput?.value; 
        
    } else { // 'A Prazo'
        if (divCamposPrazo) divCamposPrazo.style.display = 'block'; 
        const dataInicial = dataPrevisaoInput?.value;
        
        if (entrada >= metadeValor && total > 0) {
            podeMarcarFamilia = true;
            dataLiberacaoCalculada = dataInicial;
        } 
        else if (total > 0 && entrada < metadeValor) {
            const faltaParaMeta = metadeValor - entrada;
            const restanteTotal = total - entrada;
            let mensagemProjecao = "";

            if (qtdParcelas > 0 && restanteTotal > 0) {
                const valorParcela = restanteTotal / qtdParcelas;
                const parcelasNecessarias = valorParcela > 0 ? Math.ceil(faltaParaMeta / valorParcela) : 0;
                let dataAlvoTexto = "(defina a data)";
                
                if (dataInicial && parcelasNecessarias > 0) {
                    const mesesParaAdicionar = parcelasNecessarias - 1;
                    const mesesReais = mesesParaAdicionar < 0 ? 0 : mesesParaAdicionar;
                    
                    const d = new Date(dataInicial + "T00:00:00"); 
                    d.setMonth(d.getMonth() + mesesReais);
                    
                    dataAlvoTexto = d.toLocaleDateString('pt-BR');
                    dataLiberacaoCalculada = d.toISOString().split('T')[0];
                    
                    mensagemProjecao = `<br><br>
                    <span style="color: #664d03; background-color: #fff3cd; padding: 4px 8px; border-radius: 4px; border: 1px solid #ffecb5; display: block; margin-top: 5px;">
                        <i class="bi bi-calendar-event"></i> 
                        Estimativa: Atingirá 50% ao pagar a <strong>${parcelasNecessarias}ª parcela</strong>.<br>
                        Data prevista para liberação: <strong>${dataAlvoTexto}</strong>
                    </span>`;
                }
            }

            if (alertaEntrada) alertaEntrada.style.display = 'block';
            if (textoAlertaEntrada) textoAlertaEntrada.innerHTML = `
                A entrada atual não atinge 50% do valor total.<br>
                Faltam <strong>${formatarMoeda(faltaParaMeta)}</strong> para liberar a família.
                ${mensagemProjecao}
            `;
        }
        calcularParcelas(); 
    }

    // Atualiza Checkbox e Cursor
    if (podeMarcarFamilia) {
        if (checkFamilia) checkFamilia.disabled = false;
        if (wrapperFamilia) wrapperFamilia.classList.remove('disabled');
    } else {
        if (checkFamilia) checkFamilia.disabled = true;
        if (checkFamilia) checkFamilia.checked = false;
        if (wrapperFamilia) wrapperFamilia.classList.add('disabled');
    }
    
    validarEtapa3(); // Valida o form final
}

function validarEtapa3() {
    const tipoValido = filtroTipo?.value !== '';
    const produtoValido = selectProduto?.value !== '';
    const osValida = inputNumeroOS?.value.trim() !== '';
    const valorValido = (parseFloat(valorFinal?.value) || 0) > 0;

    let prazoValido = true;
    if (condicaoPagamento?.value === 'A Prazo') {
        const parcelasValidas = (parseInt(qtdeParcelas?.value) || 0) > 0;
        const dataValida = dataPrevisaoInput?.value !== '';
        prazoValido = parcelasValidas && dataValida; 
    }

    const formValido = tipoValido && produtoValido && osValida && valorValido && prazoValido;
    
    if (btnConcluirVenda) btnConcluirVenda.disabled = !formValido || isSubmitting;
}

// --- CORREÇÃO APLICADA AQUI: REMOÇÃO DE ALERTS NATIVOS ---
function finalizarVenda() {
    if (isSubmitting) return; 
    isSubmitting = true;

    if (btnConcluirVenda) {
        btnConcluirVenda.disabled = true;
        btnConcluirVenda.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Salvando...';
    }
    
    const form = document.getElementById('formVenda');
    const formData = new FormData(form);
    formData.append('acao', 'salvar_venda');

    if (condicaoPagamento?.value === 'A Vista') {
        formData.append('valor_entrada', '');
        formData.append('qtde_parcelas', '');
        formData.append('data_previsao_cobranca', ''); 
    } else {
        formData.append('valor_entrada', valorEntrada?.value);
        formData.append('qtde_parcelas', qtdeParcelas?.value);
        formData.append('data_previsao_cobranca', dataLiberacaoCalculada); 
    }

    fetch('pages/vendas/actions.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'sucesso') {
            // SUCESSO COM TOAST E TIMEOUT
            showNotification('sucesso', data.msg, 2000);
            setTimeout(() => { location.reload(); }, 2000);
        } else {
            // ERRO COM TOAST
            showNotification('erro', 'Erro: ' + data.msg, 5000);
            
            isSubmitting = false; 
            if (btnConcluirVenda) btnConcluirVenda.disabled = false;
            if (btnConcluirVenda) btnConcluirVenda.innerHTML = 'Concluir Venda';
        }
    })
    .catch(err => {
        console.error('Fetch Error:', err);
        // ERRO DE REDE COM TOAST
        showNotification('erro', 'Erro de comunicação. Tente novamente.', 5000);
        
        isSubmitting = false; 
        if (btnConcluirVenda) btnConcluirVenda.disabled = false;
        if (btnConcluirVenda) btnConcluirVenda.innerHTML = 'Concluir Venda';
    });
}

// ... (Restante do código) ...

// --- 4. ADICIONANDO EVENT LISTENERS ---
document.addEventListener('DOMContentLoaded', () => {

    // --- SÓ EXECUTA SE ESTIVER NA PÁGINA DE VENDAS ---
    if (typeof btnNovaVenda !== 'undefined' && btnNovaVenda) {

        // Etapa 1
        btnNovaVenda.addEventListener('click', () => {
            modal?.classList.add('active');
            currentStep = 1;
            currentSubStep1 = 'busca';
            currentSubStep2 = 'busca';
            atualizarUI();
        });

        linkCadastrarNovoMantenedor?.addEventListener('click', (e) => {
            e.preventDefault();
            mostrarCadastroMantenedor();
        });

        inputBusca?.addEventListener('keyup', function () {

            const termo = this.value;

            if (termo.length < 3) {
                listaResultados.innerHTML = '';
                listaResultados.style.display = 'none';
                btnNext1.disabled = true;
                document.getElementById('selected_mantenedor_id').value = '';
                return;
            }

            const formData = new FormData();
            formData.append('acao', 'buscar_mantenedor');
            formData.append('termo', termo);

            fetch('pages/vendas/actions.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                listaResultados.innerHTML = '';
                if (data.length > 0) {
                    listaResultados.style.display = 'block';

                    data.forEach(cliente => {
                        const div = document.createElement('div');
                        div.className = 'result-item';
                        div.innerHTML = `
                            <strong>${cliente.nome}</strong>
                            <br>
                            <small>${cliente.email}</small>
                        `;
                        div.onclick = () => selecionarMantenedor(cliente.id, cliente.nome);
                        listaResultados.appendChild(div);
                    });

                } else {
                    listaResultados.style.display = 'none';
                    btnNext1.disabled = true;
                    document.getElementById('selected_mantenedor_id').value = '';
                }
            });
        });

        novoMantenedorNome?.addEventListener('input', validarFormNovoMantenedor);
        novoMantenedorEmail?.addEventListener('input', validarFormNovoMantenedor);
        btnSalvarNovoMantenedor?.addEventListener('click', salvarNovoMantenedor);


        // Etapa 2
        linkCadastrarNovoContrato?.addEventListener('click', (e) => {
            e.preventDefault();
            mostrarCadastroContrato();
        });

        btnSalvarNovoContrato?.addEventListener('click', salvarNovoContrato);
        novoContratoCemiterio?.addEventListener('change', validarFormNovoContrato);
        novoContratoNumero?.addEventListener('input', validarFormNovoContrato);


        // Etapa 3
        filtroTipo?.addEventListener('change', filtrarProdutos);
        selectProduto?.addEventListener('change', validarEtapa3);
        inputNumeroOS?.addEventListener('input', validarEtapa3);
        condicaoPagamento?.addEventListener('change', verificarRegraPagamento);
        valorFinal?.addEventListener('input', verificarRegraPagamento);
        valorEntrada?.addEventListener('input', verificarRegraPagamento);
        qtdeParcelas?.addEventListener('input', verificarRegraPagamento);
        dataPrevisaoInput?.addEventListener('change', verificarRegraPagamento);
        btnConcluirVenda?.addEventListener('click', finalizarVenda);


        // Botões de Voltar
        document.querySelector('#step-1-cadastro-mantenedor .btn-back')?.addEventListener('click', mostrarBuscaMantenedor);
        document.querySelector('#step-2-busca-contrato .btn-back')?.addEventListener('click', () => passoAnterior(1));
        document.querySelector('#step-2-cadastro-contrato .btn-back')?.addEventListener('click', mostrarBuscaContrato);
        document.querySelector('#step-3 .btn-back')?.addEventListener('click', () => passoAnterior(2));


        // Botões de Avançar
        document.querySelector('#step-1-busca-mantenedor .btn-primary')?.addEventListener('click', () => proximoPasso(2));
        document.querySelector('#step-2-busca-contrato .btn-primary')?.addEventListener('click', () => proximoPasso(3));
    }

    const closeModalBtn = document.querySelector('.close-modal');
});