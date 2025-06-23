// Sistema de Navegação - Distribuidora
document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
});

function initializeNavigation() {
    const navTabs = document.querySelectorAll('.nav-tab');
    const contentSections = document.querySelectorAll('.content-section');

    // Event listener para as abas de navegação
    navTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetSection = this.getAttribute('data-section');
            switchSection(targetSection);
        });
    });

    // Mostrar a seção inicial (dashboard)
    switchSection('dashboard');
}

function switchSection(sectionName) {
    const navTabs = document.querySelectorAll('.nav-tab');
    const contentSections = document.querySelectorAll('.content-section');

    // Remove active class de todas as abas
    navTabs.forEach(tab => {
        tab.classList.remove('active');
    });

    // Remove active class de todas as seções
    contentSections.forEach(section => {
        section.classList.remove('active');
    });

    // Adiciona active class na aba clicada
    const activeTab = document.querySelector(`[data-section="${sectionName}"]`);
    if (activeTab) {
        activeTab.classList.add('active');
    }

    // Mostra a seção correspondente
    const activeSection = document.getElementById(sectionName);
    if (activeSection) {
        activeSection.classList.add('active');
    }

    // Atualiza a URL sem recarregar a página (opcional)
    updateURL(sectionName);
}

function updateURL(sectionName) {
    // Atualiza a URL para refletir a seção atual
    // Isso permite que o usuário use o botão voltar do navegador
    const url = new URL(window.location);
    url.searchParams.set('section', sectionName);
    window.history.pushState({ section: sectionName }, '', url);
}

// Handle do botão voltar do navegador
window.addEventListener('popstate', function(event) {
    if (event.state && event.state.section) {
        switchSection(event.state.section);
    } else {
        // Se não há estado, verifica se há parâmetro na URL
        const urlParams = new URLSearchParams(window.location.search);
        const section = urlParams.get('section') || 'dashboard';
        switchSection(section);
    }
});

// Verifica se há uma seção específica na URL ao carregar a página
window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const section = urlParams.get('section');
    
    if (section) {
        // Pequeno delay para garantir que o DOM está totalmente carregado
        setTimeout(() => {
            switchSection(section);
        }, 100);
    }
});

// Função utilitária para navegação programática (se necessário)
function navigateTo(sectionName) {
    switchSection(sectionName);
}

// Exporta a função para uso global se necessário
window.navigateTo = navigateTo;