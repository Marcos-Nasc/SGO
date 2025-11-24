// assets/js/dashboard_gestor.js

document.addEventListener('DOMContentLoaded', () => {
    
    // --- Bloco de Anotações ---
    const textarea = document.getElementById('blocoNotasGestor');
    const feedback = document.getElementById('notasFeedback');
    let timerId = null; // Debounce timer
    
    if (textarea) {
        // 1. Carregar nota
        fetch('pages/dashboard_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'acao=carregar_nota'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'sucesso') {
                textarea.value = data.conteudo || '';
            }
        });

        // 2. Salvar nota com debounce
        textarea.addEventListener('keyup', () => {
            clearTimeout(timerId);
            if (feedback) {
                feedback.textContent = 'Salvando...';
                feedback.style.color = 'var(--cor-texto-secundario)';
            }
            timerId = setTimeout(() => {
                salvarNota(textarea.value);
            }, 2000);
        });
    }

    function salvarNota(conteudo) {
        const formData = new FormData();
        formData.append('acao', 'salvar_nota');
        formData.append('conteudo', conteudo);

        fetch('pages/dashboard_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // USANDO SHOWNOTIFICATION
            if (data.status === 'sucesso' && feedback) {
                feedback.textContent = 'Salvo!';
                feedback.style.color = '#198754';
                // CORREÇÃO: Status 'sucesso' (do PHP) e tempo de 2000ms
                showNotification('sucesso', 'Anotações salvas automaticamente!', 2000); 
            } else if (feedback) {
                feedback.textContent = 'Erro ao salvar.';
                feedback.style.color = '#dc3545';
                showNotification('erro', 'Falha ao salvar anotações.', 4000);
            }
        })
        .catch(err => {
            showNotification('erro', 'Erro de comunicação ao salvar anotações.', 4000);
        });
    }

    // --- LÓGICA DA AGENDA E CALENDÁRIO ---
    
    // Lista Principal (Agenda)
    const listaAgendaPrincipal = document.getElementById('lista-agenda-gestor');
    const filterButtons = document.querySelectorAll('.filter-group .btn-filter');
    
    // Calendário
    const calendarWidget = document.querySelector('.calendar-grid');
    const agendaDiariaBox = document.getElementById('agenda-diaria-calendario');
    const agendaDiariaTitulo = document.getElementById('agenda-diaria-titulo');
    const agendaDiariaLista = document.getElementById('agenda-diaria-lista');

    // Adiciona o listener ao container do calendário
    if (calendarWidget) {
        calendarWidget.addEventListener('click', (e) => {
            const diaEl = e.target.closest('.calendar-day');
            if (diaEl && !diaEl.classList.contains('empty')) {
                // Chama a função que preenche a div ABAIXO do calendário
                carregarAgendaDoCalendario(diaEl);
            }
        });
    }
    
    // Adiciona listeners aos botões de filtro (Hoje, Amanhã, etc.)
    filterButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const filtro = button.id.replace('btn-agenda-', ''); // 'hoje', 'amanha', 'semana', etc.
            // Chama a função que preenche a lista PRINCIPAL
            carregarAgenda(filtro, button);
        });
    });

    /**
     * Carrega a lista PRINCIPAL (Próximos Agendamentos)
     */
    window.carregarAgenda = function(filtro, el) {
        // Limpa seleção do calendário
        document.querySelectorAll('.calendar-day.selected').forEach(day => day.classList.remove('selected'));
        agendaDiariaBox.style.display = 'none';
        
        // Ativa o botão correto
        filterButtons.forEach(btn => btn.classList.remove('active'));
        el.classList.add('active');
        
        // Busca os dados
        fetchAgenda(filtro, null, listaAgendaPrincipal);
    }

    /**
     * Carrega a lista SECUNDÁRIA (Abaixo do Calendário)
     */
    function carregarAgendaDoCalendario(diaEl) {
        const dataSelecionada = diaEl.getAttribute('data-date');
        
        // Limpa seleção dos filtros principais
        filterButtons.forEach(btn => btn.classList.remove('active'));
        
        // Gerencia a seleção do dia
        document.querySelectorAll('.calendar-day.selected').forEach(day => day.classList.remove('selected'));
        diaEl.classList.add('selected');
        
        // Formata o título
        const dataObj = new Date(dataSelecionada + 'T00:00:00');
        const dataFormatada = dataObj.toLocaleDateString('pt-BR', { day: '2-digit', month: 'long' });
        agendaDiariaTitulo.textContent = `Agendamentos para ${dataFormatada}`;
        
        // Mostra a caixa e busca os dados
        agendaDiariaBox.style.display = 'block';
        fetchAgenda('data', dataSelecionada, agendaDiariaLista);
    }

    /**
     * Função Genérica de Busca (Fetch)
     */
    function fetchAgenda(filtro, data, listaElemento) {
        let formData = new FormData();
        formData.append('acao', 'buscar_agendamentos_gestor');
        formData.append('filtro', filtro);
        if (data) {
            formData.append('data_selecionada', data);
        }

        listaElemento.innerHTML = '<li class="agenda-item-vazio">Carregando...</li>';

        fetch('pages/dashboard_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            listaElemento.innerHTML = ''; // Limpa a lista
            if (data.status === 'sucesso' && data.agendamentos.length > 0) {
                data.agendamentos.forEach(item => {
                    const dataAg = new Date(item.data_agendada);
                    const dia = dataAg.getDate();
                    const mes = dataAg.toLocaleString('pt-BR', { month: 'short' }).toUpperCase().replace('.', '');
                    const hora = dataAg.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                    
                    const li = document.createElement('li');
                    li.className = 'agenda-item';
                    li.innerHTML = `
                        <div class="agenda-data">
                            <span class="dia">${dia}</span>
                            <span class="mes">${mes}</span>
                        </div>
                        <div class="agenda-info">
                            <span class="titulo">OS ${item.numero_os} - ${item.cliente_nome}</span>
                            <span class="subtitulo">${item.status} às ${hora}</span>
                        </div>
                    `;
                    listaElemento.appendChild(li);
                });
            } else {
                listaElemento.innerHTML = '<li class="agenda-item-vazio">Nenhum agendamento encontrado.</li>';
            }
        })
        .catch(err => {
            listaElemento.innerHTML = '<li class="agenda-item-vazio">Erro ao carregar agenda.</li>';
            showNotification('error', 'Falha na comunicação ao carregar agenda.', 4000);
        });
    }

});