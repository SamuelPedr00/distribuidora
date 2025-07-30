/**
 * Módulo de Fluxo de Caixa
 * Gerencia filtros, relatórios e movimentações do caixa
 */

class CaixaModule {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.carregarDadosIniciais();
        console.log('✅ Módulo de caixa inicializado');
    }

    bindEvents() {
        // Validação do formulário de caixa
        const formCaixa = document.getElementById('formCaixa');
        if (formCaixa) {
            formCaixa.addEventListener('submit', (e) => {
                if (!this.validarFormularioCaixa(formCaixa)) {
                    e.preventDefault();
                }
            });
        }

        // Auto-formatação de valores monetários
        const valorCaixa = document.getElementById('valor_caixa');
        if (valorCaixa) {
            valorCaixa.addEventListener('blur', (e) => {
                const valor = parseFloat(e.target.value);
                if (!isNaN(valor)) {
                    e.target.value = valor.toFixed(2);
                }
            });
        }
    }

    validarFormularioCaixa(form) {
        const tipo = form.querySelector('#tipo_caixa').value;
        const valor = parseFloat(form.querySelector('#valor_caixa').value);
        const categoria = form.querySelector('#categoria_caixa').value;
        const descricao = form.querySelector('#descricaoCaixa').value.trim();

        if (!tipo) {
            alert('❌ Selecione o tipo da movimentação');
            return false;
        }

        if (!categoria) {
            alert('❌ Selecione a categoria');
            return false;
        }

        if (isNaN(valor) || valor <= 0) {
            alert('❌ Valor deve ser maior que zero');
            return false;
        }

        if (!descricao) {
            alert('❌ Descrição é obrigatória');
            return false;
        }

        return true;
    }

    async carregarDadosIniciais() {
        try {
            await this.filtrarCaixa();
        } catch (error) {
            console.error('❌ Erro ao carregar dados iniciais do caixa:', error);
        }
    }

    async filtrarCaixa() {
        const dataInicio = document.getElementById('dataInicio').value;
        const dataFim = document.getElementById('dataFim').value;
        const categoria = document.getElementById('filtroCategoria').value;

        try {
            console.log('🔍 Filtrando dados do caixa...');
            
            const params = new URLSearchParams({
                dataInicio: dataInicio || '',
                dataFim: dataFim || '',
                categoria: categoria || ''
            });

            const response = await fetch(`/caixa/filtro?${params}`);
            const data = await response.json();

            this.atualizarTotais(data);
            this.listarMovimentacoes(data.dados);
            this.listarResumoCategorias(data.resumo);

        } catch (error) {
            console.error('❌ Erro ao filtrar caixa:', error);
            alert('Erro ao carregar dados do caixa');
        }
    }

    atualizarTotais(data) {
        const elementos = {
            totalEntradas: document.getElementById('totalEntradas'),
            totalSaidas: document.getElementById('totalSaidas'),
            saldoPeriodo: document.getElementById('saldoPeriodo')
        };

        if (elementos.totalEntradas) {
            elementos.totalEntradas.textContent = this.formatarValor(data.entradas);
        }
        
        if (elementos.totalSaidas) {
            elementos.totalSaidas.textContent = this.formatarValor(data.saidas);
        }
        
        if (elementos.saldoPeriodo) {
            elementos.saldoPeriodo.textContent = this.formatarValor(data.saldo);
            
            // Adicionar classe de cor baseada no saldo
            elementos.saldoPeriodo.className = 'stat-value';
            if (data.saldo > 0) {
                elementos.saldoPeriodo.classList.add('text-success');
            } else if (data.saldo < 0) {
                elementos.saldoPeriodo.classList.add('text-danger');
            }
        }

        console.log('💰 Totais atualizados:', {
            entradas: data.entradas,
            saidas: data.saidas,
            saldo: data.saldo
        });
    }

    listarMovimentacoes(dados) {
        const tbody = document.getElementById('listaCaixa');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (!dados || dados.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhuma movimentação encontrada</td></tr>';
            return;
        }

        dados.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${this.formatarData(item.data)}</td>
                <td class="${item.tipo === 'entrada' ? 'text-success' : 'text-danger'}">
                    ${item.tipo === 'entrada' ? '💵 Entrada' : '💸 Saída'}
                </td>
                <td>${this.formatarCategoria(item.categoria)}</td>
                <td>${item.descricao}</td>
                <td class="${item.tipo === 'entrada' ? 'text-success' : 'text-danger'}">
                    ${this.formatarValor(item.valor)}
                </td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="window.caixaModule.excluirMovimentacao(${item.id})">
                        🗑️ Excluir
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        console.log(`📋 ${dados.length} movimentações listadas`);
    }

    listarResumoCategorias(resumo) {
        const container = document.getElementById('resumoCategorias');
        if (!container) return;

        container.innerHTML = '';

        if (!resumo || Object.keys(resumo).length === 0) {
            container.innerHTML = '<p class="text-center">Nenhum dado por categoria</p>';
            return;
        }

        for (let categoria in resumo) {
            const valor = resumo[categoria];
            const div = document.createElement('div');
            div.classList.add('categoria-resumo');
            
            if (valor > 0) {
                div.classList.add('categoria-entrada');
            } else if (valor < 0) {
                div.classList.add('categoria-saida');
            }

            div.innerHTML = `
                <span class="categoria-nome">${this.formatarCategoria(categoria)}</span>
                <span class="categoria-valor">${this.formatarValor(Math.abs(valor))}</span>
            `;
            
            container.appendChild(div);
        }

        console.log('📊 Resumo por categorias atualizado');
    }

    limparFiltros() {
        document.getElementById('dataInicio').value = '';
        document.getElementById('dataFim').value = '';
        document.getElementById('filtroCategoria').value = '';
        this.filtrarCaixa();
        
        console.log('🧹 Filtros limpos');
    }

    formatarValor(valor) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(valor || 0);
    }

    formatarData(data) {
        if (!data) return 'Data inválida';
        
        try {
            return new Date(data).toLocaleString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (error) {
            return data;
        }
    }

    formatarCategoria(categoria) {
        const categorias = {
            'venda': '💰 Vendas',
            'compra': '📦 Compras',
            'despesa_operacional': '🏢 Despesas Operacionais',
            'despesa_administrativa': '📋 Despesas Administrativas',
            'prolabore': '👤 Pró-labore',
            'investimento': '📈 Investimentos',
            'emprestimo': '🏦 Empréstimos',
            'impostos': '📋 Impostos',
            'outros': '📝 Outros'
        };

        return categorias[categoria] || categoria;
    }

    async excluirMovimentacao(id) {
        if (!confirm('Tem certeza que deseja excluir esta movimentação?')) {
            return;
        }

        try {
            console.log(`🗑️ Excluindo movimentação: ${id}`);
            
            const response = await fetch(`/caixa/excluir/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.APP_DATA.csrfToken,
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (data.sucesso) {
                await this.filtrarCaixa(); // Recarregar dados
                alert('Movimentação excluída com sucesso!');
            } else {
                alert('Erro ao excluir movimentação: ' + (data.erro || 'Erro desconhecido'));
            }

        } catch (error) {
            console.error('❌ Erro ao excluir movimentação:', error);
            alert('Erro ao excluir movimentação');
        }
    }

    // Obter relatório completo
    async gerarRelatorio(dataInicio, dataFim) {
        try {
            const params = new URLSearchParams({
                dataInicio: dataInicio || '',
                dataFim: dataFim || '',
                formato: 'completo'
            });

            const response = await fetch(`/caixa/relatorio?${params}`);
            const data = await response.json();

            return data;

        } catch (error) {
            console.error('❌ Erro ao gerar relatório:', error);
            throw error;
        }
    }
}

// Funções globais para uso no HTML
window.filtrarCaixa = function() {
    if (window.caixaModule) {
        window.caixaModule.filtrarCaixa();
    }
};

window.limparFiltros = function() {
    if (window.caixaModule) {
        window.caixaModule.limparFiltros();
    }
};

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.caixaModule = new CaixaModule();
});