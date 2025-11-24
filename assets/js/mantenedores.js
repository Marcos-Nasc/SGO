// assets/js/mantenedores.js

// Mapeamento dos Elementos do DOM
const modalMantenedor = document.getElementById('modalMantenedor');
const formMantenedor = document.getElementById('formMantenedor');
const modalListaContratos = document.getElementById('modalListaContratos');
const modalEditarContrato = document.getElementById('modalEditarContrato');

let mantenedorAtualId = null;
let nomeMantenedorAtual = "";
let listaCemiterios = []; 

// --- FUNÇÕES DE FECHAR MODAL (Globais) ---

window.fecharModalContratos = function() {
    if(modalListaContratos) modalListaContratos.classList.remove('active');
}
window.fecharModalMantenedor = function() {
    if(modalMantenedor) modalMantenedor.classList.remove('active');
}
window.fecharModalEditarContrato = function() {
    if(modalEditarContrato) modalEditarContrato.classList.remove('active');
    // Atualiza a lista se estivesse vendo contratos de alguém
    if(mantenedorAtualId) {
        carregarListaContratos(mantenedorAtualId);
    }
}

// --- AUXILIAR: Carregar Cemitérios ---
async function carregarCemiteriosParaSelect(selectElementId) {
    if (listaCemiterios.length === 0) {
        const formData = new FormData();
        formData.append('acao', 'buscar_cemiterios');
        try {
            const res = await fetch('pages/mantenedores/actions.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.status === 'sucesso') {
                listaCemiterios = data.cemiterios;
            }
        } catch (error) {
            console.error('Erro ao buscar cemitérios', error);
        }
    }
    
    const select = document.getElementById(selectElementId);
    if (!select) return;

    select.innerHTML = '<option value="">Selecione a Filial...</option>';
    listaCemiterios.forEach(cem => {
        const option = document.createElement('option');
        option.value = cem.id;
        option.textContent = cem.nome;
        select.appendChild(option);
    });
}


// --- CRUD MANTENEDOR ---

window.abrirModalMantenedor = function() {
    if(!modalMantenedor) return;
    document.getElementById('tituloModalMantenedor').innerText = "Novo Cliente";
    if(formMantenedor) formMantenedor.reset();
    document.getElementById('mantenedorId').value = "";
    modalMantenedor.classList.add('active');
}

window.editarMantenedor = function(user) {
    if(!modalMantenedor) return;
    document.getElementById('tituloModalMantenedor').innerText = "Editar Cliente";
    document.getElementById('mantenedorId').value = user.id;
    document.getElementById('mantenedorNome').value = user.nome;
    document.getElementById('mantenedorEmail').value = user.email;
    document.getElementById('mantenedorTelefone').value = user.telefone;
    document.getElementById('mantenedorStatus').value = user.status;
    modalMantenedor.classList.add('active');
}

// Listener do Form
if(formMantenedor) {
    formMantenedor.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(formMantenedor);
        
        fetch('pages/mantenedores/actions.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            const tipo = data.status === 'sucesso' ? 'sucesso' : 'erro';
            showNotification(tipo, data.msg, 2000);
            if(data.status === 'sucesso') {
                setTimeout(() => { location.reload(); }, 2000);
            }
        })
        .catch(err => {
            console.error(err);
            showNotification('erro', 'Erro de comunicação.', 4000);
        });
    });
}

window.excluirMantenedor = function(id) {
    // Verifica se a função global existe antes de chamar
    if (typeof abrirGlobalConfirm === 'function') {
        abrirGlobalConfirm(
            "Tem certeza que deseja excluir este cliente?<br><strong>Atenção:</strong> Todos os contratos vinculados também serão apagados.", // Mensagem
            "Sim, Excluir", // Texto do Botão
            () => processarExclusaoMantenedor(id), // Callback (Ação)
            "Excluir Cliente" // Título
        );
    } else {
        // Fallback caso o script global não tenha carregado
        if(confirm("Tem certeza que deseja excluir este cliente?")) {
            processarExclusaoMantenedor(id);
        }
    }
}

function processarExclusaoMantenedor(id) {
    const formData = new FormData();
    formData.append('acao', 'excluir_mantenedor');
    formData.append('id', id);
    
    fetch('pages/mantenedores/actions.php', { method: 'POST', body: formData })
    .then(r => r.json()) // Agora voltamos a esperar JSON direto
    .then(data => {
        // Define o tipo de notificação baseada na resposta
        const tipo = data.status === 'sucesso' ? 'sucesso' : 'erro';
        
        // Exibe o Toast (ShowNotification)
        if (typeof showNotification === 'function') {
            showNotification(tipo, data.msg, 3000);
        } else {
            alert(data.msg); // Fallback simples
        }

        // Se deu certo, recarrega a página após um tempinho
        if(data.status === 'sucesso') {
            setTimeout(() => { location.reload(); }, 1500);
        }
    })
    .catch(err => {
        console.error("Erro:", err);
        if (typeof showNotification === 'function') {
            showNotification('erro', 'Erro de comunicação ao tentar excluir.', 3000);
        } else {
            alert('Erro de comunicação ao tentar excluir.');
        }
    });
}


// --- LISTA DE CONTRATOS ---

// *** ESTA É A FUNÇÃO QUE O BOTÃO ESTÁ CHAMANDO ***
window.verContratos = function(id, nome) {
    console.log("Abrindo contratos para ID:", id); // Debug no console
    
    if(!modalListaContratos) {
        console.error("Modal 'modalListaContratos' não encontrado no HTML.");
        return;
    }

    mantenedorAtualId = id;
    nomeMantenedorAtual = nome;
    
    const titulo = document.getElementById('tituloModalContratos');
    if(titulo) titulo.innerText = "Contratos de " + nome;
    
    modalListaContratos.classList.add('active');
    carregarListaContratos(id);
}

window.carregarListaContratos = function(id) {
    const divConteudo = document.getElementById('listaContratosConteudo');
    if(!divConteudo) return;

    divConteudo.innerHTML = '<div style="text-align:center; padding:20px;">Carregando...</div>';
    
    const formData = new FormData();
    formData.append('acao', 'listar_contratos');
    formData.append('mantenedor_id', id);
    
    fetch('pages/mantenedores/actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'sucesso') {
            if(data.contratos && data.contratos.length > 0) {
                let html = '<div class="contract-list-container">';
                data.contratos.forEach(c => {
                    let loc = `Jazigo: ${c.jazigo || '-'} • Q: ${c.quadra || '-'} • B: ${c.bloco || '-'}`;
                    html += `
                        <div class="contract-card-item">
                            <div class="contract-header">
                                <div class="contract-details">
                                    <h5>Contrato ${c.numero}</h5>
                                    <span class="contract-location">${loc}</span>
                                    <p><i class="bi bi-geo-alt-fill"></i> ${c.cemiterio_nome}</p>
                                </div>
                            </div>
                            <div style="display:flex; gap:5px;">
                                <button class="btn-details" title="Editar" onclick="abrirModalEditarContrato(${c.id})"> <i class="bi bi-pencil-square"></i> Editar</button>
                                <button class="btn-delete" title="Excluir" onclick="excluirContrato(${c.id})" style="color: red; border: 1px solid red;"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>`;
                });
                html += '</div>';
                divConteudo.innerHTML = html;
            } else {
                divConteudo.innerHTML = `<div class="contract-empty">Nenhum contrato encontrado para este cliente.</div>`;
            }
        } else {
            divConteudo.innerHTML = `<p style="color:red; text-align:center;">${data.msg}</p>`;
        }
    })
    .catch(err => {
        console.error(err);
        divConteudo.innerHTML = '<p style="color:red; text-align:center;">Erro de comunicação.</p>';
    });
}

window.excluirContrato = function(contratoId) {
    if (typeof abrirGlobalConfirm === 'function') {
        abrirGlobalConfirm(
            "Tem certeza que deseja excluir este contrato?",
            "Sim, Excluir",
            () => processarExclusaoContrato(contratoId),
            "Excluir Contrato"
        );
    } else {
        if(confirm("Excluir contrato?")) processarExclusaoContrato(contratoId);
    }
}

function processarExclusaoContrato(contratoId) {
    const formData = new FormData();
    formData.append('acao', 'excluir_contrato');
    formData.append('id', contratoId);

    fetch('pages/mantenedores/actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        const tipo = data.status === 'sucesso' ? 'sucesso' : 'erro';
        showNotification(tipo, data.msg, 2000);
        if(data.status === 'sucesso') {
            carregarListaContratos(mantenedorAtualId);
        }
    })
    .catch(err => {
        showNotification('erro', 'Erro de comunicação.', 4000);
    });
}

// --- EDIÇÃO CONTRATO ---

window.abrirModalEditarContrato = async function(contratoId) {
    if(!modalEditarContrato) return;
    document.getElementById('tituloEditarContrato').innerText = "Editando Contrato ID: " + contratoId;
    await carregarCemiteriosParaSelect('editContratoCemiterio');
    modalEditarContrato.classList.add('active');
    
    const formData = new FormData();
    formData.append('acao', 'buscar_contrato_id');
    formData.append('id', contratoId);

    try {
        const res = await fetch('pages/mantenedores/actions.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.status === 'sucesso' && data.contrato) {
            const c = data.contrato;
            document.getElementById('editContratoId').value = c.id;
            document.getElementById('editContratoCemiterio').value = c.cemiterio_id;
            document.getElementById('editContratoNumero').value = c.numero;
            document.getElementById('editContratoJazigo').value = c.jazigo;
            document.getElementById('editContratoQuadra').value = c.quadra;
            document.getElementById('editContratoBloco').value = c.bloco;
        }
    } catch(e) { console.error(e); }
}

// Listener do Form Editar Contrato
const formEditContrato = document.getElementById('formEditarContrato');
if(formEditContrato) {
    formEditContrato.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('pages/mantenedores/actions.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            const tipo = data.status === 'sucesso' ? 'sucesso' : 'erro';
            showNotification(tipo, data.msg, 2000);
            if(data.status === 'sucesso') {
                setTimeout(() => { fecharModalEditarContrato(); }, 2000);
            }
        });
    });
}