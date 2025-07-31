function editarEstoque(produtoId) {
    // Buscar a linha da tabela
    const linha = document.querySelector(`[onclick="editarEstoque(${produtoId})"]`).closest('tr');
    const quantidade = linha.querySelectorAll('td')[2].innerText;

    // Preencher o modal com os dados
    document.getElementById('estoqueProdutoId').value = produtoId;
    document.getElementById('estoqueQuantidade').value = quantidade;

    // Abrir o modal
    document.getElementById('modalEditarEstoque').style.display = 'flex'; // ao abrir
}

function fecharModalEstoque() {
    document.getElementById('modalEditarEstoque').style.display = 'none';
}
