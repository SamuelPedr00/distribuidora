/**
 * Script Principal da Aplicação
 * Coordena todos os módulos e funcionalidades globais
 */

class SistemaDistribuidora {
    constructor() {
        this.version = '2.0.0';
        this.modules = {};
        this.init();
    }

    init() {
        console.log(`🚀 Iniciando Sistema Distribuidora v${this.version}`);
        
        this.bindGlobalEvents();
        this.initializePerformanceMonitoring();
        this.processSessionMessages();
        
        console.log('✅ Sistema inicializado com sucesso');
    }

    bindGlobalEvents() {
        // Prevenção de envio duplo de formulários
        this.preventDoubleSubmit();
        
        // Confirmações automáticas para ações destrutivas
        this.bindDestructiveActions();
        
        // Auto-save de dados do formulário (draft)
        this.initAutoSave();
        
        // Atalhos de teclado globais
        this.bindKeyboardShortcuts();
        
        // Event listeners para mudanças de seção
        window.addEventListener('sectionChanged', (e) => {
            this.onSectionChanged(e.detail.section);
        });
    }

    preventDoubleSubmit() {
        document.addEventListener('submit', (e) => {
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (submitBtn && !submitBtn.disabled) {
                // Desabilitar botão temporariamente
                submitBtn.disabled = true;
                submitBtn.textContent = '⏳ Processando...';
                
                // Reabilitar após 3 segundos (caso não haja redirecionamento)
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = submitBtn.dataset.originalText || 'Enviar';
                }, 3000);
            }
        });
    }

    bindDestructiveActions() {
        document.addEventListener('click', (e) => {
            const button = e.target.closest('[data-confirm]');
            if (button) {
                e.preventDefault();
                const message = button.dataset.confirm;
                
                if (window.messagesModule) {
                    window.messagesModule.confirmar(message).then(confirmed => {
                        if (confirmed) {
                            // Se for um formulário, submeter
                            const form = button.closest('form');
                            if (form) {
                                form.submit();
                            }
                            // Se for um link, navegar
                            else if (button.href) {
                                window.location.href = button.href;
                            }
                            // Se tem onclick, executar
                            else if (button.onclick) {
                                button.onclick();
                            }
                        }
                    });
                } else {
                    if (confirm(message)) {
                        const form = button.closest('form');
                        if (form) {
                            form.submit();
                        } else if (button.href) {
                            window.location.href = button.href;
                        }
                    }
                }
            }
        });
    }

    initAutoSave() {
        // Auto-save de rascunhos a cada 30 segundos
        const forms = document.querySelectorAll('form[data-autosave]');
        
        forms.forEach(form => {
            const formId = form.id || 'form_' + Date.now();
            let saveTimer;
            
            form.addEventListener('input', () => {
                clearTimeout(saveTimer);
                saveTimer = setTimeout(() => {
                    this.saveFormDraft(formId, form);
                }, 30000);
            });
            
            // Carregar rascunho salvo
            this.loadFormDraft(formId, form);
        });
    }

    saveFormDraft(formId, form) {
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        try {
            localStorage.setItem(`draft_${formId}`, JSON.stringify({
                data: data,
                timestamp: Date.now()
            }));
            
            console.log(`💾 Rascunho salvo: ${formId}`);
        } catch (error) {
            console.warn('⚠️ Erro ao salvar rascunho:', error);
        }
    }

    loadFormDraft(formId, form) {
        try {
            const saved = localStorage.getItem(`draft_${formId}`);
            if (!saved) return;
            
            const { data, timestamp } = JSON.parse(saved);
            
            // Se o rascunho tem mais de 24 horas, ignorar
            if (Date.now() - timestamp > 24 * 60 * 60 * 1000) {
                localStorage.removeItem(`draft_${formId}`);
                return;
            }
            
            // Preencher campos
            Object.entries(data).forEach(([key, value]) => {
                const field = form.querySelector(`[name="${key}"]`);
                if (field && !field.value) {
                    field.value = value;
                }
            });
            
            console.log(`📋 Rascunho carregado: ${formId}`);
            
        } catch (error) {
            console.warn('⚠️ Erro ao carregar rascunho:', error);
        }
    }

    bindKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + S = Salvar formulário atual
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const activeForm = document.querySelector('form:focus-within');
                if (activeForm) {
                    activeForm.requestSubmit();
                }
            }
            
            // Ctrl/Cmd + K = Foco na busca (se existir)
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchField = document.querySelector('input[type="search"], input[placeholder*="buscar" i]');
                if (searchField) {
                    searchField.focus();
                }
            }
            
            // Esc = Fechar modais
            if (e.key === 'Escape') {
                if (window.modalSystem) {
                    window.modalSystem.fecharTodosModais();
                }
            }
        });
    }

    onSectionChanged(section) {
        // Lógica específica para cada seção
        switch (section) {
            case 'dashboard':
                this.updateDashboard();
                break;
            case 'caixa':
                if (window.caixaModule) {
                    window.caixaModule.carregarDadosIniciais();
                }
                break;
            case 'venda':
                // Limpar formulário de venda ao entrar na seção
                if (window.vendasModule) {
                    const itens = document.querySelectorAll('#itensVenda .item-venda');
                    if (itens.length === 0) {
                        window.vendasModule.adicionarItem();
                    }
                }
                break;
            case 'credito':
                // Limpar formulário de crédito ao entrar na seção
                if (window.creditoModule) {
                    const itens = document.querySelectorAll('#itensCredito .item-venda');
                    if (itens.length === 0) {
                        window.creditoModule.adicionarItemCredito();
                    }
                }
                break;
        }
        
        // Atualizar título da página
        document.title = `${this.getSectionTitle(section)} - Sistema Distribuidora`;
    }

    getSectionTitle(section) {
        const titles = {
            dashboard: 'Dashboard',
            produtos: 'Produtos',
            estoque: 'Estoque',
            movimentacao: 'Movimentação',
            venda: 'Vendas',
            credito: 'Crédito',
            cliente: 'Clientes',
            caixa: 'Fluxo de Caixa'
        };
        
        return titles[section] || 'Sistema';
    }

    async updateDashboard() {
        // Atualizar dados do dashboard se necessário
        try {
            const stats = await this.fetchDashboardStats();
            this.updateDashboardStats(stats);
        } catch (error) {
            console.warn('⚠️ Erro ao atualizar dashboard:', error);
        }
    }

    async fetchDashboardStats() {
        // Esta função faria uma requisição para buscar estatísticas atualizadas
        // Por ora, retornamos dados mockados
        return {
            totalProdutos: document.getElementById('totalProdutos')?.textContent || '0',
            produtosBaixo: document.getElementById('produtosBaixo')?.textContent || '0',
            saldoCaixa: document.getElementById('saldoCaixa')?.textContent || 'R$ 0,00'
        };
    }

    updateDashboardStats(stats) {
        // Atualizar elementos do dashboard com animação
        Object.entries(stats).forEach(([key, value]) => {
            const element = document.getElementById(key);
            if (element && element.textContent !== value) {
                element.style.transform = 'scale(1.1)';
                element.textContent = value;
                
                setTimeout(() => {
                    element.style.transform = 'scale(1)';
                }, 200);
            }
        });
    }

    initializePerformanceMonitoring() {
        // Monitor de performance básico
        if ('performance' in window) {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    const timing = performance.timing;
                    const loadTime = timing.loadEventEnd - timing.navigationStart;
                    
                    console.log(`⚡ Página carregada em ${loadTime}ms`);
                    
                    if (loadTime > 3000) {
                        console.warn('⚠️ Carregamento lento detectado');
                    }
                }, 0);
            });
        }
    }

    processSessionMessages() {
        // Processar mensagens de sessão PHP/Laravel
        const successMessage = document.querySelector('meta[name="success-message"]');
        const errorMessage = document.querySelector('meta[name="error-message"]');
        
        if (successMessage && window.messagesModule) {
            window.messagesModule.sucesso(successMessage.content, 3000);
        }
        
        if (errorMessage && window.messagesModule) {
            window.messagesModule.erro(errorMessage.content);
        }
    }

    // API para outros módulos
    getModule(name) {
        return this.modules[name];
    }

    registerModule(name, module) {
        this.modules[name] = module;
        console.log(`📦 Módulo registrado: ${name}`);
    }

    // Utilitários globais
    formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value || 0);
    }

    formatDate(date) {
        return new Date(date).toLocaleDateString('pt-BR');
    }

    formatDateTime(date) {
        return new Date(date).toLocaleString('pt-BR');
    }

    // Debug e desenvolvimento
    enableDebugMode() {
        window.DEBUG = true;
        document.body.classList.add('debug-mode');
        console.log('🐛 Modo debug ativado');
    }

    // Informações do sistema
    getSystemInfo() {
        return {
            version: this.version,
            modules: Object.keys(this.modules),
            userAgent: navigator.userAgent,
            timestamp: new Date().toISOString()
        };
    }
}

// Inicializar sistema quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.sistemaDistribuidora = new SistemaDistribuidora();
    
    // Expor utilitários globalmente
    window.formatCurrency = (value) => window.sistemaDistribuidora.formatCurrency(value);
    window.formatDate = (date) => window.sistemaDistribuidora.formatDate(date);
    window.formatDateTime = (date) => window.sistemaDistribuidora.formatDateTime(date);
    
    // Comando de debug no console
    window.debug = () => window.sistemaDistribuidora.enableDebugMode();
    window.info = () => console.table(window.sistemaDistribuidora.getSystemInfo());
});

// Service Worker para cache (opcional)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(() => console.log('📱 Service Worker registrado'))
            .catch(() => console.log('⚠️ Service Worker não disponível'));
    });
}