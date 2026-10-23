<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../helpers/usuario.php';

$idAtual = (int) ($_SESSION['aluno_id'] ?? 0);
$souProfessor = eh_professor();

$ranking = buscar_ranking($conexao);
$totalAlunos = count($ranking);
$podio = array_slice($ranking, 0, 3);

// Onde eu estou e quanto falta para o próximo.
$minhaLinha = null;
$minhaPosicaoNaLista = null;

foreach ($ranking as $indice => $linha) {
    if ($linha['id_aluno'] === $idAtual) {
        $minhaLinha = $linha;
        $minhaPosicaoNaLista = $indice;
        break;
    }
}

$mensagemBico = '';

if ($minhaLinha !== null) {
    $nomeAcima = null;
    $faltam = 0;

    for ($i = $minhaPosicaoNaLista - 1; $i >= 0; $i--) {
        if ($ranking[$i]['xp'] > $minhaLinha['xp']) {
            $nomeAcima = $ranking[$i]['nome'];
            $faltam = $ranking[$i]['xp'] - $minhaLinha['xp'];
            break;
        }
    }

    $mensagemBico = frase_do_bico($minhaLinha['posicao'], $totalAlunos, $nomeAcima, $faltam);
}

$medalhas = [1 => 'ouro', 2 => 'prata', 3 => 'bronze'];
?>

<main class="container">
    <section class="hero">
        <span class="badge">Reino de Numéria</span>
        <h2>Ranking dos aventureiros</h2>
        <p>
            Quem juntou mais XP nas batalhas e nas poções? Aqui aparecem todos os
            alunos cadastrados, do primeiro ao último lugar.
        </p>

        <?php if ($souProfessor): ?>
            <p class="ajuda-campo">
                Você está vendo como professor(a): só alunos entram no ranking.
                Para ver os detalhes de cada um, abra o
                <a href="?pagina=professor">painel do professor</a>.
            </p>
        <?php endif; ?>

        <?php if ($minhaLinha !== null): ?>
            <div role="status" class="feedback-msg feedback-sucesso">
                <div class="bico-feedback">
                    <span class="bico-icone" aria-hidden="true"></span>
                    <div>
                        <p>
                            <strong>
                                Você está em <?= (int) $minhaLinha['posicao'] ?>º lugar,
                                com <?= number_format($minhaLinha['xp'], 0, ',', '.') ?> XP!
                            </strong>
                        </p>
                        <p><?= htmlspecialchars($mensagemBico, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($totalAlunos === 0): ?>
            <div class="feedback-msg feedback-erro">
                <div class="bico-feedback">
                    <span class="bico-icone" aria-hidden="true"></span>
                    <div>
                        <p><strong>Ainda não há alunos no ranking.</strong></p>
                        <p>Seja o primeiro a jogar e conquistar XP!</p>
                    </div>
                </div>
            </div>
        <?php else: ?>

            <!-- Pódio: segundo, primeiro e terceiro lugar -->
            <ol class="podio" aria-label="Os três primeiros colocados">
                <?php foreach ($podio as $linha): ?>
                    <?php $lugar = min(3, (int) $linha['posicao']); ?>
                    <li class="podio-<?= $lugar ?><?= $linha['id_aluno'] === $idAtual ? ' podio-voce' : '' ?>">
                        <img class="podio-medalha"
                             src="<?= htmlspecialchars(asset('assets/img/medalha-' . $medalhas[$lugar] . '.svg'), ENT_QUOTES, 'UTF-8') ?>"
                             alt="Medalha de <?= $medalhas[$lugar] ?>">
                        <strong class="podio-nome"><?= htmlspecialchars($linha['nome'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span class="podio-xp"><?= number_format($linha['xp'], 0, ',', '.') ?> XP</span>
                        <span class="podio-lugar"><?= (int) $linha['posicao'] ?>º</span>
                    </li>
                <?php endforeach; ?>
            </ol>

            <div class="tabela-wrap" role="region" aria-label="Tabela completa do ranking" tabindex="0">
                <table class="tabela">
                    <caption class="sr-only">Ranking de alunos por XP</caption>
                    <thead>
                        <tr>
                            <th scope="col">Posição</th>
                            <th scope="col">Aluno</th>
                            <th scope="col" class="num">XP</th>
                            <th scope="col" class="num">Partidas</th>
                            <th scope="col" class="num">Aproveitamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ranking as $linha): ?>
                            <?php $perc = aproveitamento($linha['acertos'], $linha['erros']); ?>
                            <tr class="<?= $linha['id_aluno'] === $idAtual ? 'linha-voce' : '' ?>">
                                <th scope="row" class="pos">
                                    <?php if (isset($medalhas[$linha['posicao']])): ?>
                                        <img class="pos-medalha"
                                             src="<?= htmlspecialchars(asset('assets/img/medalha-' . $medalhas[$linha['posicao']] . '.svg'), ENT_QUOTES, 'UTF-8') ?>"
                                             alt="">
                                    <?php endif; ?>
                                    <?= (int) $linha['posicao'] ?>º
                                </th>
                                <td>
                                    <?= htmlspecialchars($linha['nome'], ENT_QUOTES, 'UTF-8') ?>
                                    <?php if ($linha['id_aluno'] === $idAtual): ?>
                                        <span class="tag-voce">você</span>
                                    <?php endif; ?>
                                </td>
                                <td class="num"><strong><?= number_format($linha['xp'], 0, ',', '.') ?></strong></td>
                                <td class="num"><?= $linha['partidas'] ?></td>
                                <td class="num"><?= $perc === null ? '—' : $perc . '%' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <p class="ajuda-campo">
                Os pontos vêm de todas as partidas: Batalha na Floresta e Poções Mágicas.
                Empates dividem a mesma posição.
            </p>
        <?php endif; ?>

        <p class="acoes-jogo">
            <a href="?pagina=home#jogos" class="btn">Ir para os jogos</a>
        </p>
    </section>
</main>
