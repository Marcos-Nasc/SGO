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
const inputNumeroOS = document.getElementById('numero_os'); // Agora vai encontrar
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
        const circleEl = stepEl.querySelector('.step-circle');
        
        circleEl.innerHTML = i; // Reseta para número

        if (i < currentStep) {
            stepEl.classList.add('completed');
            circleEl.innerHTML = '<i class="bi bi-check-lg"></i>';
        }
        if (i === currentStep) {
            stepEl.classList.add('active');
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
    modal.classList.remove('active');
    document.getElementById('formVenda').reset();
    inputBusca.value = '';
    listaResultados.innerHTML = '';
    listaResultados.style.display = 'none';
    listaContratos.innerHTML = '';
    
    document.getElementById('selected_mantenedor_id').value = '';
    document.getElementById('selected_contrato_id').value = '';
    
    // Desabilita todos os botões de navegação
    btnNext1.disabled = true;
    btnNext2.disabled = true;
    btnSalvarNovoMantenedor.disabled = true;
    btnSalvarNovoContrato.disabled = true;
    btnConcluirVenda.disabled = true;
    btnConcluirVenda.innerHTML = 'Concluir Venda'; // Reseta o texto do botão

    // Reseta a trava de segurança
    isSubmitting = false; 

    novoMantenedorNome.value = '';
    novoMantenedorEmail.value = '';
    novoMantenedorTelefone.value = '';

    // Reseta Etapa 3
    checkFamilia.disabled = true;
    checkFamilia.checked = false;
    wrapperFamilia.classList.add('disabled');
    divCamposPrazo.style.display = 'none';
    alertaEntrada.style.display = 'none';
    resumoParcelas.style.display = 'none';
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
    btnNext1.disabled = true;
    document.getElementById('selected_mantenedor_id').value = '';
    atualizarUI();
}
function selecionarMantenedor(id, nome) {
    document.getElementById('selected_mantenedor_id').value = id;
    inputBusca.value = nome;
    document.getElementById('labelNomeMantenedor').innerText = nome;
    btnNext1.disabled = false; // Habilita o botão
    listaResultados.style.display = 'none';
    carregarContratos(id);
}
function validarFormNovoMantenedor() {
    const nomeValido = novoMantenedorNome.value.trim().length > 2;
    const emailValido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(novoMantenedorEmail.value.trim());
    btnSalvarNovoMantenedor.disabled = !(nomeValido && emailValido);
}
function salvarNovoMantenedor() {
    if (isSubmitting) return;
    isSubmitting = true;
    btnSalvarNovoMantenedor.disabled = true;

    const formData = new FormData();
    formData.append('acao', 'salvar_mantenedor');
    formData.append('nome', novoMantenedorNome.value);
    formData.append('email', novoMantenedorEmail.value);
    formData.append('telefone', novoMantenedorTelefone.value);

    fetch('pages/vendas/actions.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        isSubmitting = false; 
        if (data.status === 'sucesso') {
            selecionarMantenedor(data.id, data.nome);
            proximoPasso(2);
        } else {
            alert('Erro ao cadastrar mantenedor: ' + data.msg);
            btnSalvarNovoMantenedor.disabled = false;
        }
    })
    .catch(err => {
        isSubmitting = false; 
        btnSalvarNovoMantenedor.disabled = false;
        alert('Erro na comunicação com o servidor.');
    });
}

// ETAPA 2: CONTRATO
function mostrarCadastroContrato() {
    currentSubStep2 = 'cadastro';
    const nomeMantenedor = document.getElementById('labelNomeMantenedor').innerText;
    document.getElementById('labelNomeMantenedorCadastro').innerText = nomeMantenedor;
    carregarCemiterios();
    validarFormNovoContrato();
    atualizarUI();
}
function mostrarBuscaContrato() {
    currentSubStep2 = 'busca';
    btnNext2.disabled = true;
    document.getElementById('selected_contrato_id').value = '';
    atualizarUI();
}
function carregarContratos(mantenedorId) {
    const formData = new FormData();
    formData.append('acao', 'buscar_contratos');
    formData.append('mantenedor_id', mantenedorId);
    
    document.getElementById('selected_contrato_id').value = '';
    btnNext2.disabled = true;

    fetch('pages/vendas/actions.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        listaContratos.innerHTML = '';
        if (data.length > 0) {
            data.forEach(contrato => {
                const div = document.createElement('div');
                div.className = 'result-item';
                div.innerHTML = `<strong>Contrato: ${contrato.numero}</strong><br>
                                 <small>Jazigo: ${contrato.jazigo || '-'}, Quadra: ${contrato.quadra || '-'}, Bloco: ${contrato.bloco || '-'}</small>`;
                div.onclick = function() {
                    document.querySelectorAll('#listaContratos .result-item').forEach(el => el.classList.remove('selected'));
                    this.classList.add('selected');
                    document.getElementById('selected_contrato_id').value = contrato.id;
                    btnNext2.disabled = false; // Habilita o botão
                };
                listaContratos.appendChild(div);
            });
        } else {
            listaContratos.innerHTML = '<p style="padding:10px; color:var(--cor-texto-secundario);">Nenhum contrato encontrado. Cadastre um novo.</p>';
            btnNext2.disabled = true;
        }
    });
}
function carregarCemiterios() {
    const selectCemiterio = document.getElementById('novoContratoCemiterio');
    if (selectCemiterio.options.length > 1) return;
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
    const filialValida = novoContratoCemiterio.value !== '';
    const numeroValido = novoContratoNumero.value.trim().length > 0;
    btnSalvarNovoContrato.disabled = !(filialValida && numeroValido);
}
function salvarNovoContrato() {
    if (isSubmitting) return;
    isSubmitting = true;
    btnSalvarNovoContrato.disabled = true;

    const formData = new FormData();
    formData.append('acao', 'salvar_contrato');
    formData.append('mantenedor_id', document.getElementById('selected_mantenedor_id').value);
    formData.append('cemiterio_id', novoContratoCemiterio.value);
    formData.append('numero', novoContratoNumero.value);
    formData.append('jazigo', document.getElementById('novoContratoJazigo').value);
    formData.append('quadra', document.getElementById('novoContratoQuadra').value);
    formData.append('bloco', document.getElementById('novoContratoBloco').value);

    fetch('pages/vendas/actions.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        isSubmitting = false; 
        if (data.status === 'sucesso') {
            document.getElementById('selected_contrato_id').value = data.id;
            proximoPasso(3);
        } else {
            alert('Erro: ' + data.msg);
            btnSalvarNovoContrato.disabled = false;
        }
    })
    .catch(err => {
        isSubmitting = false; 
        btnSalvarNovoContrato.disabled = false;
        alert('Erro ao comunicar com servidor.');
    });
}

// ETAPA 3: DADOS DA VENDA
function filtrarProdutos() {
    const tipo = filtroTipo.value;
    selectProduto.value = "";
    selectProduto.disabled = (tipo === "");
    
    selectProduto.querySelectorAll('option').forEach(opt => {
        if (opt.value === "") return;
        opt.style.display = (opt.getAttribute('data-tipo') === tipo) ? 'block' : 'none';
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

// *** FUNÇÃO CALCULAR PARCELAS (RECOLOCADA) ***
function calcularParcelas() {
    const total = parseFloat(valorFinal.value) || 0;
    const entrada = parseFloat(valorEntrada.value) || 0;
    const parcelas = parseInt(qtdeParcelas.value) || 0;

    const entradaReal = (entrada > total) ? total : entrada;
    const restante = total - entradaReal;

    if (parcelas > 0 && total > 0) {
        if (restante > 0) {
            const valorParcela = restante / parcelas;
            resumoParcelas.style.display = 'flex';
            textoCalculoParcela.innerHTML = `Restante <strong>${formatarMoeda(restante)}</strong> em <strong>${parcelas}x</strong> de <strong>${formatarMoeda(valorParcela)}</strong>`;
        } else {
            resumoParcelas.style.display = 'flex';
            textoCalculoParcela.innerHTML = "Entrada cobre o valor total (Venda quitada).";
        }
    } else {
        resumoParcelas.style.display = 'none';
    }
}

// *** FUNÇÃO VERIFICAR REGRAS (LÓGICA DA FAMÍLIA) ***
function verificarRegraPagamento() {
    const condicao = condicaoPagamento.value;
    const total = parseFloat(valorFinal.value) || 0;
    const entrada = parseFloat(valorEntrada.value) || 0;
    const qtdParcelas = parseInt(qtdeParcelas.value) || 0;
    
    const metadeValor = total / 2;
    let podeMarcarFamilia = false;
    
    alertaEntrada.style.display = 'none';
    textoAlertaEntrada.innerHTML = '';
    dataLiberacaoCalculada = null; // Reseta a data calculada

    if (condicao === 'A Vista') {
        podeMarcarFamilia = true;
        divCamposPrazo.style.display = 'none';
        valorEntrada.value = '';
        qtdeParcelas.value = '';
        dataLiberacaoCalculada = dataPrevisaoInput.value; 
        
    } else { // 'A Prazo'
        divCamposPrazo.style.display = 'block'; 
        const dataInicial = dataPrevisaoInput.value;
        
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

            alertaEntrada.style.display = 'block';
            textoAlertaEntrada.innerHTML = `
                A entrada atual não atinge 50% do valor total.<br>
                Faltam <strong>${formatarMoeda(faltaParaMeta)}</strong> para liberar a família.
                ${mensagemProjecao}
            `;
        }
        calcularParcelas(); // Chama a função que estava faltando
    }

    // Atualiza Checkbox e Cursor
    if (podeMarcarFamilia) {
        checkFamilia.disabled = false;
        wrapperFamilia.classList.remove('disabled');
    } else {
        checkFamilia.disabled = true;
        checkFamilia.checked = false;
        wrapperFamilia.classList.add('disabled');
    }
    
    validarEtapa3(); // Valida o form final
}

function validarEtapa3() {
    const tipoValido = filtroTipo.value !== '';
    const produtoValido = selectProduto.value !== '';
    const osValida = inputNumeroOS.value.trim() !== '';
    const valorValido = (parseFloat(valorFinal.value) || 0) > 0;

    let prazoValido = true;
    if (condicaoPagamento.value === 'A Prazo') {
        const parcelasValidas = (parseInt(qtdeParcelas.value) || 0) > 0;
        const dataValida = dataPrevisaoInput.value !== '';
        prazoValido = parcelasValidas && dataValida; 
    }

    const formValido = tipoValido && produtoValido && osValida && valorValido && prazoValido;
    // Só habilita o botão se o formulário for válido E não estiver enviando
    btnConcluirVenda.disabled = !formValido || isSubmitting;
}

function finalizarVenda() {
    if (isSubmitting) return; 
    isSubmitting = true;

    btnConcluirVenda.disabled = true;
    btnConcluirVenda.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Salvando...';
    
    const form = document.getElementById('formVenda');
    const formData = new FormData(form);
    formData.append('acao', 'salvar_venda');

    if (condicaoPagamento.value === 'A Vista') {
        formData.append('valor_entrada', '');
        formData.append('qtde_parcelas', '');
        formData.append('data_previsao_cobranca', ''); // Envia vazio se for 'A Vista'
    } else {
        formData.append('valor_entrada', valorEntrada.value);
        formData.append('qtde_parcelas', qtdeParcelas.value);
        // Envia a data de liberação calculada (que pode ser a 1ª parcela se 50% foi pago)
        formData.append('data_previsao_cobranca', dataLiberacaoCalculada); 
    }

    fetch('pages/vendas/actions.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'sucesso') {
            alert(data.msg);
            location.reload();
        } else {
            alert('Erro: ' + data.msg);
            isSubmitting = false; 
            btnConcluirVenda.disabled = false;
            btnConcluirVenda.innerHTML = 'Concluir Venda';
        }
    })
    .catch(err => {
        console.error('Fetch Error:', err);
        alert('Erro de comunicação. Tente novamente.');
        isSubmitting = false; 
        btnConcluirVenda.disabled = false;
        btnConcluirVenda.innerHTML = 'Concluir Venda';
    });
}

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


    // --- LISTENER DO MODAL (GLOBAL) ---
    const closeModalBtn = document.querySelector('.close-modal');
    // opcional: closeModalBtn?.addEventListener('click', fecharModal);
});