// assets/js/processos.js

const modalProcesso = document.getElementById('modalEditarProcesso');
const formProcesso = document.getElementById('formEditarProcesso');
let currentVendaIdExclusao = null;
let tiposProdutosCache = []; 
let listaProdutosCache = [];

function fecharModalProcesso() {
    if(modalProcesso) modalProcesso.classList.remove('active');
}

// --- CÁLCULO FINANCEIRO INTELIGENTE ---
function calcularFinanceiro() {
    const condicao = document.getElementById('editCondicao').value;
    
    // Elementos
    const elTotal = document.getElementById('editValorFinal');
    const elEntrada = document.getElementById('editValorEntrada');
    const elQtd = document.getElementById('editQtdParcelas');
    const elDataVenda = document.getElementById('editDataVenda');
    
    // Outputs
    const elValorParcelaDisplay = document.getElementById('editValorParcelaDisplay');
    const elValorParcelaCalc = document.getElementById('editValorParcelaCalc');
    const elDataPrev = document.getElementById('editDataPrevisaoCalc');

    if (condicao === 'A Vista') {
        elValorParcelaDisplay.value = 'Quitado';
        elValorParcelaCalc.value = '0.00';
        elDataPrev.value = elDataVenda.value; // Previsão imediata
        return;
    }

    // Valores
    const total = parseFloat(elTotal.value) || 0;
    const entrada = parseFloat(elEntrada.value) || 0;
    const qtd = parseInt(elQtd.value) || 1;
    
    // 1. Valor da Parcela: (Total - Entrada) / Qtd
    let restante = total - entrada;
    if (restante < 0) restante = 0;
    
    const valorParcela = restante / qtd;
    
    elValorParcelaDisplay.value = valorParcela.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    elValorParcelaCalc.value = valorParcela.toFixed(2);

    // 2. Previsão de 50%
    const meta50 = total / 2;
    
    if (entrada >= meta50) {
        // Entrada já cobriu 50%
        elDataPrev.value = elDataVenda.value;
    } else {
        // Quanto falta para 50%?
        const faltaPara50 = meta50 - entrada;
        // Quantas parcelas são necessárias para cobrir o que falta?
        const parcelasNecessarias = Math.ceil(faltaPara50 / valorParcela);
        
        // Somar meses à data da venda
        if (elDataVenda.value) {
            const dataBase = new Date(elDataVenda.value);
            // Adiciona os meses
            dataBase.setMonth(dataBase.getMonth() + parcelasNecessarias);
            // Formata YYYY-MM-DD
            elDataPrev.value = dataBase.toISOString().split('T')[0];
        }
    }
}

// Adicionar Listeners para Cálculo
document.addEventListener('DOMContentLoaded', () => {
    // Carregar selects (código anterior...)
    fetch('pages/processos/actions.php', { 
        method: 'POST', 
        body: new URLSearchParams({ acao: 'listar_produtos_tipos' }) 
    })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'sucesso') {
            tiposProdutosCache = data.tipos;
            listaProdutosCache = data.itens;
            const selTipo = document.getElementById('editTipoProdutoSelect');
            if(selTipo) {
                selTipo.innerHTML = '<option value="">Selecione...</option>';
                tiposProdutosCache.forEach(t => selTipo.innerHTML += `<option value="${t}">${t}</option>`);
                selTipo.addEventListener('change', function() { atualizarSelectProdutos(this.value); });
            }
        }
    });

    // Listeners Financeiros
    ['editValorFinal', 'editValorEntrada', 'editQtdParcelas', 'editDataVenda'].forEach(id => {
        const el = document.getElementById(id);
        if(el) el.addEventListener('input', calcularFinanceiro);
    });
});

function atualizarSelectProdutos(tipo, produtoIdSelecionado = null) {
    const selProd = document.getElementById('editProdutoIdSelect');
    selProd.innerHTML = '<option value="">Selecione...</option>';
    const filtrados = listaProdutosCache.filter(p => p.tipo === tipo);
    filtrados.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id; opt.textContent = p.nome;
        if(produtoIdSelecionado && p.id == produtoIdSelecionado) opt.selected = true;
        selProd.appendChild(opt);
    });
}

window.toggleParcelamento = function() {
    const condicao = document.getElementById('editCondicao').value;
    const wrapper = document.getElementById('divCamposParcelado');
    if(condicao === 'A Vista') {
        wrapper.style.display = 'none';
        document.getElementById('editValorEntrada').value = '';
        document.getElementById('editQtdParcelas').value = '';
    } else {
        wrapper.style.display = 'block';
    }
    calcularFinanceiro(); // Recalcula ao mudar
}

// --- ABRIR EDIÇÃO ---
function editarProcesso(vendaId) {
    if(!modalProcesso) return;
    currentVendaIdExclusao = vendaId;

    const formData = new FormData();
    formData.append('acao', 'buscar_dados_processo');
    formData.append('venda_id', vendaId);

    fetch('pages/processos/actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'sucesso') {
            const d = data.dados;
            
            // IDs e Cliente
            document.getElementById('editVendaId').value = d.venda_id;
            document.getElementById('editContratoId').value = d.contrato_id;
            document.getElementById('editAgendamentoId').value = d.agendamento_id || '';
            document.getElementById('editClienteNome').value = d.mantenedor_nome;
            document.getElementById('editClienteEmail').value = d.mantenedor_email;
            document.getElementById('editClienteTelefone').value = d.mantenedor_telefone;
            document.getElementById('editDataVenda').value = d.data_venda; 

            // Produto
            const tipoAtual = d.servico_tipo || (tiposProdutosCache.length > 0 ? tiposProdutosCache[0] : '');
            document.getElementById('editTipoProdutoSelect').value = tipoAtual;
            atualizarSelectProdutos(tipoAtual, d.produto_servico_id);

            // Contrato (Readonly)
            document.getElementById('editOS').value = d.numero_os;
            document.getElementById('editNF').value = d.numero_nf;
            document.getElementById('editContratoNum').value = d.contrato_numero;
            document.getElementById('editJazigo').value = d.jazigo;
            document.getElementById('editQuadra').value = d.quadra;
            document.getElementById('editBloco').value = d.bloco;

            // Financeiro
            document.getElementById('editValorFinal').value = d.valor_final;
            document.getElementById('editCondicao').value = d.condicao_pagamento;
            document.getElementById('editValorEntrada').value = d.valor_entrada;
            document.getElementById('editQtdParcelas').value = d.qtde_parcelas;
            
            toggleParcelamento(); // Ajusta visual e chama calcularFinanceiro()

            // Status
            document.getElementById('editStatusVenda').value = d.status_venda;
            document.getElementById('editStatusAgendamento').value = d.status_agendamento || 'Pendente de Contato';
            document.getElementById('editDataAgendada').value = d.data_agendada_formatada;
            document.getElementById('editObsAgendamento').value = d.obs_agendamento || '';
            document.getElementById('editObsVenda').value = d.obs_venda || '';

            // --- FOTOS LADO A LADO ---
            const areaFotos = document.getElementById('editorFotosArea');
            if (areaFotos) {
                areaFotos.innerHTML = '';
                if (d.fotos && (d.fotos.antes.length > 0 || d.fotos.depois.length > 0)) {
                    let html = `<h5 style="font-size: 0.9rem; color: #adb5bd; margin-bottom: 10px;">Fotos</h5><div class="fotos-grid-editor">`;
                    
                    // Coluna Antes
                    html += `<div class="foto-column">`;
                    d.fotos.antes.forEach(f => {
                        html += `
                            <div class="editor-photo-wrapper" id="foto-container-${f.id}">
                                <img src="${f.caminho}">
                                <button type="button" class="btn-delete-photo" onclick="deletarFotoProcesso(${f.id}, '${f.caminho.replace(/\\/g, '\\\\')}')">
                                    <i class="bi bi-x"></i>
                                </button>
                                <div style="position:absolute; bottom:0; background:rgba(0,0,0,0.6); color:white; width:100%; text-align:center; font-size:0.8rem; padding:2px;">Antes</div>
                            </div>`;
                    });
                    html += `</div>`;

                    // Coluna Depois
                    html += `<div class="foto-column">`;
                    d.fotos.depois.forEach(f => {
                        html += `
                            <div class="editor-photo-wrapper" id="foto-container-${f.id}">
                                <img src="${f.caminho}">
                                <button type="button" class="btn-delete-photo" onclick="deletarFotoProcesso(${f.id}, '${f.caminho.replace(/\\/g, '\\\\')}')">
                                    <i class="bi bi-x"></i>
                                </button>
                                <div style="position:absolute; bottom:0; background:rgba(0,0,0,0.6); color:white; width:100%; text-align:center; font-size:0.8rem; padding:2px;">Depois</div>
                            </div>`;
                    });
                    html += `</div>`;
                    
                    html += `</div>`;
                    areaFotos.innerHTML = html;
                }
            }

            modalProcesso.classList.add('active');
        } else {
            showNotification('erro', data.msg);
        }
    })
    .catch(err => { console.error(err); showNotification('erro', 'Erro ao buscar dados.'); });
}

// --- DELETAR FOTO ---
function deletarFotoProcesso(fotoId, caminhoArquivo) {
    // Verifica se o modal global foi carregado corretamente
    if (typeof abrirGlobalConfirm === 'function') {
        abrirGlobalConfirm(
            "Tem certeza que deseja excluir esta foto permanentemente?", // Mensagem
            "Sim, Excluir", // Texto do Botão
            () => processarExclusaoFoto(fotoId, caminhoArquivo), // Ação (Callback)
            "Excluir Foto" // Título do Modal
        );
    } else {
        // Fallback caso o script global falhe
        if(confirm("Excluir esta foto?")) processarExclusaoFoto(fotoId, caminhoArquivo);
    }
}

// Função auxiliar que faz a comunicação com o PHP
function processarExclusaoFoto(fotoId, caminhoArquivo) {
    const formData = new FormData();
    formData.append('acao', 'excluir_foto_processo');
    formData.append('foto_id', fotoId);
    formData.append('caminho', caminhoArquivo);

    fetch('pages/processos/actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'sucesso') {
            // Remove visualmente a foto do grid
            const el = document.getElementById(`foto-container-${fotoId}`);
            if(el) {
                el.style.transition = "opacity 0.3s, transform 0.3s";
                el.style.opacity = "0";
                el.style.transform = "scale(0.8)";
                setTimeout(() => el.remove(), 300);
            }
            showNotification('sucesso', 'Foto removida com sucesso.');
        } else {
            showNotification('erro', data.msg);
        }
    })
    .catch(err => { 
        console.error(err); 
        showNotification('erro', 'Erro de comunicação ao excluir foto.'); 
    });
}

// --- SALVAR ---
if(formProcesso) {
    formProcesso.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('pages/processos/actions.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if(data.status === 'sucesso') {
                showNotification('sucesso', data.msg, 2000);
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification('erro', data.msg);
            }
        });
    });
}

// --- EXCLUIR ---
function acaoExcluirDoModal() {
    if (currentVendaIdExclusao) {
        fecharModalProcesso(); // Fecha o editor para mostrar a confirmação limpa
        excluirProcesso(currentVendaIdExclusao);
    }
}

// --- FUNÇÃO PRINCIPAL DE EXCLUSÃO (COM MODAL GLOBAL) ---
function excluirProcesso(vendaId) {
    // Verifica se a função do modal global existe
    if (typeof abrirGlobalConfirm === 'function') {
        abrirGlobalConfirm(
            "Tem certeza que deseja apagar este processo?<br><strong>Atenção:</strong> A Venda, o Contrato e o Agendamento serão excluídos permanentemente.", // Mensagem
            "Sim, Excluir Tudo", // Texto do Botão Confirmar
            () => processarExclusao(vendaId), // Função a executar se confirmar
            "Excluir Processo" // Título
        );
    } else {
        // Fallback (segurança caso o script global falhe)
        if (confirm("Tem certeza que deseja excluir este processo completo?")) {
            processarExclusao(vendaId);
        }
    }
}

// --- COMUNICAÇÃO COM O PHP ---
function processarExclusao(vendaId) {
    const formData = new FormData();
    formData.append('acao', 'excluir_processo');
    formData.append('venda_id', vendaId);

    fetch('pages/processos/actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'sucesso') {
            showNotification('sucesso', data.msg, 2000);
            // Recarrega a página após a notificação aparecer
            setTimeout(() => location.reload(), 2000);
        } else {
            showNotification('erro', data.msg);
        }
    })
    .catch(err => {
        console.error(err);
        showNotification('erro', 'Erro de comunicação ao tentar excluir.');
    });
}