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
    
    // Reseta o botão de concluir, APENAS SE ELE EXISTIR nesta página
    if (btnMarcarConcluido) {
        btnMarcarConcluido.disabled = false;
        btnMarcarConcluido.innerHTML = '<i class="bi bi-check-all"></i> Marcar Serviço como Concluído';
    }

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
        // Erro ao buscar dados (por exemplo, 404)
        gridFotosAntes.innerHTML = '<span class="loading-fotos" style="color:red;">Erro ao buscar fotos.</span>';
        gridFotosDepois.innerHTML = '<span class="loading-fotos" style="color:red;">Erro ao buscar fotos.</span>';
        showNotification('erro', 'Falha na comunicação inicial com o servidor.', 5000);
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
    
    const labelBotao = input.parentElement; 
    const conteudoOriginal = labelBotao.innerHTML;
    
    // Feedback de Upload (Usa a referência da Label)
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
            
            // 1. Limpa o conteúdo atual (modo substituição)
            const gridAlvo = (tipo === 'antes') ? gridFotosAntes : gridFotosDepois;
            gridAlvo.innerHTML = ''; 
            
            // 2. Adiciona a nova foto e notifica sucesso
            adicionarPreview(data, tipo);
            // Alterado para 'sucesso' para garantir a cor verde
            showNotification('sucesso', 'Foto anexada e salva!', 2500); 
            
        } else {
            // Notifica o erro do PHP
            showNotification('erro', 'Falha no upload: ' + data.msg, 4000);
        }
    } catch (err) {
        console.error(err);
        showNotification('erro', 'Erro de comunicação ao enviar foto.', 4000); 
    }

    // Restaura o botão de upload
    isUploading = false;
    if (labelBotao) {
        // Usamos um pequeno timeout para garantir que o JS termine o processamento visual
        setTimeout(() => {
            labelBotao.innerHTML = conteudoOriginal;
        }, 100); 
    }
}

/**
 * Marca o serviço como 'Concluído - Aguardando Validação'
 * AVISO: Usa o modal global de confirmação
 */
function marcarServicoConcluido() {
    // 1. Verifica se há upload em andamento
    if (isUploading) {
        showNotification('warning', "Por favor, aguarde o término do upload das fotos.", 4000);
        return;
    }
    
    const agendamentoId = hiddenAgendamentoId.value;

    // 2. Abre o modal de confirmação personalizado
    abrirGlobalConfirm(
        `Tem certeza que deseja marcar a OS deste agendamento (ID: ${agendamentoId}) como concluída?`,
        'Sim, Concluir',
        async () => {
            // --- LÓGICA DE EXECUÇÃO (Callback) ---
            
            if(btnMarcarConcluido) {
                btnMarcarConcluido.disabled = true;
                btnMarcarConcluido.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Salvando...';
            }

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
                    // Usa data.status ou força 'sucesso'
                    showNotification('sucesso', data.msg, 2500); 
                    
                    // Remove a linha da tabela na página principal
                    const row = document.getElementById(`agendamento-row-${agendamentoId}`);
                    if (row) {
                        row.remove();
                    }
                    
                    // Recarrega a página após 2 segundos (2000ms)
                    setTimeout(() => { 
                        fecharModalFotos(); 
                        location.reload(); 
                    }, 2000);
                    
                } else {
                    showNotification('erro', 'Falha ao concluir: ' + data.msg, 4000); 
                    // Reabilita o botão em caso de erro do PHP
                    restaurarBotaoConcluir();
                }
            } catch (err) {
                showNotification('erro', 'Erro de comunicação ao salvar o status.', 4000);
                restaurarBotaoConcluir();
            }
        },
        'Concluir Serviço' // Título do Modal
    );
}

/**
 * Função auxiliar para restaurar o botão em caso de erro
 */
function restaurarBotaoConcluir() {
    if (btnMarcarConcluido) {
        btnMarcarConcluido.disabled = false;
        btnMarcarConcluido.innerHTML = '<i class="bi bi-check-all"></i> Marcar Serviço como Concluído';
    }
    isUploading = false;
}