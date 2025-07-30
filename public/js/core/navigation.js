/**
 * Sistema de Navegação por Abas
 * Gerencia a navegação entre as diferentes seções do sistema
 */

class NavigationSystem {
    constructor() {
        this.currentSection = 'dashboard';
        this.init();
    }

    init() {
        this.bindEvents();
        this.showSection(this.currentSection);
        console.log('✅ Sistema de navegação inicializado');
    }

    bindEvents() {
        // Event listener para as abas de navegação
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const section = tab.dataset.section;
                this.navigateToSection(section);
            });
        });

        // Navegação por teclado (teclas numéricas 1-8) - SOMENTE COM ALT
        document.addEventListener('keydown', (e) => {
            // Verificar se o foco está em um input, textarea ou select
            const activeElement = document.activeElement;
            const isInInput = activeElement && (
                activeElement.tagName === 'INPUT' || 
                activeElement.tagName === 'TEXTAREA' || 
                activeElement.tagName === 'SELECT' ||
                activeElement.contentEditable === 'true'
            );

            // Se estiver digitando em um campo, não interceptar
            if (isInInput) {
                return;
            }

            // Só funciona com ALT + número para evitar conflitos
            if (!e.altKey) {
                return;
            }
            
            const keyMap = {
                '1': 'dashboard',
                '2': 'produtos', 
                '3': 'estoque',
                '4': 'movimentacao',
                '5': 'venda',
                '6': 'credito',
                '7': 'cliente',
                '8': 'caixa'
            };

            if (keyMap[e.key]) {
                e.preventDefault();
                this.navigateToSection(keyMap[e.key]);
                console.log(`⌨️ Navegação por teclado: ALT+${e.key} → ${keyMap[e.key]}`);
            }
        });
    }

    navigateToSection(sectionId) {
        if (this.currentSection === sectionId) return;

        // Remove classe active da aba atual
        document.querySelector('.nav-tab.active')?.classList.remove('active');
        
        // Remove classe active da seção atual
        document.querySelector('.content-section.active')?.classList.remove('active');

        // Adiciona classe active na nova aba
        document.querySelector(`[data-section="${sectionId}"]`)?.classList.add('active');
        
        // Adiciona classe active na nova seção
        document.getElementById(sectionId)?.classList.add('active');

        this.currentSection = sectionId;
        
        // Trigger evento personalizado para outros módulos
        window.dispatchEvent(new CustomEvent('sectionChanged', {
            detail: { section: sectionId }
        }));

        console.log(`📍 Navegou para: ${sectionId}`);
    }

    showSection(sectionId) {
        this.navigateToSection(sectionId);
    }

    getCurrentSection() {
        return this.currentSection;
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.navigationSystem = new NavigationSystem();
});