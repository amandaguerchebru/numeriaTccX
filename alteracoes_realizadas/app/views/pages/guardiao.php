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

    // Se não há questão atual sorteada e o jogo continua
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
                        <span class="pixel-avatar-grande avatar-bico-armadura" aria-hidden="true"></span>
                    <?php else: ?>
                        <span class="pixel-avatar-grande avatar-guardiao-floresta" aria-hidden="true"></span>
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
                    
                    <!-- LADO DO JOGADOR (BICÓ COM ARMADURA) -->
                    <div class="lutador-card lutador-jogador">
                        <div class="lutador-avatar-wrap">
                            <span class="pixel-avatar-luta avatar-bico-armadura" aria-hidden="true"></span>
                        </div>
                        <div class="lutador-info">
                            <span class="badge-lutador">BICÓ GUERREIRO</span>
                            <h4 class="lutador-nome"><?= htmlspecialchars($_SESSION['aluno_nome'] ?? 'Herói do Reino', ENT_QUOTES, 'UTF-8') ?></h4>
                            
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

                    <!-- LADO DO GUARDIÃO DA FLORESTA -->
                    <div class="lutador-card lutador-guardiao">
                        <div class="lutador-avatar-wrap">
                            <span class="pixel-avatar-luta avatar-guardiao-floresta" aria-hidden="true"></span>
                        </div>
                        <div class="lutador-info">
                            <span class="badge-lutador badge-roxo">GUARDIÃO ANCESTRAL</span>
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
