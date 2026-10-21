<?php
/**
 * Helper do Mascote Bicó - Numéria
 * Centraliza a inteligência pedagógica das dicas e feedbacks do Bicó em todos os jogos.
 */

if (!function_exists('gerarDicaBico')) {
    /**
     * Gera uma dica pedagógica contextualizada do Bicó para o enunciado atual.
     *
     * @param string $enunciado Texto da questão
     * @param string $explicacaoDb Explicação cadastrada no banco de dados (se houver)
     * @return string Dica formatada com a personalidade do Bicó
     */
    function gerarDicaBico(string $enunciado, string $explicacaoDb = ''): string
    {
        $explicacaoLimpa = trim($explicacaoDb);
        if (!empty($explicacaoLimpa)) {
            return $explicacaoLimpa;
        }

        // 1. Detecta Operação de Adição (A + B)
        if (preg_match('/(\d+)\s*\+\s*(\d+)/', $enunciado, $matches)) {
            $n1 = (int) $matches[1];
            $n2 = (int) $matches[2];

            // Se o segundo número tem apenas 1 dígito
            if ($n2 < 10) {
                $proximaDezena = (int) (ceil($n1 / 10) * 10);
                if ($proximaDezena === $n1) {
                    $proximaDezena += 10;
                }
                $faltaParaDezena = $proximaDezena - $n1;

                if ($faltaParaDezena > 0 && $faltaParaDezena < $n2) {
                    $sobra = $n2 - $faltaParaDezena;
                    return "Decomponha o {$n2}! Some primeiro até a dezena cheia: {$n1} + {$faltaParaDezena} = {$proximaDezena}. Depois adicione o que restou ({$sobra}): {$proximaDezena} + {$sobra} = " . ($n1 + $n2) . "!";
                }

                return "Some o número direto às unidades: {$n1} + {$n2} = " . ($n1 + $n2) . ". Fique atento se passar para a próxima dezena!";
            }

            // Para dois dígitos ou mais: decomposição decimal clássica
            $dezena1 = (int) (floor($n1 / 10) * 10);
            $unidade1 = $n1 % 10;
            $dezena2 = (int) (floor($n2 / 10) * 10);
            $unidade2 = $n2 % 10;

            $somaDezenas = $dezena1 + $dezena2;
            $somaUnidades = $unidade1 + $unidade2;

            return "Separe as dezenas e as unidades! Some as dezenas ({$dezena1} + {$dezena2} = {$somaDezenas}) e depois as unidades ({$unidade1} + {$unidade2} = {$somaUnidades}). Juntando tudo: {$somaDezenas} + {$somaUnidades} = " . ($n1 + $n2) . "!";
        }

        // 2. Detecta Operação de Subtração (A - B)
        if (preg_match('/(\d+)\s*\-\s*(\d+)/', $enunciado, $matches)) {
            $n1 = (int) $matches[1];
            $n2 = (int) $matches[2];

            if ($n2 < 10) {
                return "Pense na contagem regressiva a partir de {$n1}: retire {$n2} unidades com calma para encontrar o resultado!";
            }

            $dezena2 = (int) (floor($n2 / 10) * 10);
            $unidade2 = $n2 % 10;
            $passo1 = $n1 - $dezena2;

            return "Tire as dezenas primeiro! {$n1} - {$dezena2} = {$passo1}. Agora retire as unidades restantes: {$passo1} - {$unidade2} = " . ($n1 - $n2) . "!";
        }

        // 3. Frações: Identificação de Partes
        if (stripos($enunciado, 'partes') !== false || stripos($enunciado, 'fração') !== false) {
            return "Lembre-se do segredo do alquimista: o número de baixo (denominador) representa em quantas partes iguais a poção foi dividida no total. O número de cima (numerador) mostra quantas partes de essência foram adicionadas!";
        }

        // 4. Frações: Comparação de Proporção
        if (stripos($enunciado, 'maior proporção') !== false || stripos($enunciado, 'compar') !== false) {
            return "Para comparar duas poções de frascos diferentes, use a multiplicação cruzada: multiplique o numerador da primeira pelo denominador da segunda, e vice-versa. Quem tiver o maior produto terá a mistura mais concentrada!";
        }

        return "Analise com atenção os dados do enigma! Tente quebrar o cálculo em etapas menores para chegar à resposta com segurança.";
    }
}

if (!function_exists('renderBicoFeedbackHtml')) {
    /**
     * Renderiza o componente HTML padrão de feedback com o Bicó.
     */
    function renderBicoFeedbackHtml(string $mensagem, string $tipo, string $explicacao = ''): string
    {
        if (empty($mensagem)) {
            return '';
        }

        $classeCss = ($tipo === 'acerto') ? 'feedback-sucesso' : 'feedback-erro';
        $ariaIcone = ($tipo === 'acerto') ? 'Acerto com o Bicó' : 'Apoio do Bicó';

        $html = '<div role="status" class="feedback-msg ' . $classeCss . '">';
        $html .= '<div class="bico-feedback">';
        $html .= '<span class="bico-icone" aria-label="' . $ariaIcone . '"></span>';
        $html .= '<div>';
        $html .= '<p><strong>' . htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') . '</strong></p>';
        if (!empty($explicacao)) {
            $html .= '<p class="explicacao"><strong>Dica do Bicó:</strong> ' . htmlspecialchars($explicacao, ENT_QUOTES, 'UTF-8') . '</p>';
        }
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('renderBicoDicaBotaoHtml')) {
    /**
     * Renderiza o botão interativo expansível de Dica do Bicó durante o desafio.
     */
    function renderBicoDicaBotaoHtml(string $dica): string
    {
        if (empty($dica)) {
            return '';
        }

        $html = '<details class="bico-dica-card">';
        $html .= '<summary class="bico-dica-resumo">';
        $html .= '<span class="bico-icone-mini" aria-hidden="true"></span>';
        $html .= '<span>Pedir dica mágica ao Bicó</span>';
        $html .= '</summary>';
        $html .= '<div class="bico-dica-conteudo">';
        $html .= '<p><strong>💡 Bicó sussurra:</strong> ' . htmlspecialchars($dica, ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '</div>';
        $html .= '</details>';

        return $html;
    }
}
