# 🦉 Numéria · Relatório e Pacote de Alterações Concluídas

> **Garantia de Segurança:** Seu código original localizado em `numeriaTcc/` **não foi alterado**. Todas as melhorias solicitadas foram criadas e organizadas em uma pasta separada e independente:
> 📁 **`alteracoes_realizadas/`**

---

## 📌 Sumário das Alterações Realizadas

| # | Solicitação Original | O que foi implementado | Arquivos Relacionados |
|---|----------------------|------------------------|-----------------------|
| 1 | **Apresentação do Bicó no início** | Card temático de boas-vindas com o Bicó em pixel art, sua história como tucano bruxo e sua proposta acolhedora ("tudo bem errar"). | `app/views/pages/home.php`<br>`public/assets/css/style.css` |
| 2 | **Dicas do Bicó em TODOS os jogos** | Criação do helper `bicoHelper.php` com inteligência de cálculo mental (decomposição por dezenas/unidades, sem erros de "0 + dígito") e botão expansível de dica durante a questão + feedback visual pós-resposta em **todos os jogos**. | `app/helpers/bicoHelper.php`<br>`desafio.php`, `dupla.php`, `guardiao.php`, `pocoes.php` |
| 3 | **Desafio: diminuir tempo e abandonar batalha** | Tempo ajustado para **60 segundos** (ritmo ágil de blitz/arcade), botão "Abandonar Desafio" na tela e criação da rota segura `abandonar_desafio`. | `app/views/pages/desafio.php`<br>`public/index.php` |
| 4 | **Dupla e Guardião com estética do Bicó (sem emojis artificiais)** | Criação de sprites SVG originais em pixel art (mesma técnica do Bicó): **Caçador**, **Monstrinho da Floresta**, **Guardião Ancestral** e **Corações/Escudos Pixel**. | `cacador.svg`, `monstrinho.svg`, `guardiao.svg`<br>`dupla.php`, `guardiao.php` |
| 5 | **Batalha contra o Guardião com vida e status muito maiores** | Nova **Arena de Duelo HUD**: avatares pixel de 68px, 3 corações pixel gigantes para o jogador, 3 escudos místicos para o guardião, centro "VS", métricas de acertos, erros e XP em tamanho grande e legível. | `app/views/pages/guardiao.php`<br>`public/assets/css/style.css` |
| 6 | **Poções: literalmente aparecer a receita da poção** | **Pergaminho Alquímico do Grimório**: título da receita, proporções de ingredientes divididos no todo, tubo de ensaio/frasco com divisões visuais graduadas e comparação lado a lado de caldeirões. | `app/views/pages/pocoes.php`<br>`public/assets/css/style.css` |

---

## 📂 Estrutura de Arquivos Prontos na Pasta `alteracoes_realizadas/`

```text
alteracoes_realizadas/
├── app/
│   ├── helpers/
│   │   └── bicoHelper.php          (Novo: central de dicas pedagógicas do Bicó)
│   └── views/
│       └── pages/
│           ├── home.php            (Apresentação do Bicó em destaque)
│           ├── desafio.php         (Tempo de 60s + botão abandonar + dicas do Bicó)
│           ├── dupla.php           (Caçador e Monstrinho em pixel art + dicas)
│           ├── guardiao.php        (Arena HUD com vidas e status gigantes + dicas)
│           └── pocoes.php          (Pergaminho de receita literal + frasco visual)
└── public/
    ├── index.php                   (Rota abandonar_desafio + timer de 60s)
    └── assets/
        ├── css/
        │   └── style.css           (Estilos completos da arena, receitas e avatares)
        └── img/
            ├── cacador.svg         (Pixel art do Caçador)
            ├── monstrinho.svg      (Pixel art do Monstrinho)
            ├── guardiao.svg        (Pixel art do Guardião da Floresta)
            ├── coracao-pixel.svg   (Coração de vida cheio)
            ├── coracao-vazio.svg   (Coração de vida vazio/quebrado)
            ├── escudo-guardiao.svg (Escudo místico ativo)
            └── escudo-vazio.svg    (Escudo místico quebrado)
```

---

## 🔍 Detalhamento Técnico das Soluções

### 1. Apresentação do Mascote Bicó (`home.php`)
- **Card Alquímico de Apresentação:** Inserido logo após a introdução da página inicial.
- **Identidade do Bicó:** Apresenta o tucano mágico em pixel art (`bico-feliz.svg`), seu papel como tutor e guia que celebra o erro como parte natural da evolução matemática.
- **Pílulas Visuais:** Destacam a presença das dicas em tempo real e a progressão em XP.

### 2. Dicas do Bicó em Todos os Jogos (`bicoHelper.php`)
- **Inteligência Pedagógica:**
  - Em adições de dois números de dois dígitos (ex: $34 + 18$): o Bicó ensina a somar as dezenas ($30 + 10 = 40$) e as unidades ($4 + 8 = 12$), somando no fim ($40 + 12 = 52$).
  - Em adições onde o segundo número tem 1 dígito (ex: $24 + 7$): em vez de gerar "0 + 7", o Bicó ensina a completar a dezena: *"Some até a dezena cheia ($24 + 6 = 30$) e depois junte o que sobrou ($1$), totalizando $31$!"*
  - Em subtrações: ensina a retirar primeiro as dezenas e depois as unidades.
  - Em frações: reforça o conceito de denominador (partes totais do frasco) e numerador (doses de essência).
- **Componente "Pedir Dica":** Bloco expansível estilizado `<details class="bico-dica-card">` que os alunos podem abrir durante o cálculo.
- **Feedback Padronizado:** Card com o rosto do Bicó reagindo (feliz no acerto, solidário com a explicação no erro).

### 3. Modo Desafio na Floresta (`desafio.php` e `index.php`)
- **Cronômetro:** Reduzido de 180s (3 minutos) para **60s** (1 minuto). Torna o jogo um "sprint matemático", ideal para dinâmicas em sala de aula.
- **Botão Abandonar Batalha:** Botão secundário visível durante a partida com confirmação.
- **Rota `abandonar_desafio`:** Salva o status `abandonada` e a data de término no banco de dados, limpa a sessão e retorna ao menu de modos.

### 4. Caçador e Monstrinho em Pixel Art (`dupla.php`)
- **Adeus aos Emojis:** Os emojis genéricos (🏹 e 🐾) foram substituídos por vetores SVG nítidos com estética retrô (`crispEdges`), combinando perfeitamente com a arte do Bicó.
- **Caçador:** Visual de aventureiro com chapéu verde, pena vermelha e traje de arqueiro.
- **Monstrinho:** Criatura mágica roxa e dourada da floresta com orelhas pontudas e expressão amigável.
- **Banner de Turno:** Identificação visual imersiva da vez de cada jogador.

### 5. Arena do Guardião com HUD Grande (`guardiao.php`)
- **Painel de Batalha (Arena HUD):**
  - **Lado do Jogador:** Avatar do Caçador em destaque + 3 corações pixel art grandes (32px) com brilho vermelho.
  - **Centro:** Brasão pixelado `VS` e indicador da rodada atual (`RODADA X/10`).
  - **Lado do Guardião:** Avatar imponente da criatura arbórea mística + 3 escudos rúnicos arcanos (32px).
  - **Painel de Desempenho:** Números ampliados em fonte pixel para acertos, erros e XP acumulado, visíveis com total nitidez.

### 6. Receitas Literais nas Poções (`pocoes.php`)
- **Pergaminho de Receita Oficial:** Transforma o enunciado em uma autêntica folha de grimório de alquimia:
  - Nome temático gerado (ex: *Elixir de Essência Lunar*).
  - Lista de ingredientes com a dose do todo e a dose de essência.
- **Frasco de Ensaio Alquímico Visual:** Tubo de vidro desenhado em CSS puro com marcas graduadas de cada fração. Os níveis preenchidos brilham em tom mágico e a base em solvente límpido.
- **Modo Comparação:** Apresenta dois caldeirões/frascos lado a lado para comparação direta de proporções.

---

## 🚀 Como Testar ou Aplicar as Alterações

Você tem duas formas simples de usar:

### Opção A (Testar lado a lado sem mexer no original):
No Laragon, você pode simplesmente copiar a pasta `alteracoes_realizadas` para uma pasta de teste (ex: `numeriaTcc-novo`) ou testar seus arquivos diretamente.

### Opção B (Substituir no seu projeto quando desejar):
Basta copiar os arquivos da pasta `alteracoes_realizadas/` para dentro de `numeriaTcc/`. As pastas internas coincidem perfeitamente:
- `alteracoes_realizadas/app/` $\rightarrow$ `numeriaTcc/app/`
- `alteracoes_realizadas/public/` $\rightarrow$ `numeriaTcc/public/`
