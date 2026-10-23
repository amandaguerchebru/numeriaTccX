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

    // ------------------------------------------------------------
    // Dicas do Bicó: uma dica por vez, sem entregar a resposta
    // ------------------------------------------------------------
    document.querySelectorAll('[data-dicas]').forEach((caixa) => {
        const botao = caixa.querySelector('[data-dica-btn]');
        const itens = caixa.querySelectorAll('.dica-lista li');
        const total = itens.length;
        let mostradas = 0;

        if (!botao || total === 0) {
            return;
        }

        botao.addEventListener('click', () => {
            if (mostradas >= total) {
                return;
            }

            itens[mostradas].hidden = false;
            mostradas++;

            if (mostradas >= total) {
                botao.textContent = 'Acabaram as dicas. Você consegue!';
                botao.disabled = true;
            } else {
                botao.textContent = 'Pedir outra dica (' + mostradas + '/' + total + ')';
            }
        });
    });

    // ------------------------------------------------------------
    // Confirmação antes de abandonar uma partida
    // ------------------------------------------------------------
    document.querySelectorAll('[data-confirmar]').forEach((link) => {
        link.addEventListener('click', (evento) => {
            if (!window.confirm(link.dataset.confirmar)) {
                evento.preventDefault();
            }
        });
    });

    // ------------------------------------------------------------
    // Cadastro: o código de professor só aparece para professores
    // ------------------------------------------------------------
    const campoProfessor = document.querySelector('[data-campo-professor]');
    const opcoesTipo = document.querySelectorAll('input[name="tipo"]');

    if (campoProfessor && opcoesTipo.length > 0) {
        const entrada = campoProfessor.querySelector('input');

        const atualizarCampo = () => {
            const professor = document.querySelector('input[name="tipo"]:checked').value === 'professor';
            campoProfessor.hidden = !professor;
            entrada.required = professor;
            if (!professor) {
                entrada.value = '';
            }
        };

        opcoesTipo.forEach((opcao) => opcao.addEventListener('change', atualizarCampo));
        atualizarCampo();
    }
});
