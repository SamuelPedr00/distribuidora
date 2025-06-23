<!-- Modal de Edição de Produto -->
<div id="modalEditarProduto" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>✏️ Editar Produto</h3>

        {{-- Formulário de edição --}}
        <form id="formEditarProduto" method="POST">
            @csrf
            <input type="hidden" id="editProdutoId" name="id">

            <div class="form-group">
                <label for="editNome">Nome</label>
                <input type="text" id="editNome" name="nome">
            </div>
            <div class="form-group">
                <label for="editCodigo">Código</label>
                <input type="text" id="editCodigo" name="codigo">
            </div>
            <div class="form-group">
                <label for="editCategoria">Categoria</label>
                <input type="text" id="editCategoria" name="categoria">
            </div>
            <div class="form-group">
                <label for="editCompra">Preço de Compra</label>
                <input type="number" id="editCompra" name="compra" step="0.01">
            </div>
            <div class="form-group">
                <label for="editVenda">Preço de Venda</label>
                <input type="number" id="editVenda" name="venda" step="0.01">
            </div>

            <div class="form-row">
                <button type="submit" class="btn btn-primary">💾 Salvar Alterações</button>
                <button type="button" class="btn btn-danger" onclick="fecharModal()">Cancelar</button>
            </div>
        </form>

        {{-- Formulário de exclusão --}}
        <form id="formExcluirProduto" method="POST" style="margin-top: 20px;">
            @csrf
            <button type="submit" class="btn btn-danger btn-full">🗑️ Excluir Produto</button>
        </form>
    </div>
</div>
