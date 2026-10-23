<?php

/**
 * Funções de apoio para diferenciar aluno e professor.
 */

/**
 * Garante que a tabela `aluno` tenha a coluna `tipo`.
 * Se ainda não existir, cria (todos os cadastros antigos viram "aluno").
 */
function garantir_coluna_tipo(PDO $conexao): void
{
    static $verificado = false;

    if ($verificado) {
        return;
    }

    $consulta = $conexao->query("SHOW COLUMNS FROM aluno LIKE 'tipo'");

    if (!$consulta->fetch()) {
        $conexao->exec(
            "ALTER TABLE aluno
             ADD COLUMN tipo ENUM('aluno', 'professor') NOT NULL DEFAULT 'aluno'
             AFTER email"
        );
    }

    $verificado = true;
}

function eh_professor(): bool
{
    return ($_SESSION['aluno_tipo'] ?? 'aluno') === 'professor';
}

/** Mensagem de incentivo do Bicó conforme a posição no ranking. */
function frase_do_bico(int $posicao, int $total, ?string $acima, int $faltam): string
{
    if ($total <= 1) {
        return 'Você abriu o caminho! Chame os colegas para jogar e disputar o ranking.';
    }

    if ($posicao === 1) {
        return 'Você está no topo do reino! Continue assim e ajude os colegas com o que aprendeu.';
    }

    $nomeAcima = $acima !== null ? $acima : 'o aluno da frente';

    if ($faltam <= 0) {
        return "Você empatou com {$nomeAcima}! Mais uma partida e você passa à frente.";
    }

    return "Faltam só {$faltam} XP para alcançar {$nomeAcima}. Cada tentativa é um treino. Vamos lá!";
}

/** Texto e classe do selo de aproveitamento (pontos de atenção). */
function selo_aproveitamento(int $acertos, int $erros): array
{
    $respondidas = $acertos + $erros;

    if ($respondidas < 5) {
        return ['Poucos dados', 'selo-neutro', null];
    }

    $percentual = (int) round($acertos * 100 / $respondidas);

    if ($percentual >= 75) {
        return ['Ótimo ritmo', 'selo-bom', $percentual];
    }

    if ($percentual >= 50) {
        return ['Em progresso', 'selo-medio', $percentual];
    }

    return ['Precisa de apoio', 'selo-atencao', $percentual];
}

/**
 * Ranking de todos os alunos ativos (professores não entram).
 * Empates no XP dividem a mesma posição.
 */
function buscar_ranking(PDO $conexao): array
{
    garantir_coluna_tipo($conexao);

    $consulta = $conexao->query(
        "SELECT
            a.id_aluno,
            a.nome,
            a.email,
            COALESCE(SUM(pp.xp_total), 0) AS xp,
            COALESCE(SUM(pp.acertos), 0) AS acertos,
            COALESCE(SUM(pp.erros), 0) AS erros,
            COUNT(DISTINCT CASE WHEN p.status = 'finalizada' THEN pp.id_partida END) AS partidas
         FROM aluno a
         LEFT JOIN partida_participante pp ON pp.id_aluno = a.id_aluno
         LEFT JOIN partida p ON p.id_partida = pp.id_partida
         WHERE a.ativo = 1
           AND a.tipo = 'aluno'
         GROUP BY a.id_aluno, a.nome, a.email
         ORDER BY xp DESC, acertos DESC, a.nome ASC"
    );

    $alunos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    $contador = 0;
    $posicao = 0;
    $xpAnterior = null;

    foreach ($alunos as &$aluno) {
        $aluno['id_aluno'] = (int) $aluno['id_aluno'];
        $aluno['xp'] = (int) $aluno['xp'];
        $aluno['acertos'] = (int) $aluno['acertos'];
        $aluno['erros'] = (int) $aluno['erros'];
        $aluno['partidas'] = (int) $aluno['partidas'];

        $contador++;

        if ($aluno['xp'] !== $xpAnterior) {
            $posicao = $contador;
        }

        $aluno['posicao'] = $posicao;
        $xpAnterior = $aluno['xp'];
    }
    unset($aluno);

    return $alunos;
}

/** Aproveitamento em % (null quando ainda não há respostas). */
function aproveitamento(int $acertos, int $erros): ?int
{
    $total = $acertos + $erros;

    return $total > 0 ? (int) round($acertos * 100 / $total) : null;
}
