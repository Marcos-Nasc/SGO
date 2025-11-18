// assets/js/mantenedores.js

const modalMantenedor = document.getElementById('modalMantenedor');
const formMantenedor = document.getElementById('formMantenedor');
const modalListaContratos = document.getElementById('modalListaContratos');

// --- CRUD MANTENEDOR ---
function abrirModalMantenedor() {
    document.getElementById('tituloModalMantenedor').innerText = "Novo Mantenedor";
    formMantenedor.reset();
    document.getElementById('mantenedorId').value = "";
    modalMantenedor.classList.add('active');
}

function editarMantenedor(m) {
    document.getElementById('tituloModalMantenedor').innerText = "Editar Mantenedor";
    document.getElementById('mantenedorId').value = m.id;
    document.getElementById('mantenedorNome').value = m.nome;
    document.getElementById('mantenedorEmail').value = m.email;
    document.getElementById('mantenedorTelefone').value = m.telefone;
    document.getElementById('mantenedorStatus').value = m.status;
    modalMantenedor.classList.add('active');
}

function fecharModalMantenedor() {
    modalMantenedor.classList.remove('active');
}

formMantenedor.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(formMantenedor);
    
    fetch('pages/mantenedores/actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        alert(data.msg);
        if(data.status === 'sucesso') location.reload();
    });
});

// --- VISUALIZAR CONTRATOS ---
let mantenedorAtualId = null;

function verContratos(id, nome) {
    mantenedorAtualId = id;
    document.getElementById('tituloModalContratos').innerText = "Contratos de " + nome;
    modalListaContratos.classList.add('active');
    
    const divConteudo = document.getElementById('listaContratosConteudo');
    divConteudo.innerHTML = '<div style="text-align:center; padding:20px;">Carregando...</div>';
    
    const formData = new FormData();
    formData.append('acao', 'listar_contratos');
    formData.append('mantenedor_id', id);
    
    fetch('pages/mantenedores/actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'sucesso') {
            if(data.contratos.length > 0) {
                let html = '<div class="contract-list-container">';
                
                data.contratos.forEach(c => {
                    // Formata a localização (Jazigo, Quadra, Bloco)
                    let loc = `Jazigo: ${c.jazigo || '-'} • Q: ${c.quadra || '-'} • B: ${c.bloco || '-'}`;
                    
                    html += `
                        <div class="contract-card-item">
                            <div class="contract-header">
                                <div class="contract-icon-box">
                                    <i class="bi bi-file-earmark-text-fill"></i>
                                </div>
                                <div class="contract-details">
                                    <h5>Contrato ${c.numero}</h5>
                                    <p><i class="bi bi-geo-alt"></i> ${c.cemiterio_nome}</p>
                                    <span class="contract-location">${loc}</span>
                                </div>
                            </div>
                            
                            <button class="btn-contract-action" title="Editar Contrato" onclick="alert('Editar Contrato ${c.numero} em breve...')">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                        </div>
                    `;
                });
                
                html += '</div>';
                divConteudo.innerHTML = html;
            } else {
                divConteudo.innerHTML = `
                    <div class="contract-empty">
                        <i class="bi bi-folder-x" style="font-size: 2rem; display:block; margin-bottom:10px;"></i>
                        Nenhum contrato vinculado a este cliente.
                    </div>
                `;
            }
        } else {
            divConteudo.innerHTML = '<p style="color:red; text-align:center;">Erro ao carregar contratos.</p>';
        }
    })
    .catch(err => {
        console.error(err);
        divConteudo.innerHTML = '<p style="color:red; text-align:center;">Erro de comunicação.</p>';
    });
}

function fecharModalContratos() {
    modalListaContratos.classList.remove('active');
}

function novoContratoParaMantenedor() {
    // Aqui você pode redirecionar para uma página de cadastro de contrato
    // ou abrir o modal de contrato (reutilizando o template) preenchendo o ID do mantenedor.
    alert("Funcionalidade de adicionar contrato avulso em desenvolvimento.");
}