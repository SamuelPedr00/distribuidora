/**
 * Sistema de Mensagens Corrigido
 * Gerencia exibição de mensagens sem aparecer automaticamente
 */

class MessagesModule {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        // NÃO processar mensagens automaticamente ao carregar
        console.log('✅ Sistema de mensagens inicializado (sem auto-show)');
    }

    bindEvents() {
        const modal = document.getElementById('msgModal');
        const btnFechar = document.getElementById('msgModalClose');
        
        if (btnFechar) {
            btnFechar.addEventListener('click', () => {
                this.fecharModal();
            });
        }

        if (modal) {
            // Fechar modal clicando fora
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.fecharModal();
                }
            });

            // Fechar modal com ESC
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('show')) {
                    this.fecharModal();
                }
            });
        }
    }

    mostrarMensagem(texto, tipo = 'info', duracao = 0) {
        const modal = document.getElementById('msgModal');
        const modalContent = document.getElementById('msgModalContent');
        const modalText = document.getElementById('msgModalText');

        if (!modal || !modalContent || !modalText) {
            console.error('❌ Elementos do modal de mensagem não encontrados');
            // Fallback para alert nativo
            alert(texto);
            return;
        }

        // Limpar classes anteriores
        modalContent.classList.remove('msg-modal-success', 'msg-modal-error', 'msg-modal-warning', 'msg-modal-info');

        // Adicionar classe baseada no tipo
        switch (tipo) {
            case 'success':
                modalContent.classList.add('msg-modal-success');
                break;
            case 'error':
                modalContent.classList.add('msg-modal-error');
                break;
            case 'warning':
                modalContent.classList.add('msg-modal-warning');
                break;
            default:
                modalContent.classList.add('msg-modal-info');
        }

        // Definir texto
        modalText.textContent = texto;

        // Mostrar modal
        modal.classList.add('show');
        modal.style.display = 'flex';

        // Auto-fechar se duracao for especificada
        if (duracao > 0) {
            setTimeout(() => {
                this.fecharModal();
            }, duracao);
        }

        console.log(`💬 Mensagem exibida: ${tipo} - ${texto}`);
    }

    fecharModal() {
        const modal = document.getElementById('msgModal');
        if (modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
        }
    }

    // Métodos de conveniência
    sucesso(texto, duracao = 4000) {
        this.mostrarMensagem(texto, 'success', duracao);
    }

    erro(texto, duracao = 0) {
        this.mostrarMensagem(texto, 'error', duracao);
    }

    aviso(texto, duracao = 5000) {
        this.mostrarMensagem(texto, 'warning', duracao);
    }

    info(texto, duracao = 3000) {
        this.mostrarMensagem(texto, 'info', duracao);
    }

    // Processar mensagens do servidor APENAS quando chamado explicitamente
    processarMensagensServidor() {
        // Buscar mensagens de sessão do Laravel
        const successMeta = document.querySelector('meta[name="success-message"]');
        const errorMeta = document.querySelector('meta[name="error-message"]');
        
        if (successMeta && successMeta.content) {
            this.sucesso(successMeta.content);
            return;
        }
        
        if (errorMeta && errorMeta.content) {
            this.erro(errorMeta.content);
            return;
        }

        // Verificar se há mensagens em variáveis globais do Blade
        if (typeof window.laravelMessages !== 'undefined') {
            const messages = window.laravelMessages;
            
            if (messages.success) {
                this.sucesso(messages.success);
            } else if (messages.error) {
                this.erro(messages.error);
            } else if (messages.warning) {
                this.aviso(messages.warning);
            } else if (messages.info) {
                this.info(messages.info);
            }
        }
    }

    // Notificação toast (aparece no canto da tela)
    toast(texto, tipo = 'info', duracao = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${tipo}`;
        toast.textContent = texto;

        // Estilos inline para o toast
        Object.assign(toast.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '12px 20px',
            borderRadius: '8px',
            color: 'white',
            fontWeight: '600',
            zIndex: '20000',
            transform: 'translateX(100%)',
            transition: 'transform 0.3s ease-out',
            maxWidth: '350px',
            wordWrap: 'break-word',
            fontSize: '14px',
            boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)'
        });

        // Cores baseadas no tipo
        const cores = {
            success: '#22543d',
            error: '#742a2a', 
            warning: '#744210',
            info: '#2a69ac'
        };

        toast.style.backgroundColor = cores[tipo] || cores.info;

        // Adicionar ao DOM
        document.body.appendChild(toast);

        // Animar entrada
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
        }, 100);

        // Remover após duração
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, duracao);

        console.log(`🍞 Toast exibido: ${tipo} - ${texto}`);
    }

    // Confirmação customizada
    async confirmar(texto, titulo = 'Confirmação') {
        return new Promise((resolve) => {
            // Criar modal de confirmação dinâmico
            const modal = document.createElement('div');
            modal.className = 'confirm-modal';
            modal.style.display = 'flex';
            modal.style.zIndex = '25000';

            modal.innerHTML = `
                <div class="confirm-modal-content">
                    <h3 style="margin-bottom: 20px; color: #2d3748; font-weight: 700;">${titulo}</h3>
                    <p style="margin-bottom: 30px; color: #4a5568; line-height: 1.6;">${texto}</p>
                    <div style="display: flex; gap: 12px; justify-content: flex-end;">
                        <button class="btn btn-secondary btn-sm" id="cancelarConfirm">❌ Cancelar</button>
                        <button class="btn btn-primary btn-sm" id="confirmarConfirm">✅ Confirmar</button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            // Event listeners
            document.getElementById('cancelarConfirm').addEventListener('click', () => {
                document.body.removeChild(modal);
                resolve(false);
            });

            document.getElementById('confirmarConfirm').addEventListener('click', () => {
                document.body.removeChild(modal);
                resolve(true);
            });

            // Fechar com clique fora
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    document.body.removeChild(modal);
                    resolve(false);
                }
            });

            // Fechar com ESC
            const escHandler = (e) => {
                if (e.key === 'Escape') {
                    document.removeEventListener('keydown', escHandler);
                    if (modal.parentNode) {
                        document.body.removeChild(modal);
                    }
                    resolve(false);
                }
            };
            document.addEventListener('keydown', escHandler);
        });
    }

    // Loading overlay
    mostrarLoading(texto = 'Carregando...') {
        // Remover loading anterior se existir
        this.esconderLoading();
        
        const loading = document.createElement('div');
        loading.id = 'loadingOverlay';
        loading.className = 'loading-overlay';
        
        loading.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div style="color: white; font-size: 16px; font-weight: 600;">${texto}</div>
            </div>
        `;

        document.body.appendChild(loading);
    }

    esconderLoading() {
        const loading = document.getElementById('loadingOverlay');
        if (loading) {
            loading.parentNode.removeChild(loading);
        }
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.messagesModule = new MessagesModule();
    
    // Tornar métodos disponíveis globalmente
    window.showMessage = (texto, tipo, duracao) => window.messagesModule.mostrarMensagem(texto, tipo, duracao);
    window.showSuccess = (texto, duracao) => window.messagesModule.sucesso(texto, duracao);
    window.showError = (texto, duracao) => window.messagesModule.erro(texto, duracao);
    window.showWarning = (texto, duracao) => window.messagesModule.aviso(texto, duracao);
    window.showInfo = (texto, duracao) => window.messagesModule.info(texto, duracao);
    window.showToast = (texto, tipo, duracao) => window.messagesModule.toast(texto, tipo, duracao);
    window.showConfirm = (texto, titulo) => window.messagesModule.confirmar(texto, titulo);
    window.showLoading = (texto) => window.messagesModule.mostrarLoading(texto);
    window.hideLoading = () => window.messagesModule.esconderLoading();
    
    // Função para processar mensagens do servidor (chame quando necessário)
    window.processServerMessages = () => window.messagesModule.processarMensagensServidor();
});