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
| A | **Medalhas / conquistas** | Pedido em "recompensas visuais" e no painel do aluno; hoje só há XP |
| A | **Painel do aluno** com nível atual e medalhas | A home mostra XP e contagens; falta nível e medalhas |
| A | **Feedback final com pontos de atenção e recomendação de estudo** | Está em "Sistema de Progresso"; hoje só mostra % por conteúdo |
| A | Mínimo de **quatro jogos** no projeto em grupo | Aqui há dois (o documento diz que o grupo completa) |
| B | **Turmas** (professor cria a turma e cadastra os alunos nela) | Já existe conta de professor e painel, mas o ranking e o painel mostram *todos* os alunos; falta separar por turma |
| B | **Modo mais lento / sem pressa** (acessibilidade nos jogos) | O Desafio tem relógio fixo (agora 2 min); o documento prevê desacelerar |
| B | Descrições alternativas e leitor de tela | Não achei `alt`, porque as imagens ainda são emojis; vale revisar quando houver arte |
| B | Trilha de aprendizado guiada na home | O documento descreve "hub com trilha"; a home é lista de jogos |
| B | Banco NoSQL | O documento cita relacional **e** NoSQL; só há MySQL |
| C | Hospital Numéria | O documento diz **só apresentar**, não implementar |
| C | Online entre dois dispositivos | Idem, ideia futura |

### Problemas técnicos que vi
1. `app/database/gerar_comparacao.php` e `gerar_identificacao.php` têm **0 bytes** (vazios). Se a banca abrir, convém preencher ou apagar.
2. `app/models/` está vazia e `app/config/conexao.php` tem a porta `3307` fixa; considere um arquivo de configuração separado e fora do Git.
3. As páginas aninham `<main>` dentro de `<main>` em alguns lugares e misturam muitos estilos inline (margens). Funciona, mas vale limpar.
5. O `require` do `CadastroController.php` estava com a caixa errada (o arquivo é `cadastroController.php`); funciona no Windows e quebra em Linux. Já corrigi.

## 4. Sugestão de ordem de trabalho
1. Ranking + medalhas + nível (cobre quatro itens A de uma vez).
2. Tela final de partida com Bicó, pontos de atenção e recomendação de estudo.
3. Perfil de professor e turmas.
4. Bicó nas telas de Guardião e Dupla; modo "sem pressa".

## 5. Atualização: segunda rodada de mudanças

**Feito**
- **Apresentação do Bicó na home**, com balão de fala e o que ele faz (dicas, explicações, "tudo bem errar").
- **Dicas do Bicó em todos os jogos** (Desafio, Guardião, Dupla e Poções): botão "Pedir dica ao Bicó" mostra até 3 dicas, uma por vez, sem entregar a resposta. Elas são geradas a partir do enunciado (`app/helpers/dicas.php`), então não precisam de mudança no banco. O que antes se chamava "Dica do Bicó" depois do erro virou **"Bicó explica"**.
- **Desafio**: tempo reduzido de 3 para **2 minutos** (uma única constante, `DESAFIO_DURACAO`, em `app/config/app.php`) e botão **Abandonar batalha**, com confirmação. A partida abandonada fica registrada como `abandonada`.
- **Explicação após o erro** também no Desafio e no Guardião (antes só havia a mensagem curta).
- **Dupla e Guardião sem emojis**: Bicó guerreiro (jogador logado / jogador 1), Onça (jogador 2) e Guardião ancestral, com as artes que você enviou, sobre o cenário do Vale Encantado.
- **Guardião**: vidas em corações grandes, desempenho (desafio, acertos, erros, XP) em números enormes, e o personagem atingido treme (desligado para quem prefere menos movimento).
- **Fundo da Batalha na Floresta** aplicado em todas as telas dessa área (batalha, desafio, guardião e dupla).
- **Poções**: a receita aparece como folha de pergaminho (ingredientes, modo de preparo, frasco dividido e materiais embaixo), gerada a partir da própria pergunta.

**Ainda vale conferir**
- Os arquivos PHP não foram executados no ambiente em que foram escritos (sem PHP disponível). A lógica foi revisada e o visual testado em HTML equivalente. Teste cada jogo no Laragon.
- As dicas das Poções cobrem as duas mecânicas atuais (identificação e comparação). Se criar outras mecânicas, inclua o caso em `dicas_bico()`.

## 6. Atualização: professor/aluno, ranking e novo cenário

**Feito**
- **Cenário novo da arena** (Bicó x Guardião e Bicó x Onça): uma clareira encantada em pixel art, desenhada para o projeto. Para trocar por outra arte, basta substituir o arquivo `public/assets/img/arena-clareira.png` (proporção 16:9 funciona melhor). O fundo geral da Batalha na Floresta continua sendo o Vale Encantado.
- **Login diferenciado**: na tela de entrada o usuário escolhe **Aluno** ou **Professor(a)**; se a conta não for do tipo escolhido, aparece uma mensagem explicando. O professor entra direto no painel dele; o aluno, na home.
- **Cadastro com tipo de conta**: "Eu sou Aluno / Professor(a)". Quem escolhe professor precisa digitar o **código de professor**, definido em `CODIGO_PROFESSOR` (`app/config/app.php`, valor atual `NUMERIA-PROF`). **Troque esse código antes de apresentar ou publicar**, senão qualquer aluno que o descubra vira professor.
- **Banco**: a tabela `aluno` ganha a coluna `tipo` (`aluno` ou `professor`). O script `app/database/migracao_tipo_usuario.sql` faz isso; se você esquecer de rodar, o sistema cria a coluna sozinho no primeiro cadastro ou login (usa o usuário `root` do Laragon). Contas antigas viram `aluno`.
- **Ranking dos alunos** (`?pagina=ranking`, para quem está logado): pódio com medalhas em pixel, tabela completa (posição, XP, partidas, aproveitamento), sua linha destacada e uma frase motivacional do Bicó ("faltam só X XP para alcançar..."). Empates dividem a posição. Professores não entram no ranking.
- **Painel do professor** (`?pagina=professor`, só para professores): resumo (alunos, XP médio, aproveitamento, partidas), "pontos de atenção" com os alunos abaixo de 50% de acertos e tabela com XP, acertos, erros e situação de cada aluno.
- Menu novo: **Ranking** para todos, **Painel do Professor** para professores (com selo "Professor" ao lado do nome).

**Pontos de cuidado**
- O ranking mostra o nome como foi cadastrado. Como são alunos do Fundamental II, vale orientar a usar nome e sobrenome ou apelido combinado com a escola.
- Ainda não há separação por turma: o ranking e o painel mostram todos os alunos cadastrados.
- Para criar o primeiro professor: abra "Criar conta", escolha Professor(a) e digite o código.
