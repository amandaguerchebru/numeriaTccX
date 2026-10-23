<?php

require_once __DIR__ . '/../../config/conexao.php';

if (empty($_SESSION['aluno_id'])) {
    header('Location: ?pagina=login');
    exit;
}

$idAluno = (int) $_SESSION['aluno_id'];
$mensagem = $_SESSION['guardiao_mensagem'] ?? '';
unset($_SESSION['guardiao_mensagem']);
$mensagemTipo = $_SESSION['guardiao_tipo'] ?? '';
$explicacaoErro = $_SESSION['guardiao_explicacao'] ?? '';
unset($_SESSION['guardiao_tipo'], $_SESSION['guardiao_explicacao']);
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
        $_SESSION['guardiao_dificuldade']
    );
    header('Location: ?pagina=guardiao');
    exit;
}

$consultaModo = $conexao->prepare(
    'SELECT id_modo_jogo
     FROM modo_jogo
     WHERE nome = :nome
     LIMIT 1'
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
            'SELECT id_aluno
             FROM aluno
             WHERE id_aluno = :aluno
             FOR UPDATE'
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

        $consultaStatus = $conexao->prepare(
            "SELECT status
             FROM partida
             WHERE id_partida = :partida
               AND EXISTS (
                   SELECT 1
                   FROM partida_participante
                   WHERE partida_participante.id_partida = partida.id_partida
                     AND partida_participante.id_participante = :participante
                     AND partida_participante.id_aluno = :aluno
               )"
        );
        $consultaStatus->execute([
            'partida' => $idPartida,
            'participante' => $idParticipante,
            'aluno' => $idAluno
        ]);

        if ($consultaStatus->fetchColumn() !== 'em_andamento') {
            $conexao->commit();
            return null;
        }

        $consultaResumo = $conexao->prepare(
            'SELECT xp_total, acertos, erros
             FROM partida_participante
             WHERE id_participante = :participante
               AND id_partida = :partida
               AND id_aluno = :aluno'
        );
        $consultaResumo->execute([
            'participante' => $idParticipante,
            'partida' => $idPartida,
            'aluno' => $idAluno
        ]);
        $resumo = $consultaResumo->fetch(PDO::FETCH_ASSOC);

        $consultaContagemRespostas = $conexao->prepare(
            'SELECT COUNT(*) AS total,
                    COALESCE(SUM(acertou), 0) AS acertos,
                    COALESCE(SUM(1 - acertou), 0) AS erros,
                    COALESCE(SUM(xp_ganho), 0) AS xp
             FROM resposta
             WHERE id_participante = :participante'
        );
        $consultaContagemRespostas->execute([
            'participante' => $idParticipante
        ]);
        $contagemRespostas = $consultaContagemRespostas->fetch(PDO::FETCH_ASSOC);

        $consultaDificuldade = $conexao->prepare(
            'SELECT id_dificuldade
             FROM dificuldade
             WHERE id_dificuldade = :dificuldade'
        );
        $consultaDificuldade->execute([
            'dificuldade' => $idDificuldadeSessao
        ]);

        $consultaQuestoesDeOutraDificuldade = $conexao->prepare(
            'SELECT COUNT(*)
             FROM resposta r
             WHERE r.id_participante = :participante
               AND r.id_questao IN (
                   SELECT id_questao
                   FROM questao
                   WHERE id_dificuldade <> :dificuldade
                      OR id_jogo <> 1
               )'
        );
        $consultaQuestoesDeOutraDificuldade->execute([
            'participante' => $idParticipante,
            'dificuldade' => $idDificuldadeSessao
        ]);

        if (
            !$resumo
            || $consultaDificuldade->fetchColumn() === false
            || (int) $contagemRespostas['total'] !== $questoesSessao
            || (int) $contagemRespostas['acertos'] !== $acertosSessao
            || (int) $contagemRespostas['erros'] !== $errosSessao
            || (int) $contagemRespostas['xp'] !== $xpSessao
            || (int) $resumo['xp_total'] !== $xpSessao
            || (int) $resumo['acertos'] !== $acertosSessao
            || (int) $resumo['erros'] !== $errosSessao
            || (int) $consultaQuestoesDeOutraDificuldade->fetchColumn() > 0
        ) {
            $conexao->commit();
            return null;
        }

        $consultaImportaEstado = $conexao->prepare(
            'INSERT INTO guardiao_estado
                (id_participante, id_partida, id_dificuldade,
                 id_questao_atual, vidas_jogador, vidas_guardiao,
                 questoes_respondidas)
             VALUES
                (:participante, :partida, :dificuldade, NULL,
                 :vidas_jogador, :vidas_guardiao, :questoes)'
        );
        $consultaImportaEstado->execute([
            'participante' => $idParticipante,
            'partida' => $idPartida,
            'dificuldade' => $idDificuldadeSessao,
            'vidas_jogador' => $vidasJogadorSessao,
            'vidas_guardiao' => $vidasGuardiaoSessao,
            'questoes' => $questoesSessao
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
        throw $e;
    }
};

if ($idModo !== false) {
    $partidaEncontrada = $buscarPartidaAtiva(
        $conexao,
        $idAluno,
        (int) $idModo
    );
    if ($partidaEncontrada['sem_estado']) {
        $estadoImportado = $importarEstadoDaSessao(
            $conexao,
            $idAluno,
            $partidaEncontrada['partida']
        );
        if ($estadoImportado) {
            $partidaEncontrada['estado'] = $estadoImportado;
            $partidaEncontrada['sem_estado'] = false;
        }
    }
    $partidaSemEstado = $partidaEncontrada['sem_estado'];
    if ($partidaSemEstado) {
        $partidaLegadaId = (int) $partidaEncontrada['partida']['id_partida'];
    }

    if ($partidaEncontrada['estado']) {
        $idPartida = (int) $partidaEncontrada['partida']['id_partida'];
        $idParticipante = (int) $partidaEncontrada['partida']['id_participante'];
        $estado = $partidaEncontrada['estado'];
        $partidaAtiva = true;
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['iniciar'])
    && !$partidaAtiva
    && !$partidaSemEstado
) {
    $idDificuldade = filter_input(
        INPUT_POST,
        'id_dificuldade',
        FILTER_VALIDATE_INT
    );

    if ($idDificuldade === false || $idDificuldade === null) {
        $mensagem = 'Selecione uma dificuldade válida.';
    } else {
        $consultaDificuldade = $conexao->prepare(
            'SELECT id_dificuldade
             FROM dificuldade
             WHERE id_dificuldade = :id'
        );
        $consultaDificuldade->execute(['id' => $idDificuldade]);

        if ($consultaDificuldade->fetchColumn() === false) {
            $mensagem = 'Selecione uma dificuldade válida.';
        } elseif ($idModo === false) {
            $mensagem = 'O modo Contra o Guardião não foi cadastrado no banco.';
        } else {
            $consultaQuantidadeQuestoes = $conexao->prepare(
                'SELECT COUNT(*)
                 FROM questao q
                 WHERE q.id_jogo = 1
                   AND q.id_dificuldade = :dificuldade
                   AND q.ativo = 1
                   AND EXISTS (
                       SELECT 1
                       FROM alternativa a
                       WHERE a.id_questao = q.id_questao
                   )'
            );
            $consultaQuantidadeQuestoes->execute([
                'dificuldade' => $idDificuldade
            ]);

            if ((int) $consultaQuantidadeQuestoes->fetchColumn() < 10) {
                $mensagem = 'Esta dificuldade precisa ter pelo menos 10 questões com alternativas.';
            } else {
                $conexao->beginTransaction();

                try {
                    $consultaAluno = $conexao->prepare(
                        'SELECT id_aluno
                         FROM aluno
                         WHERE id_aluno = :aluno
                         FOR UPDATE'
                    );
                    $consultaAluno->execute(['aluno' => $idAluno]);

                    if ($consultaAluno->fetchColumn() === false) {
                        throw new RuntimeException('A conta do aluno não está disponível.');
                    }

                    $partidaEncontrada = $buscarPartidaAtiva(
                        $conexao,
                        $idAluno,
                        (int) $idModo
                    );

                    if ($partidaEncontrada['partida']) {
                        $conexao->commit();
                        $mensagem = $partidaEncontrada['sem_estado']
                            ? 'Há uma partida antiga em andamento sem estado recuperável. Ela não foi substituída.'
                            : 'Sua partida em andamento foi retomada.';
                    } else {
                        $consultaCriarPartida = $conexao->prepare(
                            "INSERT INTO partida
                                (id_jogo, id_modo_jogo, tempo_segundos, status)
                             VALUES
                                (1, :modo, 0, 'em_andamento')"
                        );
                        $consultaCriarPartida->execute([
                            'modo' => (int) $idModo
                        ]);
                        $idPartida = (int) $conexao->lastInsertId();

                        $consultaCriarParticipante = $conexao->prepare(
                            'INSERT INTO partida_participante
                                (id_partida, id_aluno, xp_total, acertos, erros)
                             VALUES
                                (:partida, :aluno, 0, 0, 0)'
                        );
                        $consultaCriarParticipante->execute([
                            'partida' => $idPartida,
                            'aluno' => $idAluno
                        ]);
                        $idParticipante = (int) $conexao->lastInsertId();

                        $consultaCriarEstado = $conexao->prepare(
                            'INSERT INTO guardiao_estado
                                (id_participante, id_partida, id_dificuldade,
                                 id_questao_atual, vidas_jogador, vidas_guardiao,
                                 questoes_respondidas)
                             VALUES
                                (:participante, :partida, :dificuldade,
                                 NULL, 3, 3, 0)'
                        );
                        $consultaCriarEstado->execute([
                            'participante' => $idParticipante,
                            'partida' => $idPartida,
                            'dificuldade' => $idDificuldade
                        ]);

                        $conexao->commit();
                        header('Location: ?pagina=guardiao');
                        exit;
                    }
                } catch (Throwable $e) {
                    if ($conexao->inTransaction()) {
                        $conexao->rollBack();
                    }
                    throw $e;
                }

                $partidaEncontrada = $buscarPartidaAtiva(
                    $conexao,
                    $idAluno,
                    (int) $idModo
                );
                $partidaSemEstado = $partidaEncontrada['sem_estado'];
                if ($partidaSemEstado) {
                    $partidaLegadaId = (int) $partidaEncontrada['partida']['id_partida'];
                }

                if ($partidaEncontrada['estado']) {
                    $idPartida = (int) $partidaEncontrada['partida']['id_partida'];
                    $idParticipante = (int) $partidaEncontrada['partida']['id_participante'];
                    $estado = $partidaEncontrada['estado'];
                    $partidaAtiva = true;
                }
            }
        }
    }
}

if ($partidaSemEstado) {
    $mensagem = 'Há uma partida antiga em andamento sem estado recuperável. Ela não foi substituída.';
}

if ($partidaAtiva) {
    $idDificuldade = (int) $estado['id_dificuldade'];
    $vidasJogador = (int) $estado['vidas_jogador'];
    $vidasGuardiao = (int) $estado['vidas_guardiao'];
    $totalQuestoes = (int) $estado['questoes_respondidas'];

    $consultaResumo = $conexao->prepare(
        'SELECT xp_total, acertos, erros
         FROM partida_participante
         WHERE id_participante = :participante
           AND id_partida = :partida
           AND id_aluno = :aluno'
    );
    $consultaResumo->execute([
        'participante' => $idParticipante,
        'partida' => $idPartida,
        'aluno' => $idAluno
    ]);
    $resumo = $consultaResumo->fetch(PDO::FETCH_ASSOC);

    if (!$resumo) {
        throw new RuntimeException('Não foi possível localizar os dados do participante da partida.');
    }

    $xpTotal = (int) $resumo['xp_total'];
    $acertos = (int) $resumo['acertos'];
    $erros = (int) $resumo['erros'];
    $fimDeJogo = $vidasJogador <= 0 || $totalQuestoes >= 0;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['responder']) && !$fimDeJogo) {
        $idQuestaoEnviada = filter_input(
            INPUT_POST,
            'id_questao',
            FILTER_VALIDATE_INT
        );
        $idAlternativa = filter_input(
            INPUT_POST,
            'id_alternativa',
            FILTER_VALIDATE_INT
        );

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
            $estadoBloqueado = $consultaEstadoBloqueado->fetch(PDO::FETCH_ASSOC);

            $consultaStatusPartida = $conexao->prepare(
                "SELECT status
                 FROM partida
                 WHERE id_partida = :partida
                   AND EXISTS (
                       SELECT 1
                       FROM partida_participante
                       WHERE partida_participante.id_partida = partida.id_partida
                         AND partida_participante.id_participante = :participante
                         AND partida_participante.id_aluno = :aluno
                   )"
            );
            $consultaStatusPartida->execute([
                'partida' => $idPartida,
                'participante' => $idParticipante,
                'aluno' => $idAluno
            ]);
            $statusPartida = $consultaStatusPartida->fetchColumn();

            if (
                !$estadoBloqueado
                || $statusPartida !== 'em_andamento'
                || $idQuestaoEnviada === false
                || $idQuestaoEnviada === null
                || $idAlternativa === false
                || $idAlternativa === null
                || (int) $idQuestaoEnviada !== (int) $estadoBloqueado['id_questao_atual']
            ) {
                $mensagem = 'Esta resposta já foi processada ou não corresponde à questão atual.';
                $conexao->commit();
            } else {
                $idDificuldade = (int) $estadoBloqueado['id_dificuldade'];
                $consultaRespostaValida = $conexao->prepare(
                    'SELECT correta
                     FROM alternativa
                     WHERE id_alternativa = :alternativa
                       AND id_questao = :questao
                       AND EXISTS (
                           SELECT 1
                           FROM questao
                           WHERE questao.id_questao = alternativa.id_questao
                             AND questao.id_jogo = 1
                             AND questao.id_dificuldade = :dificuldade
                             AND questao.ativo = 1
                       )'
                );
                $consultaRespostaValida->execute([
                    'alternativa' => $idAlternativa,
                    'questao' => $idQuestaoEnviada,
                    'dificuldade' => $idDificuldade
                ]);
                $correta = $consultaRespostaValida->fetchColumn();

                if ($correta === false) {
                    $mensagem = 'Questão ou alternativa inválida.';
                    $conexao->commit();
                } else {
                    $acertouResposta = (int) $correta === 1;
                    $xpGanho = 0;

                    if ($acertouResposta) {
                        $consultaXP = $conexao->prepare(
                            'SELECT xp_por_acerto
                             FROM dificuldade
                             WHERE id_dificuldade = :dificuldade'
                        );
                        $consultaXP->execute(['dificuldade' => $idDificuldade]);
                        $xpConfigurado = $consultaXP->fetchColumn();

                        if ($xpConfigurado === false) {
                            throw new RuntimeException('O XP desta dificuldade não está configurado.');
                        }

                        $xpGanho = (int) $xpConfigurado;
                    }

                    $novasVidasJogador = max(
                        0,
                        (int) $estadoBloqueado['vidas_jogador']
                            - ($acertouResposta ? 0 : 1)
                    );
                    $novasVidasGuardiao = max(
                        0,
                        (int) $estadoBloqueado['vidas_guardiao']
                            - ($acertouResposta ? 1 : 0)
                    );
                    $novoTotalQuestoes = (int) $estadoBloqueado['questoes_respondidas'] + 1;
                    $novoAcerto = $acertouResposta ? 1 : 0;
                    $novoErro = $acertouResposta ? 0 : 1;

                    $consultaRegistro = $conexao->prepare(
                        'INSERT INTO resposta
                            (id_participante, id_questao, id_alternativa, acertou, xp_ganho)
                         VALUES
                            (:participante, :questao, :alternativa, :acertou, :xp)'
                    );
                    $consultaRegistro->execute([
                        'participante' => $idParticipante,
                        'questao' => $idQuestaoEnviada,
                        'alternativa' => $idAlternativa,
                        'acertou' => $novoAcerto,
                        'xp' => $xpGanho
                    ]);

                    $consultaAtualizaResumo = $conexao->prepare(
                        'UPDATE partida_participante
                         SET xp_total = xp_total + :xp,
                             acertos = acertos + :acertos,
                             erros = erros + :erros
                         WHERE id_participante = :participante
                           AND id_partida = :partida
                           AND id_aluno = :aluno'
                    );
                    $consultaAtualizaResumo->execute([
                        'xp' => $xpGanho,
                        'acertos' => $novoAcerto,
                        'erros' => $novoErro,
                        'participante' => $idParticipante,
                        'partida' => $idPartida,
                        'aluno' => $idAluno
                    ]);

                    $fimResposta = $novasVidasJogador <= 0 || $novoTotalQuestoes >= 10;
                    $idProximaQuestao = null;

                    if (!$fimResposta) {
                        $consultaProximaQuestao = $conexao->prepare(
                            'SELECT q.id_questao
                             FROM questao q
                             WHERE q.id_jogo = 1
                               AND q.id_dificuldade = :dificuldade
                               AND q.ativo = 1
                               AND NOT EXISTS (
                                   SELECT 1
                                   FROM resposta r
                                   WHERE r.id_participante = :participante
                                     AND r.id_questao = q.id_questao
                               )
                               AND EXISTS (
                                   SELECT 1
                                   FROM alternativa a
                                   WHERE a.id_questao = q.id_questao
                               )
                             ORDER BY RAND()
                             LIMIT 1'
                        );
                        $consultaProximaQuestao->execute([
                            'dificuldade' => $idDificuldade,
                            'participante' => $idParticipante
                        ]);
                        $proximaQuestao = $consultaProximaQuestao->fetchColumn();

                        if ($proximaQuestao !== false) {
                            $idProximaQuestao = (int) $proximaQuestao;
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
                            "UPDATE partida
                             SET status = 'finalizada',
                                 data_fim = NOW()
                             WHERE id_partida = :partida
                               AND status = 'em_andamento'"
                        );
                        $consultaFinaliza->execute(['partida' => $idPartida]);
                    }

                    $conexao->commit();

                    if ($novasVidasGuardiao <= 0) {
                        $mensagem = 'Incrível! Você acertou 3 vezes e derrotou o Guardião Ancestral! Ganhou ' . $xpGanho . ' XP!';
                    } else {
                        $mensagem = $acertouResposta
                            ? 'Resposta correta! O Guardião foi atingido e você ganhou ' . $xpGanho . ' XP!'
                            : 'Ops, talvez não seja essa a resposta correta… Você perdeu uma vida, mas tudo bem errar!';
                    }

                    $_SESSION['guardiao_tipo'] = $acertouResposta ? 'acerto' : 'erro';
                    unset($_SESSION['guardiao_explicacao']);

                    // Explicação didática e imediata, só quando erra.
                    if (!$acertouResposta) {
                        $consultaExplicacao = $conexao->prepare(
                            'SELECT explicacao_curta
                             FROM questao
                             WHERE id_questao = :questao'
                        );
                        $consultaExplicacao->execute(['questao' => $idQuestaoEnviada]);
                        $_SESSION['guardiao_explicacao'] = (string) $consultaExplicacao->fetchColumn();
                    }
                }
            }
        } catch (Throwable $e) {
            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }
            throw $e;
        }

        $_SESSION['guardiao_mensagem'] = $mensagem;
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
    $fimDeJogo = $vidasJogador <= 0 || $vidasGuardiao <= 0 || $totalQuestoes >= 10;

    if (!$fimDeJogo && $estado['id_questao_atual'] === null) {
        $conexao->beginTransaction();

        try {
            $consultaEstadoBloqueado = $conexao->prepare(
                'SELECT id_dificuldade, id_questao_atual
                 FROM guardiao_estado
                 WHERE id_participante = :participante
                   AND id_partida = :partida
                 FOR UPDATE'
            );
            $consultaEstadoBloqueado->execute([
                'participante' => $idParticipante,
                'partida' => $idPartida
            ]);
            $estadoBloqueado = $consultaEstadoBloqueado->fetch(PDO::FETCH_ASSOC);

            if ($estadoBloqueado['id_questao_atual'] === null) {
                $consultaQuestaoAtual = $conexao->prepare(
                    'SELECT q.id_questao
                     FROM questao q
                     WHERE q.id_jogo = 1
                       AND q.id_dificuldade = :dificuldade
                       AND q.ativo = 1
                       AND NOT EXISTS (
                           SELECT 1
                           FROM resposta r
                           WHERE r.id_participante = :participante
                             AND r.id_questao = q.id_questao
                       )
                       AND EXISTS (
                           SELECT 1
                           FROM alternativa a
                           WHERE a.id_questao = q.id_questao
                       )
                     ORDER BY RAND()
                     LIMIT 1'
                );
                $consultaQuestaoAtual->execute([
                    'dificuldade' => (int) $estadoBloqueado['id_dificuldade'],
                    'participante' => $idParticipante
                ]);
                $idQuestaoAtual = $consultaQuestaoAtual->fetchColumn();

                if ($idQuestaoAtual !== false) {
                    $consultaSalvaQuestao = $conexao->prepare(
                        'UPDATE guardiao_estado
                         SET id_questao_atual = :questao
                         WHERE id_participante = :participante
                           AND id_partida = :partida'
                    );
                    $consultaSalvaQuestao->execute([
                        'questao' => (int) $idQuestaoAtual,
                        'participante' => $idParticipante,
                        'partida' => $idPartida
                    ]);
                }
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
            'SELECT enunciado
             FROM questao
             WHERE id_questao = :questao
               AND id_jogo = 1
               AND id_dificuldade = :dificuldade
               AND ativo = 1'
        );
        $consultaEnunciado->execute([
            'questao' => (int) $estado['id_questao_atual'],
            'dificuldade' => (int) $estado['id_dificuldade']
        ]);
        $enunciado = $consultaEnunciado->fetchColumn();

        if ($enunciado !== false) {
            $questao = [
                'id_questao' => (int) $estado['id_questao_atual'],
                'enunciado' => $enunciado
            ];
            $consultaAlternativas = $conexao->prepare(
                'SELECT id_alternativa, texto
                 FROM alternativa
                 WHERE id_questao = :questao
                 ORDER BY id_alternativa'
            );
            $consultaAlternativas->execute([
                'questao' => (int) $estado['id_questao_atual']
            ]);
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
    $fimDeJogo = $vidasJogador <= 0 || $totalQuestoes >= 10;

    if ($fimDeJogo) {
        if ($vidasJogador <= 0) {
            $resultado = 'Fim da batalha! Você perdeu todas as vidas após '
                . $totalQuestoes . ' desafios.';
        } elseif ($acertos > $erros) {
            $resultado = '🏆 Vitória! Você superou os desafios do Guardião da Floresta!';
        } elseif ($erros > $acertos) {
            $resultado = '🛡️ Fim da batalha! O Guardião resistiu desta vez, mas você completou os desafios!';
        } else {
            $resultado = '🤝 Empate heróico! Você e o Guardião mostraram grande equilíbrio matemático!';
        }
    }
}

?>

<main class="container">
    <section class="hero">
        <span class="badge">Batalha na Floresta</span>
        <h2>Contra o Guardião</h2>

        <?php if ($mensagem !== ''): ?>
            <div role="status" class="feedback-msg <?= ($mensagemTipo === 'acerto') ? 'feedback-sucesso' : 'feedback-erro' ?>">
                <div class="bico-feedback">
                    <span class="bico-icone" aria-hidden="true"></span>
                    <div>
                        <p><strong><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></strong></p>
                        <?php if (!empty($explicacaoErro)): ?>
                            <p class="explicacao">
                                <strong>Bicó explica:</strong>
                                <?= htmlspecialchars($explicacaoErro, ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($partidaSemEstado): ?>

            <p>
                Há uma partida antiga em andamento sem estado recuperável.
                Ela não foi substituída; conclua ou abandone essa partida antes de iniciar outra.
            </p>
            <a
                href="?pagina=abandonar_guardiao&id_partida=<?= $partidaLegadaId ?>"
                class="btn btn-secondary">
                Abandonar esta partida antiga
            </a>

        <?php elseif (!$partidaAtiva): ?>

            <p>Escolha a dificuldade para iniciar a batalha.</p>

            <?php
            $consultaDificuldades = $conexao->query(
                'SELECT id_dificuldade, nome
                 FROM dificuldade
                 ORDER BY id_dificuldade'
            );
            $dificuldades = $consultaDificuldades->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <form method="POST">
                <label for="id_dificuldade">Dificuldade</label>
                <select name="id_dificuldade" id="id_dificuldade" required>
                    <option value="">Selecione</option>
                    <?php foreach ($dificuldades as $dificuldade): ?>
                        <option value="<?= (int) $dificuldade['id_dificuldade'] ?>">
                            <?= htmlspecialchars($dificuldade['nome'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" name="iniciar" value="1">
                    Iniciar batalha
                </button>
            </form>

        <?php elseif ($fimDeJogo): ?>

            <h3><?= htmlspecialchars($resultado ?? 'Batalha encerrada', ENT_QUOTES, 'UTF-8') ?></h3>

            <p>Questões respondidas: <?= $totalQuestoes ?> de 10</p>
            <p>Acertos: <?= $acertos ?></p>
            <p>Erros: <?= $erros ?></p>
            <p>XP conquistado: <?= $xpTotal ?></p>

            <a href="?pagina=guardiao&novo=1" class="btn">
                Jogar novamente
            </a>

            <a href="?pagina=batalha" class="btn">
                Voltar aos modos
            </a>

        <?php else: ?>

            <?php
            $nomeJogador = $_SESSION['aluno_nome'] ?? 'Jogador';
            $hudDesafio = min(10, $totalQuestoes + 1);
            ?>
            <section class="arena" aria-label="Placar da batalha">
                <div class="arena-lutadores">
                    <?php
                    $lt_tipo = 'bico';
                    $lt_nome = $nomeJogador;
                    $lt_papel = 'Bicó guerreiro';
                    $lt_vidas = $vidasJogador;
                    $lt_max = 3;
                    $lt_classe = 'lutador-jogador' . ($mensagemTipo === 'erro' ? ' atingido' : '');
                    $lt_vez = false;
                    include __DIR__ . '/../partials/lutador.php';
                    ?>

                    <div class="arena-vs" aria-hidden="true">VS</div>

                    <?php
                    $lt_tipo = 'guardiao';
                    $lt_nome = 'Guardião';
                    $lt_papel = 'da Floresta';
                    $lt_vidas = $vidasGuardiao;
                    $lt_max = 3;
                    $lt_classe = 'lutador-guardiao' . ($mensagemTipo === 'acerto' ? ' atingido' : '');
                    include __DIR__ . '/../partials/lutador.php';
                    ?>
                </div>

                <div class="desempenho" role="group" aria-label="Seu desempenho nesta batalha">
                    <div class="medidor">
                        <span class="medidor-rotulo">Desafio</span>
                        <strong class="medidor-valor"><?= $hudDesafio ?><small>/10</small></strong>
                    </div>
                    <div class="medidor medidor-acerto">
                        <span class="medidor-rotulo">Acertos</span>
                        <strong class="medidor-valor"><?= $acertos ?></strong>
                    </div>
                    <div class="medidor medidor-erro">
                        <span class="medidor-rotulo">Erros</span>
                        <strong class="medidor-valor"><?= $erros ?></strong>
                    </div>
                    <div class="medidor medidor-xp">
                        <span class="medidor-rotulo">XP</span>
                        <strong class="medidor-valor"><?= $xpTotal ?></strong>
                    </div>
                </div>

                <progress value="<?= $totalQuestoes ?>" max="10" aria-label="Progresso da batalha"></progress>
            </section>

            <?php if ($questao): ?>
                <article class="card">
                    <h3><?= htmlspecialchars($questao['enunciado'], ENT_QUOTES, 'UTF-8') ?></h3>

                    <?php include __DIR__ . '/../partials/dica_bico.php'; ?>
                    <?php if ($alternativas): ?>
                        <form method="POST">
                            <input type="hidden" name="id_questao" value="<?= (int) $questao['id_questao'] ?>">

                            <?php foreach ($alternativas as $alternativa): ?>
                                <label>
                                    <input
                                        type="radio"
                                        name="id_alternativa"
                                        value="<?= (int) $alternativa['id_alternativa'] ?>"
                                        required>
                                    <?= htmlspecialchars($alternativa['texto'], ENT_QUOTES, 'UTF-8') ?>
                                </label>
                                <br>
                            <?php endforeach; ?>

                            <button type="submit" name="responder" value="1">
                                Responder
                            </button>
                        </form>
                    <?php else: ?>
                        <p>Esta questão não possui alternativas.</p>
                    <?php endif; ?>
                </article>
            <?php else: ?>
                <p>Não há uma questão disponível para continuar. Seu progresso foi preservado.</p>
            <?php endif; ?>

        <?php endif; ?>

        <?php if ($partidaAtiva && !$fimDeJogo): ?>
            <p><a href="?pagina=abandonar_guardiao&id_partida=<?= $idPartida ?>" class="btn btn-secondary" data-confirmar="Quer mesmo abandonar a batalha? Seu progresso desta partida não será continuado.">Abandonar batalha</a></p>
        <?php else: ?>
            <p><a href="?pagina=batalha">Voltar à Batalha na Floresta</a></p>
        <?php endif; ?>
    </section>
</main>