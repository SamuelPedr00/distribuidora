/**
 * Módulo de Crédito
 * Gerencia adição/remoção de itens de crédito
 */

class CreditoModule {
    constructor() {
        this.indexCredito = 1;
        this.init();
    }

    init() {
        this.bindEvents();
        console.log('✅ Módulo de crédito inicializado');
    }

    bindEvents() {
        // Adicionar item de crédito
        const addItemCreditoBtn = document.getElementById('addItemCredito');
        if (addItemCreditoBtn) {
            addItemCreditoBtn.addEventListener('click', () => {
                this.adicionarItemCredito();
            });
        }

        // Remover item de crédito (já gerenciado pelo módulo de vendas)
        // Buscar preços (já gerenciado pelo módulo de vendas)
    }

    adicionarItemCredito() {
        const container = document.getElementById('itensCredito');
        const newRow = this.criarItemCredito(this.indexCredito);
        
        container.appendChild(newRow);
        this.indexCredito++;
        
        console.log(`➕ Item de crédito adicionado (índice: ${this.indexCredito - 1})`);
    }

    criarItemCredito(index) {
        const div = document.createElement('div');
        div.classList.add('form-row', 'item-venda'); // Usando mesma classe para compatibilidade
        
        const produtos = window.APP_DATA.produtos;
        const produtoOptions = produtos.map(produto => 
            `<option value="${produto.id}">${produto.nome} (${produto.codigo})</option>`
        ).join('');

        div.innerHTML = `
            <div class="form-group">
                <label>Produto</label>
                <select name="itens[${index}][produto_id]" class="produto-select" required>
                    <option value="" disabled selected>Selecione um produto</option>
                    ${produtoOptions}
                </select>
            </div>
            <div class="form-group">
                <label>Quantidade</label>
                <input type="number" name="itens[${index}][quantidade]" min="1" required>
            </div>
            <div class="form-group">
                <label>Preço</label>
                <select name="itens[${index}][preco]" class="select-preco" required>
                    <option value="">Selecione um produto primeiro</option>
                </select>
            </div>
            <div class="form-group flex-center">
                <button type="button" class="btn btn-danger btn-sm remove-item">🗑️ Remover</button>
            </div>
        `;

        return div;
    }

    // Validar formulário de crédito
    validarFormularioCredito() {
        const itens = document.querySelectorAll('#itensCredito .item-venda');
        const cliente = document.getElementById('cliente_id_credito').value;
        
        if (itens.length === 0) {
            alert('❌ Adicione ao menos um item ao crédito.');
            return false;
        }

        if (!cliente) {
            alert('❌ Selecione um cliente.');
            return false;
        }

        for (let item of itens) {
            const produto = item.querySelector('.produto-select').value;
            const quantidade = item.querySelector('input[name*="[quantidade]"]').value;
            const preco = item.querySelector('.select-preco').value;

            if (!produto || !quantidade || !preco) {
                alert('❌ Preencha todos os campos dos itens adicionados.');
                return false;
            }

            if (parseInt(quantidade) <= 0) {
                alert('❌ A quantidade deve ser maior que zero.');
                return false;
            }
        }

        return true;
    }

    // Calcular total do crédito
    calcularTotalCredito() {
        const itens = document.querySelectorAll('#itensCredito .item-venda');
        let total = 0;

        itens.forEach(item => {
            const quantidade = parseInt(item.querySelector('input[name*="[quantidade]"]').value) || 0;
            const preco = parseFloat(item.querySelector('.select-preco').value) || 0;
            total += quantidade * preco;
        });

        return total;
    }

    // Limpar formulário de crédito
    limparFormularioCredito() {
        document.getElementById('itensCredito').innerHTML = '';
        document.getElementById('cliente_id_credito').value = '';
        this.indexCredito = 1;
        console.log('🧹 Formulário de crédito limpo');
    }

    // Obter resumo do crédito para exibição
    obterResumoCredito() {
        const itens = document.querySelectorAll('#itensCredito .item-venda');
        const cliente = document.getElementById('cliente_id_credito');
        const nomeCliente = cliente.options[cliente.selectedIndex]?.text || 'Cliente não selecionado';
        
        let resumo = {
            cliente: nomeCliente,
            itens: [],
            total: 0
        };

        itens.forEach(item => {
            const produtoSelect = item.querySelector('.produto-select');
            const quantidadeInput = item.querySelector('input[name*="[quantidade]"]');
            const precoSelect = item.querySelector('.select-preco');

            if (produtoSelect.value && quantidadeInput.value && precoSelect.value) {
                const produtoNome = produtoSelect.options[produtoSelect.selectedIndex].text;
                const quantidade = parseInt(quantidadeInput.value);
                const preco = parseFloat(precoSelect.value);
                const subtotal = quantidade * preco;

                resumo.itens.push({
                    produto: produtoNome,
                    quantidade: quantidade,
                    preco: preco,
                    subtotal: subtotal
                });

                resumo.total += subtotal;
            }
        });

        return resumo;
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.creditoModule = new CreditoModule();
});