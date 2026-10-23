<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../helpers/usuario.php';

// (o acesso só de professor é garantido em public/index.php)

$ranking = buscar_ranking($conexao);
$totalAlunos = count($ranking);

$somaXp = 0;
$somaAcertos = 0;
$somaErros = 0;
$somaPartidas = 0;
$precisamApoio = [];

foreach ($ranking as $linha) {
    $somaXp += $linha['xp'];
    $somaAcertos += $linha['acertos'];
    $somaErros += $linha['erros'];
    $somaPartidas += $linha['partidas'];

    [$textoSelo, $classeSelo] = selo_aproveitamento($linha['acertos'], $linha['erros']);

    if ($classeSelo === 'selo-atencao') {
        $precisamApoio[] = $linha['nome'];
    }
}

$xpMedio = $totalAlunos > 0 ? (int) round($somaXp / $totalAlunos) : 0;
$percGeral = aproveitamento($somaAcertos, $somaErros);
?>

<main class="container">
    <section class="hero">
        <span class="badge">Painel do professor</span>
        <h2>Acompanhe sua turma</h2>
        <p>
            Olá, <strong><?= htmlspecialchars($_SESSION['aluno_nome'] ?? 'Professor(a)', ENT_QUOTES, 'UTF-8') ?></strong>!
            Aqui você vê o desempenho de cada aluno cadastrado e quem precisa de um apoio extra.
        </p>

        <div class="resumo-turma" role="group" aria-label="Resumo da turma">
            <div class="medidor">
                <span class="medidor-rotulo">Alunos</span>
                <strong class="medidor-valor"><?= $totalAlunos ?></strong>
            </div>
            <div class="medidor medidor-xp">
                <span class="medidor-rotulo">XP médio</span>
                <strong class="medidor-valor"><?= number_format($xpMedio, 0, ',', '.') ?></strong>
            </div>
            <div class="medidor medidor-acerto">
                <span class="medidor-rotulo">Aproveitamento</span>
                <strong class="medidor-valor"><?= $percGeral === null ? '—' : $percGeral . '%' ?></strong>
            </div>
            <div class="medidor">
                <span class="medidor-rotulo">Partidas</span>
                <strong class="medidor-valor"><?= $somaPartidas ?></strong>
            </div>
        </div>

        <?php if (!empty($precisamApoio)): ?>
            <div role="status" class="feedback-msg feedback-erro">
                <div class="bico-feedback">
                    <span class="bico-icone" aria-hidden="true"></span>
                    <div>
                        <p><strong>Pontos de atenção</strong></p>
                        <p>
                            Estes alunos estão com menos de 50% de acertos e podem precisar de apoio:
                            <?= htmlspecialchars(implode(', ', $precisamApoio), ENT_QUOTES, 'UTF-8') ?>.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($totalAlunos === 0): ?>
            <p>Nenhum aluno cadastrado ainda. Quando eles criarem a conta, aparecem aqui.</p>
        <?php else: ?>
            <div class="tabela-wrap" role="region" aria-label="Desempenho de cada aluno" tabindex="0">
                <table class="tabela">
                    <caption class="sr-only">Desempenho dos alunos cadastrados</caption>
                    <thead>
                        <tr>
                            <th scope="col">Pos.</th>
                            <th scope="col">Aluno</th>
                            <th scope="col" class="num">XP</th>
                            <th scope="col" class="num">Partidas</th>
                            <th scope="col" class="num">Acertos</th>
                            <th scope="col" class="num">Erros</th>
                            <th scope="col">Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ranking as $linha): ?>
                            <?php [$textoSelo, $classeSelo, $perc] = selo_aproveitamento($linha['acertos'], $linha['erros']); ?>
                            <tr>
                                <th scope="row" class="pos"><?= (int) $linha['posicao'] ?>º</th>
                                <td>
                                    <?= htmlspecialchars($linha['nome'], ENT_QUOTES, 'UTF-8') ?>
                                    <small class="email-aluno"><?= htmlspecialchars($linha['email'], ENT_QUOTES, 'UTF-8') ?></small>
                                </td>
                                <td class="num"><strong><?= number_format($linha['xp'], 0, ',', '.') ?></strong></td>
                                <td class="num"><?= $linha['partidas'] ?></td>
                                <td class="num"><?= $linha['acertos'] ?></td>
                                <td class="num"><?= $linha['erros'] ?></td>
                                <td>
                                    <span class="selo <?= $classeSelo ?>">
                                        <?= htmlspecialchars($textoSelo, ENT_QUOTES, 'UTF-8') ?>
                                        <?= $perc !== null ? '(' . $perc . '%)' : '' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <p class="ajuda-campo">
                “Situação” usa o aproveitamento (acertos ÷ respostas) e só aparece com pelo menos 5 respostas.
            </p>
        <?php endif; ?>

        <p class="acoes-jogo">
            <a href="?pagina=ranking" class="btn">Ver o ranking dos alunos</a>
        </p>
    </section>
</main>
