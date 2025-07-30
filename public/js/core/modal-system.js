/**
 * Sistema Unificado de Modais
 * Gerencia todos os modais do sistema de forma isolada
 */

class ModalSystem {
    constructor() {
        this.activeModals = new Set();
        this.init();
    }

    init() {
        this.initCreditoModal();
        this.initConfirmacaoModal();
        this.handleGlobalEvents();
        console.log('✅ Sistema de modais inicializado');
    }

    // ===== MODAL DE CRÉDITO (Ver Detalhes) =====
    initCreditoModal() {
        const modalCredito = document.getElementById('modalCredito');
        const fecharModalCredito = document.getElementById('fecharModalCredito');
        
        if (!modalCredito || !fecharModalCredito) {
            console.warn('⚠️ Modal de crédito não encontrado');
            return;
        }

        // Botões para abrir modal de crédito
        document.querySelectorAll('.abrirModalCredito').forEach(botao => {
            botao.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();

                const clienteId = botao.dataset.clienteId;
                const nomeCliente = botao.dataset.clienteNome;

                this.abrirModalCredito(clienteId, nomeCliente);
            });
        });

        // Fechar modal - botão X
        fecharModalCredito.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();
            this.fecharModalCredito();
        });

        // Fechar modal - clique fora
        modalCredito.addEventListener('click', (e) => {
            if (e.target === modalCredito) {
                this.fecharModalCredito();
            }
        });

        // Prevenir propagação dentro do modal
        const modalCreditoContent = modalCredito.querySelector('.modal-credito-content');
        if (modalCreditoContent) {
            modalCreditoContent.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }
    }

    async abrirModalCredito(clienteId, nomeCliente) {
        const modalCredito = document.getElementById('modalCredito');
        
        console.log('📋 Carregando vendas para cliente:', clienteId);

        try {
            const response = await fetch(`/api/cliente/${clienteId}/vendas-pendentes`);
            if (!response.ok) throw new Error('Erro na requisição');
            
            const vendas = await response.json();
            let html = '';
            let total = 0;

            if (vendas.length === 0) {
                html = '<p class="text-center p-lg">Nenhuma venda pendente.</p>';
            } else {
                vendas.forEach(venda => {
                    const subtotal = parseFloat(venda.total_venda || 0);
                    total += subtotal;

                    html += `
                        <div class="card-credito">
                            <p><strong>Venda:</strong> ${venda.numero_venda || 'N/A'}</p>
                            <p><strong>Data:</strong> ${venda.data_venda ? new Date(venda.data_venda).toLocaleString('pt-BR') : 'N/A'}</p>
                            <ul class="mb-sm">
                                ${venda.itens?.length
                                    ? venda.itens.map(item =>
                                        `<li>${item.produto.nome} (${item.quantidade} x R$ ${parseFloat(item.preco_venda_unitario).toFixed(2).replace('.', ',')})</li>`
                                    ).join('')
                                    : '<li>Nenhum item encontrado</li>'}
                            </ul>
                            <p><strong>Total:</strong> R$ ${subtotal.toFixed(2).replace('.', ',')}</p>
                            <form method="POST" action="/receber-venda" class="form-receber-venda-credito">
                                <input type="hidden" name="_token" value="${window.APP_DATA.csrfToken}">
                                <input type="hidden" name="venda_id" value="${venda.id}">
                                <button type="button" class="btn-credito btn-success btn-receber-credito" data-venda-id="${venda.id}">
                                    💰 Receber
                                </button>
                            </form>
                            <hr class="my-md">
                        </div>
                    `;
                });
            }

            document.getElementById('nomeClienteModal').textContent = nomeCliente;
            document.getElementById('conteudoCreditos').innerHTML = html;
            document.getElementById('totalCredito').textContent = total.toFixed(2).replace('.', ',');

            // Mostrar modal
            modalCredito.style.display = 'flex';
            this.activeModals.add('credito');

            // Event listeners para botões de receber
            document.querySelectorAll('.btn-receber-credito').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    
                    if (confirm('Deseja confirmar o recebimento desta venda?')) {
                        btn.type = 'submit';
                        btn.closest('form').submit();
                    }
                });
            });

        } catch (error) {
            console.error('❌ Erro ao buscar vendas:', error);
            alert('Erro ao buscar vendas pendentes.');
        }
    }

    fecharModalCredito() {
        const modalCredito = document.getElementById('modalCredito');
        if (this.activeModals.has('credito')) {
            console.log('🔒 Fechando modal de crédito');
            modalCredito.style.display = 'none';
            this.activeModals.delete('credito');
        }
    }

    // ===== MODAL DE CONFIRMAÇÃO (Venda/Crédito) =====
    initConfirmacaoModal() {
        const modalVenda = document.getElementById('confirmarVendaModal');
        const cancelarModal = document.getElementById('cancelarModal');
        const confirmarSubmit = document.getElementById('confirmarSubmit');
        
        if (!modalVenda) {
            console.warn('⚠️ Modal de confirmação não encontrado');
            return;
        }

        this.formularioAtual = null;

        // Botões para abrir modal de confirmação
        const btnAbrirConfirmacao = document.getElementById('abrirConfirmacao');
        const btnRegistrarCredito = document.getElementById('registrarCredito');

        if (btnAbrirConfirmacao) {
            console.log('✅ Botão "Registrar Venda" encontrado');
            
            btnAbrirConfirmacao.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                console.log('🎯 Clique em "Registrar Venda"');
                
                const formVenda = document.getElementById('formVenda');
                if (formVenda) {
                    this.abrirModalConfirmacao(formVenda, 'venda');
                } else {
                    console.error('❌ Formulário de venda não encontrado');
                    alert('Erro: Formulário de venda não encontrado');
                }
            });
        } else {
            console.error('❌ Botão "abrirConfirmacao" não encontrado');
        }

        if (btnRegistrarCredito) {
            console.log('✅ Botão "Registrar Crédito" encontrado');
            
            btnRegistrarCredito.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                console.log('🎯 Clique em "Registrar Crédito"');
                
                const formCredito = document.getElementById('formCredito');
                if (formCredito) {
                    // Validar se há itens antes de abrir o modal
                    const itens = formCredito.querySelectorAll('.item-venda');
                    if (itens.length === 0) {
                        alert('❌ Adicione ao menos um item antes de registrar o crédito.');
                        return;
                    }
                    this.abrirModalConfirmacao(formCredito, 'credito');
                } else {
                    console.error('❌ Formulário de crédito não encontrado');
                    alert('Erro: Formulário de crédito não encontrado');
                }
            });
        } else {
            console.error('❌ Botão "registrarCredito" não encontrado');
        }

        // Confirmar envio
        if (confirmarSubmit) {
            confirmarSubmit.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                console.log('✅ Confirmando envio do formulário');
                if (this.formularioAtual) {
                    this.formularioAtual.submit();
                }
            });
        }

        // Cancelar modal
        if (cancelarModal) {
            cancelarModal.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.fecharModalConfirmacao();
            });
        }

        // Fechar com clique fora
        modalVenda.addEventListener('click', (e) => {
            if (e.target === modalVenda) {
                this.fecharModalConfirmacao();
            }
        });

        // Prevenir propagação dentro do modal
        const modalVendaContent = modalVenda.querySelector('.modal-venda-content');
        if (modalVendaContent) {
            modalVendaContent.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }
    }

    abrirModalConfirmacao(formulario, tipo = 'venda') {
        console.log(`🔓 Abrindo modal de confirmação - Tipo: ${tipo}`);
        
        const modalVenda = document.getElementById('confirmarVendaModal');
        const itens = formulario.querySelectorAll('.item-venda');
        let resumoHtml = '';
        let total = 0;

        if (itens.length === 0) {
            resumoHtml = '<p class="text-center text-danger p-lg">❌ Nenhum item adicionado. Adicione ao menos um produto.</p>';
        } else {
            itens.forEach((item) => {
                const produtoSelect = item.querySelector('.produto-select');
                const quantidadeInput = item.querySelector('input[name*="[quantidade]"]');
                const precoSelect = item.querySelector('.select-preco');

                if (!produtoSelect || !quantidadeInput || !precoSelect) return;
                if (!produtoSelect.value || !quantidadeInput.value || !precoSelect.value) return;

                const produtoNome = produtoSelect.options[produtoSelect.selectedIndex].text;
                const quantidade = parseInt(quantidadeInput.value) || 0;
                const preco = parseFloat(precoSelect.value) || 0;
                const subtotal = quantidade * preco;

                total += subtotal;

                resumoHtml += `
                    <div class="flex-between p-sm border-bottom">
                        <span>${produtoNome}</span>
                        <span>${quantidade} x R$ ${this.formatarPreco(preco)} = <strong>R$ ${this.formatarPreco(subtotal)}</strong></span>
                    </div>
                `;
            });
        }

        // Adicionar informações do cliente se for crédito
        if (tipo === 'credito') {
            const clienteSelect = formulario.querySelector('[name="cliente_id"]');
            if (clienteSelect && clienteSelect.value) {
                const nomeCliente = clienteSelect.options[clienteSelect.selectedIndex].text;
                resumoHtml = `
                    <div class="mb-lg p-md" style="background: rgba(102, 126, 234, 0.1); border-radius: 8px;">
                        <strong>👤 Cliente:</strong> ${nomeCliente}
                    </div>
                ` + resumoHtml;
            }
        }

        document.getElementById('resumoVenda').innerHTML = resumoHtml || '<p class="text-center p-lg">Nenhum item adicionado.</p>';
        document.getElementById('totalVenda').textContent = this.formatarPreco(total);
        
        // Atualizar título do modal baseado no tipo
        const titulo = modalVenda.querySelector('h3');
        if (titulo) {
            titulo.textContent = tipo === 'credito' ? '💳 Confirmar Crédito' : '💰 Confirmar Venda';
        }
        
        modalVenda.style.display = 'flex';
        this.activeModals.add('confirmacao');
        this.formularioAtual = formulario;
    }

    fecharModalConfirmacao() {
        const modalVenda = document.getElementById('confirmarVendaModal');
        if (this.activeModals.has('confirmacao')) {
            console.log('🔒 Fechando modal de confirmação');
            modalVenda.style.display = 'none';
            this.activeModals.delete('confirmacao');
            this.formularioAtual = null;
        }
    }

    // ===== UTILITÁRIOS =====
    formatarPreco(valor) {
        return parseFloat(valor).toFixed(2).replace('.', ',');
    }

    // Fechar todos os modais (útil para situações de emergência)
    fecharTodosModais() {
        this.fecharModalCredito();
        this.fecharModalConfirmacao();
        console.log('🔒 Todos os modais fechados');
    }

    // Event listener global para ESC
    handleGlobalEvents() {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (this.activeModals.has('credito')) {
                    this.fecharModalCredito();
                } else if (this.activeModals.has('confirmacao')) {
                    this.fecharModalConfirmacao();
                }
            }
        });
    }

    // Verificar se algum modal está aberto
    hasActiveModal() {
        return this.activeModals.size > 0;
    }

    // Obter modal ativo
    getActiveModal() {
        return Array.from(this.activeModals)[0] || null;
    }

    // Método para debug
    debug() {
        console.log('🔍 Debug Modal System:', {
            activeModals: Array.from(this.activeModals),
            formularioAtual: this.formularioAtual?.id || null
        });
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.modalSystem = new ModalSystem();
    
    // Tornar método debug global
    window.debugModals = () => window.modalSystem.debug();
});