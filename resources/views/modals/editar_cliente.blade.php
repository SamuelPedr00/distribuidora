<div id="modal-editar-cliente" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Editar Cliente</h3>

        <form id="form-editar-cliente" method="POST" action="{{ route('clientes.update', 0) }}">
            @csrf
            @method('PUT')

            <input type="hidden" name="cliente_id" id="cliente_id">

            <div class="form-group">
                <label for="cliente_nome">Nome do Cliente</label>
                <input type="text" name="nome" id="cliente_nome" required>
            </div>

            <div class="form-row">
                <button type="submit" class="btn btn-success">Salvar</button>
                <button type="button" class="btn btn-secondary" onclick="fecharModalCliente()">Cancelar</button>
            </div>
        </form>
    </div>
</div>
