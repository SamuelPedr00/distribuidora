/**
 * Módulo de Produtos
 * Gerencia edição e operações relacionadas aos produtos
 */

class ProdutosModule {
    constructor() {
        this.produtos = window.APP_DATA.produtos || [];
        this.init();
    }

    init() {
        this.bindEvents();
        console.log('✅ Módulo de produtos inicializado');
    }

    bindEvents() {
        // Modal de edição já é gerenciado pelos includes
        // Aqui podemos adicionar validações e outras funcionalidades
        
        // Validação do formulário de cadastro
        const formProduto = document.getElementById('formProduto');
        if (formProduto) {
            formProduto.addEventListener('submit', (e) => {
                if (!this.validarFormularioProduto(formProduto)) {
                    e.preventDefault();
                }
            });
        }

        // Formatação automática de preços
        this.formatarCamposPreco();
    }

    validarFormularioProduto(form) {
        const nome = form.querySelector('#nomeProduto').value.trim();
        const codigo = form.querySelector('#codigoProduto').value.trim();
        const precoCompra = parseFloat(form.querySelector('#precoCompra').value);
        const precoVenda = parseFloat(form.querySelector('#precoVenda').value);

        if (!nome) {
            alert('❌ Nome do produto é obrigatório');
            return false;
        }

        if (!codigo) {
            alert('❌ Código do produto é obrigatório');
            return false;
        }

        if (precoCompra <= 0) {
            alert('❌ Preço de compra deve ser maior que zero');
            return false;
        }

        if (precoVenda <= 0) {
            alert('❌ Preço de venda deve ser maior que zero');
            return false;
        }

        if (precoVenda <= precoCompra) {
            if (!confirm('⚠️ O preço de venda é menor ou igual ao preço de compra. Deseja continuar?')) {
                return false;
            }
        }

        // Verificar se o código já existe
        if (this.verificarCodigoExistente(codigo)) {
            alert('❌ Já existe um produto com este código');
            return false;
        }

        return true;
    }

    verificarCodigoExistente(codigo) {
        return this.produtos.some(produto => 
            produto.codigo.toLowerCase() === codigo.toLowerCase()
        );
    }

    formatarCamposPreco() {
        const camposPreco = ['#precoCompra', '#precoVenda', '#precoFardo'];
        
        camposPreco.forEach(seletor => {
            const campo = document.querySelector(seletor);
            if (campo) {
                campo.addEventListener('blur', (e) => {
                    const valor = parseFloat(e.target.value);
                    if (!isNaN(valor)) {
                        e.target.value = valor.toFixed(2);
                    }
                });
            }
        });
    }

    // Função global para editar produto (chamada pelo HTML)
    editarProduto(id) {
        const produto = this.produtos.find(p => p.id === id);
        if (!produto) {
            alert('❌ Produto não encontrado');
            return;
        }

        // Abrir modal de edição
        const modal = document.getElementById('modalEditarProduto');
        if (!modal) {
            console.error('❌ Modal de edição não encontrado');
            return;
        }

        // Preencher formulário de edição
        const form = document.getElementById('formEditarProduto');
        if (form) {
            form.action = `/produtos/${produto.id}`;
            
            document.getElementById('editProdutoId').value = produto.id;
            document.getElementById('editNome').value = produto.nome;
            document.getElementById('editCodigo').value = produto.codigo;
            document.getElementById('editCategoria').value = produto.categoria;
            document.getElementById('editCompra').value = produto.preco_compra_atual;
            document.getElementById('editVenda').value = produto.preco_venda_atual;
            document.getElementById('editFardo').value = produto.preco_venda_fardo || '';
        }

        // Preencher formulário de exclusão
        const formExcluir = document.getElementById('formExcluirProduto');
        if (formExcluir) {
            formExcluir.action = `/produtos/desativar/${produto.id}`;
        }

        // Exibir modal
        modal.style.display = 'flex';
        
        console.log(`✏️ Editando produto: ${produto.nome}`);
    }

    // Função para fechar modal
    fecharModalProduto() {
        const modal = document.getElementById('modalEditarProduto');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Buscar produto por ID
    buscarProdutoPorId(id) {
        return this.produtos.find(produto => produto.id === parseInt(id));
    }

    // Buscar produtos por categoria
    buscarProdutosPorCategoria(categoria) {
        return this.produtos.filter(produto => 
            produto.categoria.toLowerCase().includes(categoria.toLowerCase())
        );
    }

    // Calcular margem de lucro
    calcularMargemLucro(precoCompra, precoVenda) {
        if (precoCompra <= 0) return 0;
        return ((precoVenda - precoCompra) / precoCompra) * 100;
    }

    // Obter estatísticas dos produtos
    obterEstatisticas() {
        const total = this.produtos.length;
        const comEstoque = this.produtos.filter(p => (p.estoque?.quantidade || 0) > 0).length;
        const estoqueBaixo = this.produtos.filter(p => (p.estoque?.quantidade || 0) <= 10).length;
        
        const valorTotalEstoque = this.produtos.reduce((total, produto) => {
            const quantidade = produto.estoque?.quantidade || 0;
            const preco = produto.preco_venda_atual || 0;
            return total + (quantidade * preco);
        }, 0);

        return {
            total,
            comEstoque,
            estoqueBaixo,
            valorTotalEstoque
        };
    }
}

// Tornar função global para uso no HTML
window.editarProduto = function(id) {
    if (window.produtosModule) {
        window.produtosModule.editarProduto(id);
    }
};

window.fecharModal = function() {
    if (window.produtosModule) {
        window.produtosModule.fecharModalProduto();
    }
};

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.produtosModule = new ProdutosModule();
});