/**
 * Módulo de Clientes
 * Gerencia operações relacionadas aos clientes
 */

class ClientesModule {
    constructor() {
        this.clientes = window.APP_DATA.clientes || [];
        this.init();
    }

    init() {
        this.bindEvents();
        console.log('✅ Módulo de clientes inicializado');
    }

    bindEvents() {
        // Validação do formulário de cadastro
        const formCliente = document.querySelector('form[action*="clientes.store"]');
        if (formCliente) {
            formCliente.addEventListener('submit', (e) => {
                if (!this.validarFormularioCliente(formCliente)) {
                    e.preventDefault();
                }
            });
        }

        // Auto-formatação do nome (primeira letra maiúscula)
        const nomeCliente = document.getElementById('nomeCliente');
        if (nomeCliente) {
            nomeCliente.addEventListener('blur', (e) => {
                e.target.value = this.formatarNome(e.target.value);
            });
        }
    }

    validarFormularioCliente(form) {
        const nome = form.querySelector('#nomeCliente').value.trim();

        if (!nome) {
            alert('❌ Nome do cliente é obrigatório');
            return false;
        }

        if (nome.length < 2) {
            alert('❌ Nome do cliente deve ter pelo menos 2 caracteres');
            return false;
        }

        // Verificar se já existe um cliente com este nome
        if (this.verificarNomeExistente(nome)) {
            if (!confirm('⚠️ Já existe um cliente com este nome. Deseja continuar?')) {
                return false;
            }
        }

        return true;
    }

    verificarNomeExistente(nome) {
        return this.clientes.some(cliente => 
            cliente.nome.toLowerCase() === nome.toLowerCase()
        );
    }

    formatarNome(nome) {
        return nome
            .toLowerCase()
            .split(' ')
            .map(palavra => palavra.charAt(0).toUpperCase() + palavra.slice(1))
            .join(' ');
    }

    // Função global para abrir modal de edição (chamada pelo HTML)
    abrirModalCliente(id, nome) {
        const modal = document.getElementById('modal-editar-cliente');
        if (!modal) {
            console.error('❌ Modal de edição de cliente não encontrado');
            return;
        }

        // Preencher campos do modal
        const clienteIdField = document.getElementById('cliente_id');
        const clienteNomeField = document.getElementById('cliente_nome');

        if (clienteIdField) clienteIdField.value = id;
        if (clienteNomeField) clienteNomeField.value = nome;

        // Atualizar action do formulário
        const form = document.getElementById('form-editar-cliente');
        if (form) {
            form.action = `/clientes/${id}`;
        }

        // Mostrar modal
        modal.style.display = 'flex';
        
        console.log(`✏️ Editando cliente: ${nome} (ID: ${id})`);
    }

    fecharModalCliente() {
        const modal = document.getElementById('modal-editar-cliente');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Buscar cliente por ID
    buscarClientePorId(id) {
        return this.clientes.find(cliente => cliente.id === parseInt(id));
    }

    // Buscar clientes por nome
    buscarClientesPorNome(nome) {
        return this.clientes.filter(cliente => 
            cliente.nome.toLowerCase().includes(nome.toLowerCase())
        );
    }

    // Obter clientes com crédito
    obterClientesComCredito() {
        // Esta informação viria do backend, mas podemos filtrar aqui se necessário
        return this.clientes.filter(cliente => cliente.credito > 0);
    }

    // Adicionar novo cliente à lista local (após cadastro bem-sucedido)
    adicionarCliente(cliente) {
        this.clientes.push(cliente);
        console.log(`➕ Cliente adicionado: ${cliente.nome}`);
    }

    // Atualizar cliente na lista local
    atualizarCliente(id, dadosAtualizados) {
        const index = this.clientes.findIndex(cliente => cliente.id === parseInt(id));
        if (index !== -1) {
            this.clientes[index] = { ...this.clientes[index], ...dadosAtualizados };
            console.log(`✏️ Cliente atualizado: ${dadosAtualizados.nome || this.clientes[index].nome}`);
        }
    }

    // Remover cliente da lista local
    removerCliente(id) {
        const index = this.clientes.findIndex(cliente => cliente.id === parseInt(id));
        if (index !== -1) {
            const cliente = this.clientes.splice(index, 1)[0];
            console.log(`🗑️ Cliente removido: ${cliente.nome}`);
        }
    }

    // Validar formulário de edição
    validarFormularioEdicao(form) {
        const nome = form.querySelector('#cliente_nome').value.trim();

        if (!nome) {
            alert('❌ Nome do cliente é obrigatório');
            return false;
        }

        if (nome.length < 2) {
            alert('❌ Nome do cliente deve ter pelo menos 2 caracteres');
            return false;
        }

        return true;
    }

    // Obter estatísticas dos clientes
    obterEstatisticas() {
        const total = this.clientes.length;
        const comCredito = this.clientes.filter(c => c.credito > 0).length;
        const totalCredito = this.clientes.reduce((sum, c) => sum + (c.credito || 0), 0);

        return {
            total,
            comCredito,
            totalCredito,
            semCredito: total - comCredito
        };
    }

    // Exportar lista de clientes (CSV)
    exportarCSV() {
        const headers = ['ID', 'Nome', 'Crédito', 'Data Cadastro'];
        const rows = this.clientes.map(cliente => [
            cliente.id,
            cliente.nome,
            cliente.credito || 0,
            cliente.created_at || ''
        ]);

        const csvContent = [headers, ...rows]
            .map(row => row.map(field => `"${field}"`).join(','))
            .join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `clientes_${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        console.log('📊 Lista de clientes exportada');
    }
}

// Tornar funções globais para uso no HTML
window.abrirModalCliente = function(id, nome) {
    if (window.clientesModule) {
        window.clientesModule.abrirModalCliente(id, nome);
    }
};

window.fecharModalCliente = function() {
    if (window.clientesModule) {
        window.clientesModule.fecharModalCliente();
    }
};

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.clientesModule = new ClientesModule();
});