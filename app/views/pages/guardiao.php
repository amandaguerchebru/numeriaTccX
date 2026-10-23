<?php

require_once __DIR__ . '/../../config/conexao.php';

if (empty($_SESSION['aluno_id'])) {
    header('Location: ?pagina=login');
    exit;
}

$idAluno = (int)$_SESSION['aluno_id'];
$mensagem =$_SESSION['guardiao_mensagem'] ?? '';
unset($_SESSION['guardiao_mensagem']);
$mensagemTipo =$_SESSION['guardiao_tipo'] ?? '';
$explicacaoErro =$_SESSION['guardiao_explicacao'] ?? '';
unset($_SESSION['guardiao_tipo'], $_SESSION['guardiao_explicacao']);$resultado = null;
$partidaAtiva = false;
$partidaSemEstado = false;
$partidaLegadaId = 0;
$idPartida = 0;
$idParticipante = 0;
$estado = null;
$questao = null;
$alternativas = [];$vidasJogador = 3;
$vidasGuardiao = 3;
$totalQuestoes = 0;
$acertos = 0;
$erros = 0;
$xpTotal = 0;
$fimDeJogo = false;

if (isset($_GET['novo'])) {
    unset(
        $_SESSION['guardiao_partida_id'],
        $_SESSION['guardiao_participante_id'],$_SESSION['guardiao_vidas_jogador'],
        $_SESSION['guardiao_vidas_guardiao'],$_SESSION['guardiao_questoes'],
        $_SESSION['guardiao_xp'],$_SESSION['guardiao_acertos'],
        $_SESSION['guardiao_erros'],$_SESSION['guardiao_dificuldade']
    );
    header('Location: ?pagina=guardiao');
    exit;
}

$consultaModo = $conexao->prepare( 'SELECT id_modo_jogo FROM modo_jogo WHERE nome = :nome LIMIT 1' );$consultaModo->execute(['nome' => 'Contra o Guardião']);
$idModo =$consultaModo->fetchColumn();

$buscarPartidaAtiva = static function (
    PDO $conexao,
    int $idAluno,
    int $idModo
): array {
    $consultaPartidas =$conexao->prepare(
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

    $partidas = $consultaPartidas->fetchAll(PDO::FETCH_ASSOC);$partidaSemEstado = null;

    foreach ($partidas as $partida) {$consultaEstado = $conexao->prepare( 'SELECT id_participante, id_partida, id_dificuldade, id_questao_atual, vidas_jogador, vidas_guardiao, questoes_respondidas FROM guardiao_estado WHERE id_participante = :participante AND id_partida = :partida' );$consultaEstado->execute([
            'participante' => (int) $partida['id_participante'],
            'partida' => (int) $partida['id_partida']
        ]);