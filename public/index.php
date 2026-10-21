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
