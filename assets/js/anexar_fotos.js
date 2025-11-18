// assets/js/anexar_fotos.js

const modalFotos = document.getElementById('modalFotos');
const modalFotosTitulo = document.getElementById('modalFotosTitulo');
const hiddenAgendamentoId = document.getElementById('fotos_agendamento_id');
const gridFotosAntes = document.getElementById('grid-fotos-antes');
const gridFotosDepois = document.getElementById('grid-fotos-depois');
const btnMarcarConcluido = document.getElementById('btnMarcarConcluido');

let isUploading = false; // Trava de segurança para upload

/**
 * Abre o Modal e busca as fotos já existentes
 */
async function abrirModalFotos(agendamentoId, os, mantenedorNome, dataAgendada, servicoNome) {
    // 1. Configura o modal
    modalFotosTitulo.innerText = "Anexar Fotos - OS " + os;
    hiddenAgendamentoId.value = agendamentoId;
    gridFotosAntes.innerHTML = '<span class="loading-fotos">Carregando...</span>';
    gridFotosDepois.innerHTML = '<span class="loading-fotos">Carregando...</span>';

    // --- Preenche o Resumo ---
    document.getElementById('modal-resumo-servico').innerText = "Serviço: " + servicoNome;
    document.getElementById('modal-resumo-cliente').innerText = "Cliente: " + mantenedorNome;
    document.getElementById('modal-resumo-data').innerText = "Data Agendada: " + dataAgendada;
    
    // --- ESTA É A CORREÇÃO ---
    // Reseta o botão de concluir, APENAS SE ELE EXISTIR nesta página
    if (btnMarcarConcluido) {
        btnMarcarConcluido.disabled = false;
        btnMarcarConcluido.innerHTML = '<i class="bi bi-check-all"></i> Marcar Serviço como Concluído';
    }
    // --- FIM DA CORREÇÃO ---

    isUploading = false;

    // 2. Abre o modal
    modalFotos.classList.add('active');

    // 3. Busca fotos existentes
    try {
        const response = await fetch(`pages/agendamentos/actions.php?acao=buscar_fotos&agendamento_id=${agendamentoId}`);
        const data = await response.json();
        
        gridFotosAntes.innerHTML = ''; // Limpa o "Carregando..."
        gridFotosDepois.innerHTML = ''; // Limpa o "Carregando..."

        if (data.status === 'sucesso') {
            if (data.fotos.antes.length === 0) {
                gridFotosAntes.innerHTML = '<span class="loading-fotos">Nenhuma foto "Antes".</span>';
            } else {
                data.fotos.antes.forEach(foto => adicionarPreview(foto, 'antes'));
            }
            
            if (data.fotos.depois.length === 0) {
                gridFotosDepois.innerHTML = '<span class="loading-fotos">Nenhuma foto "Depois".</span>';
            } else {
                data.fotos.depois.forEach(foto => adicionarPreview(foto, 'depois'));
            }
        }
    } catch (err) {
        gridFotosAntes.innerHTML = '<span class="loading-fotos" style="color:red;">Erro ao buscar fotos.</span>';
        gridFotosDepois.innerHTML = '<span class="loading-fotos" style="color:red;">Erro ao buscar fotos.</span>';
    }
}

function fecharModalFotos() {
    modalFotos.classList.remove('active');
}

/**
 * Adiciona a miniatura da foto na grid correta
 */
function adicionarPreview(foto, tipo) {
    const grid = (tipo === 'antes') ? gridFotosAntes : gridFotosDepois;
    
    // Limpa a mensagem "Nenhuma foto" se for a primeira
    if (grid.querySelector('.loading-fotos')) {
        grid.innerHTML = '';
    }
    
    const imgWrapper = document.createElement('div');
    imgWrapper.className = 'photo-preview';
    imgWrapper.innerHTML = `<img src="${foto.caminho_arquivo}" alt="Foto ${tipo}">`;
    grid.appendChild(imgWrapper);
}

/**
 * Lida com o upload do arquivo
 */
async function handleFotoUpload(input, tipo) {
    if (isUploading) return;
    if (input.files.length === 0) return;

    isUploading = true;
    const file = input.files[0];
    const agendamentoId = hiddenAgendamentoId.value;
    const grid = (tipo === 'antes') ? gridFotosAntes : gridFotosDepois;
    
    // --- CORREÇÃO AQUI ---
    // Guardamos a referência do elemento PAI (a Label) numa variável
    const labelBotao = input.parentElement; 
    const conteudoOriginal = labelBotao.innerHTML;
    
    // Feedback de Upload (Usa a variável labelBotao, não input.parentElement)
    labelBotao.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Enviando...';

    const formData = new FormData();
    formData.append('acao', 'upload_foto');
    formData.append('agendamento_id', agendamentoId);
    formData.append('tipo_foto', tipo);
    formData.append('foto', file);

    try {
        const response = await fetch('pages/agendamentos/actions.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.status === 'sucesso') {
            
            // --- MUDANÇA AQUI ---
            // 1. Seleciona a grid correta
            const gridAlvo = (tipo === 'antes') ? gridFotosAntes : gridFotosDepois;
            
            // 2. Limpa o conteúdo atual (remove a foto antiga visualmente)
            gridAlvo.innerHTML = ''; 
            
            // 3. Adiciona a nova foto
            adicionarPreview(data, tipo);
            // --------------------
            
        } else {
            alert('Erro no upload: ' + data.msg);
        }
    } catch (err) {
        console.error(err);
        alert('Erro de comunicação ao enviar foto.');
    }

    // Restaura o botão de upload usando a referência segura
    isUploading = false;
    if (labelBotao) {
        labelBotao.innerHTML = conteudoOriginal;
        // Não precisamos limpar o input.value porque o input foi recriado ao restaurar o HTML
    }
}

/**
 * Marca o serviço como 'Concluído - Aguardando Validação'
 */
async function marcarServicoConcluido() {
    if (isUploading) {
        alert("Por favor, aguarde o término do upload das fotos.");
        return;
    }
    
    const agendamentoId = hiddenAgendamentoId.value;
    if (!confirm(`Tem certeza que deseja marcar a OS deste agendamento (ID: ${agendamentoId}) como concluída?`)) {
        return;
    }

    btnMarcarConcluido.disabled = true;
    btnMarcarConcluido.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Salvando...';

    const formData = new FormData();
    formData.append('acao', 'marcar_concluido');
    formData.append('agendamento_id', agendamentoId);

    try {
        const response = await fetch('pages/agendamentos/actions.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.status === 'sucesso') {
            alert(data.msg);
            // Remove a linha da tabela na página principal
            const row = document.getElementById(`agendamento-row-${agendamentoId}`);
            if (row) {
                row.remove();
            }
            fecharModalFotos();
        } else {
            alert('Erro: ' + data.msg);
        }
    } catch (err) {
        alert('Erro de comunicação.');
    }

    // Reabilita o botão em caso de falha
    if (btnMarcarConcluido) {
        btnMarcarConcluido.disabled = false;
        btnMarcarConcluido.innerHTML = '<i class="bi bi-check-all"></i> Marcar Serviço como Concluído';
    }
    isUploading = false;
}