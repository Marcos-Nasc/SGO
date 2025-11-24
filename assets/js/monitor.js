// assets/js/monitor.js

const modalMonitor = document.getElementById('modalMonitor');

function abrirModalMonitor(dadosLinha) {
    if (!modalMonitor) return;

    // Feedback de carregamento
    const tituloElement = document.getElementById('monitorTitulo');
    if(tituloElement) tituloElement.innerText = "Carregando detalhes...";
    
    modalMonitor.classList.add('active');

    const formData = new FormData();
    formData.append('acao', 'buscar_detalhes_completo');
    formData.append('venda_id', dadosLinha.venda_id);

    fetch('pages/monitor/actions.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'sucesso') {
            preencherModalCompleto(data.dados);
        } else {
            // CORREÇÃO 1: Substituí alert(data.msg) por showNotification
            if (typeof showNotification === 'function') {
                showNotification('erro', data.msg);
            } else {
                alert(data.msg); // Fallback
            }
            fecharModalMonitor();
        }
    })
    .catch(err => {
        console.error(err);
        // CORREÇÃO 2: Substituí alert(...) por showNotification
        if (typeof showNotification === 'function') {
            showNotification('erro', "Erro de comunicação ao buscar detalhes.");
        }
        fecharModalMonitor(); // Opcional: fecha o modal se der erro grave
    });
}

function preencherModalCompleto(d) {
    // 1. Cabeçalho
    document.getElementById('monitorTitulo').innerText = "Detalhes da OS " + d.numero_os;
    document.getElementById('monitorCliente').innerText = d.cliente_nome;
    document.getElementById('monitorServico').innerText = d.servico_nome;
    document.getElementById('monitorOS').innerText = d.numero_os + (d.numero_nf !== 'N/A' ? ` / NF: ${d.numero_nf}` : '');
    document.getElementById('monitorData').innerText = d.data_venda;

    // 2. LÓGICA FINANCEIRA AVANÇADA
    let htmlFinanceiro = '';
    
    if (d.condicao_pagamento === 'A Prazo') {
        // Cálculo de datas estimadas
        const dataVendaObj = new Date(d.data_venda_raw);
        const parcelas = parseInt(d.qtde_parcelas) || 1;
        
        // Data Final (Estimada: Data Venda + Meses das parcelas)
        const dataFinalObj = new Date(dataVendaObj);
        dataFinalObj.setMonth(dataFinalObj.getMonth() + parcelas);
        const dataFinalStr = dataFinalObj.toLocaleDateString('pt-BR');

        // Status dos 50%
        let status50 = "";
        let classe50 = "";
        const entrada = parseFloat(d.valor_entrada_raw) || 0;
        const total = parseFloat(d.valor_final_raw) || 0;
        const metade = total / 2;

        if (entrada >= metade) {
            status50 = "Liberado (Entrada Cobriu)";
            classe50 = "badge-aprovado";
        } else if (d.data_previsao_cobranca) {
            status50 = d.data_previsao_cobranca;
        } else {
            status50 = "Aguardando Definição";
            classe50 = "badge-pendente";
        }

        htmlFinanceiro = `
            <div class="finance-grid" style="border-bottom: 1px solid var(--cor-borda); grid-template-columns: repeat(4, 1fr);">
                <div class="finance-item">
                    <label>Total</label>
                    <strong style="color:#5c9eff;">R$ ${d.valor_final}</strong>
                </div>
                <div class="finance-item">
                    <label>Entrada</label>
                    <span>R$ ${d.valor_entrada}</span>
                </div>
                <div class="finance-item parcela">
                    <label>Parcelamento</label>
                    <span>${d.qtde_parcelas}x de R$ ${d.valor_parcela}</span>
                </div>
                <div class="finance-item">
                    <label>Condição</label>
                    <span>${d.condicao_pagamento}</span>
                </div>
            </div>
            
            <div class="finance-grid" style="margin-top:10px; grid-template-columns: 1fr 1fr;">
                <div class="finance-item" style="padding: 10px; border-radius: 6px;">
                    <label><i class="bi bi-calendar-check"></i> Liberação (50%)</label>
                    <span>
                        ${status50}
                    </span>
                </div>
                <div class="finance-item" style="padding: 10px; border-radius: 6px;">
                    <label><i class="bi bi-flag-fill"></i> Quitação Estimada</label>
                    <strong style="color: #5c9eff; display:block; margin-top:5px;">${dataFinalStr}</strong>
                </div>
            </div>
        `;
    } else {
        // A Vista
        htmlFinanceiro = `
            <div class="finance-grid">
                <div class="finance-item">
                    <label>Valor Total</label>
                    <strong style="color:#198754;">R$ ${d.valor_final}</strong>
                </div>
                <div class="finance-item">
                    <label>Condição</label>
                    <strong>${d.condicao_pagamento}</strong>
                </div>
                <div class="finance-item">
                    <label>Status Pagamento</label>
                    <span class="badge badge-aprovado">Quitado</span>
                </div>
            </div>
        `;
    }

    // Injeta o HTML Financeiro
    const containerFinanceiro = document.getElementById('monitorContainerFinanceiro');
    if(containerFinanceiro) containerFinanceiro.innerHTML = htmlFinanceiro;


    // 3. Preenche Status e Agendamento
    const statusFinal = d.agendamento ? d.agendamento.status : d.status_venda;
    document.getElementById('monitorStatus').innerText = statusFinal;

    if (d.agendamento) {
        document.getElementById('monitorAgendamento').innerText = d.agendamento.data;
        document.getElementById('monitorObs').innerText = d.agendamento.obs || "Nenhuma observação.";
    } else {
        document.getElementById('monitorAgendamento').innerText = "Não agendado";
        document.getElementById('monitorObs').innerText = "-";
    }
    
    // 4. Fotos (Se houver)
    const areaFotos = document.getElementById('monitorFotosArea');
    if (areaFotos) {
        let htmlFotos = '';
        if (d.fotos_info.tem_fotos) {
            htmlFotos = `<div class="form-separator"></div><h4><i class="bi bi-images"></i> Fotos do Serviço (${d.fotos_info.status_validacao})</h4><div class="validation-photos">`;
            
            // Antes
            if (d.fotos.antes.length > 0) {
                htmlFotos += `<div class="photo-column"><h5>Antes</h5><div class="photo-validation-grid">`;
                d.fotos.antes.forEach(f => {
                    htmlFotos += `<a href="${f.caminho}" target="_blank"><img src="${f.caminho}"></a>`;
                });
                htmlFotos += `</div></div>`;
            }
            // Depois
            if (d.fotos.depois.length > 0) {
                htmlFotos += `<div class="photo-column"><h5>Depois</h5><div class="photo-validation-grid">`;
                d.fotos.depois.forEach(f => {
                    htmlFotos += `<a href="${f.caminho}" target="_blank"><img src="${f.caminho}"></a>`;
                });
                htmlFotos += `</div></div>`;
            }
            htmlFotos += `</div>`;
        }
        areaFotos.innerHTML = htmlFotos;
    }
}

function fecharModalMonitor() {
    if (modalMonitor) modalMonitor.classList.remove('active');
}