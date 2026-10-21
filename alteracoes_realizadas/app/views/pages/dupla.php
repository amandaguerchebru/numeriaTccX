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
$vencedorAvatar = 'bico';

if ($partidaAtiva) {
    $idP1 = (int) $_SESSION['dupla_p1_participante_id'];
    $idP2 = (int) $_SESSION['dupla_p2_participante_id'];
    $idPartida = (int) $_SESSION['dupla_partida_id'];
    $idDificuldade = (int) $_SESSION['dupla_dificuldade'];
    $nomeP1 = $_SESSION['dupla_p1_nome'] ?? ($_SESSION['aluno_nome'] ?? 'Bicó');
    $nomeP2 = $_SESSION['dupla_p2_nome'] ?? 'Oncinha';
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
        $papelVez = ($turnoAtual === 1) ? 'Bicó' : 'Oncinha';

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
            $vencedorTexto = "🏆 Vitória de {$nomeP1} (Bicó Guerreiro)!";
            $vencedorAvatar = 'bico';
        } elseif ($acertos2 > $acertos1) {
            $vencedorTexto = "🏆 Vitória de {$nomeP2} (A Oncinha)!";
            $vencedorAvatar = 'oncinha';
        } else {
            $vencedorTexto = "🤝 Empate Mágico! Bicó e Oncinha mostraram perfeita harmonia!";
            $vencedorAvatar = 'ambos';
        }
    }
}
?>

<main class="container">
    <section class="hero">
        <span class="badge">Batalha na Floresta · Modo Dupla Local</span>
        <h2>Duelo na Floresta (Bicó Guerreiro vs Oncinha)</h2>
        <p>Bicó com armadura (jogador logado) e a Oncinha (sua duplinha) em desafios de matemática no mesmo computador!</p>

        <!-- FEEDBACK DO BICÓ -->
        <?php if ($mensagem !== ''): ?>
            <?= renderBicoFeedbackHtml($mensagem, $mensagemTipo, $explicacao) ?>
        <?php endif; ?>

        <?php if (!$partidaAtiva): ?>

            <article class="card">
                <h3>Configurar Duelo em Dupla</h3>
                <p>O <strong>Jogador 1</strong> é representado pelo Bicó com Armadura (usuário logado) e o <strong>Jogador 2</strong> é a Oncinha, a duplinha que entra para jogar junto!</p>

                <?php
                $consultaDificuldades = $conexao->query(
                    'SELECT id_dificuldade, nome, xp_por_acerto FROM dificuldade ORDER BY id_dificuldade'
                );
                $dificuldades = $consultaDificuldades->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <form method="POST">
                    <div style="margin-bottom: 20px;">
                        <label>
                            <strong>Jogador 1 (Você) — Bicó com Armadura:</strong><br>
                            <div class="input-com-avatar">
                                <span class="pixel-avatar-preview avatar-bico-armadura" aria-hidden="true"></span>
                                <input type="text" value="<?= htmlspecialchars($_SESSION['aluno_nome'] ?? 'Jogador 1', ENT_QUOTES, 'UTF-8') ?>" disabled>
                            </div>
                        </label>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label for="apelido_jogador2">
                            <strong>Jogador 2 — Oncinha (Duplinha de Aventuras):</strong><br>
                            <div class="input-com-avatar">
                                <span class="pixel-avatar-preview avatar-oncinha" aria-hidden="true"></span>
                                <input
                                    type="text"
                                    name="apelido_jogador2"
                                    id="apelido_jogador2"
                                    placeholder="Apelido da duplinha (Oncinha)"
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
                    <?php if ($vencedorAvatar === 'bico'): ?>
                        <span class="pixel-avatar-grande avatar-bico-armadura" aria-hidden="true"></span>
                    <?php elseif ($vencedorAvatar === 'oncinha'): ?>
                        <span class="pixel-avatar-grande avatar-oncinha" aria-hidden="true"></span>
                    <?php else: ?>
                        <div class="dupla-empate-avatars">
                            <span class="pixel-avatar-grande avatar-bico-armadura" aria-hidden="true"></span>
                            <span class="pixel-avatar-grande avatar-oncinha" aria-hidden="true"></span>
                        </div>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($vencedorTexto, ENT_QUOTES, 'UTF-8') ?></h3>
                </div>
                <p>O duelo de matemática na floresta foi concluído! O Bicó parabeniza os dois heróis:</p>

                <div class="grid" style="margin-top: 20px;">
                    <article class="card">
                        <div class="card-avatar-topo">
                            <span class="pixel-avatar-preview avatar-bico-armadura" aria-hidden="true"></span>
                            <h4><?= htmlspecialchars($nomeP1, ENT_QUOTES, 'UTF-8') ?> (Bicó Guerreiro)</h4>
                        </div>
                        <p>Questões: <?= (int) $_SESSION['dupla_p1_questoes'] ?> de 10</p>
                        <p class="destaque-numero" style="color: var(--cor-acerto) !important;">✅ <?= (int) $_SESSION['dupla_p1_acertos'] ?> acertos</p>
                        <p>Erros: <?= (int) $_SESSION['dupla_p1_erros'] ?></p>
                        <p>XP: <strong>⭐ <?= (int) $_SESSION['dupla_p1_xp'] ?> XP</strong></p>
                    </article>

                    <article class="card">
                        <div class="card-avatar-topo">
                            <span class="pixel-avatar-preview avatar-oncinha" aria-hidden="true"></span>
                            <h4><?= htmlspecialchars($nomeP2, ENT_QUOTES, 'UTF-8') ?> (Oncinha)</h4>
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
            $papelAtual = ($turno === 1) ? 'Bicó' : 'Oncinha';
            $classeAvatarAtual = ($turno === 1) ? 'avatar-bico-armadura' : 'avatar-oncinha';
            ?>

            <!-- PLACAR EM TEMPO REAL COM AVATARES EM PIXEL ART -->
            <div class="grid dupla-placar-grid" style="margin-bottom: 24px;">
                <article class="card card-jogador <?= ($turno === 1) ? 'turno-ativo' : '' ?>">
                    <div class="card-avatar-topo">
                        <span class="pixel-avatar-preview avatar-bico-armadura" aria-hidden="true"></span>
                        <div>
                            <h4><?= htmlspecialchars($nomeP1, ENT_QUOTES, 'UTF-8') ?></h4>
                            <span class="badge-personagem">Bicó Guerreiro</span>
                        </div>
                    </div>
                    <p>Desafio: <strong><?= (int) $_SESSION['dupla_p1_questoes'] ?>/10</strong></p>
                    <p>Acertos: <strong><?= (int) $_SESSION['dupla_p1_acertos'] ?></strong> | XP: ⭐ <strong><?= (int) $_SESSION['dupla_p1_xp'] ?></strong></p>
                    <?php if ($turno === 1): ?>
                        <div class="badge-vez">⚔️ SUA VEZ DE ATACAR!</div>
                    <?php endif; ?>
                </article>

                <article class="card card-jogador <?= ($turno === 2) ? 'turno-ativo' : '' ?>">
                    <div class="card-avatar-topo">
                        <span class="pixel-avatar-preview avatar-oncinha" aria-hidden="true"></span>
                        <div>
                            <h4><?= htmlspecialchars($nomeP2, ENT_QUOTES, 'UTF-8') ?></h4>
                            <span class="badge-personagem badge-roxo">Oncinha</span>
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
