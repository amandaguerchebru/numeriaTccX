<?php

/**
 * Dicas do Bicó: ajuda em etapas, SEM entregar a resposta final.
 *
 * Devolve uma lista de até 3 dicas, da mais geral para a mais específica.
 * Funciona a partir do enunciado da questão, então vale para as questões
 * que já estão no banco (não precisa mudar nenhuma tabela).
 */
function dicas_bico(string $enunciado, ?string $mecanica = null): array
{
    // ---------- Frações: identificação ----------
    if (
        $mecanica === 'identificacao_fracao'
        && preg_match('/dividida em (\d+) partes iguais/u', $enunciado, $m)
    ) {
        $total = (int) $m[1];

        return [
            'Toda fração tem duas partes: o número de baixo (denominador) diz em quantas partes iguais o todo foi dividido.',
            "Na receita, o frasco foi dividido em {$total} partes iguais. Esse {$total} é o número que vai embaixo da fração.",
            'Agora o número de cima (numerador) conta só as partes que receberam essência. Quantas foram? Procure na receita e confira nas alternativas.',
        ];
    }

    // ---------- Frações: comparação ----------
    if (
        $mecanica === 'comparacao_fracao'
        && preg_match_all('/(\d+)\/(\d+)/', $enunciado, $m, PREG_SET_ORDER)
        && count($m) >= 2
    ) {
        [$a, $b] = [(int) $m[0][1], (int) $m[0][2]];
        [$c, $d] = [(int) $m[1][1], (int) $m[1][2]];

        return [
            'Frações com números de baixo diferentes são difíceis de comparar “no olho”. Uma boa saída é a multiplicação cruzada.',
            "Calcule {$a} × {$d} (da primeira poção) e {$c} × {$b} (da segunda poção).",
            'Compare os dois resultados: o maior produto está do lado da fração maior. Qual poção ficou com o maior número?',
        ];
    }

    // ---------- Adição e subtração ----------
    if (preg_match('/(\d+)\s*([+\-−–])\s*(\d+)/u', $enunciado, $m)) {
        $a = (int) $m[1];
        $soma = ($m[2] === '+');
        $b = (int) $m[3];
        $ub = $b % 10;           // unidades do segundo número
        $semUnidades = $b - $ub; // segundo número sem as unidades

        // Segundo número de um dígito só: usa a dezena cheia mais próxima.
        if ($b < 10) {
            $falta = 10 - ($a % 10);
            $ua = $a % 10;

            if ($soma) {
                return [
                    'Somar é juntar quantidades. Com um número pequeno, vale contar a partir do maior.',
                    "Quanto falta para {$a} chegar à próxima dezena cheia? Faltam {$falta}.",
                    $falta >= $b
                        ? "Como {$b} não é maior que {$falta}, você não passa da dezena: some só as unidades."
                        : "Divida o {$b} em {$falta} + o que sobra: primeiro complete a dezena, depois some o resto.",
                ];
            }

            return [
                'Subtrair é tirar uma quantidade de outra. Com um número pequeno, olhe para as unidades.',
                "As unidades de {$a} são {$ua}. Dá para tirar {$b} direto delas?",
                $ua >= $b
                    ? 'Dá! Tire só das unidades: as dezenas continuam iguais.'
                    : "Não dá. Tire primeiro {$ua} para chegar na dezena cheia e depois tire o que falta (" . ($b - $ua) . ') da dezena de baixo.',
            ];
        }

        if ($soma) {
            if ($ub === 0) {
                return [
                    'Somar é juntar quantidades. Comece olhando como cada número termina.',
                    "Como {$b} termina em zero, as unidades de {$a} não mudam. Só as dezenas (ou centenas) mudam.",
                    "Some " . intdiv($b, 10) . " dezena(s) ao número de dezenas de {$a}, e depois recoloque as unidades.",
                ];
            }

            return [
                'Somar é juntar quantidades. Uma boa estratégia é somar por partes: primeiro a parte grande, depois a pequena.',
                "Separe o {$b} em {$semUnidades} + {$ub}. Primeiro some {$a} + {$semUnidades}; isso dá " . ($a + $semUnidades) . '.',
                'Agora falta só uma parte: a partir de ' . ($a + $semUnidades) . ", conte mais {$ub}. Cuidado com o “vai um”!",
            ];
        }

        if ($ub === 0) {
            return [
                'Subtrair é tirar uma quantidade de outra. Comece olhando como cada número termina.',
                "Como {$b} termina em zero, as unidades de {$a} não mudam. Só as dezenas (ou centenas) mudam.",
                'Tire ' . intdiv($b, 10) . ' dezena(s) do número de dezenas de ' . $a . ', e depois recoloque as unidades.',
            ];
        }

        return [
            'Subtrair é tirar uma quantidade de outra. Uma boa estratégia é tirar por partes: primeiro a parte grande, depois a pequena.',
            "Separe o {$b} em {$semUnidades} + {$ub}. Primeiro tire {$semUnidades} de {$a}; sobra " . ($a - $semUnidades) . '.',
            'Agora falta só uma parte: de ' . ($a - $semUnidades) . ", tire mais {$ub}. Se precisar, conte para trás!",
        ];
    }

    // ---------- Genérica (se o enunciado for de outro tipo) ----------
    return [
        'Leia o enunciado com calma e sublinhe os números importantes.',
        'Separe a conta em partes menores e resolva uma de cada vez.',
        'Depois de achar um resultado, confira se ele combina com alguma alternativa.',
    ];
}

/** Sorteia (sempre o mesmo para a mesma questão) o nome da poção da receita. */
function nome_pocao(int $idQuestao): string
{
    $nomes = ['Poção da Alegria', 'Poção da Cura', 'Poção do Foco', 'Poção do Aprendizado'];

    return $nomes[$idQuestao % count($nomes)];
}

/** Fração em forma de livro: numerador em cima, denominador embaixo. */
function fracao_html(int $n, int $d): string
{
    return '<span class="frac" aria-label="' . $n . ' sobre ' . $d . '">'
        . '<span class="frac-n">' . $n . '</span>'
        . '<span class="frac-d">' . $d . '</span></span>';
}
