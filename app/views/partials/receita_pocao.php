<?php
/**
 * Folha de receita da poção (aparece antes da pergunta).
 * Espera: $questao['id_questao'], $questao['enunciado'], $questao['mecanica'].
 */
require_once __DIR__ . '/../../helpers/dicas.php';

$enunciadoReceita = (string) ($questao['enunciado'] ?? '');
$mecanicaReceita = $questao['mecanica'] ?? null;
$nomeReceita = nome_pocao((int) ($questao['id_questao'] ?? 0));

$partes = null;   // identificação
$duas = null;     // comparação

if (
    $mecanicaReceita === 'identificacao_fracao'
    && preg_match('/dividida em (\d+) partes iguais\.\s*(\d+) partes/u', $enunciadoReceita, $m)
) {
    $partes = ['total' => (int) $m[1], 'essencia' => (int) $m[2]];
} elseif (
    $mecanicaReceita === 'comparacao_fracao'
    && preg_match_all('/(\d+)\/(\d+)/', $enunciadoReceita, $m, PREG_SET_ORDER)
    && count($m) >= 2
) {
    $duas = [
        ['nome' => 'Poção da Lua', 'n' => (int) $m[0][1], 'd' => (int) $m[0][2], 'ingrediente' => 'essência lunar'],
        ['nome' => 'Poção da Folha', 'n' => (int) $m[1][1], 'd' => (int) $m[1][2], 'ingrediente' => 'essência de folha'],
    ];
}
?>
<figure class="receita" aria-label="Receita da poção">
    <div class="receita-fita"><?= htmlspecialchars($nomeReceita, ENT_QUOTES, 'UTF-8') ?></div>
    <p class="receita-sub">Receita do Laboratório de Numéria</p>

    <?php if ($partes !== null):
        $t = $partes['total'];
        $e = min($partes['essencia'], $t);
        $resto = $t - $e;
    ?>
        <div class="receita-corpo">
            <div class="receita-bloco">
                <h4>Ingredientes</h4>
                <ul class="receita-lista">
                    <li><span class="qtd">1</span> frasco de cristal, dividido em <strong><?= $t ?></strong> partes iguais</li>
                    <li><span class="qtd"><?= $e ?></span> <?= $e === 1 ? 'parte' : 'partes' ?> de <strong>essência lunar</strong></li>
                    <?php if ($resto > 0): ?>
                        <li><span class="qtd"><?= $resto ?></span> <?= $resto === 1 ? 'parte' : 'partes' ?> de orvalho de fada</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="receita-bloco">
                <h4>Modo de preparo</h4>
                <ol class="receita-passos">
                    <li>Divida o frasco em <?= $t ?> partes iguais.</li>
                    <li>Coloque essência lunar em <?= $e ?> dessas partes.</li>
                    <?php if ($resto > 0): ?>
                        <li>Complete o resto com orvalho de fada.</li>
                    <?php endif; ?>
                    <li>Misture e descubra: que fração do frasco recebeu essência?</li>
                </ol>
            </div>
        </div>

        <div class="frasco" role="img"
             aria-label="Frasco dividido em <?= $t ?> partes iguais, com essência lunar em <?= $e ?>">
            <?php for ($i = 1; $i <= $t; $i++): ?>
                <span class="<?= $i <= $e ? 'cheio' : '' ?>"></span>
            <?php endfor; ?>
        </div>

    <?php elseif ($duas !== null): ?>
        <div class="receita-corpo receita-dupla">
            <?php foreach ($duas as $p): ?>
                <div class="receita-bloco">
                    <h4><?= htmlspecialchars($p['nome'], ENT_QUOTES, 'UTF-8') ?></h4>
                    <ul class="receita-lista">
                        <li><span class="qtd"><?= fracao_html($p['n'], $p['d']) ?></span> de <strong><?= $p['ingrediente'] ?></strong></li>
                        <li><span class="qtd">1</span> colher de pó de fada</li>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="receita-nota">Prepare as duas poções e descubra qual leva a maior proporção de essência.</p>

    <?php else: ?>
        <div class="receita-corpo">
            <div class="receita-bloco">
                <h4>Modo de preparo</h4>
                <p><?= htmlspecialchars($enunciadoReceita, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="receita-materiais">
        <h4>Materiais necessários</h4>
        <ul>
            <li>Frasco de cristal</li>
            <li>Essência mágica</li>
            <li>Orvalho de fada</li>
            <li>Colher de prata</li>
        </ul>
    </div>
</figure>
