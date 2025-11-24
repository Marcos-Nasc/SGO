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

<div class="page-header" style="margin-bottom: 20px;">
    <h2>Gerenciamento de Clientes (Mantenedores)</h2>
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

<div class="content-widget" style="padding: 15;">
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
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($m = $resultado->fetch_assoc()):
                    $statusClass = $m['status'] == 'Ativo' ? 'badge-aprovado' : 'badge-rejeitado';
                    $tel = $m['telefone'];

                    // Tratamento de segurança para o JSON do botão Editar
                    $jsonCliente = htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8');
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
                            <button class="btn-details" onclick='editarMantenedor(<?php echo $jsonCliente; ?>)' style="padding: 10px;">
                                <i class="bi bi-pencil-square"></i> Editar
                            </button>

                            <button class="btn-details"
                                style="background-color: var(--cor-card); color: var(--cor-link-ativo-texto); border: 1px solid var(--cor-link-ativo-texto); padding: 10px"
                                onclick="verContratos(<?php echo $m['id']; ?>, '<?php echo addslashes($m['nome']); ?>')">
                                <i class="bi bi-file-earmark-text-fill"></i> Contratos
                            </button>

                            <button class="btn-delete" title="Excluir Cliente"
                                onclick="excluirMantenedor(<?php echo $m['id']; ?>)" style="color: red; border: 1px solid red; font-size: 0.9em">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding:30px; color:var(--cor-texto-secundario);">Nenhum cliente encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="modalMantenedor" class="modal-overlay">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="tituloModalMantenedor">Editar Cliente</h3>
            <button class="close-modal" onclick="fecharModalMantenedor()">&times;</button>
        </div>

        <form id="formMantenedor">
            <input type="hidden" name="acao" value="salvar_mantenedor">
            <input type="hidden" name="id" id="mantenedorId">

            <div class="modal-form-grid">
                <div class="form-group full-width">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" id="mantenedorNome" required>
                </div>
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" id="mantenedorEmail">
                </div>
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone" id="mantenedorTelefone">
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
                <button type="submit" class="btn-primary">Salvar Alterações</button>
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
            <button type="button" class="btn-back" onclick="fecharModalContratos()">Fechar</button>
        </div>
    </div>
</div>

<div id="modalEditarContrato" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 id="tituloEditarContrato">Editar Contrato</h3>
            <button class="close-modal" onclick="fecharModalEditarContrato()">&times;</button>
        </div>

        <form id="formEditarContrato">
            <input type="hidden" name="acao" value="editar_contrato">
            <input type="hidden" name="id" id="editContratoId">

            <div class="form-group">
                <label>Filial (Cemitério)</label>
                <select name="cemiterio_id" id="editContratoCemiterio" required>
                </select>
            </div>

            <div class="form-group">
                <label>Número do Contrato</label>
                <input type="text" name="numero" id="editContratoNumero" required>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px;">
                <div class="form-group">
                    <label>Jazigo</label>
                    <input type="text" name="jazigo" id="editContratoJazigo">
                </div>
                <div class="form-group">
                    <label>Quadra</label>
                    <input type="text" name="quadra" id="editContratoQuadra">
                </div>
                <div class="form-group">
                    <label>Bloco</label>
                    <input type="text" name="bloco" id="editContratoBloco">
                </div>
            </div>

            <div class="form-separator"></div>
            <div class="modal-footer-buttons" style="justify-content: flex-end;">
                <button type="button" class="btn-back" onclick="fecharModalEditarContrato()">Cancelar</button>
                <button type="submit" class="btn-primary">Salvar Edição</button>
            </div>
        </form>
    </div>
</div>