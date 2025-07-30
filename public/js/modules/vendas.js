/**
 * Módulo de Vendas
 * Gerencia adição/remoção de itens de venda e busca de preços
 */

class VendasModule {
    constructor() {
        this.index = 1;
        this.init();
    }

    init() {
        this.bindEvents();
        console.log('✅ Módulo de vendas inicializado');
    }

    bindEvents() {
        // Adicionar item de venda
        const addItemBtn = document.getElementById('addItem');
        if (addItemBtn) {
            addItemBtn.addEventListener('click', () => {
                this.adicionarItem();
            });
        }

        // Remover item (delegação de eventos)
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-item')) {
                this.removerItem(e.target);
            }
        });

        // Buscar preços quando produto é selecionado (delegação de eventos)
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('produto-select')) {
                this.buscarPrecosProduto(e.target);
            }
        });

        // Reverter vendas
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-reverter')) {
                e.preventDefault();
                this.reverterVenda(e.target.dataset.id);
            }
        });
    }

    adicionarItem() {
        const container = document.getElementById('itensVenda');
        const newRow = this.criarItemVenda(this.index);
        
        container.appendChild(newRow);
        this.index++;
        
        console.log(`➕ Item de venda adicionado (índice: ${this.index - 1})`);
    }

    criarItemVenda(index) {
        const div = document.createElement('div');
        div.classList.add('form-row', 'item-venda');
        
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

    removerItem(button) {
        const itemVenda = button.closest('.item-venda');
        if (itemVenda) {
            itemVenda.remove();
            console.log('🗑️ Item de venda removido');
        }
    }

    async buscarPrecosProduto(selectElement) {
        const produtoId = selectElement.value;
        const itemVenda = selectElement.closest('.item-venda');
        const selectPreco = itemVenda.querySelector('.select-preco');

        if (!produtoId) {
            selectPreco.innerHTML = '<option value="">Selecione um produto primeiro</option>';
            return;
        }

        try {
            console.log(`💰 Buscando preços do produto: ${produtoId}`);
            
            const response = await fetch(`/produto/precos/${produtoId}`);
            const data = await response.json();

            let options = '';

            if (data.preco_venda_atual) {
                options += `
                    <option value="${data.preco_venda_atual}">
                        Unitário - R$ ${parseFloat(data.preco_venda_atual).toFixed(2).replace('.', ',')}
                    </option>
                `;
            }

            if (data.preco_venda_fardo) {
                options += `
                    <option value="${data.preco_venda_fardo}">
                        Fardo - R$ ${parseFloat(data.preco_venda_fardo).toFixed(2).replace('.', ',')}
                    </option>
                `;
            }

            selectPreco.innerHTML = options || '<option value="">Nenhum preço disponível</option>';

        } catch (error) {
            console.error('❌ Erro ao buscar preços:', error);
            selectPreco.innerHTML = '<option value="">Erro ao carregar preços</option>';
            alert('Erro ao buscar preços do produto.');
        }
    }

    async reverterVenda(vendaId) {
        if (!confirm('Tem certeza que deseja reverter esta venda?')) {
            return;
        }

        try {
            console.log(`🔄 Revertendo venda: ${vendaId}`);
            
            const response = await fetch(`/vendas/reverter/${vendaId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.APP_DATA.csrfToken,
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (data.mensagem) {
                alert(data.mensagem);
                location.reload();
            } else if (data.erro) {
                alert('Erro: ' + data.erro);
            } else {
                alert('Erro inesperado.');
            }

        } catch (error) {
            console.error('❌ Erro ao reverter venda:', error);
            alert('Erro ao reverter venda.');
        }
    }

    // Validar formulário antes do envio
    validarFormulario() {
        const itens = document.querySelectorAll('#itensVenda .item-venda');
        
        if (itens.length === 0) {
            alert('❌ Adicione ao menos um item à venda.');
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

    // Calcular total da venda
    calcularTotal() {
        const itens = document.querySelectorAll('#itensVenda .item-venda');
        let total = 0;

        itens.forEach(item => {
            const quantidade = parseInt(item.querySelector('input[name*="[quantidade]"]').value) || 0;
            const preco = parseFloat(item.querySelector('.select-preco').value) || 0;
            total += quantidade * preco;
        });

        return total;
    }

    // Limpar formulário
    limparFormulario() {
        document.getElementById('itensVenda').innerHTML = '';
        document.getElementById('observacoes_venda').value = '';
        this.index = 1;
        console.log('🧹 Formulário de venda limpo');
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.vendasModule = new VendasModule();
});