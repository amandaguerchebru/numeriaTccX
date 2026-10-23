<?php
/**
 * Caixa "Dicas do Bicó".
 * Espera: $questao['enunciado'] e, opcionalmente, $questao['mecanica'].
 * As dicas aparecem uma por vez (script.js cuida do botão).
 */
require_once __DIR__ . '/../../helpers/dicas.php';

$dicasDoBico = dicas_bico(
    (string) ($questao['enunciado'] ?? ''),
    $questao['mecanica'] ?? null
);
$totalDicas = count($dicasDoBico);
?>
<div class="dica-bico" data-dicas data-total="<?= $totalDicas ?>">
    <div class="dica-bico-topo">
        <span class="bico-mini" aria-hidden="true"></span>
        <div class="dica-bico-fala">
            <strong>Precisa de uma ajudinha?</strong>
            <span>Eu te dou uma dica por vez, sem contar a resposta.</span>
        </div>
        <button type="button" class="dica-btn" data-dica-btn>
            Pedir dica ao Bicó (0/<?= $totalDicas ?>)
        </button>
    </div>

    <ol class="dica-lista" aria-live="polite">
        <?php foreach ($dicasDoBico as $i => $dica): ?>
            <li hidden>
                <span class="dica-num">Dica <?= $i + 1 ?></span>
                <?= htmlspecialchars($dica, ENT_QUOTES, 'UTF-8') ?>
            </li>
        <?php endforeach; ?>
    </ol>
</div>
