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
    // Ex: "Uma poção foi dividida em 8 partes iguais. 3 partes receberam essência lunar..."
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
    // Ex: "A Poção da Lua contém 3/4 de essência e a Poção da Folha contém 2/3..."
    if (preg_match('/(Poção [\w\s]+?) contém (\d+\/\d+).*?(Poção [\w\s]+?) contém (\d+\/\d+)/ui', $enunciado, $m)) {
        return [
            'tipo' => 'comparacao',
            'pocao1_nome' => trim($m[1]),
            'pocao1_fracao' => trim($m[2]),
            'pocao2_nome' => trim($m[3]),
            'pocao2_fracao' => trim($m[4])
        ];
    }

    // Padrão genérico se o regex não bater
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
