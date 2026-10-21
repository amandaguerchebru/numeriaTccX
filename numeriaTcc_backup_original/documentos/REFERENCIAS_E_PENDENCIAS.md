# Numéria · Referências de design e lista do que falta

## 1. Dez referências estudadas

Escolhi sites com a mesma iniciativa (matemática + jogo + público infantojuvenil), misturando **portais antigos e confiáveis** (de onde veio o jeito do CSS) com **plataformas gamificadas atuais** (de onde vieram as ideias de mecânica).

| # | Site | No ar desde | O que ele faz bem | O que o Numéria pegou |
|---|------|-------------|-------------------|------------------------|
| 1 | [Math Is Fun](https://www.mathsisfun.com) | ~2000 (criado por um professor, até hoje independente) | Interface limpa, listas organizadas por tema e idade, explicações em linguagem de aluno | Texto grande e legível, links sublinhados, hierarquia simples, nada de enfeite que atrapalhe |
| 2 | [Cool Math Games](https://www.coolmath-games.com) | 1997 | Um dos sites educativos mais antigos e reconhecidos; espaço seguro para jogar | Tom de "sala de jogos", cores chapadas e botões grandes |
| 3 | [Funbrain](https://www.funbrain.com) | 1996/97 | Jogos como Math Baseball, em que o acerto vira ponto no jogo; visual de "arcade" | Botões que afundam, ideia de fliperama, matemática dentro da regra do jogo |
| 4 | [Só Matemática](https://www.somatematica.com.br) | 1998 (Brasil) | Referência brasileira, recomendada por professores; muito conteúdo e jogos | Português claro, navegação sem mistério, credibilidade escolar |
| 5 | [Escola Games](https://www.escolagames.com.br) | (portal brasileiro, gratuito) | Jogos organizados por matéria e nível (fácil, médio, difícil), sem cadastro | Níveis explícitos e rótulos diretos, como no seu fácil/médio/difícil |
| 6 | [Math Playground](https://www.mathplayground.com) | 2002 (criado por professora) | Jogos por série e tema, selo de privacidade infantil, poucos anúncios | Organização por tema, linguagem "aprender brincando", cuidado com privacidade |
| 7 | [Arcademics](https://www.arcademics.com) | (anos 2000) | Jogos multiplayer em tempo real, de corrida e competição, com relatório de progresso | Competição leve entre colegas, jogo em dupla, tela de resultado |
| 8 | [Prodigy](https://www.prodigygame.com) | 2012 | RPG de mago em que cada feitiço exige resolver uma conta; avatar, pets e zonas liberadas | Combinar tema de magia + matemática; personagem como motivo para voltar. **Evitar**: pressão de compra e propaganda no jogo (crítica recorrente de pais) |
| 9 | [Khan Academy Kids](https://www.khanacademy.org/kids) | 2018 | Guias animais que acompanham a criança, recompensas e insígnias, sem anúncios | Mascote presente nas telas e nos recados, tom afetuoso ao errar |
| 10 | [Duolingo](https://www.duolingo.com) | 2011 | XP, sequência diária, ligas semanais, mascote que reage ao desempenho | Ideias de ranking e meta diária. **Cuidado**: críticas dizem que sequências podem virar pressão e vício |

Também ajudou: a observação de que muita gente prefere interfaces simples e conhecidas, em que a navegação principal é óbvia (avaliações antigas do Math Is Fun elogiam isso).

## 2. Como isso virou CSS

**O que foi tirado (cara de "gerado por IA"):** degradês por todo lado, texto com degradê, brilhos e sombras difusas, cantos todos arredondados iguais, cartões que "flutuam" no hover, emojis como logotipo (✨🦉), fundo verde-menta, crachás-pílula.

**O que entrou (cara de site educativo de verdade):**

- **Folha de caderno quadriculado** como fundo.
- **Molduras pixel** de tinta escura e **sombras duras** (sem blur), no espírito Mario/Undertale que você pediu.
- **Botões que afundam** ao clicar.
- **Cores alegres**, como o documento pede: amarelo, laranja do bico, azul de céu, roxo do chapéu. O verde virou só detalhe.
- **Cartões com faixa colorida** e uma torção mínima, como se tivessem sido colados à mão (desligada para quem prefere menos movimento).
- **Erro sem vermelho de alarme**: fundo lilás e o Bicó triste, seguindo o "tudo bem errar".
- **Bicó em pixel art** (neutro, feliz, triste) no logotipo, nos recados e no cantinho do topo. Os arquivos SVG estão em `public/assets/img/`.
- **Acessibilidade**: botões A / A / A de tamanho do texto (lembrados entre páginas), link "pular para o conteúdo", foco visível, alvos de toque de 48px, contraste alto.

Arquivos alterados: `public/assets/css/style.css`, `public/assets/js/script.js`, `app/views/layout/header.php`, `app/views/pages/pocoes.php` e `dupla.php` (só removi estilos inline que brigavam com as alternativas), `public/index.php` (um `require` com maiúscula errada) e a pasta `public/assets/img/`.

**Detalhe de fonte:** o título e os números usam *Press Start 2P* (Google Fonts, já ligada no `header.php`). Sem internet, cai para Courier New e continua legível. Se a escola trabalhar offline, vale baixar a fonte e usar `@font-face` local.

## 3. O que falta (comparando o documento do TCC com o código)

Marquei só o que conferi no código. **Prioridade: A** = pedido explícito do documento e visível na banca; **B** = importante; **C** = futuro.

### Já existe
- Cadastro e login com senha protegida (hash) e sessão renovada.
- Batalha na Floresta: Desafio (3 min), Contra o Guardião, Dupla Local.
- Poções Mágicas: identificação e comparação de frações.
- Níveis de dificuldade, XP, acertos/erros, histórico de partidas e desempenho por conteúdo.
- Feedback após erro com explicação (Poções e Dupla).

### Falta

| Prio | Item | Observação |
|------|------|------------|
| A | **Ranking** de alunos por pontuação | O documento pede a tabela; não encontrei no código |
| A | **Medalhas / conquistas** | Pedido em "recompensas visuais" e no painel do aluno; hoje só há XP |
| A | **Painel do aluno** com nível atual e medalhas | A home mostra XP e contagens; falta nível e medalhas |
| A | **Feedback final com pontos de atenção e recomendação de estudo** | Está em "Sistema de Progresso"; hoje só mostra % por conteúdo |
| A | Mínimo de **quatro jogos** no projeto em grupo | Aqui há dois (o documento diz que o grupo completa) |
| B | **Perfil professor/admin e turmas** | Cadastro só cria `aluno`; não há turma nem conta de professor |
| B | **Dicas progressivas** | Só aparece em Poções; falta nos demais jogos |
| B | **Modo mais lento / sem pressa** (acessibilidade nos jogos) | O Desafio tem relógio fixo de 3 min; o documento prevê desacelerar |
| B | Descrições alternativas e leitor de tela | Não achei `alt`, porque as imagens ainda são emojis; vale revisar quando houver arte |
| B | Reação do Bicó em **todas** as telas de jogo | O documento quer o mascote também em Dupla e Guardião; hoje só nas Poções |
| B | Trilha de aprendizado guiada na home | O documento descreve "hub com trilha"; a home é lista de jogos |
| B | Banco NoSQL | O documento cita relacional **e** NoSQL; só há MySQL |
| C | Hospital Numéria | O documento diz **só apresentar**, não implementar |
| C | Online entre dois dispositivos | Idem, ideia futura |

### Problemas técnicos que vi
1. `app/database/gerar_comparacao.php` e `gerar_identificacao.php` têm **0 bytes** (vazios). Se a banca abrir, convém preencher ou apagar.
2. `app/models/` está vazia e `app/config/conexao.php` tem a porta `3307` fixa; considere um arquivo de configuração separado e fora do Git.
3. As páginas aninham `<main>` dentro de `<main>` em alguns lugares e misturam muitos estilos inline (margens). Funciona, mas vale limpar.
4. Vidas do Guardião usam emojis (❤️🟣). Troque por corações em pixel quando houver sprites.
5. O `require` do `CadastroController.php` estava com a caixa errada (o arquivo é `cadastroController.php`); funciona no Windows e quebra em Linux. Já corrigi.

## 4. Sugestão de ordem de trabalho
1. Ranking + medalhas + nível (cobre quatro itens A de uma vez).
2. Tela final de partida com Bicó, pontos de atenção e recomendação de estudo.
3. Perfil de professor e turmas.
4. Bicó nas telas de Guardião e Dupla; modo "sem pressa".
