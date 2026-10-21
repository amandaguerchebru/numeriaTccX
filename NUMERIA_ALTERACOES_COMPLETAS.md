# 📦 NUMÉRIA · ARQUIVO COMPLETO COM TODAS AS ALTERAÇÕES

Este arquivo reúne em um **único documento** todo o código-fonte final, completo e atualizado de cada arquivo do projeto com todas as alterações solicitadas implementadas.

---

## 📑 Índice dos Arquivos neste Documento

1. [Arquivo 1: `app/helpers/bicoHelper.php` (Central de Dicas do Bicó)](#arquivo-1-apphelpersbicohelperphp)
2. [Arquivo 2: `app/views/pages/home.php` (Apresentação do Bicó na Home)](#arquivo-2-appviewspageshomephp)
3. [Arquivo 3: `app/views/pages/desafio.php` (Desafio 60s + Abandonar + Dicas)](#arquivo-3-appviewspagesdesafiophp)
4. [Arquivo 4: `app/views/pages/dupla.php` (Caçador & Monstrinho Pixel + Dicas)](#arquivo-4-appviewspagesduplaphp)
5. [Arquivo 5: `app/views/pages/guardiao.php` (Arena HUD Grande + Vidas Pixel + Dicas)](#arquivo-5-appviewspagesguardiaophp)
6. [Arquivo 6: `app/views/pages/pocoes.php` (Receita Literal de Poção + Frasco Visual)](#arquivo-6-appviewspagespocoesphp)
7. [Arquivo 7: `public/index.php` (Rota de Abandono do Desafio + Timer 60s)](#arquivo-7-publicindexphp)
8. [Arquivo 8: `public/assets/css/style.css` (Novos Estilos CSS Completos)](#arquivo-8-publicassetscssstylecss)
9. [Arquivos 9 a 15: Sprites SVG em Pixel Art (Caçador, Monstrinho, Guardião, Corações, Escudos)](#arquivos-9-a-15-sprites-svg-em-pixel-art)

---

## Arquivo 1: `app/helpers/bicoHelper.php`

```php
<?php
/**
 * Helper do Mascote Bicó - Numéria
 * Centraliza a inteligência pedagógica das dicas e feedbacks do Bicó em todos os jogos.
 */

if (!function_exists('gerarDicaBico')) {
    /**
     * Gera uma dica pedagógica contextualizada do Bicó para o enunciado atual.
     *
     * @param string $enunciado Texto da questão
     * @param string $explicacaoDb Explicação cadastrada no banco de dados (se houver)
     * @return string Dica formatada com a personalidade do Bicó
     */
    function gerarDicaBico(string $enunciado, string $explicacaoDb = ''): string
    {
        $explicacaoLimpa = trim($explicacaoDb);
        if (!empty($explicacaoLimpa)) {
            return $explicacaoLimpa;
        }

        // 1. Detecta Operação de Adição (A + B)
        if (preg_match('/(\d+)\s*\+\s*(\d+)/', $enunciado, $matches)) {
            $n1 = (int) $matches[1];
            $n2 = (int) $matches[2];

            // Se o segundo número tem apenas 1 dígito (evita dizer "0 + dígito")
            if ($n2 < 10) {
                $proximaDezena = (int) (ceil($n1 / 10) * 10);
                if ($proximaDezena === $n1) {
                    $proximaDezena += 10;
                }
                $faltaParaDezena = $proximaDezena - $n1;

                if ($faltaParaDezena > 0 && $faltaParaDezena < $n2) {
                    $sobra = $n2 - $faltaParaDezena;
                    return "Decomponha o {$n2}! Some primeiro até a dezena cheia: {$n1} + {$faltaParaDezena} = {$proximaDezena}. Depois adicione o que restou ({$sobra}): {$proximaDezena} + {$sobra} = " . ($n1 + $n2) . "!";
                }

                return "Some o número direto às unidades: {$n1} + {$n2} = " . ($n1 + $n2) . ". Fique atento se passar para a próxima dezena!";
            }

            // Para dois dígitos ou mais: decomposição decimal clássica
            $dezena1 = (int) (floor($n1 / 10) * 10);
            $unidade1 = $n1 % 10;
            $dezena2 = (int) (floor($n2 / 10) * 10);
            $unidade2 = $n2 % 10;

            $somaDezenas = $dezena1 + $dezena2;
            $somaUnidades = $unidade1 + $unidade2;

            return "Separe as dezenas e as unidades! Some as dezenas ({$dezena1} + {$dezena2} = {$somaDezenas}) e depois as unidades ({$unidade1} + {$unidade2} = {$somaUnidades}). Juntando tudo: {$somaDezenas} + {$somaUnidades} = " . ($n1 + $n2) . "!";
        }

        // 2. Detecta Operação de Subtração (A - B)
        if (preg_match('/(\d+)\s*\-\s*(\d+)/', $enunciado, $matches)) {
            $n1 = (int) $matches[1];
            $n2 = (int) $matches[2];

            if ($n2 < 10) {
                return "Pense na contagem regressiva a partir de {$n1}: retire {$n2} unidades com calma para encontrar o resultado!";
            }

            $dezena2 = (int) (floor($n2 / 10) * 10);
            $unidade2 = $n2 % 10;
            $passo1 = $n1 - $dezena2;

            return "Tire as dezenas primeiro! {$n1} - {$dezena2} = {$passo1}. Agora retire as unidades restantes: {$passo1} - {$unidade2} = " . ($n1 - $n2) . "!";
        }

        // 3. Frações: Identificação de Partes
        if (stripos($enunciado, 'partes') !== false || stripos($enunciado, 'fração') !== false) {
            return "Lembre-se do segredo do alquimista: o número de baixo (denominador) representa em quantas partes iguais a poção foi dividida no total. O número de cima (numerador) mostra quantas partes de essência foram adicionadas!";
        }

        // 4. Frações: Comparação de Proporção
        if (stripos($enunciado, 'maior proporção') !== false || stripos($enunciado, 'compar') !== false) {
            return "Para comparar duas poções de frascos diferentes, use a multiplicação cruzada: multiplique o numerador da primeira pelo denominador da segunda, e vice-versa. Quem tiver o maior produto terá a mistura mais concentrada!";
        }

        return "Analise com atenção os dados do enigma! Tente quebrar o cálculo em etapas menores para chegar à resposta com segurança.";
    }
}

if (!function_exists('renderBicoFeedbackHtml')) {
    /**
     * Renderiza o componente HTML padrão de feedback com o Bicó.
     */
    function renderBicoFeedbackHtml(string $mensagem, string $tipo, string $explicacao = ''): string
    {
        if (empty($mensagem)) {
            return '';
        }

        $classeCss = ($tipo === 'acerto') ? 'feedback-sucesso' : 'feedback-erro';
        $ariaIcone = ($tipo === 'acerto') ? 'Acerto com o Bicó' : 'Apoio do Bicó';

        $html = '<div role="status" class="feedback-msg ' . $classeCss . '">';
        $html .= '<div class="bico-feedback">';
        $html .= '<span class="bico-icone" aria-label="' . $ariaIcone . '"></span>';
        $html .= '<div>';
        $html .= '<p><strong>' . htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') . '</strong></p>';
        if (!empty($explicacao)) {
            $html .= '<p class="explicacao"><strong>Dica do Bicó:</strong> ' . htmlspecialchars($explicacao, ENT_QUOTES, 'UTF-8') . '</p>';
        }
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('renderBicoDicaBotaoHtml')) {
    /**
     * Renderiza o botão interativo expansível de Dica do Bicó durante o desafio.
     */
    function renderBicoDicaBotaoHtml(string $dica): string
    {
        if (empty($dica)) {
            return '';
        }

        $html = '<details class="bico-dica-card">';
        $html .= '<summary class="bico-dica-resumo">';
        $html .= '<span class="bico-icone-mini" aria-hidden="true"></span>';
        $html .= '<span>Pedir dica mágica ao Bicó</span>';
        $html .= '</summary>';
        $html .= '<div class="bico-dica-conteudo">';
        $html .= '<p><strong>💡 Bicó sussurra:</strong> ' . htmlspecialchars($dica, ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '</div>';
        $html .= '</details>';

        return $html;
    }
}
```

---

## Arquivo 2: `app/views/pages/home.php`

```php
<?php
// O acompanhamento aparece apenas para alunos conectados.
$resumoAluno = null;

if (isset($_SESSION['aluno_id'])) {
    require_once __DIR__ . '/../../config/conexao.php';

    $idAluno = (int) $_SESSION['aluno_id'];

    // Busca o total de XP do aluno.
    $consultaXP = $conexao->prepare(
        'SELECT COALESCE(SUM(xp_total), 0)
         FROM partida_participante
         WHERE id_aluno = :aluno'
    );
    $consultaXP->execute(['aluno' => $idAluno]);
    $totalXP = (int) $consultaXP->fetchColumn();

    // Conta as partidas finalizadas do aluno.
    $consultaPartidas = $conexao->prepare(
        "SELECT COUNT(*)
         FROM partida_participante
         WHERE id_aluno = :aluno
           AND id_partida IN (
               SELECT id_partida
               FROM partida
               WHERE status = 'finalizada'
           )"
    );
    $consultaPartidas->execute(['aluno' => $idAluno]);
    $totalPartidas = (int) $consultaPartidas->fetchColumn();

    // Soma os acertos registrados.
    $consultaAcertos = $conexao->prepare(
        'SELECT COALESCE(SUM(acertos), 0)
         FROM partida_participante
         WHERE id_aluno = :aluno'
    );
    $consultaAcertos->execute(['aluno' => $idAluno]);
    $totalAcertos = (int) $consultaAcertos->fetchColumn();

    // Soma os erros registrados.
    $consultaErros = $conexao->prepare(
        'SELECT COALESCE(SUM(erros), 0)
         FROM partida_participante
         WHERE id_aluno = :aluno'
    );
    $consultaErros->execute(['aluno' => $idAluno]);
    $totalErros = (int) $consultaErros->fetchColumn();

    $resumoAluno = [
        'xp' => $totalXP,
        'partidas' => $totalPartidas,
        'acertos' => $totalAcertos,
        'erros' => $totalErros
    ];
}
?>

<main>
    <div class="container">
        <!-- SEÇÃO HERO DE BOAS-VINDAS -->
        <section class="hero" aria-labelledby="titulo-principal">
            <span class="badge">Reino Encantado da Matemática</span>

            <?php if (isset($_SESSION['aluno_id'])): ?>
                <p>
                    Olá, <strong><?= htmlspecialchars($_SESSION['aluno_nome'], ENT_QUOTES, 'UTF-8') ?></strong>!
                    Que bom ter você de volta a Numéria!
                </p>
            <?php else: ?>
                <p>
                    Entre na sua conta ou cadastre-se pelo menu superior
                    para iniciar sua aventura matemática!
                </p>
            <?php endif; ?>

            <h2 id="titulo-principal">Bem-vindo ao Numéria</h2>
            <p>
                Uma plataforma de aprendizagem matemática gamificada voltada para alunos do Ensino Fundamental II,
                onde cada conta resolvida é um feitiço dominado e cada erro é um passo para a sabedoria.
            </p>
        </section>

        <!-- APRESENTAÇÃO DO BICÓ: TUCANO MÁGICO E MASCOTE OFICIAL -->
        <section class="card bico-apresentacao-card" aria-label="Apresentação do Mascote Bicó">
            <div class="bico-apresentacao-grid">
                <div class="bico-mascote-avatar" role="img" aria-label="Bicó, o Tucano Mágico"></div>
                <div class="bico-mascote-bio">
                    <span class="badge">Mascote Oficial de Numéria</span>
                    <h3 class="bico-titulo">Conheça o Bicó! O Tucano Mágico 🦉✨</h3>
                    <p>
                        Saudações, aventureiro! Eu sou o <strong>Bicó</strong>, um tucano feiticeiro de bico dourado e chapéu estelar.
                        Fui escolhido pelo Reino de Numéria para ser o seu guia oficial em todas as aventuras matemáticas!
                    </p>
                    <p>
                        Aqui no Numéria, <strong>tudo bem errar</strong>! O aprendizado é uma receita que se aprimora a cada tentativa.
                        Em <em>todos os jogos</em> — da Batalha na Floresta ao Laboratório de Poções —, eu estarei ao seu lado sussurrando
                        <strong>dicas especiais</strong>, estratégias de cálculo mental e explicações passo a passo quando você precisar!
                    </p>
                    <div class="bico-dicas-pilulas">
                        <span class="bico-pilula">💡 Dicas em tempo real</span>
                        <span class="bico-pilula">🧪 Mestre das Poções</span>
                        <span class="bico-pilula">🏹 Guia na Floresta</span>
                        <span class="bico-pilula">🛡️ Aliado contra o Guardião</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- CATÁLOGO DE JOGOS DISPONÍVEIS -->
        <section id="jogos" aria-labelledby="jogos-titulo" style="margin-top: 32px;">
            <h2 id="jogos-titulo">Jogos disponíveis</h2>

            <div class="grid">
                <article class="card" aria-label="Batalha na Floresta">
                    <h3>Batalha na Floresta</h3>
                    <p>Desafios rápidos de adição e subtração! Escolha entre o Desafio Relâmpago, o épico Contra o Guardião ou o Duelo em Dupla Local.</p>

                    <?php if (isset($_SESSION['aluno_id'])): ?>
                        <a href="?pagina=batalha">Jogar Batalha</a>
                    <?php else: ?>
                        <a href="?pagina=login">Entrar para jogar</a>
                    <?php endif; ?>
                </article>

                <article class="card" aria-label="Poções Mágicas">
                    <h3>Poções Mágicas</h3>
                    <p>Entre no laboratório arcano para preparar receitas mágicas com frações, identificando partes e comparando poções poderosas com o Bicó.</p>

                    <?php if (isset($_SESSION['aluno_id'])): ?>
                        <a href="?pagina=pocoes">Jogar Poções</a>
                    <?php else: ?>
                        <a href="?pagina=login">Entrar para jogar</a>
                    <?php endif; ?>
                </article>
            </div>
        </section>

        <!-- ACOMPANHAMENTO DO ALUNO -->
        <?php if ($resumoAluno !== null): ?>
            <section id="acompanhamento" aria-labelledby="acompanhamento-titulo" style="margin-top: 32px;">
                <h2 id="acompanhamento-titulo">Meu acompanhamento</h2>
                <p>Acompanhe sua evolução e conquistas no reino de Numéria!</p>

                <div class="grid">
                    <article class="card">
                        <h3>XP acumulado</h3>
                        <p class="destaque-numero">⭐ <?= number_format($resumoAluno['xp'], 0, ',', '.') ?> XP</p>
                    </article>

                    <article class="card">
                        <h3>Partidas finalizadas</h3>
                        <p class="destaque-numero">🎮 <?= $resumoAluno['partidas'] ?></p>
                    </article>

                    <article class="card">
                        <h3>Acertos</h3>
                        <p class="destaque-numero" style="color: var(--cor-acerto) !important;">✅ <?= $resumoAluno['acertos'] ?></p>
                    </article>

                    <article class="card">
                        <h3>Erros (Tentativas)</h3>
                        <p class="destaque-numero" style="color: var(--roxo) !important;">📚 <?= $resumoAluno['erros'] ?></p>
                    </article>
                </div>
            </section>
        <?php endif; ?>

        <?php if (isset($_SESSION['aluno_id'])): ?>
            <p style="margin-top: 20px;">
                <a href="?pagina=historico" class="btn">
                    📊 Ver meu histórico detalhado de partidas
                </a>
            </p>
        <?php endif; ?>

        <!-- SOBRE A PLATAFORMA -->
        <section id="sobre" aria-labelledby="sobre-titulo" style="margin-top: 32px;">
            <h2 id="sobre-titulo">Objetivo da plataforma</h2>
            <p>
                O Numéria é uma plataforma educacional desenvolvida para
                apoiar alunos do Ensino Fundamental II no aprendizado
                de matemática por meio de jogos interativos.
            </p>

            <p>
                A plataforma busca tornar o aprendizado mais envolvente,
                registrar o progresso dos estudantes e ajudar a identificar
                conteúdos que precisam de mais atenção com o apoio do mascote Bicó.
            </p>

            <p>
                O projeto é desenvolvido pela XGuerTech, com a proposta
                de transformar desafios matemáticos em oportunidades
                de exploração, prática e descoberta.
            </p>
        </section>
    </div>
</main>
```

---

## Arquivo 3: `app/views/pages/desafio.php`

```php
<?php

require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../helpers/bicoHelper.php';

// ==================================================
// 1. VERIFICAR SE O ALUNO ESTÁ CONECTADO
// ==================================================
if (empty($_SESSION['aluno_id'])) {
    header('Location: ?pagina=login');
    exit;
}

$tempoDesafioTotal = 60; // Tempo reduzido para 60 segundos (1 minuto) para mais dinamismo

// ==================================================
// 2. INICIAR OU REINICIAR A PARTIDA
// ==================================================
if (
    !isset($_SESSION['desafio_inicio']) ||
    !isset($_SESSION['desafio_partida_id']) ||
    isset($_GET['novo'])
) {
    // Busca o modo Desafio no banco.
    $consultaModo = $conexao->prepare(
        'SELECT id_modo_jogo
         FROM modo_jogo
         WHERE nome = :nome
         LIMIT 1'
    );
    $consultaModo->execute(['nome' => 'Desafio']);
    $idModo = $consultaModo->fetchColumn();

    if ($idModo === false) {
        exit('Erro: o modo Desafio não foi encontrado no banco de dados.');
    }

    // Cria a partida com tempo de 60 segundos.
    $consultaPartida = $conexao->prepare(
        'INSERT INTO partida
            (id_jogo, id_modo_jogo, tempo_segundos, status)
         VALUES
            (:jogo, :modo, :tempo, :status)'
    );
    $consultaPartida->execute([
        'jogo' => 1,
        'modo' => (int) $idModo,
        'tempo' => $tempoDesafioTotal,
        'status' => 'em_andamento'
    ]);
    $idPartida = (int) $conexao->lastInsertId();

    // Registra o aluno participante.
    $consultaParticipante = $conexao->prepare(
        'INSERT INTO partida_participante
            (id_partida, id_aluno, xp_total, acertos, erros)
         VALUES
            (:partida, :aluno, 0, 0, 0)'
    );
    $consultaParticipante->execute([
        'partida' => $idPartida,
        'aluno' => (int) $_SESSION['aluno_id']
    ]);

    // Guarda identificadores na sessão.
    $_SESSION['desafio_partida_id'] = $idPartida;
    $_SESSION['desafio_participante_id'] = (int) $conexao->lastInsertId();
    $_SESSION['desafio_inicio'] = time();
    $_SESSION['desafio_xp'] = 0;
    $_SESSION['desafio_acertos'] = 0;
    $_SESSION['desafio_erros'] = 0;
    unset($_SESSION['desafio_feedback']);

    if (isset($_GET['novo'])) {
        header('Location: ?pagina=desafio');
        exit;
    }
}

$_SESSION['desafio_xp'] = (int) ($_SESSION['desafio_xp'] ?? 0);
$_SESSION['desafio_acertos'] = (int) ($_SESSION['desafio_acertos'] ?? 0);
$_SESSION['desafio_erros'] = (int) ($_SESSION['desafio_erros'] ?? 0);

// ==================================================
// 3. CALCULAR TEMPO RESTANTE
// ==================================================
$tempoRestante = max(0, $tempoDesafioTotal - (time() - (int) $_SESSION['desafio_inicio']));

$mensagem = '';
$mensagemTipo = '';
$explicacao = '';
$questao = null;
$alternativas = [];

// ==================================================
// 3.1. FINALIZAR PARTIDA NO BANCO SE TEMPO ESGOTADO
// ==================================================
if ($tempoRestante <= 0 && !empty($_SESSION['desafio_partida_id'])) {
    $consultaFim = $conexao->prepare(
        "UPDATE partida
         SET status = 'finalizada',
             data_fim = NOW()
         WHERE id_partida = :partida
           AND status = 'em_andamento'"
    );
    $consultaFim->execute(['partida' => (int) $_SESSION['desafio_partida_id']]);
}

// ==================================================
// 4. PROCESSAR A RESPOSTA
// ==================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tempoRestante > 0 && isset($_POST['responder_desafio'])) {
    $idQuestao = filter_input(INPUT_POST, 'id_questao', FILTER_VALIDATE_INT);
    $idAlternativa = filter_input(INPUT_POST, 'id_alternativa', FILTER_VALIDATE_INT);
    $idParticipante = (int) ($_SESSION['desafio_participante_id'] ?? 0);
    $idPartida = (int) ($_SESSION['desafio_partida_id'] ?? 0);

    if ($idQuestao && $idAlternativa && $idParticipante && $idPartida) {
        $consulta = $conexao->prepare(
            'SELECT a.correta, q.enunciado, q.explicacao_curta
             FROM alternativa a
             JOIN questao q ON q.id_questao = a.id_questao
             WHERE a.id_alternativa = :alternativa
               AND a.id_questao = :questao
               AND q.id_jogo = 1
               AND q.ativo = 1
             LIMIT 1'
        );
        $consulta->execute([
            'alternativa' => $idAlternativa,
            'questao' => $idQuestao
        ]);
        $dadosResposta = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($dadosResposta) {
            $consultaValida = $conexao->prepare(
                "SELECT id_participante
                 FROM partida_participante
                 WHERE id_participante = :participante
                   AND id_partida = :partida
                   AND id_aluno = :aluno
                   AND EXISTS (
                       SELECT 1
                       FROM partida
                       WHERE partida.id_partida = :partida_verificacao
                         AND partida.status = 'em_andamento'
                   )
                 LIMIT 1"
            );
            $consultaValida->execute([
                'participante' => $idParticipante,
                'partida' => $idPartida,
                'aluno' => (int) $_SESSION['aluno_id'],
                'partida_verificacao' => $idPartida
            ]);

            if ($consultaValida->fetchColumn()) {
                $acertou = (int) $dadosResposta['correta'] === 1;
                $xpQuestao = 0;

                if ($acertou) {
                    $consultaXP = $conexao->prepare(
                        'SELECT xp_por_acerto
                         FROM dificuldade
                         WHERE id_dificuldade = (
                             SELECT id_dificuldade
                             FROM questao
                             WHERE id_questao = :questao
                               AND id_jogo = 1
                               AND ativo = 1
                         )
                         LIMIT 1'
                    );
                    $consultaXP->execute(['questao' => $idQuestao]);
                    $xpQuestao = (int) $consultaXP->fetchColumn();
                }

                // Registra resposta
                $consultaRegistro = $conexao->prepare(
                    'INSERT INTO resposta (id_participante, id_questao, id_alternativa, acertou, xp_ganho)
                     VALUES (:participante, :questao, :alternativa, :acertou, :xp)'
                );
                $consultaRegistro->execute([
                    'participante' => $idParticipante,
                    'questao' => $idQuestao,
                    'alternativa' => $idAlternativa,
                    'acertou' => $acertou ? 1 : 0,
                    'xp' => $xpQuestao
                ]);

                // Atualiza estatísticas
                if ($acertou) {
                    $consultaAtualiza = $conexao->prepare(
                        'UPDATE partida_participante
                         SET xp_total = xp_total + :xp,
                             acertos = acertos + 1
                         WHERE id_participante = :participante'
                    );
                    $consultaAtualiza->execute([
                        'xp' => $xpQuestao,
                        'participante' => $idParticipante
                    ]);

                    $_SESSION['desafio_xp'] += $xpQuestao;
                    $_SESSION['desafio_acertos']++;

                    $_SESSION['desafio_feedback'] = [
                        'tipo' => 'acerto',
                        'texto' => "✨ Feitiço perfeito! Você acertou e ganhou {$xpQuestao} XP!",
                        'explicacao' => $dadosResposta['explicacao_curta'] ?? ''
                    ];
                } else {
                    $consultaAtualiza = $conexao->prepare(
                        'UPDATE partida_participante
                         SET erros = erros + 1
                         WHERE id_participante = :participante'
                    );
                    $consultaAtualiza->execute(['participante' => $idParticipante]);

                    $_SESSION['desafio_erros']++;

                    $dicaErro = gerarDicaBico($dadosResposta['enunciado'], $dadosResposta['explicacao_curta'] ?? '');

                    $_SESSION['desafio_feedback'] = [
                        'tipo' => 'erro',
                        'texto' => "🦉 Ops, não foi desta vez! Mas tudo bem errar, o Bicó te ajuda a calcular:",
                        'explicacao' => $dicaErro
                    ];
                }
            }
        }
    }

    $tempoRestante = max(0, $tempoDesafioTotal - (time() - (int) $_SESSION['desafio_inicio']));
    header('Location: ?pagina=desafio');
    exit;
}

if (!empty($_SESSION['desafio_feedback'])) {
    $mensagem = $_SESSION['desafio_feedback']['texto'];
    $mensagemTipo = $_SESSION['desafio_feedback']['tipo'];
    $explicacao = $_SESSION['desafio_feedback']['explicacao'];
    unset($_SESSION['desafio_feedback']);
}

// ==================================================
// 5. BUSCAR NOVA QUESTÃO
// ==================================================
if ($tempoRestante > 0) {
    $consulta = $conexao->prepare(
        'SELECT id_questao, enunciado, explicacao_curta
         FROM questao
         WHERE id_jogo = 1
           AND ativo = 1
         ORDER BY RAND()
         LIMIT 1'
    );
    $consulta->execute();
    $questao = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($questao) {
        $consultaAlternativas = $conexao->prepare(
            'SELECT id_alternativa, texto
             FROM alternativa
             WHERE id_questao = :questao
             ORDER BY id_alternativa'
        );
        $consultaAlternativas->execute(['questao' => $questao['id_questao']]);
        $alternativas = $consultaAlternativas->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<main class="container">
    <section class="hero">
        <span class="badge">Batalha na Floresta · Modo Desafio</span>
        <h2>Desafio Relâmpago (60 Segundos)</h2>
        <p>Responda o máximo de contas de adição e subtração antes que o tempo acabe!</p>

        <!-- CRONÔMETRO E STATUS -->
        <div class="painel-status desafio-painel-status">
            <div class="desafio-hud-grid">
                <div>
                    <span class="label-hud">Tempo restante:</span>
                    <strong id="cronometro" class="cronometro-destaque">01:00</strong>
                </div>
                <div>
                    <span class="label-hud">XP Conquistado:</span>
                    <strong id="pontuacaoXP">⭐ <?= (int) $_SESSION['desafio_xp'] ?> XP</strong>
                </div>
                <div>
                    <span class="label-hud">Acertos:</span>
                    <strong id="pontuacaoAcertos" style="color: var(--amarelo);">✅ <?= (int) $_SESSION['desafio_acertos'] ?></strong>
                </div>
                <div>
                    <span class="label-hud">Erros:</span>
                    <strong id="pontuacaoErros" style="color: #ffb4b4;">❌ <?= (int) $_SESSION['desafio_erros'] ?></strong>
                </div>
            </div>
            <progress id="barraTempo" value="<?= $tempoRestante ?>" max="<?= $tempoDesafioTotal ?>" style="width: 100%; margin-top: 14px;" aria-label="Tempo restante"></progress>
        </div>

        <!-- FEEDBACK DO BICÓ (ACERTO / ERRO) -->
        <?php if ($mensagem !== ''): ?>
            <?= renderBicoFeedbackHtml($mensagem, $mensagemTipo, $explicacao) ?>
        <?php endif; ?>

        <!-- ÁREA FINAL DO DESAFIO (QUANDO O TEMPO ACABA) -->
        <article
            id="areaFim"
            class="card resultado-card"
            <?= $tempoRestante > 0 ? 'hidden' : '' ?>
            style="margin-top: 24px;">

            <h3>⏱️ Desafio Relâmpago Encerrado!</h3>
            <p>O tempo de 60 segundos terminou! Veja seu desempenho nesta aventura com o Bicó:</p>

            <?php
            $acertosDesafio = (int) ($_SESSION['desafio_acertos'] ?? 0);
            $errosDesafio = (int) ($_SESSION['desafio_erros'] ?? 0);
            $totalRespondidasDesafio = $acertosDesafio + $errosDesafio;
            $xpDesafio = (int) ($_SESSION['desafio_xp'] ?? 0);
            ?>

            <div class="grid" style="margin: 20px 0;">
                <article class="card">
                    <h4>Questões Respondidas</h4>
                    <p class="destaque-numero"><?= $totalRespondidasDesafio ?></p>
                </article>

                <article class="card">
                    <h4>Acertos Rápidos</h4>
                    <p class="destaque-numero" style="color: var(--cor-acerto) !important;">✅ <?= $acertosDesafio ?></p>
                </article>

                <article class="card">
                    <h4>Tentativas / Erros</h4>
                    <p class="destaque-numero" style="color: var(--cor-erro) !important;">❌ <?= $errosDesafio ?></p>
                </article>

                <article class="card">
                    <h4>XP Total Conquistado</h4>
                    <p class="destaque-numero" style="color: var(--cor-ouro-escuro) !important;">⭐ <?= $xpDesafio ?> XP</p>
                </article>
            </div>

            <div style="margin-top: 20px;">
                <a href="?pagina=desafio&novo=1" class="btn">
                    ⚡ Jogar novamente (Mais 60s)
                </a>
                <a href="?pagina=batalha" class="btn btn-secondary">
                    Voltar aos modos de Batalha
                </a>
            </div>
        </article>

        <!-- ÁREA DA QUESTÃO ATIVA -->
        <div id="areaQuestao" <?= $tempoRestante <= 0 ? 'hidden' : '' ?>>
            <?php if ($questao): ?>
                <?php
                $dicaAtiva = gerarDicaBico($questao['enunciado'], $questao['explicacao_curta'] ?? '');
                ?>
                <article class="card">
                    <span class="badge">Desafio de Cálculo Rápido</span>
                    <h3 style="font-size: 1.4rem; margin: 14px 0;">
                        <?= htmlspecialchars($questao['enunciado'], ENT_QUOTES, 'UTF-8') ?>
                    </h3>

                    <!-- DICA MÁGICA DO BICÓ SOB DEMANDA -->
                    <?= renderBicoDicaBotaoHtml($dicaAtiva) ?>

                    <?php if (count($alternativas) > 0): ?>
                        <form method="POST" id="formResposta" style="margin-top: 18px;">
                            <input type="hidden" name="id_questao" value="<?= (int) $questao['id_questao'] ?>">

                            <fieldset>
                                <legend class="sr-only">Selecione a resposta correta</legend>
                                <?php foreach ($alternativas as $alternativa): ?>
                                    <label>
                                        <input
                                            type="radio"
                                            name="id_alternativa"
                                            value="<?= (int) $alternativa['id_alternativa'] ?>"
                                            required>
                                        <strong><?= htmlspecialchars($alternativa['texto'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    </label>
                                    <br>
                                <?php endforeach; ?>
                            </fieldset>

                            <div style="margin-top: 18px;">
                                <button type="submit" name="responder_desafio" value="1" class="btn">
                                    Confirmar Resposta ⚡
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <p>Esta questão ainda não possui alternativas.</p>
                    <?php endif; ?>
                </article>

                <!-- BOTÃO DE ABANDONAR BATALHA -->
                <p style="margin-top: 24px;">
                    <a href="?pagina=abandonar_desafio" class="btn btn-secondary" onclick="return confirm('Tem certeza que deseja abandonar este desafio relâmpago?');">
                        🏳️ Abandonar desafio e voltar aos modos
                    </a>
                </p>

            <?php else: ?>
                <p>Não foi encontrada uma questão ativa para este jogo.</p>
            <?php endif; ?>
        </div>

    </section>
</main>

<!-- JAVASCRIPT DO CRONÔMETRO -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let segundos = <?= (int) $tempoRestante ?>;
        const tempoTotal = <?= (int) $tempoDesafioTotal ?>;

        const cronometro = document.getElementById('cronometro');
        const barraTempo = document.getElementById('barraTempo');
        const formulario = document.getElementById('formResposta');
        const areaQuestao = document.getElementById('areaQuestao');
        const areaFim = document.getElementById('areaFim');

        let intervaloCronometro = null;

        function atualizarExibicao() {
            const minutos = Math.floor(segundos / 60);
            const resto = segundos % 60;
            cronometro.textContent = String(minutos).padStart(2, '0') + ':' + String(resto).padStart(2, '0');
            if (barraTempo) {
                barraTempo.value = segundos;
            }
        }

        function finalizarDesafio() {
            if (intervaloCronometro !== null) {
                clearInterval(intervaloCronometro);
                intervaloCronometro = null;
            }
            segundos = 0;
            cronometro.textContent = '00:00';
            if (barraTempo) barraTempo.value = 0;

            if (formulario) {
                formulario.querySelectorAll('input, button').forEach(function(el) {
                    el.disabled = true;
                });
            }

            if (areaQuestao) areaQuestao.hidden = true;
            if (areaFim) areaFim.hidden = false;

            fetch('?pagina=finalizar_desafio', {
                method: 'POST',
                credentials: 'same-origin'
            }).catch(function(err) {
                console.error(err);
            });
        }

        atualizarExibicao();

        if (segundos <= 0) {
            finalizarDesafio();
            return;
        }

        intervaloCronometro = setInterval(function() {
            segundos--;
            if (segundos <= 0) {
                finalizarDesafio();
                return;
            }
            atualizarExibicao();
        }, 1000);
    });
</script>
```

---

## Arquivo 4: `app/views/pages/dupla.php`

```php
<?php

require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../helpers/bicoHelper.php';

if (empty($_SESSION['aluno_id'])) {
    header('Location: ?pagina=login');
    exit;
}

$idAluno = (int) $_SESSION['aluno_id'];
$mensagem = '';
$mensagemTipo = '';
$explicacao = '';

// ============================================
// 1. REINICIAR / LIMPAR ESTADO SE SOLICITADO
// ============================================
if (isset($_GET['novo'])) {
    unset(
        $_SESSION['dupla_partida_id'],
        $_SESSION['dupla_p1_participante_id'],
        $_SESSION['dupla_p2_participante_id'],
        $_SESSION['dupla_p1_nome'],
        $_SESSION['dupla_p2_nome'],
        $_SESSION['dupla_dificuldade'],
        $_SESSION['dupla_turno'],
        $_SESSION['dupla_p1_questoes'],
        $_SESSION['dupla_p2_questoes'],
        $_SESSION['dupla_p1_acertos'],
        $_SESSION['dupla_p1_erros'],
        $_SESSION['dupla_p1_xp'],
        $_SESSION['dupla_p2_acertos'],
        $_SESSION['dupla_p2_erros'],
        $_SESSION['dupla_p2_xp'],
        $_SESSION['dupla_feedback']
    );
    header('Location: ?pagina=dupla');
    exit;
}

// ============================================
// 2. VALIDAR SE PARTIDA ATIVA CONTINUA EM ANDAMENTO NO BANCO
// ============================================
if (!empty($_SESSION['dupla_partida_id'])) {
    $consultaPartidaValida = $conexao->prepare(
        "SELECT status
         FROM partida
         WHERE id_partida = :partida
           AND EXISTS (
               SELECT 1
               FROM partida_participante
               WHERE partida_participante.id_partida = partida.id_partida
                 AND partida_participante.id_aluno = :aluno
           )
         LIMIT 1"
    );
    $consultaPartidaValida->execute([
        'partida' => (int) $_SESSION['dupla_partida_id'],
        'aluno' => $idAluno
    ]);
    $statusAtual = $consultaPartidaValida->fetchColumn();

    if ($statusAtual !== 'em_andamento') {
        unset(
            $_SESSION['dupla_partida_id'],
            $_SESSION['dupla_p1_participante_id'],
            $_SESSION['dupla_p2_participante_id'],
            $_SESSION['dupla_p1_nome'],
            $_SESSION['dupla_p2_nome'],
            $_SESSION['dupla_dificuldade'],
            $_SESSION['dupla_turno'],
            $_SESSION['dupla_p1_questoes'],
            $_SESSION['dupla_p2_questoes'],
            $_SESSION['dupla_p1_acertos'],
            $_SESSION['dupla_p1_erros'],
            $_SESSION['dupla_p1_xp'],
            $_SESSION['dupla_p2_acertos'],
            $_SESSION['dupla_p2_erros'],
            $_SESSION['dupla_p2_xp'],
            $_SESSION['dupla_feedback']
        );
    }
}

// ============================================
// 3. INICIAR UMA NOVA PARTIDA EM DUPLA
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['iniciar_dupla'])) {
    $idDificuldade = filter_input(INPUT_POST, 'id_dificuldade', FILTER_VALIDATE_INT);
    $apelidoP2 = trim($_POST['apelido_jogador2'] ?? '');
    if ($apelidoP2 === '') {
        $apelidoP2 = 'Jogador 2';
    }
    if (mb_strlen($apelidoP2) > 40) {
        $apelidoP2 = mb_substr($apelidoP2, 0, 40);
    }

    $consultaDificuldade = $conexao->prepare(
        'SELECT id_dificuldade, nome, xp_por_acerto
         FROM dificuldade
         WHERE id_dificuldade = :id'
    );
    $consultaDificuldade->execute(['id' => $idDificuldade]);
    $dificuldade = $consultaDificuldade->fetch(PDO::FETCH_ASSOC);

    if (!$dificuldade) {
        $mensagem = 'Selecione uma dificuldade válida.';
        $mensagemTipo = 'erro';
    } else {
        $consultaModo = $conexao->prepare(
            'SELECT id_modo_jogo FROM modo_jogo WHERE nome = :nome LIMIT 1'
        );
        $consultaModo->execute(['nome' => 'Dupla Local']);
        $idModo = $consultaModo->fetchColumn();

        if ($idModo === false) {
            $mensagem = 'Modo Dupla Local não encontrado no banco.';
            $mensagemTipo = 'erro';
        } else {
            $consultaPartida = $conexao->prepare(
                "INSERT INTO partida (id_jogo, id_modo_jogo, tempo_segundos, status)
                 VALUES (1, :modo, 0, 'em_andamento')"
            );
            $consultaPartida->execute(['modo' => (int) $idModo]);
            $idPartida = (int) $conexao->lastInsertId();

            $nomeP1 = $_SESSION['aluno_nome'] ?? 'Jogador 1';

            // Jogador 1 (Caçador)
            $consultaP1 = $conexao->prepare(
                'INSERT INTO partida_participante (id_partida, id_aluno, apelido, xp_total, acertos, erros)
                 VALUES (:partida, :aluno, :apelido, 0, 0, 0)'
            );
            $consultaP1->execute([
                'partida' => $idPartida,
                'aluno' => $idAluno,
                'apelido' => $nomeP1
            ]);
            $idP1Participante = (int) $conexao->lastInsertId();

            // Jogador 2 (Monstrinho)
            $consultaP2 = $conexao->prepare(
                'INSERT INTO partida_participante (id_partida, id_aluno, apelido, xp_total, acertos, erros)
                 VALUES (:partida, NULL, :apelido, 0, 0, 0)'
            );
            $consultaP2->execute([
                'partida' => $idPartida,
                'apelido' => $apelidoP2
            ]);
            $idP2Participante = (int) $conexao->lastInsertId();

            $_SESSION['dupla_partida_id'] = $idPartida;
            $_SESSION['dupla_p1_participante_id'] = $idP1Participante;
            $_SESSION['dupla_p2_participante_id'] = $idP2Participante;
            $_SESSION['dupla_p1_nome'] = $nomeP1;
            $_SESSION['dupla_p2_nome'] = $apelidoP2;
            $_SESSION['dupla_dificuldade'] = (int) $dificuldade['id_dificuldade'];
            $_SESSION['dupla_turno'] = 1;
            $_SESSION['dupla_p1_questoes'] = 0;
            $_SESSION['dupla_p2_questoes'] = 0;
            $_SESSION['dupla_p1_acertos'] = 0;
            $_SESSION['dupla_p1_erros'] = 0;
            $_SESSION['dupla_p1_xp'] = 0;
            $_SESSION['dupla_p2_acertos'] = 0;
            $_SESSION['dupla_p2_erros'] = 0;
            $_SESSION['dupla_p2_xp'] = 0;
            unset($_SESSION['dupla_feedback']);

            header('Location: ?pagina=dupla');
            exit;
        }
    }
}

// ============================================
// 4. ESTADO DA PARTIDA EM DUPLA
// ============================================
$partidaAtiva = !empty($_SESSION['dupla_partida_id']);
$fimDeJogo = false;
$vencedorTexto = '';
$vencedorAvatar = 'cacador';

if ($partidaAtiva) {
    $idP1 = (int) $_SESSION['dupla_p1_participante_id'];
    $idP2 = (int) $_SESSION['dupla_p2_participante_id'];
    $idPartida = (int) $_SESSION['dupla_partida_id'];
    $idDificuldade = (int) $_SESSION['dupla_dificuldade'];
    $nomeP1 = $_SESSION['dupla_p1_nome'] ?? 'Caçador';
    $nomeP2 = $_SESSION['dupla_p2_nome'] ?? 'Monstrinho';
    $turnoAtual = (int) ($_SESSION['dupla_turno'] ?? 1);

    $totalP1 = (int) $_SESSION['dupla_p1_questoes'];
    $totalP2 = (int) $_SESSION['dupla_p2_questoes'];
    $fimDeJogo = ($totalP1 >= 10 && $totalP2 >= 10);

    // ============================================
    // 5. PROCESSAR RESPOSTA DO TURNO
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['responder_dupla']) && !$fimDeJogo) {
        $idQuestao = filter_input(INPUT_POST, 'id_questao', FILTER_VALIDATE_INT);
        $idAlternativa = filter_input(INPUT_POST, 'id_alternativa', FILTER_VALIDATE_INT);

        $idParticipanteVez = ($turnoAtual === 1) ? $idP1 : $idP2;
        $nomeVez = ($turnoAtual === 1) ? $nomeP1 : $nomeP2;
        $papelVez = ($turnoAtual === 1) ? 'Caçador' : 'Monstrinho';

        $consultaValida = $conexao->prepare(
            'SELECT a.correta, q.enunciado, q.explicacao_curta
             FROM alternativa a
             JOIN questao q ON q.id_questao = a.id_questao
             WHERE a.id_alternativa = :alternativa
               AND a.id_questao = :questao
               AND q.id_jogo = 1
               AND q.id_dificuldade = :dificuldade
               AND q.ativo = 1
             LIMIT 1'
        );
        $consultaValida->execute([
            'alternativa' => $idAlternativa,
            'questao' => $idQuestao,
            'dificuldade' => $idDificuldade
        ]);
        $dadosResp = $consultaValida->fetch(PDO::FETCH_ASSOC);

        if ($dadosResp) {
            $acertou = ((int) $dadosResp['correta'] === 1);
            $xpGanho = 0;

            if ($acertou) {
                $consultaXP = $conexao->prepare(
                    'SELECT xp_por_acerto FROM dificuldade WHERE id_dificuldade = :dif'
                );
                $consultaXP->execute(['dif' => $idDificuldade]);
                $xpGanho = (int) $consultaXP->fetchColumn();
            }

            // Registra resposta
            $consultaIns = $conexao->prepare(
                'INSERT INTO resposta (id_participante, id_questao, id_alternativa, acertou, xp_ganho)
                 VALUES (:participante, :questao, :alternativa, :acertou, :xp)'
            );
            $consultaIns->execute([
                'participante' => $idParticipanteVez,
                'questao' => $idQuestao,
                'alternativa' => $idAlternativa,
                'acertou' => $acertou ? 1 : 0,
                'xp' => $xpGanho
            ]);

            // Atualiza participante
            $consultaUpd = $conexao->prepare(
                'UPDATE partida_participante
                 SET xp_total = xp_total + :xp,
                     acertos = acertos + :acertos,
                     erros = erros + :erros
                 WHERE id_participante = :participante'
            );
            $consultaUpd->execute([
                'xp' => $xpGanho,
                'acertos' => $acertou ? 1 : 0,
                'erros' => $acertou ? 0 : 1,
                'participante' => $idParticipanteVez
            ]);

            // Atualiza sessão
            if ($turnoAtual === 1) {
                $_SESSION['dupla_p1_questoes']++;
                if ($acertou) {
                    $_SESSION['dupla_p1_acertos']++;
                    $_SESSION['dupla_p1_xp'] += $xpGanho;
                } else {
                    $_SESSION['dupla_p1_erros']++;
                }
            } else {
                $_SESSION['dupla_p2_questoes']++;
                if ($acertou) {
                    $_SESSION['dupla_p2_acertos']++;
                    $_SESSION['dupla_p2_xp'] += $xpGanho;
                } else {
                    $_SESSION['dupla_p2_erros']++;
                }
            }

            $dicaBico = gerarDicaBico($dadosResp['enunciado'], $dadosResp['explicacao_curta'] ?? '');

            if ($acertou) {
                $feedMsg = "✨ Grande jogada de {$nomeVez} ({$papelVez})! Resposta correta (+{$xpGanho} XP)!";
                $feedTipo = 'acerto';
            } else {
                $feedMsg = "🦉 {$nomeVez} ({$papelVez}) quase acertou! Mas errar é praticar, confira o cálculo:";
                $feedTipo = 'erro';
            }

            $_SESSION['dupla_feedback'] = [
                'tipo' => $feedTipo,
                'texto' => $feedMsg,
                'explicacao' => $dicaBico
            ];

            // Alterna o turno
            $novaQ1 = (int) $_SESSION['dupla_p1_questoes'];
            $novaQ2 = (int) $_SESSION['dupla_p2_questoes'];

            if ($novaQ1 < 10 && $novaQ2 < 10) {
                $_SESSION['dupla_turno'] = ($turnoAtual === 1) ? 2 : 1;
            } elseif ($novaQ1 < 10) {
                $_SESSION['dupla_turno'] = 1;
            } elseif ($novaQ2 < 10) {
                $_SESSION['dupla_turno'] = 2;
            }

            if ($novaQ1 >= 10 && $novaQ2 >= 10) {
                $consultaFim = $conexao->prepare(
                    "UPDATE partida SET status = 'finalizada', data_fim = NOW() WHERE id_partida = :partida AND status = 'em_andamento'"
                );
                $consultaFim->execute(['partida' => $idPartida]);
            }

            header('Location: ?pagina=dupla');
            exit;
        }
    }

    $qP1 = (int) $_SESSION['dupla_p1_questoes'];
    $qP2 = (int) $_SESSION['dupla_p2_questoes'];
    $fimDeJogo = ($qP1 >= 10 && $qP2 >= 10);

    if (!empty($_SESSION['dupla_feedback'])) {
        $mensagem = $_SESSION['dupla_feedback']['texto'];
        $mensagemTipo = $_SESSION['dupla_feedback']['tipo'];
        $explicacao = $_SESSION['dupla_feedback']['explicacao'];
        unset($_SESSION['dupla_feedback']);
    }

    $questao = null;
    $alternativas = [];

    if (!$fimDeJogo) {
        $turnoAtual = (int) ($_SESSION['dupla_turno'] ?? 1);

        $consultaQ = $conexao->prepare(
            'SELECT id_questao, enunciado, explicacao_curta
             FROM questao
             WHERE id_jogo = 1
               AND id_dificuldade = :dificuldade
               AND ativo = 1
               AND id_questao NOT IN (
                   SELECT id_questao FROM resposta WHERE id_participante IN (:p1, :p2)
               )
             ORDER BY RAND()
             LIMIT 1'
        );
        $consultaQ->execute([
            'dificuldade' => $idDificuldade,
            'p1' => $idP1,
            'p2' => $idP2
        ]);
        $questao = $consultaQ->fetch(PDO::FETCH_ASSOC);

        if ($questao) {
            $consultaAlt = $conexao->prepare(
                'SELECT id_alternativa, texto FROM alternativa WHERE id_questao = :questao ORDER BY id_alternativa'
            );
            $consultaAlt->execute(['questao' => (int) $questao['id_questao']]);
            $alternativas = $consultaAlt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $fimDeJogo = true;
            $consultaFim = $conexao->prepare(
                "UPDATE partida SET status = 'finalizada', data_fim = NOW() WHERE id_partida = :partida AND status = 'em_andamento'"
            );
            $consultaFim->execute(['partida' => $idPartida]);
        }
    }

    if ($fimDeJogo) {
        $acertos1 = (int) $_SESSION['dupla_p1_acertos'];
        $acertos2 = (int) $_SESSION['dupla_p2_acertos'];

        if ($acertos1 > $acertos2) {
            $vencedorTexto = "🏆 Vitória de {$nomeP1} (O Caçador)!";
            $vencedorAvatar = 'cacador';
        } elseif ($acertos2 > $acertos1) {
            $vencedorTexto = "🏆 Vitória de {$nomeP2} (O Monstrinho)!";
            $vencedorAvatar = 'monstrinho';
        } else {
            $vencedorTexto = "🤝 Empate Mágico! Ambos os aventureiros foram brilhantes!";
            $vencedorAvatar = 'ambos';
        }
    }
}
?>

<main class="container">
    <section class="hero">
        <span class="badge">Batalha na Floresta · Modo Dupla Local</span>
        <h2>Duelo na Floresta (Caçador vs Monstrinho)</h2>
        <p>Dois aventureiros no mesmo teclado com a estética mágica de Numéria!</p>

        <!-- FEEDBACK DO BICÓ -->
        <?php if ($mensagem !== ''): ?>
            <?= renderBicoFeedbackHtml($mensagem, $mensagemTipo, $explicacao) ?>
        <?php endif; ?>

        <?php if (!$partidaAtiva): ?>

            <article class="card">
                <h3>Configurar Duelo em Dupla</h3>
                <p>O <strong>Jogador 1</strong> é o Caçador e o <strong>Jogador 2</strong> é o Monstrinho da Floresta em pixel art.</p>

                <?php
                $consultaDificuldades = $conexao->query(
                    'SELECT id_dificuldade, nome, xp_por_acerto FROM dificuldade ORDER BY id_dificuldade'
                );
                $dificuldades = $consultaDificuldades->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <form method="POST">
                    <div style="margin-bottom: 20px;">
                        <label>
                            <strong>Jogador 1 — O Caçador da Floresta:</strong><br>
                            <div class="input-com-avatar">
                                <span class="pixel-avatar-preview avatar-cacador" aria-hidden="true"></span>
                                <input type="text" value="<?= htmlspecialchars($_SESSION['aluno_nome'] ?? 'Jogador 1', ENT_QUOTES, 'UTF-8') ?>" disabled>
                            </div>
                        </label>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label for="apelido_jogador2">
                            <strong>Jogador 2 — O Monstrinho Mágico:</strong><br>
                            <div class="input-com-avatar">
                                <span class="pixel-avatar-preview avatar-monstrinho" aria-hidden="true"></span>
                                <input
                                    type="text"
                                    name="apelido_jogador2"
                                    id="apelido_jogador2"
                                    placeholder="Apelido do amigo (Monstrinho)"
                                    maxlength="40"
                                    required>
                            </div>
                        </label>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label for="id_dificuldade">
                            <strong>Dificuldade dos Desafios:</strong><br>
                            <select name="id_dificuldade" id="id_dificuldade" required>
                                <option value="">Selecione uma dificuldade</option>
                                <?php foreach ($dificuldades as $dif): ?>
                                    <option value="<?= (int) $dif['id_dificuldade'] ?>">
                                        <?= htmlspecialchars($dif['nome'], ENT_QUOTES, 'UTF-8') ?> (<?= (int) $dif['xp_por_acerto'] ?> XP por acerto)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>

                    <button type="submit" name="iniciar_dupla" value="1" class="btn">
                        Iniciar Duelo em Dupla
                    </button>
                </form>
            </article>

            <p style="margin-top: 24px;">
                <a href="?pagina=batalha">Voltar aos modos de Batalha</a>
            </p>

        <?php elseif ($fimDeJogo): ?>

            <article class="card resultado-card">
                <div class="resultado-vencedor-destaque">
                    <?php if ($vencedorAvatar === 'cacador'): ?>
                        <span class="pixel-avatar-grande avatar-cacador" aria-hidden="true"></span>
                    <?php elseif ($vencedorAvatar === 'monstrinho'): ?>
                        <span class="pixel-avatar-grande avatar-monstrinho" aria-hidden="true"></span>
                    <?php else: ?>
                        <div class="dupla-empate-avatars">
                            <span class="pixel-avatar-grande avatar-cacador" aria-hidden="true"></span>
                            <span class="pixel-avatar-grande avatar-monstrinho" aria-hidden="true"></span>
                        </div>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($vencedorTexto, ENT_QUOTES, 'UTF-8') ?></h3>
                </div>
                <p>O duelo de matemática na floresta foi concluído! O Bicó parabeniza os dois heróis:</p>

                <div class="grid" style="margin-top: 20px;">
                    <article class="card">
                        <div class="card-avatar-topo">
                            <span class="pixel-avatar-preview avatar-cacador" aria-hidden="true"></span>
                            <h4><?= htmlspecialchars($nomeP1, ENT_QUOTES, 'UTF-8') ?> (Caçador)</h4>
                        </div>
                        <p>Questões: <?= (int) $_SESSION['dupla_p1_questoes'] ?> de 10</p>
                        <p class="destaque-numero" style="color: var(--cor-acerto) !important;">✅ <?= (int) $_SESSION['dupla_p1_acertos'] ?> acertos</p>
                        <p>Erros: <?= (int) $_SESSION['dupla_p1_erros'] ?></p>
                        <p>XP: <strong>⭐ <?= (int) $_SESSION['dupla_p1_xp'] ?> XP</strong></p>
                    </article>

                    <article class="card">
                        <div class="card-avatar-topo">
                            <span class="pixel-avatar-preview avatar-monstrinho" aria-hidden="true"></span>
                            <h4><?= htmlspecialchars($nomeP2, ENT_QUOTES, 'UTF-8') ?> (Monstrinho)</h4>
                        </div>
                        <p>Questões: <?= (int) $_SESSION['dupla_p2_questoes'] ?> de 10</p>
                        <p class="destaque-numero" style="color: var(--roxo) !important;">✅ <?= (int) $_SESSION['dupla_p2_acertos'] ?> acertos</p>
                        <p>Erros: <?= (int) $_SESSION['dupla_p2_erros'] ?></p>
                        <p>XP: <strong>⭐ <?= (int) $_SESSION['dupla_p2_xp'] ?> XP</strong></p>
                    </article>
                </div>

                <div style="margin-top: 24px;">
                    <a href="?pagina=dupla&novo=1" class="btn">Jogar novamente</a>
                    <a href="?pagina=abandonar_dupla" class="btn btn-secondary">Voltar aos modos</a>
                </div>
            </article>

        <?php else: ?>

            <?php
            $turno = (int) ($_SESSION['dupla_turno'] ?? 1);
            $nomeAtual = ($turno === 1) ? $nomeP1 : $nomeP2;
            $papelAtual = ($turno === 1) ? 'Caçador' : 'Monstrinho';
            $classeAvatarAtual = ($turno === 1) ? 'avatar-cacador' : 'avatar-monstrinho';
            ?>

            <!-- PLACAR EM TEMPO REAL COM AVATARES EM PIXEL ART -->
            <div class="grid dupla-placar-grid" style="margin-bottom: 24px;">
                <article class="card card-jogador <?= ($turno === 1) ? 'turno-ativo' : '' ?>">
                    <div class="card-avatar-topo">
                        <span class="pixel-avatar-preview avatar-cacador" aria-hidden="true"></span>
                        <div>
                            <h4><?= htmlspecialchars($nomeP1, ENT_QUOTES, 'UTF-8') ?></h4>
                            <span class="badge-personagem">O Caçador</span>
                        </div>
                    </div>
                    <p>Desafio: <strong><?= (int) $_SESSION['dupla_p1_questoes'] ?>/10</strong></p>
                    <p>Acertos: <strong><?= (int) $_SESSION['dupla_p1_acertos'] ?></strong> | XP: ⭐ <strong><?= (int) $_SESSION['dupla_p1_xp'] ?></strong></p>
                    <?php if ($turno === 1): ?>
                        <div class="badge-vez">🎯 SUA VEZ DE ATACAR!</div>
                    <?php endif; ?>
                </article>

                <article class="card card-jogador <?= ($turno === 2) ? 'turno-ativo' : '' ?>">
                    <div class="card-avatar-topo">
                        <span class="pixel-avatar-preview avatar-monstrinho" aria-hidden="true"></span>
                        <div>
                            <h4><?= htmlspecialchars($nomeP2, ENT_QUOTES, 'UTF-8') ?></h4>
                            <span class="badge-personagem badge-roxo">O Monstrinho</span>
                        </div>
                    </div>
                    <p>Desafio: <strong><?= (int) $_SESSION['dupla_p2_questoes'] ?>/10</strong></p>
                    <p>Acertos: <strong><?= (int) $_SESSION['dupla_p2_acertos'] ?></strong> | XP: ⭐ <strong><?= (int) $_SESSION['dupla_p2_xp'] ?></strong></p>
                    <?php if ($turno === 2): ?>
                        <div class="badge-vez">🐾 SUA VEZ DE ATACAR!</div>
                    <?php endif; ?>
                </article>
            </div>

            <!-- FAIXA DE DESTAQUE DO TURNO -->
            <div class="destaque-turno turno-banner-pixel">
                <div class="turno-flex">
                    <span class="pixel-avatar-preview <?= $classeAvatarAtual ?>" aria-hidden="true"></span>
                    <h3>Vez de <?= htmlspecialchars($nomeAtual, ENT_QUOTES, 'UTF-8') ?> (<?= $papelAtual ?>)</h3>
                </div>
            </div>

            <?php if ($questao): ?>
                <?php
                $dicaDupla = gerarDicaBico($questao['enunciado'], $questao['explicacao_curta'] ?? '');
                ?>
                <article class="card">
                    <span class="badge">Desafio de <?= $papelAtual ?></span>
                    <h3 style="font-size: 1.4rem; margin: 14px 0;">
                        <?= htmlspecialchars($questao['enunciado'], ENT_QUOTES, 'UTF-8') ?>
                    </h3>

                    <!-- DICA DO BICÓ EM DUPLA -->
                    <?= renderBicoDicaBotaoHtml($dicaDupla) ?>

                    <?php if ($alternativas): ?>
                        <form method="POST" style="margin-top: 18px;">
                            <input type="hidden" name="id_questao" value="<?= (int) $questao['id_questao'] ?>">

                            <fieldset>
                                <legend class="sr-only">Escolha a resposta correta</legend>
                                <?php foreach ($alternativas as $alt): ?>
                                    <label>
                                        <input
                                            type="radio"
                                            name="id_alternativa"
                                            value="<?= (int) $alt['id_alternativa'] ?>"
                                            required>
                                        <strong><?= htmlspecialchars($alt['texto'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    </label>
                                    <br>
                                <?php endforeach; ?>
                            </fieldset>

                            <div style="margin-top: 18px;">
                                <button type="submit" name="responder_dupla" value="1" class="btn">
                                    Confirmar Ataque de <?= htmlspecialchars($nomeAtual, ENT_QUOTES, 'UTF-8') ?>
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <p>Esta questão não possui alternativas cadastradas.</p>
                    <?php endif; ?>
                </article>
            <?php else: ?>
                <p>Não há mais questões disponíveis para esta rodada.</p>
            <?php endif; ?>

            <p style="margin-top: 24px;">
                <a href="?pagina=abandonar_dupla" class="btn btn-secondary">
                    Abandonar partida em dupla e voltar aos modos
                </a>
            </p>

        <?php endif; ?>
    </section>
</main>
```

---

## Arquivo 5: `app/views/pages/guardiao.php`

```php
<?php

require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../helpers/bicoHelper.php';

if (empty($_SESSION['aluno_id'])) {
    header('Location: ?pagina=login');
    exit;
}

$idAluno = (int) $_SESSION['aluno_id'];
$mensagem = $_SESSION['guardiao_mensagem'] ?? '';
$mensagemTipo = $_SESSION['guardiao_mensagem_tipo'] ?? 'acerto';
$explicacao = $_SESSION['guardiao_explicacao'] ?? '';
unset($_SESSION['guardiao_mensagem'], $_SESSION['guardiao_mensagem_tipo'], $_SESSION['guardiao_explicacao']);

$resultado = null;
$partidaAtiva = false;
$partidaSemEstado = false;
$partidaLegadaId = 0;
$idPartida = 0;
$idParticipante = 0;
$estado = null;
$questao = null;
$alternativas = [];
$vidasJogador = 3;
$vidasGuardiao = 3;
$totalQuestoes = 0;
$acertos = 0;
$erros = 0;
$xpTotal = 0;
$fimDeJogo = false;

if (isset($_GET['novo'])) {
    unset(
        $_SESSION['guardiao_partida_id'],
        $_SESSION['guardiao_participante_id'],
        $_SESSION['guardiao_vidas_jogador'],
        $_SESSION['guardiao_vidas_guardiao'],
        $_SESSION['guardiao_questoes'],
        $_SESSION['guardiao_xp'],
        $_SESSION['guardiao_acertos'],
        $_SESSION['guardiao_erros'],
        $_SESSION['guardiao_dificuldade'],
        $_SESSION['guardiao_feedback']
    );
    header('Location: ?pagina=guardiao');
    exit;
}

$consultaModo = $conexao->prepare(
    'SELECT id_modo_jogo FROM modo_jogo WHERE nome = :nome LIMIT 1'
);
$consultaModo->execute(['nome' => 'Contra o Guardião']);
$idModo = $consultaModo->fetchColumn();

$buscarPartidaAtiva = static function (
    PDO $conexao,
    int $idAluno,
    int $idModo
): array {
    $consultaPartidas = $conexao->prepare(
        "SELECT id_partida, id_participante
         FROM partida_participante
         WHERE id_aluno = :aluno
           AND id_partida IN (
               SELECT id_partida
               FROM partida
               WHERE id_modo_jogo = :modo
                 AND status = 'em_andamento'
           )
         ORDER BY id_partida DESC"
    );
    $consultaPartidas->execute([
        'aluno' => $idAluno,
        'modo' => $idModo
    ]);

    $partidas = $consultaPartidas->fetchAll(PDO::FETCH_ASSOC);
    $partidaSemEstado = null;

    foreach ($partidas as $partida) {
        $consultaEstado = $conexao->prepare(
            'SELECT id_participante, id_partida, id_dificuldade,
                    id_questao_atual, vidas_jogador, vidas_guardiao,
                    questoes_respondidas
             FROM guardiao_estado
             WHERE id_participante = :participante
               AND id_partida = :partida'
        );
        $consultaEstado->execute([
            'participante' => (int) $partida['id_participante'],
            'partida' => (int) $partida['id_partida']
        ]);
        $estado = $consultaEstado->fetch(PDO::FETCH_ASSOC);

        if ($estado) {
            return [
                'partida' => $partida,
                'estado' => $estado,
                'sem_estado' => false
            ];
        }

        if ($partidaSemEstado === null) {
            $partidaSemEstado = $partida;
        }
    }

    return [
        'partida' => $partidaSemEstado,
        'estado' => null,
        'sem_estado' => $partidaSemEstado !== null
    ];
};

$importarEstadoDaSessao = static function (
    PDO $conexao,
    int $idAluno,
    array $partida
): ?array {
    $chavesNecessarias = [
        'guardiao_partida_id',
        'guardiao_participante_id',
        'guardiao_dificuldade',
        'guardiao_vidas_jogador',
        'guardiao_vidas_guardiao',
        'guardiao_questoes',
        'guardiao_xp',
        'guardiao_acertos',
        'guardiao_erros'
    ];

    foreach ($chavesNecessarias as $chave) {
        if (!isset($_SESSION[$chave]) || filter_var($_SESSION[$chave], FILTER_VALIDATE_INT) === false) {
            return null;
        }
    }

    $idPartida = (int) $partida['id_partida'];
    $idParticipante = (int) $partida['id_participante'];

    if (
        (int) $_SESSION['guardiao_partida_id'] !== $idPartida
        || (int) $_SESSION['guardiao_participante_id'] !== $idParticipante
    ) {
        return null;
    }

    $idDificuldadeSessao = (int) $_SESSION['guardiao_dificuldade'];
    $vidasJogadorSessao = (int) $_SESSION['guardiao_vidas_jogador'];
    $vidasGuardiaoSessao = (int) $_SESSION['guardiao_vidas_guardiao'];
    $questoesSessao = (int) $_SESSION['guardiao_questoes'];
    $xpSessao = (int) $_SESSION['guardiao_xp'];
    $acertosSessao = (int) $_SESSION['guardiao_acertos'];
    $errosSessao = (int) $_SESSION['guardiao_erros'];

    if (
        $idDificuldadeSessao <= 0
        || $vidasJogadorSessao < 0
        || $vidasJogadorSessao > 3
        || $vidasGuardiaoSessao < 0
        || $vidasGuardiaoSessao > 3
        || $questoesSessao < 0
        || $questoesSessao > 10
        || $xpSessao < 0
        || $acertosSessao < 0
        || $errosSessao < 0
    ) {
        return null;
    }

    $conexao->beginTransaction();

    try {
        $consultaAluno = $conexao->prepare(
            'SELECT id_aluno FROM aluno WHERE id_aluno = :aluno FOR UPDATE'
        );
        $consultaAluno->execute(['aluno' => $idAluno]);

        if ($consultaAluno->fetchColumn() === false) {
            $conexao->commit();
            return null;
        }

        $consultaEstadoExistente = $conexao->prepare(
            'SELECT id_participante, id_partida, id_dificuldade,
                    id_questao_atual, vidas_jogador, vidas_guardiao,
                    questoes_respondidas
             FROM guardiao_estado
             WHERE id_participante = :participante
               AND id_partida = :partida'
        );
        $consultaEstadoExistente->execute([
            'participante' => $idParticipante,
            'partida' => $idPartida
        ]);
        $estadoExistente = $consultaEstadoExistente->fetch(PDO::FETCH_ASSOC);

        if ($estadoExistente) {
            $conexao->commit();
            return $estadoExistente;
        }

        $consultaInsere = $conexao->prepare(
            'INSERT INTO guardiao_estado
                (id_participante, id_partida, id_dificuldade, id_questao_atual,
                 vidas_jogador, vidas_guardiao, questoes_respondidas)
             VALUES
                (:participante, :partida, :dificuldade, NULL,
                 :vidas_jogador, :vidas_guardiao, :questoes)'
        );
        $consultaInsere->execute([
            'participante' => $idParticipante,
            'partida' => $idPartida,
            'dificuldade' => $idDificuldadeSessao,
            'vidas_jogador' => $vidasJogadorSessao,
            'vidas_guardiao' => $vidasGuardiaoSessao,
            'questoes' => $questoesSessao
        ]);

        $consultaAtualizaParticipante = $conexao->prepare(
            'UPDATE partida_participante
             SET xp_total = :xp,
                 acertos = :acertos,
                 erros = :erros
             WHERE id_participante = :participante
               AND id_partida = :partida'
        );
        $consultaAtualizaParticipante->execute([
            'xp' => $xpSessao,
            'acertos' => $acertosSessao,
            'erros' => $errosSessao,
            'participante' => $idParticipante,
            'partida' => $idPartida
        ]);

        $conexao->commit();

        return [
            'id_participante' => $idParticipante,
            'id_partida' => $idPartida,
            'id_dificuldade' => $idDificuldadeSessao,
            'id_questao_atual' => null,
            'vidas_jogador' => $vidasJogadorSessao,
            'vidas_guardiao' => $vidasGuardiaoSessao,
            'questoes_respondidas' => $questoesSessao
        ];
    } catch (Throwable $e) {
        if ($conexao->inTransaction()) {
            $conexao->rollBack();
        }
        return null;
    }
};

$infoPartidaAtiva = $buscarPartidaAtiva($conexao, $idAluno, (int) $idModo);
$partidaAtiva = false;

if ($infoPartidaAtiva['estado'] !== null) {
    $partidaAtiva = true;
    $idPartida = (int) $infoPartidaAtiva['partida']['id_partida'];
    $idParticipante = (int) $infoPartidaAtiva['partida']['id_participante'];
    $estado = $infoPartidaAtiva['estado'];
} elseif ($infoPartidaAtiva['sem_estado']) {
    $estadoRecuperado = $importarEstadoDaSessao($conexao, $idAluno, $infoPartidaAtiva['partida']);
    if ($estadoRecuperado !== null) {
        $partidaAtiva = true;
        $idPartida = (int) $infoPartidaAtiva['partida']['id_partida'];
        $idParticipante = (int) $infoPartidaAtiva['partida']['id_participante'];
        $estado = $estadoRecuperado;
    } else {
        $partidaSemEstado = true;
        $partidaLegadaId = (int) $infoPartidaAtiva['partida']['id_partida'];
    }
}

// INICIAR NOVA BATALHA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['iniciar']) && !$partidaAtiva && !$partidaSemEstado) {
    $idDificuldade = filter_input(INPUT_POST, 'id_dificuldade', FILTER_VALIDATE_INT);

    $consultaDificuldade = $conexao->prepare(
        'SELECT id_dificuldade, nome, xp_por_acerto FROM dificuldade WHERE id_dificuldade = :id'
    );
    $consultaDificuldade->execute(['id' => $idDificuldade]);
    $dificuldade = $consultaDificuldade->fetch(PDO::FETCH_ASSOC);

    if (!$dificuldade) {
        $mensagem = 'Selecione uma dificuldade válida.';
        $mensagemTipo = 'erro';
    } else {
        $consultaPartida = $conexao->prepare(
            "INSERT INTO partida (id_jogo, id_modo_jogo, tempo_segundos, status)
             VALUES (1, :modo, 0, 'em_andamento')"
        );
        $consultaPartida->execute(['modo' => (int) $idModo]);
        $idPartida = (int) $conexao->lastInsertId();

        $consultaParticipante = $conexao->prepare(
            'INSERT INTO partida_participante (id_partida, id_aluno, apelido, xp_total, acertos, erros)
             VALUES (:partida, :aluno, :apelido, 0, 0, 0)'
        );
        $consultaParticipante->execute([
            'partida' => $idPartida,
            'aluno' => $idAluno,
            'apelido' => $_SESSION['aluno_nome'] ?? 'Caçador'
        ]);
        $idParticipante = (int) $conexao->lastInsertId();

        $consultaEstado = $conexao->prepare(
            'INSERT INTO guardiao_estado
                (id_participante, id_partida, id_dificuldade, id_questao_atual,
                 vidas_jogador, vidas_guardiao, questoes_respondidas)
             VALUES
                (:participante, :partida, :dificuldade, NULL, 3, 3, 0)'
        );
        $consultaEstado->execute([
            'participante' => $idParticipante,
            'partida' => $idPartida,
            'dificuldade' => (int) $dificuldade['id_dificuldade']
        ]);

        header('Location: ?pagina=guardiao');
        exit;
    }
}

// PROCESSAR RESPOSTA NA BATALHA
if ($partidaAtiva) {
    $consultaResumo = $conexao->prepare(
        'SELECT xp_total, acertos, erros
         FROM partida_participante
         WHERE id_participante = :participante
           AND id_partida = :partida
           AND id_aluno = :aluno'
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['responder'])) {
        $idQuestao = filter_input(INPUT_POST, 'id_questao', FILTER_VALIDATE_INT);
        $idAlternativa = filter_input(INPUT_POST, 'id_alternativa', FILTER_VALIDATE_INT);

        $conexao->beginTransaction();
        try {
            $consultaEstadoBloqueado = $conexao->prepare(
                'SELECT id_dificuldade, id_questao_atual, vidas_jogador,
                        vidas_guardiao, questoes_respondidas
                 FROM guardiao_estado
                 WHERE id_participante = :participante
                   AND id_partida = :partida
                 FOR UPDATE'
            );
            $consultaEstadoBloqueado->execute([
                'participante' => $idParticipante,
                'partida' => $idPartida
            ]);
            $estadoAtual = $consultaEstadoBloqueado->fetch(PDO::FETCH_ASSOC);

            if ($estadoAtual && (int) $estadoAtual['id_questao_atual'] === $idQuestao) {
                $vidasJogadorAtual = (int) $estadoAtual['vidas_jogador'];
                $vidasGuardiaoAtual = (int) $estadoAtual['vidas_guardiao'];
                $questoesAtual = (int) $estadoAtual['questoes_respondidas'];
                $idDificuldade = (int) $estadoAtual['id_dificuldade'];

                $consultaAlt = $conexao->prepare(
                    'SELECT a.correta, q.enunciado, q.explicacao_curta
                     FROM alternativa a
                     JOIN questao q ON q.id_questao = a.id_questao
                     WHERE a.id_alternativa = :alternativa
                       AND a.id_questao = :questao
                       AND q.id_jogo = 1
                       AND q.id_dificuldade = :dificuldade
                       AND q.ativo = 1
                     LIMIT 1'
                );
                $consultaAlt->execute([
                    'alternativa' => $idAlternativa,
                    'questao' => $idQuestao,
                    'dificuldade' => $idDificuldade
                ]);
                $dadosResp = $consultaAlt->fetch(PDO::FETCH_ASSOC);

                if ($dadosResp) {
                    $acertouResposta = ((int) $dadosResp['correta'] === 1);
                    $xpGanho = 0;

                    if ($acertouResposta) {
                        $consultaXP = $conexao->prepare(
                            'SELECT xp_por_acerto FROM dificuldade WHERE id_dificuldade = :dificuldade'
                        );
                        $consultaXP->execute(['dificuldade' => $idDificuldade]);
                        $xpGanho = (int) $consultaXP->fetchColumn();
                    }

                    $novasVidasJogador = $acertouResposta ? $vidasJogadorAtual : max(0, $vidasJogadorAtual - 1);
                    $novasVidasGuardiao = $acertouResposta ? max(0, $vidasGuardiaoAtual - 1) : $vidasGuardiaoAtual;
                    $novoTotalQuestoes = $questoesAtual + 1;
                    $fimResposta = ($novasVidasJogador <= 0 || $novoTotalQuestoes >= 10);

                    // Registra resposta
                    $consultaInsereResp = $conexao->prepare(
                        'INSERT INTO resposta (id_participante, id_questao, id_alternativa, acertou, xp_ganho)
                         VALUES (:participante, :questao, :alternativa, :acertou, :xp)'
                    );
                    $consultaInsereResp->execute([
                        'participante' => $idParticipante,
                        'questao' => $idQuestao,
                        'alternativa' => $idAlternativa,
                        'acertou' => $acertouResposta ? 1 : 0,
                        'xp' => $xpGanho
                    ]);

                    // Atualiza participante
                    $consultaAtualizaPart = $conexao->prepare(
                        'UPDATE partida_participante
                         SET xp_total = xp_total + :xp,
                             acertos = acertos + :acertos,
                             erros = erros + :erros
                         WHERE id_participante = :participante'
                    );
                    $consultaAtualizaPart->execute([
                        'xp' => $xpGanho,
                        'acertos' => $acertouResposta ? 1 : 0,
                        'erros' => $acertouResposta ? 0 : 1,
                        'participante' => $idParticipante
                    ]);

                    // Sorteia próxima questão
                    $idProximaQuestao = null;
                    if (!$fimResposta) {
                        $consultaProxima = $conexao->prepare(
                            'SELECT q.id_questao
                             FROM questao q
                             WHERE q.id_jogo = 1
                               AND q.id_dificuldade = :dificuldade
                               AND q.ativo = 1
                               AND NOT EXISTS (
                                   SELECT 1 FROM resposta r
                                   WHERE r.id_participante = :participante
                                     AND r.id_questao = q.id_questao
                               )
                               AND EXISTS (
                                   SELECT 1 FROM alternativa a WHERE a.id_questao = q.id_questao
                               )
                             ORDER BY RAND()
                             LIMIT 1'
                        );
                        $consultaProxima->execute([
                            'dificuldade' => $idDificuldade,
                            'participante' => $idParticipante
                        ]);
                        $proxima = $consultaProxima->fetchColumn();
                        if ($proxima !== false) {
                            $idProximaQuestao = (int) $proxima;
                        }
                    }

                    $consultaAtualizaEstado = $conexao->prepare(
                        'UPDATE guardiao_estado
                         SET id_questao_atual = :questao,
                             vidas_jogador = :vidas_jogador,
                             vidas_guardiao = :vidas_guardiao,
                             questoes_respondidas = :questoes
                         WHERE id_participante = :participante
                           AND id_partida = :partida'
                    );
                    $consultaAtualizaEstado->execute([
                        'questao' => $idProximaQuestao,
                        'vidas_jogador' => $novasVidasJogador,
                        'vidas_guardiao' => $novasVidasGuardiao,
                        'questoes' => $novoTotalQuestoes,
                        'participante' => $idParticipante,
                        'partida' => $idPartida
                    ]);

                    if ($fimResposta) {
                        $consultaFinaliza = $conexao->prepare(
                            "UPDATE partida SET status = 'finalizada', data_fim = NOW()
                             WHERE id_partida = :partida AND status = 'em_andamento'"
                        );
                        $consultaFinaliza->execute(['partida' => $idPartida]);
                    }

                    $conexao->commit();

                    $dicaBico = gerarDicaBico($dadosResp['enunciado'], $dadosResp['explicacao_curta'] ?? '');

                    if ($acertouResposta) {
                        $_SESSION['guardiao_mensagem'] = "✨ Golpe certeiro! O Guardião foi atingido e você ganhou {$xpGanho} XP!";
                        $_SESSION['guardiao_mensagem_tipo'] = 'acerto';
                        $_SESSION['guardiao_explicacao'] = $dadosResp['explicacao_curta'] ?? '';
                    } else {
                        $_SESSION['guardiao_mensagem'] = "🛡️ O Guardião bloqueou seu ataque e você perdeu 1 coração! Mas o Bicó te ensina o cálculo:";
                        $_SESSION['guardiao_mensagem_tipo'] = 'erro';
                        $_SESSION['guardiao_explicacao'] = $dicaBico;
                    }
                }
            }
        } catch (Throwable $e) {
            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }
            throw $e;
        }

        header('Location: ?pagina=guardiao');
        exit;
    }

    $consultaEstadoAtual = $conexao->prepare(
        'SELECT id_dificuldade, id_questao_atual, vidas_jogador,
                vidas_guardiao, questoes_respondidas
         FROM guardiao_estado
         WHERE id_participante = :participante
           AND id_partida = :partida'
    );
    $consultaEstadoAtual->execute([
        'participante' => $idParticipante,
        'partida' => $idPartida
    ]);
    $estado = $consultaEstadoAtual->fetch(PDO::FETCH_ASSOC);

    $vidasJogador = (int) $estado['vidas_jogador'];
    $vidasGuardiao = (int) $estado['vidas_guardiao'];
    $totalQuestoes = (int) $estado['questoes_respondidas'];
    $fimDeJogo = ($vidasJogador <= 0 || $totalQuestoes >= 10);

    if (!$fimDeJogo && $estado['id_questao_atual'] === null) {
        $conexao->beginTransaction();
        try {
            $consultaQAtual = $conexao->prepare(
                'SELECT q.id_questao
                 FROM questao q
                 WHERE q.id_jogo = 1
                   AND q.id_dificuldade = :dificuldade
                   AND q.ativo = 1
                   AND NOT EXISTS (
                       SELECT 1 FROM resposta r
                       WHERE r.id_participante = :participante AND r.id_questao = q.id_questao
                   )
                   AND EXISTS (
                       SELECT 1 FROM alternativa a WHERE a.id_questao = q.id_questao
                   )
                 ORDER BY RAND()
                 LIMIT 1'
            );
            $consultaQAtual->execute([
                'dificuldade' => (int) $estado['id_dificuldade'],
                'participante' => $idParticipante
            ]);
            $idQNova = $consultaQAtual->fetchColumn();

            if ($idQNova !== false) {
                $consultaSalvaQ = $conexao->prepare(
                    'UPDATE guardiao_estado SET id_questao_atual = :questao WHERE id_participante = :participante AND id_partida = :partida'
                );
                $consultaSalvaQ->execute([
                    'questao' => (int) $idQNova,
                    'participante' => $idParticipante,
                    'partida' => $idPartida
                ]);
            }
            $conexao->commit();
        } catch (Throwable $e) {
            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }
            throw $e;
        }

        $consultaEstadoAtual->execute([
            'participante' => $idParticipante,
            'partida' => $idPartida
        ]);
        $estado = $consultaEstadoAtual->fetch(PDO::FETCH_ASSOC);
    }

    if ($estado['id_questao_atual'] !== null && !$fimDeJogo) {
        $consultaEnunciado = $conexao->prepare(
            'SELECT enunciado, explicacao_curta FROM questao WHERE id_questao = :questao AND id_jogo = 1 AND ativo = 1'
        );
        $consultaEnunciado->execute(['questao' => (int) $estado['id_questao_atual']]);
        $dadosQ = $consultaEnunciado->fetch(PDO::FETCH_ASSOC);

        if ($dadosQ) {
            $questao = [
                'id_questao' => (int) $estado['id_questao_atual'],
                'enunciado' => $dadosQ['enunciado'],
                'explicacao_curta' => $dadosQ['explicacao_curta'] ?? ''
            ];

            $consultaAlternativas = $conexao->prepare(
                'SELECT id_alternativa, texto FROM alternativa WHERE id_questao = :questao ORDER BY id_alternativa'
            );
            $consultaAlternativas->execute(['questao' => (int) $estado['id_questao_atual']]);
            $alternativas = $consultaAlternativas->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    $consultaResumo->execute([
        'participante' => $idParticipante,
        'partida' => $idPartida,
        'aluno' => $idAluno
    ]);
    $resumo = $consultaResumo->fetch(PDO::FETCH_ASSOC);
    $xpTotal = (int) $resumo['xp_total'];
    $acertos = (int) $resumo['acertos'];
    $erros = (int) $resumo['erros'];

    if ($fimDeJogo) {
        if ($vidasJogador <= 0) {
            $resultado = 'Fim da batalha! O Guardião foi forte, mas você lutou com coragem e aprendeu muito com o Bicó!';
        } elseif ($acertos > $erros) {
            $resultado = '🏆 Vitória Gloriosa! Você dominou a matemática e superou o Guardião da Floresta!';
        } elseif ($erros > $acertos) {
            $resultado = '🛡️ Fim da batalha! O Guardião resistiu desta vez, mas o reino agradece sua determinação!';
        } else {
            $resultado = '🤝 Empate Heróico! Você e o Guardião mostraram perfeito equilíbrio matemático!';
        }
    }
}
?>

<main class="container">
    <section class="hero">
        <span class="badge">Batalha na Floresta · Modo Guardião</span>
        <h2>Contra o Guardião da Floresta</h2>
        <p>Enfrente a entidade ancestral da floresta num duelo épico de matemática!</p>

        <!-- FEEDBACK DO BICÓ -->
        <?php if ($mensagem !== ''): ?>
            <?= renderBicoFeedbackHtml($mensagem, $mensagemTipo, $explicacao) ?>
        <?php endif; ?>

        <?php if ($partidaSemEstado): ?>
            <p>
                Há uma partida antiga em andamento sem estado recuperável.
                Ela não foi substituída; conclua ou abandone essa partida antes de iniciar outra.
            </p>
            <a href="?pagina=abandonar_guardiao&id_partida=<?= $partidaLegadaId ?>" class="btn btn-secondary">
                Abandonar partida antiga
            </a>

        <?php elseif (!$partidaAtiva): ?>
            <article class="card">
                <h3>Escolha o nível do Guardião</h3>
                <p>O Guardião da Floresta testará sua precisão e velocidade em contas de adição e subtração.</p>

                <?php
                $consultaDificuldades = $conexao->query('SELECT id_dificuldade, nome, xp_por_acerto FROM dificuldade ORDER BY id_dificuldade');
                $dificuldades = $consultaDificuldades->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <form method="POST">
                    <div style="margin-bottom: 20px;">
                        <label for="id_dificuldade">
                            <strong>Dificuldade da Batalha:</strong><br>
                            <select name="id_dificuldade" id="id_dificuldade" required>
                                <option value="">Selecione o nível</option>
                                <?php foreach ($dificuldades as $dificuldade): ?>
                                    <option value="<?= (int) $dificuldade['id_dificuldade'] ?>">
                                        <?= htmlspecialchars($dificuldade['nome'], ENT_QUOTES, 'UTF-8') ?> (<?= (int) $dificuldade['xp_por_acerto'] ?> XP por acerto)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>

                    <button type="submit" name="iniciar" value="1" class="btn">
                        ⚔️ Iniciar Batalha Épica
                    </button>
                </form>
            </article>

            <p style="margin-top: 24px;">
                <a href="?pagina=batalha">Voltar aos modos de Batalha</a>
            </p>

        <?php elseif ($fimDeJogo): ?>

            <article class="card resultado-card">
                <div class="resultado-vencedor-destaque">
                    <?php if ($vidasJogador > 0 && $acertos >= $erros): ?>
                        <span class="pixel-avatar-grande avatar-cacador" aria-hidden="true"></span>
                    <?php else: ?>
                        <span class="pixel-avatar-grande avatar-guardiao" aria-hidden="true"></span>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($resultado ?? 'Batalha encerrada', ENT_QUOTES, 'UTF-8') ?></h3>
                </div>

                <div class="grid" style="margin-top: 20px;">
                    <article class="card">
                        <h4>Desafios Enfrentados</h4>
                        <p class="destaque-numero"><?= $totalQuestoes ?> de 10</p>
                    </article>

                    <article class="card">
                        <h4>Golpes Certos (Acertos)</h4>
                        <p class="destaque-numero" style="color: var(--cor-acerto) !important;">✅ <?= $acertos ?></p>
                    </article>

                    <article class="card">
                        <h4>Bloqueios (Erros)</h4>
                        <p class="destaque-numero" style="color: var(--cor-erro) !important;">❌ <?= $erros ?></p>
                    </article>

                    <article class="card">
                        <h4>XP Total Conquistado</h4>
                        <p class="destaque-numero" style="color: var(--cor-ouro-escuro) !important;">⭐ <?= $xpTotal ?> XP</p>
                    </article>
                </div>

                <div style="margin-top: 24px;">
                    <a href="?pagina=guardiao&novo=1" class="btn">⚔️ Desafiar novamente</a>
                    <a href="?pagina=batalha" class="btn btn-secondary">Voltar aos modos</a>
                </div>
            </article>

        <?php else: ?>

            <!-- ARENA DE BATALHA COM INFORMAÇÕES DE VIDA E DESEMPENHO MUITO MAIORES E VISÍVEIS -->
            <div class="arena-batalha-container card">
                <div class="arena-duelo-grid">
                    
                    <!-- LADO DO JOGADOR (CAÇADOR) -->
                    <div class="lutador-card lutador-jogador">
                        <div class="lutador-avatar-wrap">
                            <span class="pixel-avatar-luta avatar-cacador" aria-hidden="true"></span>
                        </div>
                        <div class="lutador-info">
                            <span class="badge-lutador">HERÓI DO REINO</span>
                            <h4 class="lutador-nome"><?= htmlspecialchars($_SESSION['aluno_nome'] ?? 'Caçador', ENT_QUOTES, 'UTF-8') ?></h4>
                            
                            <!-- CORAÇÕES DE VIDA GRANDES EM PIXEL ART -->
                            <div class="vidas-container-grande" aria-label="Vidas do Jogador: <?= $vidasJogador ?> de 3">
                                <?php for ($i = 1; $i <= 3; $i++): ?>
                                    <span class="coracao-pixel-hud <?= ($i <= $vidasJogador) ? 'coracao-cheio' : 'coracao-vazio' ?>" aria-hidden="true"></span>
                                <?php endfor; ?>
                                <span class="texto-vidas-hud"><?= $vidasJogador ?>/3 VIDAS</span>
                            </div>
                        </div>
                    </div>

                    <!-- CENTRO: VERSUS E RODADA -->
                    <div class="arena-vs-centro">
                        <div class="emblema-vs">VS</div>
                        <div class="rodada-badge">RODADA <?= min(10, $totalQuestoes + 1) ?>/10</div>
                    </div>

                    <!-- LADO DO GUARDIÃO -->
                    <div class="lutador-card lutador-guardiao">
                        <div class="lutador-avatar-wrap">
                            <span class="pixel-avatar-luta avatar-guardiao" aria-hidden="true"></span>
                        </div>
                        <div class="lutador-info">
                            <span class="badge-lutador badge-roxo">ANCESTRAL</span>
                            <h4 class="lutador-nome">Guardião da Floresta</h4>
                            
                            <!-- ESCUDOS MÍSTICOS GRANDES EM PIXEL ART -->
                            <div class="vidas-container-grande" aria-label="Escudos do Guardião: <?= $vidasGuardiao ?> de 3">
                                <?php for ($i = 1; $i <= 3; $i++): ?>
                                    <span class="escudo-pixel-hud <?= ($i <= $vidasGuardiao) ? 'escudo-cheio' : 'escudo-vazio' ?>" aria-hidden="true"></span>
                                <?php endfor; ?>
                                <span class="texto-vidas-hud"><?= $vidasGuardiao ?>/3 ESCUDOS</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- PAINEL DE DESEMPENHO AMPLIADO -->
                <div class="arena-desempenho-painel">
                    <div class="arena-stats-grid">
                        <div class="stat-box">
                            <span class="stat-rotulo">Desafio Atual</span>
                            <strong class="stat-valor"><?= min(10, $totalQuestoes + 1) ?> de 10</strong>
                        </div>
                        <div class="stat-box">
                            <span class="stat-rotulo">Acertos Certeiros</span>
                            <strong class="stat-valor stat-acerto">✅ <?= $acertos ?></strong>
                        </div>
                        <div class="stat-box">
                            <span class="stat-rotulo">Bloqueios / Erros</span>
                            <strong class="stat-valor stat-erro">❌ <?= $erros ?></strong>
                        </div>
                        <div class="stat-box">
                            <span class="stat-rotulo">XP Acumulado</span>
                            <strong class="stat-valor stat-ouro">⭐ <?= $xpTotal ?> XP</strong>
                        </div>
                    </div>
                    <div class="barra-batalha-wrap">
                        <span class="barra-rotulo">Progresso da Batalha:</span>
                        <progress value="<?= $totalQuestoes ?>" max="10" aria-label="Progresso da Batalha contra o Guardião"></progress>
                    </div>
                </div>
            </div>

            <!-- CARD DA QUESTÃO ATIVA -->
            <?php if ($questao): ?>
                <?php
                $dicaGuardiao = gerarDicaBico($questao['enunciado'], $questao['explicacao_curta'] ?? '');
                ?>
                <article class="card" style="margin-top: 24px;">
                    <span class="badge">Ataque Matemático</span>
                    <h3 style="font-size: 1.45rem; margin: 14px 0;">
                        <?= htmlspecialchars($questao['enunciado'], ENT_QUOTES, 'UTF-8') ?>
                    </h3>

                    <!-- DICA DO BICÓ NO GUARDIÃO -->
                    <?= renderBicoDicaBotaoHtml($dicaGuardiao) ?>

                    <?php if ($alternativas): ?>
                        <form method="POST" style="margin-top: 18px;">
                            <input type="hidden" name="id_questao" value="<?= (int) $questao['id_questao'] ?>">

                            <fieldset>
                                <legend class="sr-only">Escolha sua resposta de ataque</legend>
                                <?php foreach ($alternativas as $alternativa): ?>
                                    <label>
                                        <input
                                            type="radio"
                                            name="id_alternativa"
                                            value="<?= (int) $alternativa['id_alternativa'] ?>"
                                            required>
                                        <strong><?= htmlspecialchars($alternativa['texto'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    </label>
                                    <br>
                                <?php endforeach; ?>
                            </fieldset>

                            <div style="margin-top: 18px;">
                                <button type="submit" name="responder" value="1" class="btn">
                                    ⚔️ Desferir Golpe Matemático
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <p>Esta questão não possui alternativas cadastradas.</p>
                    <?php endif; ?>
                </article>
            <?php else: ?>
                <p>Não há uma questão disponível para continuar. Seu progresso foi preservado.</p>
            <?php endif; ?>

            <p style="margin-top: 24px;">
                <a href="?pagina=abandonar_guardiao&id_partida=<?= $idPartida ?>" class="btn btn-secondary" onclick="return confirm('Tem certeza que deseja abandonar esta batalha contra o Guardião?');">
                    🏳️ Abandonar batalha e voltar aos modos
                </a>
            </p>

        <?php endif; ?>
    </section>
</main>
```

---

## Arquivo 6: `app/views/pages/pocoes.php`

```php
<?php

require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../helpers/bicoHelper.php';

if (empty($_SESSION['aluno_id'])) {
    header('Location: ?pagina=login');
    exit;
}

$idAluno = (int) $_SESSION['aluno_id'];
$mensagem = '';
$mensagemTipo = '';
$explicacao = '';
$resultado = null;

// ============================================
// 1. REINICIAR / LIMPAR SE SOLICITADO
// ============================================
if (isset($_GET['novo'])) {
    unset(
        $_SESSION['pocoes_partida_id'],
        $_SESSION['pocoes_participante_id'],
        $_SESSION['pocoes_dificuldade'],
        $_SESSION['pocoes_questoes'],
        $_SESSION['pocoes_xp'],
        $_SESSION['pocoes_acertos'],
        $_SESSION['pocoes_erros'],
        $_SESSION['pocoes_feedback']
    );
    header('Location: ?pagina=pocoes');
    exit;
}

// ============================================
// 2. VALIDAR SE PARTIDA ATIVA CONTINUA EM ANDAMENTO NO BANCO
// ============================================
if (!empty($_SESSION['pocoes_partida_id'])) {
    $consultaPartidaValida = $conexao->prepare(
        "SELECT status
         FROM partida
         WHERE id_partida = :partida
           AND EXISTS (
               SELECT 1
               FROM partida_participante
               WHERE partida_participante.id_partida = partida.id_partida
                 AND partida_participante.id_aluno = :aluno
           )
         LIMIT 1"
    );
    $consultaPartidaValida->execute([
        'partida' => (int) $_SESSION['pocoes_partida_id'],
        'aluno' => $idAluno
    ]);
    $statusAtual = $consultaPartidaValida->fetchColumn();

    if ($statusAtual !== 'em_andamento') {
        unset(
            $_SESSION['pocoes_partida_id'],
            $_SESSION['pocoes_participante_id'],
            $_SESSION['pocoes_dificuldade'],
            $_SESSION['pocoes_questoes'],
            $_SESSION['pocoes_xp'],
            $_SESSION['pocoes_acertos'],
            $_SESSION['pocoes_erros'],
            $_SESSION['pocoes_feedback']
        );
    }
}

// ============================================
// 3. INICIAR UMA NOVA PARTIDA DE POÇÕES
// ============================================
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['iniciar_pocoes'])
) {
    $idDificuldade = filter_input(
        INPUT_POST,
        'id_dificuldade',
        FILTER_VALIDATE_INT
    );

    $consultaDificuldade = $conexao->prepare(
        'SELECT id_dificuldade, nome, xp_por_acerto
         FROM dificuldade
         WHERE id_dificuldade = :id'
    );
    $consultaDificuldade->execute(['id' => $idDificuldade]);
    $dificuldade = $consultaDificuldade->fetch(PDO::FETCH_ASSOC);

    if (!$dificuldade) {
        $mensagem = 'Selecione uma dificuldade válida.';
        $mensagemTipo = 'erro';
    } else {
        // Jogo 2: Poções Mágicas, Modo 1: Desafio / Individual
        $consultaPartida = $conexao->prepare(
            "INSERT INTO partida (id_jogo, id_modo_jogo, tempo_segundos, status)
             VALUES (2, 1, 0, 'em_andamento')"
        );
        $consultaPartida->execute();
        $idPartida = (int) $conexao->lastInsertId();

        $nomeAluno = $_SESSION['aluno_nome'] ?? 'Aventureiro';

        $consultaParticipante = $conexao->prepare(
            'INSERT INTO partida_participante (id_partida, id_aluno, apelido, xp_total, acertos, erros)
             VALUES (:partida, :aluno, :apelido, 0, 0, 0)'
        );
        $consultaParticipante->execute([
            'partida' => $idPartida,
            'aluno' => $idAluno,
            'apelido' => $nomeAluno
        ]);
        $idParticipante = (int) $conexao->lastInsertId();

        $_SESSION['pocoes_partida_id'] = $idPartida;
        $_SESSION['pocoes_participante_id'] = $idParticipante;
        $_SESSION['pocoes_dificuldade'] = (int) $dificuldade['id_dificuldade'];
        $_SESSION['pocoes_questoes'] = 0;
        $_SESSION['pocoes_xp'] = 0;
        $_SESSION['pocoes_acertos'] = 0;
        $_SESSION['pocoes_erros'] = 0;
        unset($_SESSION['pocoes_feedback']);

        header('Location: ?pagina=pocoes');
        exit;
    }
}

// ============================================
// 4. ESTADO DA PARTIDA
// ============================================
$partidaAtiva = !empty($_SESSION['pocoes_partida_id']);
$fimDeJogo = false;

if ($partidaAtiva) {
    $idPartida = (int) $_SESSION['pocoes_partida_id'];
    $idParticipante = (int) $_SESSION['pocoes_participante_id'];
    $idDificuldade = (int) $_SESSION['pocoes_dificuldade'];
    $totalQuestoes = (int) $_SESSION['pocoes_questoes'];

    $fimDeJogo = ($totalQuestoes >= 10);

    // ============================================
    // 5. PROCESSAR RESPOSTA
    // ============================================
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['responder_pocao']) &&
        !$fimDeJogo
    ) {
        $idQuestao = filter_input(INPUT_POST, 'id_questao', FILTER_VALIDATE_INT);
        $idAlternativa = filter_input(INPUT_POST, 'id_alternativa', FILTER_VALIDATE_INT);

        $consultaQuestao = $conexao->prepare(
            'SELECT id_questao, enunciado, explicacao_curta
             FROM questao
             WHERE id_questao = :questao
               AND id_jogo = 2
               AND id_dificuldade = :dificuldade
               AND ativo = 1
               AND id_questao IN (
                   SELECT id_questao
                   FROM alternativa
                   WHERE id_alternativa = :alternativa
               )'
        );
        $consultaQuestao->execute([
            'questao' => $idQuestao,
            'dificuldade' => $idDificuldade,
            'alternativa' => $idAlternativa
        ]);
        $dadosQuestao = $consultaQuestao->fetch(PDO::FETCH_ASSOC);

        if ($dadosQuestao) {
            $consultaCorreta = $conexao->prepare(
                'SELECT correta
                 FROM alternativa
                 WHERE id_alternativa = :alternativa
                   AND id_questao = :questao'
            );
            $consultaCorreta->execute([
                'alternativa' => $idAlternativa,
                'questao' => $idQuestao
            ]);
            $correta = $consultaCorreta->fetchColumn();

            if ($correta !== false) {
                $acertou = ((int) $correta === 1);
                $xpGanho = 0;

                if ($acertou) {
                    $consultaXP = $conexao->prepare(
                        'SELECT xp_por_acerto FROM dificuldade WHERE id_dificuldade = :dificuldade'
                    );
                    $consultaXP->execute(['dificuldade' => $idDificuldade]);
                    $xpGanho = (int) $consultaXP->fetchColumn();
                }

                $consultaRegistro = $conexao->prepare(
                    'INSERT INTO resposta (id_participante, id_questao, id_alternativa, acertou, xp_ganho)
                     VALUES (:participante, :questao, :alternativa, :acertou, :xp)'
                );
                $consultaRegistro->execute([
                    'participante' => $idParticipante,
                    'questao' => $idQuestao,
                    'alternativa' => $idAlternativa,
                    'acertou' => $acertou ? 1 : 0,
                    'xp' => $xpGanho
                ]);

                $consultaAtualiza = $conexao->prepare(
                    'UPDATE partida_participante
                     SET xp_total = xp_total + :xp,
                         acertos = acertos + :acertos,
                         erros = erros + :erros
                     WHERE id_participante = :participante'
                );
                $consultaAtualiza->execute([
                    'xp' => $xpGanho,
                    'acertos' => $acertou ? 1 : 0,
                    'erros' => $acertou ? 0 : 1,
                    'participante' => $idParticipante
                ]);

                $_SESSION['pocoes_questoes']++;
                if ($acertou) {
                    $_SESSION['pocoes_acertos']++;
                    $_SESSION['pocoes_xp'] += $xpGanho;
                    $feedbackMsg = "✨ Poção preparada com perfeição alquímica! Você conquistou {$xpGanho} XP!";
                    $feedbackTipo = 'acerto';
                } else {
                    $_SESSION['pocoes_erros']++;
                    $feedbackMsg = "🧪 A mistura borbulhou e escapou! Mas errar é praticar: o Bicó te mostra a receita certa:";
                    $feedbackTipo = 'erro';
                }

                $dicaPocao = gerarDicaBico($dadosQuestao['enunciado'], $dadosQuestao['explicacao_curta'] ?? '');

                $_SESSION['pocoes_feedback'] = [
                    'tipo' => $feedbackTipo,
                    'texto' => $feedbackMsg,
                    'explicacao' => $dicaPocao
                ];

                $novoTotalQuestoes = (int) $_SESSION['pocoes_questoes'];
                if ($novoTotalQuestoes >= 10) {
                    $consultaFim = $conexao->prepare(
                        "UPDATE partida
                         SET status = 'finalizada',
                             data_fim = NOW()
                         WHERE id_partida = :partida
                           AND status = 'em_andamento'"
                    );
                    $consultaFim->execute(['partida' => $idPartida]);
                }

                header('Location: ?pagina=pocoes');
                exit;
            }
        }
    }

    $totalQuestoes = (int) $_SESSION['pocoes_questoes'];
    $fimDeJogo = ($totalQuestoes >= 10);

    if (!empty($_SESSION['pocoes_feedback'])) {
        $mensagem = $_SESSION['pocoes_feedback']['texto'];
        $mensagemTipo = $_SESSION['pocoes_feedback']['tipo'];
        $explicacao = $_SESSION['pocoes_feedback']['explicacao'];
        unset($_SESSION['pocoes_feedback']);
    }

    $questao = null;
    $alternativas = [];

    if (!$fimDeJogo) {
        $consultaQ = $conexao->prepare(
            'SELECT id_questao, enunciado, explicacao_curta, mecanica
             FROM questao
             WHERE id_jogo = 2
               AND id_dificuldade = :dificuldade
               AND ativo = 1
               AND id_questao NOT IN (
                   SELECT id_questao
                   FROM resposta
                   WHERE id_participante = :participante
               )
             ORDER BY RAND()
             LIMIT 1'
        );
        $consultaQ->execute([
            'dificuldade' => $idDificuldade,
            'participante' => $idParticipante
        ]);
        $questao = $consultaQ->fetch(PDO::FETCH_ASSOC);

        if ($questao) {
            $consultaAlt = $conexao->prepare(
                'SELECT id_alternativa, texto
                 FROM alternativa
                 WHERE id_questao = :questao
                 ORDER BY id_alternativa'
            );
            $consultaAlt->execute(['questao' => (int) $questao['id_questao']]);
            $alternativas = $consultaAlt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $fimDeJogo = true;
            $consultaFim = $conexao->prepare(
                "UPDATE partida
                 SET status = 'finalizada',
                     data_fim = NOW()
                 WHERE id_partida = :partida
                   AND status = 'em_andamento'"
            );
            $consultaFim->execute(['partida' => $idPartida]);
        }
    }
}

/**
 * Função para analisar o enunciado e extrair os dados da Receita da Poção
 */
function extrairDadosReceita(string $enunciado, string $mecanica): array
{
    // Caso 1: Identificação de fração
    if (preg_match('/dividida em (\d+) partes.*?(\d+) partes receberam ess[êe]ncia ([\w\s]+?)\./ui', $enunciado, $m)) {
        $totalPartes = (int) $m[1];
        $partesUsadas = (int) $m[2];
        $ingrediente = trim($m[3]);

        return [
            'tipo' => 'identificacao',
            'nome_pocao' => 'Elixir de ' . ucwords($ingrediente),
            'ingrediente_raro' => ucwords($ingrediente),
            'partes_total' => $totalPartes,
            'partes_usadas' => $partesUsadas,
            'partes_restantes' => max(0, $totalPartes - $partesUsadas)
        ];
    }

    // Caso 2: Comparação de frações
    if (preg_match('/(Poção [\w\s]+?) contém (\d+\/\d+).*?(Poção [\w\s]+?) contém (\d+\/\d+)/ui', $enunciado, $m)) {
        return [
            'tipo' => 'comparacao',
            'pocao1_nome' => trim($m[1]),
            'pocao1_fracao' => trim($m[2]),
            'pocao2_nome' => trim($m[3]),
            'pocao2_fracao' => trim($m[4])
        ];
    }

    // Padrão genérico de fallback
    return [
        'tipo' => 'identificacao',
        'nome_pocao' => 'Filtro Alquímico de Numéria',
        'ingrediente_raro' => 'Essência Lunar Encantada',
        'partes_total' => 6,
        'partes_usadas' => 2,
        'partes_restantes' => 4
    ];
}
?>

<main class="container">
    <section class="hero">
        <span class="badge">Laboratório Alquímico de Numéria</span>
        <h2>Laboratório de Poções Mágicas</h2>
        <p>Aprenda frações preparando receitas mágicas no caldeirão do <strong>Tucano Bicó</strong>!</p>

        <!-- FEEDBACK DO BICÓ -->
        <?php if ($mensagem !== ''): ?>
            <?= renderBicoFeedbackHtml($mensagem, $mensagemTipo, $explicacao) ?>
        <?php endif; ?>

        <?php if (!$partidaAtiva): ?>

            <article class="card">
                <h3>Escolha o nível do seu Grimório de Poções</h3>
                <p>
                    No laboratório de Numéria, cada receita exige frações exatas de ingredientes raros
                    como essência lunar, lágrimas de dragão e seiva de folha mágica.
                </p>

                <?php
                $consultaDifs = $conexao->query(
                    'SELECT id_dificuldade, nome, xp_por_acerto
                     FROM dificuldade
                     ORDER BY id_dificuldade'
                );
                $dificuldades = $consultaDifs->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <form method="POST">
                    <div style="margin-bottom: 20px;">
                        <label for="id_dificuldade">
                            <strong>Dificuldade do Grimório:</strong><br>
                            <select name="id_dificuldade" id="id_dificuldade" required>
                                <option value="">Selecione uma dificuldade</option>
                                <?php foreach ($dificuldades as $dif): ?>
                                    <option value="<?= (int) $dif['id_dificuldade'] ?>">
                                        <?= htmlspecialchars($dif['nome'], ENT_QUOTES, 'UTF-8') ?> — <?= (int) $dif['xp_por_acerto'] ?> XP por poção correta
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>

                    <button type="submit" name="iniciar_pocoes" value="1" class="btn">
                        🧪 Abrir Grimório e Entrar no Laboratório
                    </button>
                </form>
            </article>

            <p style="margin-top: 24px;">
                <a href="?pagina=home#jogos">Voltar ao catálogo de jogos</a>
            </p>

        <?php elseif ($fimDeJogo): ?>

            <article class="card resultado-card">
                <h3>🎉 Jornada de Alquimia Concluída!</h3>
                <p>O Mestre Alquimista Bicó agradece sua ajuda na preparação das poções do reino!</p>

                <div class="grid" style="margin-top: 20px;">
                    <article class="card">
                        <h4>Poções Preparadas</h4>
                        <p class="destaque-numero"><?= (int) $_SESSION['pocoes_questoes'] ?> de 10</p>
                    </article>

                    <article class="card">
                        <h4>Poções Perfeitas</h4>
                        <p class="destaque-numero" style="color: var(--cor-acerto) !important;">✅ <?= (int) $_SESSION['pocoes_acertos'] ?></p>
                    </article>

                    <article class="card">
                        <h4>Misturas Instáveis</h4>
                        <p class="destaque-numero" style="color: var(--cor-erro) !important;">⚠️ <?= (int) $_SESSION['pocoes_erros'] ?></p>
                    </article>

                    <article class="card">
                        <h4>XP Total Conquistado</h4>
                        <p class="destaque-numero" style="color: var(--cor-ouro-escuro) !important;">⭐ <?= (int) $_SESSION['pocoes_xp'] ?> XP</p>
                    </article>
                </div>

                <div style="margin-top: 24px;">
                    <a href="?pagina=pocoes&novo=1" class="btn">🧪 Preparar mais poções</a>
                    <a href="?pagina=abandonar_pocoes" class="btn btn-secondary">Voltar aos jogos</a>
                </div>
            </article>

        <?php else: ?>

            <div class="painel-status" style="margin-bottom: 20px;">
                <p>
                    Receita Atual: <strong><?= $totalQuestoes + 1 ?> de 10</strong> |
                    Poções Perfeitas: <strong><?= (int) $_SESSION['pocoes_acertos'] ?></strong> |
                    XP Acumulado: <strong>⭐ <?= (int) $_SESSION['pocoes_xp'] ?> XP</strong>
                </p>
                <progress value="<?= $totalQuestoes ?>" max="10" style="width: 100%;" aria-label="Progresso das receitas"></progress>
            </div>

            <?php if ($questao): ?>
                <?php
                $dadosReceita = extrairDadosReceita($questao['enunciado'], $questao['mecanica'] ?? '');
                $dicaPocao = gerarDicaBico($questao['enunciado'], $questao['explicacao_curta'] ?? '');
                ?>

                <!-- LITERALMENTE O PERGAMINHO DE RECEITA DA POÇÃO -->
                <article class="card receita-pergaminho-card">
                    <div class="receita-cabecalho">
                        <span class="receita-selo-bico" aria-hidden="true">📜</span>
                        <div>
                            <span class="badge">GRIMÓRIO DO BICÓ · RECEITA #<?= $totalQuestoes + 1 ?></span>
                            <h3 class="receita-nome">
                                <?= htmlspecialchars($dadosReceita['nome_pocao'] ?? 'Receita Arcana', ENT_QUOTES, 'UTF-8') ?>
                            </h3>
                        </div>
                    </div>

                    <?php if ($dadosReceita['tipo'] === 'identificacao'): ?>
                        
                        <!-- TABELA DE INGREDIENTES E VISUAL DO FRASCO -->
                        <div class="receita-corpo-grid">
                            
                            <!-- Ingredientes em formato de lista alquímica -->
                            <div class="receita-ingredientes">
                                <h4>📋 Ingredientes & Proporções:</h4>
                                <ul class="ingredientes-lista">
                                    <li>
                                        <strong>Frasco Alquímico:</strong>
                                        Graduado em <strong><?= $dadosReceita['partes_total'] ?> partes iguais</strong> (o todo).
                                    </li>
                                    <li>
                                        <strong>Ingrediente Raro:</strong>
                                        <strong><?= $dadosReceita['partes_usadas'] ?> partes</strong> de <?= htmlspecialchars($dadosReceita['ingrediente_raro'], ENT_QUOTES, 'UTF-8') ?>.
                                    </li>
                                    <li>
                                        <strong>Base Diluente:</strong>
                                        <strong><?= $dadosReceita['partes_restantes'] ?> partes</strong> de Água Encantada da Floresta.
                                    </li>
                                </ul>
                                <p class="receita-desafio-instrucao">
                                    ⚖️ <strong>Instrução do Bicó:</strong> Qual fração do frasco foi preenchida com a essência rara?
                                </p>
                            </div>

                            <!-- Frasco de Tubo de Ensaio Alquímico Visual -->
                            <div class="frasco-visual-area">
                                <span class="frasco-titulo">Frasco de Ensaio</span>
                                <div class="frasco-vidro" style="--total-partes: <?= $dadosReceita['partes_total'] ?>;">
                                    <?php for ($i = $dadosReceita['partes_total']; $i >= 1; $i--): ?>
                                        <?php $ehEssencia = ($i <= $dadosReceita['partes_usadas']); ?>
                                        <div class="frasco-segmento <?= $ehEssencia ? 'segmento-essencia' : 'segmento-base' ?>">
                                            <span class="segmento-marca">dose <?= $i ?>/<?= $dadosReceita['partes_total'] ?></span>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                <span class="frasco-legenda">🧪 Líquido Mágico</span>
                            </div>

                        </div>

                    <?php else: ?>

                        <!-- COMPARAÇÃO DE DUAS POÇÕES LADO A LADO -->
                        <div class="receita-comparacao-grid">
                            <div class="receita-pocao-coluna">
                                <h4>🧪 <?= htmlspecialchars($dadosReceita['pocao1_nome'], ENT_QUOTES, 'UTF-8') ?></h4>
                                <p class="pocao-fracao-destaque">Concentração: <strong><?= htmlspecialchars($dadosReceita['pocao1_fracao'], ENT_QUOTES, 'UTF-8') ?></strong> de essência</p>
                            </div>

                            <div class="receita-vs-coluna">
                                <strong>VS</strong>
                            </div>

                            <div class="receita-pocao-coluna">
                                <h4>🍃 <?= htmlspecialchars($dadosReceita['pocao2_nome'], ENT_QUOTES, 'UTF-8') ?></h4>
                                <p class="pocao-fracao-destaque">Concentração: <strong><?= htmlspecialchars($dadosReceita['pocao2_fracao'], ENT_QUOTES, 'UTF-8') ?></strong> de essência</p>
                            </div>
                        </div>

                        <p class="receita-desafio-instrucao" style="margin-top: 14px;">
                            ⚖️ <strong>Instrução do Grimório:</strong> Compare as concentrações e descubra qual poção possui a maior proporção de essência mágica!
                        </p>

                    <?php endif; ?>

                    <!-- DICA MÁGICA DO BICÓ -->
                    <?= renderBicoDicaBotaoHtml($dicaPocao) ?>

                    <!-- ALTERNATIVAS DE RESPOSTA -->
                    <?php if ($alternativas): ?>
                        <form method="POST" style="margin-top: 20px;">
                            <input type="hidden" name="id_questao" value="<?= (int) $questao['id_questao'] ?>">

                            <fieldset>
                                <legend class="sr-only">Escolha a fração correta para a poção</legend>
                                <?php foreach ($alternativas as $alt): ?>
                                    <label>
                                        <input
                                            type="radio"
                                            name="id_alternativa"
                                            value="<?= (int) $alt['id_alternativa'] ?>"
                                            required>
                                        <strong><?= htmlspecialchars($alt['texto'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    </label>
                                <?php endforeach; ?>
                            </fieldset>

                            <div style="margin-top: 20px;">
                                <button type="submit" name="responder_pocao" value="1" class="btn">
                                    ✨ Misturar Ingredientes e Selar a Poção
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <p>Esta receita não possui ingredientes cadastrados.</p>
                    <?php endif; ?>
                </article>
            <?php else: ?>
                <p>Não há mais receitas disponíveis para esta dificuldade no momento.</p>
            <?php endif; ?>

            <p style="margin-top: 24px;">
                <a href="?pagina=abandonar_pocoes" class="btn btn-secondary">
                    Guardar frascos e voltar aos jogos
                </a>
            </p>

        <?php endif; ?>
    </section>
</main>
```

---

## Arquivo 7: `public/index.php`

```php
<?php

session_start();

require __DIR__ . '/../app/config/app.php';

$pagina = $_GET['pagina'] ?? 'home';

$paginasPermitidas = [
    'home',
    'cadastro',
    'login',
    'sair',
    'batalha',
    'pocoes',
    'desafio',
    'finalizar_desafio',
    'historico',
    'guardiao',
    'dupla',
    'abandonar_desafio',
    'abandonar_guardiao',
    'abandonar_dupla',
    'abandonar_pocoes'
];

// VALIDAR A ROTA SOLICITADA
if (!in_array($pagina, $paginasPermitidas, true)) {
    http_response_code(404);
    $pagina = 'home';
}

// PROTEGER ACESSO ÀS PÁGINAS DOS JOGOS
if (
    in_array(
        $pagina,
        [
            'batalha',
            'pocoes',
            'desafio',
            'guardiao',
            'dupla',
            'historico'
        ],
        true
    )
    && !isset($_SESSION['aluno_id'])
) {
    header('Location: ?pagina=login');
    exit;
}

// ENCERRAR SESSÃO
if ($pagina === 'sair') {
    $_SESSION = [];
    session_destroy();

    header('Location: ?pagina=home');
    exit;
}

// FINALIZAR DESAFIO PELO CRONÔMETRO (60 SEGUNDOS)
if ($pagina === 'finalizar_desafio') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Método não permitido.');
    }

    if (
        empty($_SESSION['aluno_id']) ||
        empty($_SESSION['desafio_partida_id']) ||
        empty($_SESSION['desafio_participante_id']) ||
        !isset($_SESSION['desafio_inicio'])
    ) {
        http_response_code(403);
        exit('Partida não encontrada.');
    }

    // Confirma que os 60 segundos já passaram.
    if (time() - (int) $_SESSION['desafio_inicio'] < 60) {
        http_response_code(400);
        exit('O tempo da partida ainda não terminou.');
    }

    require_once __DIR__ . '/../app/config/conexao.php';

    $consultaFim = $conexao->prepare(
        "UPDATE partida
         SET status = 'finalizada',
             data_fim = NOW()
         WHERE id_partida = :partida
           AND status = 'em_andamento'
           AND EXISTS (
               SELECT 1
               FROM partida_participante
               WHERE partida_participante.id_partida = partida.id_partida
                 AND partida_participante.id_participante = :participante
                 AND partida_participante.id_aluno = :aluno
           )"
    );

    $consultaFim->execute([
        'partida' => (int) $_SESSION['desafio_partida_id'],
        'participante' => (int) $_SESSION['desafio_participante_id'],
        'aluno' => (int) $_SESSION['aluno_id']
    ]);

    if ($consultaFim->rowCount() > 0) {
        exit('Partida finalizada.');
    }

    $consultaStatus = $conexao->prepare(
        "SELECT status
         FROM partida
         WHERE id_partida = :partida"
    );

    $consultaStatus->execute([
        'partida' => (int) $_SESSION['desafio_partida_id']
    ]);

    if ($consultaStatus->fetchColumn() === 'finalizada') {
        exit('Partida já finalizada.');
    }

    http_response_code(403);
    exit('Não foi possível finalizar esta partida.');
}

// ABANDONAR DESAFIO RELÂMPAGO
if ($pagina === 'abandonar_desafio') {
    if (!empty($_SESSION['aluno_id']) && !empty($_SESSION['desafio_partida_id'])) {
        require_once __DIR__ . '/../app/config/conexao.php';

        $idPartida = (int) $_SESSION['desafio_partida_id'];
        $idAluno = (int) $_SESSION['aluno_id'];

        $consultaAbandono = $conexao->prepare(
            "UPDATE partida
             SET status = 'abandonada',
                 data_fim = NOW()
             WHERE id_partida = :partida
               AND status = 'em_andamento'
               AND EXISTS (
                   SELECT 1
                   FROM partida_participante
                   WHERE partida_participante.id_partida = partida.id_partida
                     AND partida_participante.id_aluno = :aluno
               )"
        );

        $consultaAbandono->execute([
            'partida' => $idPartida,
            'aluno' => $idAluno
        ]);
    }

    unset(
        $_SESSION['desafio_partida_id'],
        $_SESSION['desafio_participante_id'],
        $_SESSION['desafio_inicio'],
        $_SESSION['desafio_xp'],
        $_SESSION['desafio_acertos'],
        $_SESSION['desafio_erros'],
        $_SESSION['desafio_feedback']
    );

    header('Location: ?pagina=batalha');
    exit;
}

// ABANDONAR CONTRA O GUARDIÃO
if ($pagina === 'abandonar_guardiao') {
    if (empty($_SESSION['aluno_id'])) {
        http_response_code(403);
        exit('É necessário entrar na conta para abandonar esta partida.');
    }

    $idPartidaGuardiao = filter_input(
        INPUT_GET,
        'id_partida',
        FILTER_VALIDATE_INT
    );

    if ($idPartidaGuardiao === false || $idPartidaGuardiao === null || $idPartidaGuardiao <= 0) {
        http_response_code(400);
        exit('Identificador de partida inválido.');
    }

    require_once __DIR__ . '/../app/config/conexao.php';

    $consultaModo = $conexao->prepare(
        'SELECT id_modo_jogo
         FROM modo_jogo
         WHERE nome = :nome
         LIMIT 1'
    );
    $consultaModo->execute(['nome' => 'Contra o Guardião']);
    $idModoGuardiao = $consultaModo->fetchColumn();

    if ($idModoGuardiao === false) {
        http_response_code(500);
        exit('O modo Contra o Guardião não foi cadastrado no banco.');
    }

    $idAluno = (int) $_SESSION['aluno_id'];
    $conexao->beginTransaction();

    try {
        $consultaPartida = $conexao->prepare(
            'SELECT status
             FROM partida
             WHERE id_partida = :partida
               AND id_modo_jogo = :modo
             FOR UPDATE'
        );
        $consultaPartida->execute([
            'partida' => (int) $idPartidaGuardiao,
            'modo' => (int) $idModoGuardiao
        ]);
        $statusPartida = $consultaPartida->fetchColumn();

        $consultaParticipante = $conexao->prepare(
            'SELECT id_participante
             FROM partida_participante
             WHERE id_partida = :partida
               AND id_aluno = :aluno
             LIMIT 1'
        );
        $consultaParticipante->execute([
            'partida' => (int) $idPartidaGuardiao,
            'aluno' => $idAluno
        ]);
        $idParticipanteGuardiao = $consultaParticipante->fetchColumn();

        if ($statusPartida !== 'em_andamento' || $idParticipanteGuardiao === false) {
            $conexao->rollBack();
            http_response_code(409);
            exit('A partida não está em andamento ou não pertence ao aluno autenticado.');
        }

        $consultaAbandono = $conexao->prepare(
            "UPDATE partida
             SET status = 'abandonada',
                 data_fim = NOW()
             WHERE id_partida = :partida
               AND id_modo_jogo = :modo
               AND status = 'em_andamento'
               AND EXISTS (
                   SELECT 1
                   FROM partida_participante
                   WHERE partida_participante.id_partida = partida.id_partida
                     AND partida_participante.id_participante = :participante
                     AND partida_participante.id_aluno = :aluno
               )"
        );
        $consultaAbandono->execute([
            'partida' => (int) $idPartidaGuardiao,
            'modo' => (int) $idModoGuardiao,
            'participante' => (int) $idParticipanteGuardiao,
            'aluno' => $idAluno
        ]);

        if ($consultaAbandono->rowCount() !== 1) {
            $conexao->rollBack();
            http_response_code(409);
            exit('Não foi possível confirmar o abandono da partida.');
        }

        $consultaRemoveEstado = $conexao->prepare(
            'DELETE FROM guardiao_estado
             WHERE id_partida = :partida
               AND id_participante = :participante'
        );
        $consultaRemoveEstado->execute([
            'partida' => (int) $idPartidaGuardiao,
            'participante' => (int) $idParticipanteGuardiao
        ]);

        $conexao->commit();
    } catch (Throwable $e) {
        if ($conexao->inTransaction()) {
            $conexao->rollBack();
        }
        throw $e;
    }

    unset(
        $_SESSION['guardiao_partida_id'],
        $_SESSION['guardiao_participante_id'],
        $_SESSION['guardiao_vidas_jogador'],
        $_SESSION['guardiao_vidas_guardiao'],
        $_SESSION['guardiao_questoes'],
        $_SESSION['guardiao_xp'],
        $_SESSION['guardiao_acertos'],
        $_SESSION['guardiao_erros'],
        $_SESSION['guardiao_dificuldade'],
        $_SESSION['guardiao_feedback']
    );

    header('Location: ?pagina=batalha');
    exit;
}

// ABANDONAR DUPLA LOCAL
if ($pagina === 'abandonar_dupla') {
    if (!empty($_SESSION['aluno_id']) && !empty($_SESSION['dupla_partida_id'])) {
        require_once __DIR__ . '/../app/config/conexao.php';

        $idPartida = (int) $_SESSION['dupla_partida_id'];
        $idAluno = (int) $_SESSION['aluno_id'];

        $consultaAbandono = $conexao->prepare(
            "UPDATE partida
             SET status = 'abandonada',
                 data_fim = NOW()
             WHERE id_partida = :partida
               AND status = 'em_andamento'
               AND EXISTS (
                   SELECT 1
                   FROM partida_participante
                   WHERE partida_participante.id_partida = partida.id_partida
                     AND partida_participante.id_aluno = :aluno
               )"
        );

        $consultaAbandono->execute([
            'partida' => $idPartida,
            'aluno' => $idAluno
        ]);
    }

    unset(
        $_SESSION['dupla_partida_id'],
        $_SESSION['dupla_p1_participante_id'],
        $_SESSION['dupla_p2_participante_id'],
        $_SESSION['dupla_p1_nome'],
        $_SESSION['dupla_p2_nome'],
        $_SESSION['dupla_dificuldade'],
        $_SESSION['dupla_turno'],
        $_SESSION['dupla_p1_questoes'],
        $_SESSION['dupla_p2_questoes'],
        $_SESSION['dupla_p1_acertos'],
        $_SESSION['dupla_p1_erros'],
        $_SESSION['dupla_p1_xp'],
        $_SESSION['dupla_p2_acertos'],
        $_SESSION['dupla_p2_erros'],
        $_SESSION['dupla_p2_xp'],
        $_SESSION['dupla_feedback']
    );

    header('Location: ?pagina=batalha');
    exit;
}

// ABANDONAR POÇÕES MÁGICAS
if ($pagina === 'abandonar_pocoes') {
    if (!empty($_SESSION['aluno_id']) && !empty($_SESSION['pocoes_partida_id'])) {
        require_once __DIR__ . '/../app/config/conexao.php';

        $idPartida = (int) $_SESSION['pocoes_partida_id'];
        $idAluno = (int) $_SESSION['aluno_id'];

        $consultaAbandono = $conexao->prepare(
            "UPDATE partida
             SET status = 'abandonada',
                 data_fim = NOW()
             WHERE id_partida = :partida
               AND status = 'em_andamento'
               AND EXISTS (
                   SELECT 1
                   FROM partida_participante
                   WHERE partida_participante.id_partida = partida.id_partida
                     AND partida_participante.id_aluno = :aluno
               )"
        );

        $consultaAbandono->execute([
            'partida' => $idPartida,
            'aluno' => $idAluno
        ]);
    }

    unset(
        $_SESSION['pocoes_partida_id'],
        $_SESSION['pocoes_participante_id'],
        $_SESSION['pocoes_dificuldade'],
        $_SESSION['pocoes_questoes'],
        $_SESSION['pocoes_xp'],
        $_SESSION['pocoes_acertos'],
        $_SESSION['pocoes_erros'],
        $_SESSION['pocoes_feedback']
    );

    header('Location: ?pagina=home#jogos');
    exit;
}

// PROCESSAR FORMULÁRIOS DE CADASTRO E LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($pagina === 'cadastro') {
        require __DIR__ . '/../app/controllers/cadastroController.php';
    }

    if ($pagina === 'login') {
        require __DIR__ . '/../app/controllers/loginController.php';
    }
}

// EXIBIR PÁGINA
include __DIR__ . '/../app/views/layout/header.php';

include __DIR__ . '/../app/views/pages/' . $pagina . '.php';

include __DIR__ . '/../app/views/layout/footer.php';
```

---

## Arquivo 8: `public/assets/css/style.css`

*(Adicionar ao final do seu `style.css` atual)*

```css
/* ==========================================================================
   ATUALIZAÇÕES BICÓ, DUPLA, GUARDIÃO E POÇÕES (NOVAS ADIÇÕES)
   ========================================================================== */

/* 1. Apresentação do Bicó na Home */
.bico-apresentacao-card {
    background: #fffdf0;
    border: 3px solid var(--tinta);
    box-shadow: var(--pixel), 8px 8px 0 4px var(--amarelo);
    margin: 28px 0;
    padding: 24px;
}
.bico-apresentacao-grid {
    display: flex;
    gap: 24px;
    align-items: center;
}
.bico-mascote-avatar {
    width: 90px;
    height: 100px;
    flex-shrink: 0;
    background: url("../img/bico-feliz.svg") center / contain no-repeat;
    image-rendering: pixelated;
}
.bico-mascote-bio {
    flex: 1;
}
.bico-mascote-bio h3 {
    margin: 8px 0 10px;
    font-size: 1.35rem;
    color: var(--roxo);
}
.bico-dicas-pilulas {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
}
.bico-pilula {
    font-size: 0.8rem;
    font-weight: 700;
    background: var(--amarelo-claro);
    color: var(--tinta);
    border: 2px solid var(--tinta);
    padding: 4px 10px;
    box-shadow: 2px 2px 0 var(--tinta);
}

/* 2. Dicas Interativas do Bicó (Em todos os jogos) */
.bico-dica-card {
    margin: 16px 0;
    background: var(--amarelo-claro);
    border: 3px dashed var(--tinta);
    padding: 10px 14px;
    cursor: pointer;
}
.bico-dica-resumo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    color: var(--azul-escuro);
    list-style: none;
    user-select: none;
}
.bico-dica-resumo::-webkit-details-marker {
    display: none;
}
.bico-dica-resumo:hover {
    color: var(--roxo);
}
.bico-icone-mini {
    width: 26px;
    height: 28px;
    display: inline-block;
    background: url("../img/bico-neutro.svg") center / contain no-repeat;
    image-rendering: pixelated;
    flex-shrink: 0;
}
.bico-dica-conteudo {
    margin-top: 10px;
    padding: 12px 14px;
    background: var(--branco);
    border: 2px solid var(--tinta);
    font-size: 0.96rem;
    line-height: 1.5;
    box-shadow: 3px 3px 0 var(--tinta);
}

/* 3. Avatares e Sprites em Pixel Art (Caçador, Monstrinho, Guardião) */
.avatar-cacador {
    background-image: url("../img/cacador.svg");
}
.avatar-monstrinho {
    background-image: url("../img/monstrinho.svg");
}
.avatar-guardiao {
    background-image: url("../img/guardiao.svg");
}

.pixel-avatar-preview {
    display: inline-block;
    width: 44px;
    height: 44px;
    background-position: center;
    background-repeat: no-repeat;
    background-size: contain;
    image-rendering: pixelated;
    border: 2px solid var(--tinta);
    background-color: var(--papel-escuro);
    box-shadow: 3px 3px 0 var(--tinta);
    vertical-align: middle;
}
.pixel-avatar-luta {
    display: inline-block;
    width: 68px;
    height: 68px;
    background-position: center;
    background-repeat: no-repeat;
    background-size: contain;
    image-rendering: pixelated;
}
.pixel-avatar-grande {
    display: inline-block;
    width: 76px;
    height: 76px;
    background-position: center;
    background-repeat: no-repeat;
    background-size: contain;
    image-rendering: pixelated;
    border: 3px solid var(--tinta);
    background-color: var(--papel-escuro);
    box-shadow: 4px 4px 0 var(--tinta);
}

.input-com-avatar {
    display: flex;
    align-items: center;
    gap: 12px;
}
.input-com-avatar input {
    flex: 1;
}

/* Estilos de Dupla */
.dupla-placar-grid {
    gap: 20px;
}
.card-jogador {
    transition: transform 0.2s;
}
.card-avatar-topo {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
}
.badge-personagem {
    display: inline-block;
    font-size: 0.72rem;
    font-family: var(--fonte-pixel);
    background: var(--verde-claro);
    color: var(--verde);
    border: 1px solid var(--verde);
    padding: 2px 6px;
    margin-top: 2px;
}
.badge-personagem.badge-roxo {
    background: var(--roxo-claro);
    color: var(--roxo);
    border-color: var(--roxo);
}
.badge-vez {
    margin-top: 10px;
    padding: 6px 10px;
    background: var(--tinta);
    color: var(--amarelo);
    font-family: var(--fonte-pixel);
    font-size: 0.7rem;
    text-align: center;
    box-shadow: var(--pixel);
}
.turno-banner-pixel .turno-flex {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
}
.resultado-vencedor-destaque {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
.dupla-empate-avatars {
    display: flex;
    gap: 16px;
}

/* 4. Arena do Guardião (HUD Maior e Visível) */
.arena-batalha-container {
    background: #191c2b;
    color: var(--papel);
    border: 4px solid var(--tinta);
    box-shadow: var(--pixel), 10px 10px 0 4px rgba(29, 35, 64, 0.3);
    padding: 24px;
    margin-bottom: 24px;
}
.arena-duelo-grid {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 18px;
    align-items: center;
    background: #24293e;
    border: 3px solid var(--tinta);
    padding: 20px;
    box-shadow: inset 0 0 10px rgba(0,0,0,0.5);
}
.lutador-card {
    display: flex;
    align-items: center;
    gap: 16px;
}
.lutador-guardiao {
    flex-direction: row-reverse;
    text-align: right;
}
.lutador-avatar-wrap {
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #141724;
    border: 3px solid var(--papel-escuro);
    box-shadow: 4px 4px 0 var(--tinta);
    flex-shrink: 0;
}
.badge-lutador {
    font-family: var(--fonte-pixel);
    font-size: 0.6rem;
    color: var(--amarelo);
    letter-spacing: 0.5px;
}
.badge-lutador.badge-roxo {
    color: #c4a1ff;
}
.lutador-nome {
    font-size: 1.15rem;
    margin: 4px 0 6px;
    color: var(--branco);
}
.vidas-container-grande {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 6px;
}
.lutador-guardiao .vidas-container-grande {
    justify-content: flex-end;
}
.coracao-pixel-hud {
    width: 32px;
    height: 32px;
    display: inline-block;
    image-rendering: pixelated;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
}
.coracao-cheio {
    background-image: url("../img/coracao-pixel.svg");
    filter: drop-shadow(0 0 4px rgba(214, 58, 44, 0.6));
}
.coracao-vazio {
    background-image: url("../img/coracao-vazio.svg");
    opacity: 0.65;
}
.escudo-pixel-hud {
    width: 32px;
    height: 32px;
    display: inline-block;
    image-rendering: pixelated;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
}
.escudo-cheio {
    background-image: url("../img/escudo-guardiao.svg");
    filter: drop-shadow(0 0 5px rgba(138, 99, 210, 0.8));
}
.escudo-vazio {
    background-image: url("../img/escudo-vazio.svg");
    opacity: 0.65;
}
.texto-vidas-hud {
    font-family: var(--fonte-pixel);
    font-size: 0.68rem;
    color: var(--amarelo);
    margin-left: 6px;
}
.arena-vs-centro {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.emblema-vs {
    font-family: var(--fonte-pixel);
    font-size: 1.35rem;
    color: var(--amarelo);
    background: #141724;
    border: 3px solid var(--tinta);
    padding: 8px 14px;
    box-shadow: 3px 3px 0 var(--laranja);
}
.rodada-badge {
    font-family: var(--fonte-pixel);
    font-size: 0.62rem;
    color: var(--papel);
    background: var(--tinta);
    padding: 3px 8px;
    border: 1px solid var(--papel-escuro);
}
.arena-desempenho-painel {
    margin-top: 18px;
}
.arena-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 12px;
}
.stat-box {
    background: #141724;
    border: 2px solid #363c56;
    padding: 10px 14px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.stat-rotulo {
    font-size: 0.82rem;
    color: var(--papel-escuro);
}
.stat-valor {
    font-family: var(--fonte-pixel);
    font-size: 1.1rem;
    color: var(--branco);
}
.stat-valor.stat-acerto { color: #5ce68b; }
.stat-valor.stat-erro { color: #ff8c8c; }
.stat-valor.stat-ouro { color: var(--amarelo); }
.barra-batalha-wrap {
    margin-top: 14px;
}
.barra-rotulo {
    font-size: 0.85rem;
    color: var(--papel-escuro);
    margin-bottom: 4px;
    display: block;
}

/* 5. Jogo das Poções (Pergaminho de Receita Literal e Tubo Alquímico) */
.receita-pergaminho-card {
    background: #fffdf2 !important;
    border: 4px solid var(--tinta);
    box-shadow: var(--pixel), 8px 8px 0 4px rgba(106, 63, 181, 0.25);
    padding: 24px;
    position: relative;
}
.receita-cabecalho {
    display: flex;
    align-items: center;
    gap: 16px;
    border-bottom: 3px dashed var(--tinta);
    padding-bottom: 14px;
    margin-bottom: 18px;
}
.receita-selo-bico {
    font-size: 2.2rem;
    line-height: 1;
}
.receita-nome {
    margin: 4px 0 0;
    font-size: 1.45rem;
    color: var(--roxo);
}
.receita-corpo-grid {
    display: grid;
    grid-template-columns: 1fr 180px;
    gap: 24px;
    align-items: center;
    margin: 16px 0;
}
.ingredientes-lista {
    list-style: none;
    padding: 0;
    margin: 10px 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.ingredientes-lista li {
    background: var(--papel);
    padding: 8px 12px;
    border-left: 4px solid var(--roxo);
    border-top: 1px solid rgba(0,0,0,0.1);
    border-right: 1px solid rgba(0,0,0,0.1);
    border-bottom: 1px solid rgba(0,0,0,0.1);
    font-size: 0.95rem;
}
.receita-desafio-instrucao {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--tinta);
    background: var(--amarelo-claro);
    padding: 10px 14px;
    border: 2px solid var(--tinta);
    margin-top: 14px;
}
.frasco-visual-area {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    background: var(--papel-escuro);
    border: 3px solid var(--tinta);
    padding: 14px 10px;
    box-shadow: 4px 4px 0 var(--tinta);
}
.frasco-titulo {
    font-family: var(--fonte-pixel);
    font-size: 0.65rem;
    color: var(--tinta);
}
.frasco-vidro {
    width: 90px;
    height: 180px;
    background: rgba(255, 255, 255, 0.7);
    border: 4px solid var(--tinta);
    border-radius: 0 0 16px 16px;
    box-shadow: inset -4px 0 0 rgba(0,0,0,0.1), inset 4px 0 0 rgba(255,255,255,0.8);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.frasco-segmento {
    flex: 1;
    border-bottom: 2px dashed rgba(29, 35, 64, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
}
.segmento-essencia {
    background: linear-gradient(135deg, #7c4dff, #512da8);
    color: var(--branco);
}
.segmento-base {
    background: rgba(212, 232, 248, 0.35);
    color: var(--tinta-suave);
}
.segmento-marca {
    font-family: var(--fonte-pixel);
    font-size: 0.48rem;
    line-height: 1;
    text-shadow: 1px 1px 0 rgba(0,0,0,0.3);
}
.frasco-legenda {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--roxo);
}

/* Comparação de Poções */
.receita-comparacao-grid {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 16px;
    align-items: center;
    margin: 16px 0;
}
.receita-pocao-coluna {
    background: var(--papel);
    border: 3px solid var(--tinta);
    padding: 16px;
    text-align: center;
    box-shadow: 4px 4px 0 var(--tinta);
}
.receita-vs-coluna {
    font-family: var(--fonte-pixel);
    font-size: 1.2rem;
    color: var(--roxo);
}
.pocao-fracao-destaque {
    font-size: 1.05rem;
    margin: 8px 0 0;
}
.pocao-fracao-destaque strong {
    font-family: var(--fonte-pixel);
    color: var(--roxo);
    font-size: 1.2rem;
}

/* 6. Desafio Relâmpago */
.desafio-hud-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 14px;
    align-items: center;
}
.label-hud {
    display: block;
    font-size: 0.8rem;
    color: var(--papel-escuro);
}
.cronometro-destaque {
    font-family: var(--fonte-pixel);
    font-size: 1.45rem;
    color: var(--amarelo) !important;
}

/* Responsividade adicional */
@media (max-width: 760px) {
    .bico-apresentacao-grid {
        flex-direction: column;
        text-align: center;
    }
    .bico-dicas-pilulas {
        justify-content: center;
    }
    .arena-duelo-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    .lutador-guardiao {
        flex-direction: row;
        text-align: left;
    }
    .lutador-guardiao .vidas-container-grande {
        justify-content: flex-start;
    }
    .receita-corpo-grid {
        grid-template-columns: 1fr;
    }
    .receita-comparacao-grid {
        grid-template-columns: 1fr;
    }
}
```

---

## Arquivos 9 a 15: Sprites SVG em Pixel Art

### `public/assets/img/cacador.svg`
```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" shape-rendering="crispEdges" role="img" aria-label="Caçador em pixel art">
  <rect x="7" y="1" width="2" height="1" fill="#d63a2c"/>
  <rect x="8" y="2" width="2" height="1" fill="#d63a2c"/>
  <rect x="9" y="3" width="1" height="1" fill="#d63a2c"/>
  <rect x="9" y="2" width="6" height="2" fill="#2e7d4f"/>
  <rect x="8" y="4" width="9" height="2" fill="#2e7d4f"/>
  <rect x="6" y="6" width="13" height="2" fill="#1f4a7a"/>
  <rect x="7" y="8" width="2" height="3" fill="#8a5a00"/>
  <rect x="16" y="8" width="2" height="3" fill="#8a5a00"/>
  <rect x="9" y="8" width="7" height="4" fill="#ffd23f"/>
  <rect x="9" y="9" width="2" height="1" fill="#1d2340"/>
  <rect x="14" y="9" width="2" height="1" fill="#1d2340"/>
  <rect x="10" y="9" width="1" height="1" fill="#ffffff"/>
  <rect x="15" y="9" width="1" height="1" fill="#ffffff"/>
  <rect x="11" y="11" width="3" height="1" fill="#f26b1d"/>
  <rect x="6" y="12" width="1" height="8" fill="#8a5a00"/>
  <rect x="7" y="12" width="11" height="6" fill="#2e7d4f"/>
  <rect x="9" y="12" width="2" height="6" fill="#8a5a00"/>
  <rect x="8" y="18" width="9" height="2" fill="#1d2340"/>
  <rect x="8" y="20" width="3" height="3" fill="#1f4a7a"/>
  <rect x="14" y="20" width="3" height="3" fill="#1f4a7a"/>
  <rect x="7" y="23" width="4" height="1" fill="#8a5a00"/>
  <rect x="14" y="23" width="4" height="1" fill="#8a5a00"/>
</svg>
```

### `public/assets/img/monstrinho.svg`
```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" shape-rendering="crispEdges" role="img" aria-label="Monstrinho em pixel art">
  <rect x="5" y="3" width="3" height="3" fill="#ffd23f"/>
  <rect x="6" y="2" width="1" height="1" fill="#1d2340"/>
  <rect x="16" y="3" width="3" height="3" fill="#ffd23f"/>
  <rect x="17" y="2" width="1" height="1" fill="#1d2340"/>
  <rect x="6" y="6" width="12" height="12" fill="#6a3fb5"/>
  <rect x="7" y="5" width="10" height="1" fill="#6a3fb5"/>
  <rect x="5" y="8" width="1" height="8" fill="#6a3fb5"/>
  <rect x="18" y="8" width="1" height="8" fill="#6a3fb5"/>
  <rect x="7" y="9" width="3" height="4" fill="#ffffff"/>
  <rect x="8" y="10" width="2" height="3" fill="#1d2340"/>
  <rect x="8" y="10" width="1" height="1" fill="#ffffff"/>
  <rect x="14" y="9" width="3" height="4" fill="#ffffff"/>
  <rect x="14" y="10" width="2" height="3" fill="#1d2340"/>
  <rect x="14" y="10" width="1" height="1" fill="#ffffff"/>
  <rect x="6" y="13" width="2" height="1" fill="#ff8fb1"/>
  <rect x="16" y="13" width="2" height="1" fill="#ff8fb1"/>
  <rect x="11" y="13" width="2" height="1" fill="#ffffff"/>
  <rect x="10" y="14" width="4" height="1" fill="#1d2340"/>
  <rect x="9" y="15" width="6" height="3" fill="#ffd23f"/>
  <rect x="7" y="18" width="3" height="3" fill="#502d91"/>
  <rect x="14" y="18" width="3" height="3" fill="#502d91"/>
  <rect x="6" y="21" width="4" height="1" fill="#1d2340"/>
  <rect x="14" y="21" width="4" height="1" fill="#1d2340"/>
</svg>
```

### `public/assets/img/guardiao.svg`
```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 28" width="28" height="28" shape-rendering="crispEdges" role="img" aria-label="Guardião da Floresta em pixel art">
  <rect x="3" y="1" width="2" height="4" fill="#593617"/>
  <rect x="5" y="3" width="3" height="2" fill="#2e7d4f"/>
  <rect x="23" y="1" width="2" height="4" fill="#593617"/>
  <rect x="20" y="3" width="3" height="2" fill="#2e7d4f"/>
  <rect x="7" y="5" width="14" height="4" fill="#593617"/>
  <rect x="10" y="4" width="8" height="1" fill="#2e7d4f"/>
  <rect x="6" y="9" width="16" height="12" fill="#422912"/>
  <rect x="5" y="11" width="1" height="8" fill="#321f0d"/>
  <rect x="22" y="11" width="1" height="8" fill="#321f0d"/>
  <rect x="8" y="11" width="3" height="3" fill="#ffd23f"/>
  <rect x="9" y="12" width="1" height="1" fill="#ffffff"/>
  <rect x="17" y="11" width="3" height="3" fill="#ffd23f"/>
  <rect x="18" y="12" width="1" height="1" fill="#ffffff"/>
  <rect x="13" y="13" width="2" height="4" fill="#38cfb8"/>
  <rect x="11" y="15" width="6" height="1" fill="#38cfb8"/>
  <rect x="10" y="18" width="8" height="2" fill="#1d2340"/>
  <rect x="4" y="19" width="4" height="6" fill="#593617"/>
  <rect x="20" y="19" width="4" height="6" fill="#593617"/>
  <rect x="8" y="21" width="12" height="5" fill="#321f0d"/>
  <rect x="6" y="26" width="5" height="2" fill="#593617"/>
  <rect x="17" y="26" width="5" height="2" fill="#593617"/>
</svg>
```

### `public/assets/img/coracao-pixel.svg`
```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16" shape-rendering="crispEdges" role="img" aria-label="Coração Cheio">
  <rect x="3" y="1" width="4" height="2" fill="#1d2340"/>
  <rect x="9" y="1" width="4" height="2" fill="#1d2340"/>
  <rect x="2" y="2" width="6" height="2" fill="#d63a2c"/>
  <rect x="8" y="2" width="6" height="2" fill="#d63a2c"/>
  <rect x="3" y="3" width="2" height="2" fill="#ffffff"/>
  <rect x="1" y="3" width="14" height="6" fill="#d63a2c"/>
  <rect x="1" y="3" width="1" height="6" fill="#1d2340"/>
  <rect x="14" y="3" width="1" height="6" fill="#1d2340"/>
  <rect x="2" y="9" width="12" height="2" fill="#d63a2c"/>
  <rect x="2" y="9" width="1" height="2" fill="#1d2340"/>
  <rect x="13" y="9" width="1" height="2" fill="#1d2340"/>
  <rect x="4" y="11" width="8" height="2" fill="#d63a2c"/>
  <rect x="3" y="11" width="1" height="2" fill="#1d2340"/>
  <rect x="12" y="11" width="1" height="2" fill="#1d2340"/>
  <rect x="6" y="13" width="4" height="2" fill="#d63a2c"/>
  <rect x="5" y="13" width="1" height="2" fill="#1d2340"/>
  <rect x="10" y="13" width="1" height="2" fill="#1d2340"/>
  <rect x="7" y="15" width="2" height="1" fill="#1d2340"/>
</svg>
```

### `public/assets/img/coracao-vazio.svg`
```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16" shape-rendering="crispEdges" role="img" aria-label="Coração Vazio">
  <rect x="3" y="1" width="4" height="2" fill="#1d2340"/>
  <rect x="9" y="1" width="4" height="2" fill="#1d2340"/>
  <rect x="2" y="2" width="6" height="2" fill="#6d758d"/>
  <rect x="8" y="2" width="6" height="2" fill="#6d758d"/>
  <rect x="1" y="3" width="14" height="6" fill="#6d758d"/>
  <rect x="1" y="3" width="1" height="6" fill="#1d2340"/>
  <rect x="14" y="3" width="1" height="6" fill="#1d2340"/>
  <rect x="8" y="3" width="1" height="3" fill="#1d2340"/>
  <rect x="7" y="6" width="1" height="2" fill="#1d2340"/>
  <rect x="8" y="8" width="1" height="2" fill="#1d2340"/>
  <rect x="2" y="9" width="12" height="2" fill="#6d758d"/>
  <rect x="2" y="9" width="1" height="2" fill="#1d2340"/>
  <rect x="13" y="9" width="1" height="2" fill="#1d2340"/>
  <rect x="4" y="11" width="8" height="2" fill="#6d758d"/>
  <rect x="3" y="11" width="1" height="2" fill="#1d2340"/>
  <rect x="12" y="11" width="1" height="2" fill="#1d2340"/>
  <rect x="6" y="13" width="4" height="2" fill="#6d758d"/>
  <rect x="5" y="13" width="1" height="2" fill="#1d2340"/>
  <rect x="10" y="13" width="1" height="2" fill="#1d2340"/>
  <rect x="7" y="15" width="2" height="1" fill="#1d2340"/>
</svg>
```

### `public/assets/img/escudo-guardiao.svg`
```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16" shape-rendering="crispEdges" role="img" aria-label="Gema Mística Ativa">
  <rect x="5" y="1" width="6" height="1" fill="#1d2340"/>
  <rect x="3" y="2" width="2" height="2" fill="#1d2340"/>
  <rect x="11" y="2" width="2" height="2" fill="#1d2340"/>
  <rect x="5" y="2" width="6" height="2" fill="#8a63d2"/>
  <rect x="2" y="4" width="1" height="8" fill="#1d2340"/>
  <rect x="13" y="4" width="1" height="8" fill="#1d2340"/>
  <rect x="3" y="4" width="10" height="8" fill="#6a3fb5"/>
  <rect x="5" y="4" width="2" height="2" fill="#ffffff"/>
  <rect x="7" y="6" width="2" height="4" fill="#ffd23f"/>
  <rect x="3" y="12" width="2" height="2" fill="#1d2340"/>
  <rect x="11" y="12" width="2" height="2" fill="#1d2340"/>
  <rect x="5" y="12" width="6" height="2" fill="#4d2c88"/>
  <rect x="5" y="14" width="6" height="1" fill="#1d2340"/>
</svg>
```

### `public/assets/img/escudo-vazio.svg`
```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16" shape-rendering="crispEdges" role="img" aria-label="Gema Mística Quebrada">
  <rect x="5" y="1" width="6" height="1" fill="#1d2340"/>
  <rect x="3" y="2" width="2" height="2" fill="#1d2340"/>
  <rect x="11" y="2" width="2" height="2" fill="#1d2340"/>
  <rect x="5" y="2" width="6" height="2" fill="#5a6075"/>
  <rect x="2" y="4" width="1" height="8" fill="#1d2340"/>
  <rect x="13" y="4" width="1" height="8" fill="#1d2340"/>
  <rect x="3" y="4" width="10" height="8" fill="#434858"/>
  <rect x="7" y="5" width="2" height="3" fill="#1d2340"/>
  <rect x="6" y="8" width="3" height="1" fill="#1d2340"/>
  <rect x="8" y="9" width="1" height="2" fill="#1d2340"/>
  <rect x="3" y="12" width="2" height="2" fill="#1d2340"/>
  <rect x="11" y="12" width="2" height="2" fill="#1d2340"/>
  <rect x="5" y="12" width="6" height="2" fill="#303440"/>
  <rect x="5" y="14" width="6" height="1" fill="#1d2340"/>
</svg>
```
