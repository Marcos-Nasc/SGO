<?php
// A sessão e a conexão com $conexao já foram iniciadas no index.php
$sc_id = $_SESSION['usuario_id']; // ID do "Sucesso do Cliente"

// -- 1. BUSCAR A PRÓXIMA TAREFA NA FILA --
// Busca por DOIS status: o que precisa validar E o que já foi validado (para enviar)
$sql = "
    SELECT 
        a.id AS agendamento_id, 
        a.data_agendada, 
        a.status,
        v.numero_os, 
        v.familia_comparecer,
        m.nome AS mantenedor_nome, 
        m.email AS mantenedor_email,
        p.nome AS servico_nome
    FROM agendamentos a
    JOIN vendas v ON a.venda_id = v.id
    JOIN mantenedores m ON v.mantenedor_id = m.id
    JOIN produtos_servicos p ON v.produto_servico_id = p.id
    WHERE 
        a.status = 'Concluído - Aguardando Validação' -- <-- APENAS PENDENTES
    ORDER BY 
        a.atualizado_em ASC -- Pega o mais antigo da fila
    LIMIT 1
";
$resultado = $conexao->query($sql);
$agendamento = ($resultado && $resultado->num_rows > 0) ? $resultado->fetch_assoc() : null;

$fotos_antes = [];
$fotos_depois = [];
$is_ja_validado = false; // INICIALIZAÇÃO DA VARIÁVEL (Correção do Warning)

if ($agendamento) {
    $ag_id = $agendamento['agendamento_id'];
    // Verifica se o status é 'Finalizado Internamente' (já validado)
    $is_ja_validado = ($agendamento['status'] == 'Finalizado Internamente');
    
    // Busca as fotos
    $sql_fotos = "SELECT id, tipo, caminho_arquivo, status_validacao FROM fotos_servico WHERE agendamento_id = ?";
    $stmt_fotos = $conexao->prepare($sql_fotos);
    $stmt_fotos->bind_param("i", $ag_id);
    $stmt_fotos->execute();
    $res_fotos = $stmt_fotos->get_result();
    while ($foto = $res_fotos->fetch_assoc()) {
        if ($foto['tipo'] == 'antes') {
            $fotos_antes[] = $foto;
        } else {
            $fotos_depois[] = $foto;
        }
    }
}
?>

<div class="page-header" style="margin-bottom: 20px;">
    <h2>Validação de Serviços</h2>
</div>

<?php if ($agendamento): // Se existe uma tarefa, mostra a tela de validação ?>
    
    <div class="content-widget" id="validation-widget">
        <div class="validation-summary">
            <div>
                <h3 style="color: var(--cor-link-ativo-texto); margin-bottom: 10px;">
                    OS: <?php echo htmlspecialchars($agendamento['numero_os']); ?>
                </h3>
                <span style="font-size: 1.2rem; font-weight: 600; color: var(--cor-texto-primario); display: block; margin-bottom: 10px;">
                    <?php echo htmlspecialchars($agendamento['servico_nome']); ?>
                </span>
                <span>Cliente: <?php echo htmlspecialchars($agendamento['mantenedor_nome']); ?></span>
                <span>Família Comparecerá: <?php echo $agendamento['familia_comparecer'] ? 'Sim' : 'Não'; ?></span>
            </div>
            
            <?php if ($is_ja_validado): ?>
                <span class="badge badge-aprovado">Fotos Validadas</span>
            <?php else: ?>
                <span class="badge badge-cobranca">Aguardando Validação</span>
            <?php endif; ?>
        </div>

        <div class="validation-photos">
            <div class="photo-column">
                <h4>Antes</h4>
                <div class="photo-validation-grid">
                    <?php if (empty($fotos_antes)): ?>
                        <p class="text-muted">Nenhuma foto "Antes" enviada.</p>
                    <?php else: ?>
                        <?php foreach ($fotos_antes as $foto): ?>
                            <a href="<?php echo $foto['caminho_arquivo']; ?>" target="_blank" title="Clique para ampliar">
                                <img src="<?php echo $foto['caminho_arquivo']; ?>" alt="Foto Antes">
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="photo-column">
                <h4>Depois</h4>
                <div class="photo-validation-grid">
                     <?php if (empty($fotos_depois)): ?>
                        <p class="text-muted">Nenhuma foto "Depois" enviada.</p>
                    <?php else: ?>
                        <?php foreach ($fotos_depois as $foto): ?>
                             <a href="<?php echo $foto['caminho_arquivo']; ?>" target="_blank" title="Clique para ampliar">
                                <img src="<?php echo $foto['caminho_arquivo']; ?>" alt="Foto Depois">
                             </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="form-separator"></div>

        <div class="validation-actions">
            <div class="form-group" style="flex-grow: 1;">
                <label for="obsValidacao">Observação da validação (Obrigatório se "Invalidar")</label>
                <textarea id="obsValidacao" placeholder="Se invalidar, explique o motivo para o Gestor..."></textarea>
            </div>
            <div class="validation-buttons">
                <button class="btn-validation reject" id="btnInvalidar" 
                        onclick="invalidarServico(<?php echo $agendamento['agendamento_id']; ?>)"
                        <?php if($is_ja_validado) echo 'disabled'; ?>>
                    <i class="bi bi-x-circle-fill"></i> Invalidar
                </button>
                <button class="btn-validation validate" id="btnValidar" 
                        onclick="validarFotos(<?php echo $agendamento['agendamento_id']; ?>)"
                        <?php if($is_ja_validado) echo 'disabled'; ?>>
                    <i class="bi bi-check-circle-fill"></i> Validar Fotos
                </button>
                <button class="btn-validation email <?php if($is_ja_validado) echo 'highlight-next-step'; ?>" id="btnEnviarEmail" 
                        onclick="abrirModalConfirmarEmail(
                            <?php echo $agendamento['agendamento_id']; ?>,
                            '<?php echo htmlspecialchars($agendamento['mantenedor_nome']); ?>',
                            '<?php echo htmlspecialchars($agendamento['mantenedor_email']); ?>'
                        )">
                    <i class="bi bi-send-fill"></i> Enviar E-mail
                </button>
            </div>
        </div>
        
    </div>

<?php else: // Se não há tarefas ?>
    <div class="content-widget">
        <div style="padding: 40px; text-align: center; color: var(--cor-texto-secundario);">
            <i class="bi bi-check2-all" style="font-size: 3rem; margin-bottom: 10px; display:block;"></i>
            <h3>Nenhum serviço aguardando validação!</h3>
            <p>Bom trabalho. A fila de validação está limpa.</p>
        </div>
    </div>
<?php endif; ?>

<div id="modalConfirmarEmail" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Confirmar Envio de E-mail</h3>
            <button class="close-modal" onclick="fecharModalConfirmarEmail()">&times;</button>
        </div>
        
        <p style="font-size: 1rem; color: var(--cor-texto-secundario); margin-bottom: 20px;">
            Você está prestes a enviar o e-mail de conclusão do serviço (com as fotos em anexo) para:
        </p>
        
        <div class="detalhe-item" style="margin-top: 20px;">
            <label>Cliente</label>
            <span id="email-confirm-cliente" style="font-size: 1.1rem; font-weight: 500;">...</span>
        </div>
        <div class="detalhe-item" style="margin-top: 15px;">
            <label>E-mail</label>
            <span id="email-confirm-email" style="font-size: 1.1rem; font-weight: 500;">...</span>
        </div>
        
        <div class="form-separator"></div>
        
        <div class="modal-footer-buttons" style="justify-content: flex-end;">
            <button type="button" class="btn-back" onclick="fecharModalConfirmarEmail()">Cancelar</button>
            <button type="button" class="btn-validation email" id="btnConfirmarEnvioFinal" onclick="executarEnvioEmail(this)">
                <i class="bi bi-send-fill"></i> Confirmar Envio
            </button>
        </div>
    </div>
</div>