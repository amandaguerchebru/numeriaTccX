document.addEventListener('DOMContentLoaded', () => {
    // ano do rodapé
    const yearElement = document.getElementById('year');
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }

    // alvo do link "pular para o conteúdo"
    const principal = document.querySelector('main');
    if (principal && !principal.id) {
        principal.id = 'conteudo';
        principal.setAttribute('tabindex', '-1');
    }

    // tamanho do texto (A / A / A), lembrado entre as páginas
    const botoes = document.querySelectorAll('.acess-fonte button');
    const raiz = document.documentElement;

    function aplicarFonte(tamanho) {
        if (tamanho === 'normal') {
            raiz.removeAttribute('data-fonte');
        } else {
            raiz.setAttribute('data-fonte', tamanho);
        }
        botoes.forEach((botao) => {
            botao.setAttribute('aria-pressed', String(botao.dataset.fonte === tamanho));
        });
    }

    let salvo = 'normal';
    try {
        salvo = localStorage.getItem('numeria_fonte') || 'normal';
    } catch (e) { /* navegador sem armazenamento: segue o normal */ }
    aplicarFonte(salvo);

    botoes.forEach((botao) => {
        botao.addEventListener('click', () => {
            aplicarFonte(botao.dataset.fonte);
            try {
                localStorage.setItem('numeria_fonte', botao.dataset.fonte);
            } catch (e) { /* ignora */ }
        });
    });
});
