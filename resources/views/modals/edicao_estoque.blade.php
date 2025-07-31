<!-- Modal Editar Estoque -->
<div id="modalEditarEstoque" class="modal" style="display: none;">
    <div class="modal-content" tabindex="-1">
        <span class="msg-modal-close" onclick="fecharModalEstoque()">&times;</span>
        <h2>Editar Estoque</h2>

        <form method="POST" action="{{ route('estoque.atualizar') }}">
            @csrf
            <input type="hidden" name="produto_id" id="estoqueProdutoId">

            <label for="estoqueQuantidade">Quantidade:</label>
            <input type="number" name="quantidade" id="estoqueQuantidade" min="0" required>

            <div class="btn-group" style="margin-top: 1rem;">
                <button type="submit" class="btn-venda btn-success">💾 Salvar</button>
                <button type="button" class="btn-venda btn-secondary" onclick="fecharModalEstoque()">Cancelar</button>
            </div>
        </form>
    </div>
</div>
