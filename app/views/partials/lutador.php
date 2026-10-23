<?php
/**
 * Um lutador da arena, em pixel art.
 * Variáveis: $lt_tipo ('bico'|'guardiao'|'onca'), $lt_nome, $lt_papel,
 *   $lt_vidas (int|null), $lt_max (int), $lt_extra (html), $lt_classe, $lt_vez (bool)
 */
$lt_vidas = $lt_vidas ?? null;
$lt_max = $lt_max ?? 3;
$lt_extra = $lt_extra ?? '';
$lt_classe = $lt_classe ?? '';
$lt_vez = $lt_vez ?? false;

$lt_descricoes = [
    'bico' => 'Bicó, o tucano bruxo, de armadura roxa, com escudo e espada',
    'guardiao' => 'Guardião ancestral da floresta, feito de galhos e folhas',
    'onca' => 'Onça aventureira de capa verde',
];
$lt_tipo = isset($lt_descricoes[$lt_tipo]) ? $lt_tipo : 'bico';
?>
<div class="lutador <?= htmlspecialchars($lt_classe, ENT_QUOTES, 'UTF-8') ?> <?= $lt_vez ? 'lutador-vez' : '' ?>">
    <?php if ($lt_vez): ?>
        <span class="lutador-balao">Sua vez!</span>
    <?php endif; ?>
    <div class="lutador-sprite sprite-<?= $lt_tipo ?>"
         role="img"
         aria-label="<?= $lt_descricoes[$lt_tipo] ?>"></div>
    <div class="lutador-nome">
        <?= htmlspecialchars($lt_nome, ENT_QUOTES, 'UTF-8') ?>
        <small><?= htmlspecialchars($lt_papel, ENT_QUOTES, 'UTF-8') ?></small>
    </div>
    <?php if ($lt_vidas !== null): ?>
        <div class="vidas" role="img" aria-label="Vidas: <?= $lt_vidas ?> de <?= $lt_max ?>">
            <?php for ($i = 1; $i <= $lt_max; $i++): ?>
                <span class="vida <?= $i <= $lt_vidas ? 'cheia' : 'vazia' ?>"></span>
            <?php endfor; ?>
        </div>
        <div class="vidas-texto"><?= $lt_vidas ?>/<?= $lt_max ?> vidas</div>
    <?php endif; ?>
    <?= $lt_extra ?>
</div>
