<?php
// pages/mantenedores/view.php
$nivel = $_SESSION['usuario_nivel'];
if ($nivel != 'Administrador' && $nivel != 'Gestor' && $nivel != 'Vendedor') {
    echo '<div class="alert alert-danger">Acesso negado.</div>';
    exit;
}

$busca = $_GET['busca'] ?? '';
$sql = "SELECT * FROM mantenedores WHERE nome LIKE '%$busca%' OR email LIKE '%$busca%' ORDER BY nome ASC";
$resultado = $conexao->query($sql);
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Gerenciamento de Clientes (Mantenedores)</h2>
    <button class="btn-primary" onclick="abrirModalMantenedor()">
        <i class="bi bi-person-plus-fill"></i> Novo Cliente
    </button>
</div>

<div class="content-widget" style="margin-bottom: 20px; padding: 20px;">
    <form method="GET" action="index.php" style="display: flex; gap: 15px;">
        <input type="hidden" name="page" value="mantenedores">
        <input type="hidden" name="action" value="view">
        <div class="form-group" style="flex-grow: 1; margin-bottom: 0;">
            <input type="text" name="busca" placeholder="Buscar por nome, e-mail ou telefone..." value="<?php echo htmlspecialchars($busca); ?>">
        </div>
        <button type="submit" class="btn-primary" style="height: 42px;">
            <i class="bi bi-search"></i> Buscar
        </button>
    </form>
</div>

<div class="content-widget" style="padding: 20px;">
    <table class="widget-table">
        <thead>
            <tr>
                <th>Nome Completo</th>
                <th>Contato (E-mail)</th>
                <th>Telefone</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if($resultado && $resultado->num_rows > 0): ?>
                <?php while($m = $resultado->fetch_assoc()): 
                    $statusClass = $m['status'] == 'Ativo' ? 'badge-aprovado' : 'badge-rejeitado';
                    // Formata telefone visualmente (opcional)
                    $tel = $m['telefone']; 
                ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <strong><?php echo htmlspecialchars($m['nome']); ?></strong>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($m['email']); ?></td>
                    <td><?php echo htmlspecialchars($tel); ?></td>
                    <td><span class="badge <?php echo $statusClass; ?>"><?php echo $m['status']; ?></span></td>
                    <td class="acoes-buttons">
                        <button class="btn-details" onclick='editarMantenedor(<?php echo json_encode($m); ?>)'>
                            <i class="bi bi-pencil-square"></i> Editar
                        </button>
                        <button class="btn-approve" style="background-color: var(--cor-link-ativo-fundo); color: var(--cor-link-ativo-texto); border: 1px solid var(--cor-link-ativo-texto);" 
                                onclick="verContratos(<?php echo $m['id']; ?>, '<?php echo htmlspecialchars($m['nome']); ?>')">
                            <i class="bi bi-file-earmark-text"></i> Contratos
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--cor-texto-secundario);">Nenhum cliente encontrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="modalMantenedor" class="modal-overlay">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="tituloModalMantenedor">Novo Cliente</h3>
            <button class="close-modal" onclick="fecharModalMantenedor()">&times;</button>
        </div>

        <form id="formMantenedor">
            <input type="hidden" name="acao" value="salvar_mantenedor">
            <input type="hidden" name="id" id="mantenedorId">

            <div class="modal-form-grid">
                <div class="form-group full-width">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" id="mantenedorNome" placeholder="Ex: João da Silva" required>
                </div>
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" id="mantenedorEmail" placeholder="cliente@email.com">
                </div>
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone" id="mantenedorTelefone" placeholder="(XX) XXXXX-XXXX">
                </div>
                <div class="form-group full-width">
                    <label>Status</label>
                    <select name="status" id="mantenedorStatus">
                        <option value="Ativo">Ativo</option>
                        <option value="Inativo">Inativo</option>
                    </select>
                </div>
            </div>

            <div class="form-separator"></div>
            <div class="modal-footer-buttons" style="justify-content: flex-end;">
                <button type="button" class="btn-back" onclick="fecharModalMantenedor()">Cancelar</button>
                <button type="submit" class="btn-primary">Salvar Dados</button>
            </div>
        </form>
    </div>
</div>

<div id="modalListaContratos" class="modal-overlay">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="tituloModalContratos">Contratos do Cliente</h3>
            <button class="close-modal" onclick="fecharModalContratos()">&times;</button>
        </div>
        
        <div id="listaContratosConteudo"></div>
        
        <div class="form-separator"></div>
        
        <div class="modal-footer-buttons" style="justify-content: flex-end;">
            <button class="btn-primary" id="btnNovoContratoManual" onclick="novoContratoParaMantenedor()">
                <i class="bi bi-plus-lg"></i> Adicionar Contrato
            </button>
        </div>
    </div>
</div>

<script src="assets/js/mantenedores.js"></script>