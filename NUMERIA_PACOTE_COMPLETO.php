<?php
/**
 * ==============================================================================
 * NUMÉRIA · PACOTE COMPLETO DE ALTERAÇÕES EM ARQUIVO ÚNICO
 * ==============================================================================
 * Este arquivo único contém todas as alterações, novos arquivos, códigos PHP,
 * CSS e SVGs desenvolvidos para o Numéria conforme suas solicitações.
 *
 * Você pode visualizar este arquivo no navegador via Laragon:
 * http://localhost/numeriaTcc-completo%20teste/NUMERIA_PACOTE_COMPLETO.php
 *
 * Ou abri-lo diretamente em qualquer editor de código.
 */

$mensagemAcao = '';
$tipoMensagem = '';

// Ação opcional: Aplicar alterações com backup automático do original
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aplicar_alteracoes'])) {
    $dirOrigem = __DIR__ . DIRECTORY_SEPARATOR . 'alteracoes_realizadas';
    $dirDestino = __DIR__ . DIRECTORY_SEPARATOR . 'numeriaTcc';
    $dirBackup = __DIR__ . DIRECTORY_SEPARATOR . 'numeriaTcc_backup_' . date('Ymd_His');

    if (!is_dir($dirOrigem)) {
        $mensagemAcao = 'A pasta de alterações preparadas não foi encontrada.';
        $tipoMensagem = 'erro';
    } elseif (!is_dir($dirDestino)) {
        $mensagemAcao = 'A pasta de destino numeriaTcc não foi encontrada.';
        $tipoMensagem = 'erro';
    } else {
        // 1. Cria backup de segurança do seu projeto original
        copiarDiretorioRecursivo($dirDestino, $dirBackup);

        // 2. Aplica as alterações na pasta numeriaTcc
        copiarDiretorioRecursivo($dirOrigem, $dirDestino);

        $mensagemAcao = "✅ Sucesso! Foi criado um backup seguro em '" . basename($dirBackup) . "' e todas as alterações foram aplicadas em 'numeriaTcc'!";
        $tipoMensagem = 'sucesso';
    }
}

function copiarDiretorioRecursivo(string $origem, string $destino): void
{
    if (!is_dir($destino)) {
        mkdir($destino, 0777, true);
    }
    $itens = scandir($origem);
    foreach ($itens as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $caminhoOrigem = $origem . DIRECTORY_SEPARATOR . $item;
        $caminhoDestino = $destino . DIRECTORY_SEPARATOR . $item;

        if (is_dir($caminhoOrigem)) {
            copiarDiretorioRecursivo($caminhoOrigem, $caminhoDestino);
        } else {
            copy($caminhoOrigem, $caminhoDestino);
        }
    }
}

$listaArquivos = [
    [
        'nome' => 'app/helpers/bicoHelper.php',
        'titulo' => '1. Central de Dicas e Feedbacks do Bicó (Novo)',
        'descricao' => 'Gera dicas pedagógicas inteligentes de cálculo mental (decomposição por dezenas/unidades sem o erro de 0 + dígito) e componentes visuais do Bicó para todos os jogos.',
        'caminho' => __DIR__ . '/alteracoes_realizadas/app/helpers/bicoHelper.php'
    ],
    [
        'nome' => 'app/views/pages/home.php',
        'titulo' => '2. Apresentação do Mascote Bicó na Home',
        'descricao' => 'Card temático apresentando o Bicó em pixel art, sua história de tucano bruxo e a proposta acolhedora do Numéria ("tudo bem errar").',
        'caminho' => __DIR__ . '/alteracoes_realizadas/app/views/pages/home.php'
    ],
    [
        'nome' => 'app/views/pages/desafio.php',
        'titulo' => '3. Batalha na Floresta · Desafio (60s + Abandonar + Dicas)',
        'descricao' => 'Tempo ajustado para 60 segundos (blitz rápido), botão de abandono de batalha com confirmação e dicas do Bicó.',
        'caminho' => __DIR__ . '/alteracoes_realizadas/app/views/pages/desafio.php'
    ],
    [
        'nome' => 'app/views/pages/dupla.php',
        'titulo' => '4. Batalha na Floresta · Modo Dupla (Caçador & Monstrinho Pixel + Dicas)',
        'descricao' => 'Substituição de emojis artificiais pelos sprites em pixel art do Caçador e do Monstrinho, com banner de turno e dicas do Bicó.',
        'caminho' => __DIR__ . '/alteracoes_realizadas/app/views/pages/dupla.php'
    ],
    [
        'nome' => 'app/views/pages/guardiao.php',
        'titulo' => '5. Contra o Guardião (Arena HUD Grande + Vidas Pixel + Dicas)',
        'descricao' => 'Arena de duelo completa com avatares de 68px, 3 corações pixel gigantes para o jogador, 3 escudos místicos para o guardião e métricas de desempenho destacadas.',
        'caminho' => __DIR__ . '/alteracoes_realizadas/app/views/pages/guardiao.php'
    ],
    [
        'nome' => 'app/views/pages/pocoes.php',
        'titulo' => '6. Laboratório de Poções (Receita Literal de Poção + Frasco Alquímico Visual)',
        'descricao' => 'Apresenta a receita literal em pergaminho com ingredientes medidos, tubo de ensaio graduado desenhado em CSS puro e comparação de caldeirões.',
        'caminho' => __DIR__ . '/alteracoes_realizadas/app/views/pages/pocoes.php'
    ],
    [
        'nome' => 'public/index.php',
        'titulo' => '7. Roteador Principal (Rota abandonar_desafio + Timer 60s)',
        'descricao' => 'Inclui a nova rota de abandono do modo desafio e sincroniza a validação do timer para 60 segundos.',
        'caminho' => __DIR__ . '/alteracoes_realizadas/public/index.php'
    ],
    [
        'nome' => 'public/assets/css/style.css',
        'titulo' => '8. Folha de Estilos Atualizada (style.css)',
        'descricao' => 'Estilos da Arena do Guardião, Pergaminho de Poções, Tubo de Ensaio Alquímico, avatares pixel e caixas de dicas do Bicó.',
        'caminho' => __DIR__ . '/alteracoes_realizadas/public/assets/css/style.css'
    ],
    [
        'nome' => 'public/assets/img/cacador.svg',
        'titulo' => '9. Sprite SVG: Caçador em Pixel Art',
        'descricao' => 'Avatar vetorial do Caçador / Jogador 1 em pixel art retro.',
        'caminho' => __DIR__ . '/alteracoes_realizadas/public/assets/img/cacador.svg'
    ],
    [
        'nome' => 'public/assets/img/monstrinho.svg',
        'titulo' => '10. Sprite SVG: Monstrinho em Pixel Art',
        'descricao' => 'Avatar vetorial do Monstrinho / Jogador 2 em pixel art retro.',
        'caminho' => __DIR__ . '/alteracoes_realizadas/public/assets/img/monstrinho.svg'
    ],
    [
        'nome' => 'public/assets/img/guardiao.svg',
        'titulo' => '11. Sprite SVG: Guardião da Floresta em Pixel Art',
        'descricao' => 'Avatar vetorial da entidade ancestral da floresta.',
        'caminho' => __DIR__ . '/alteracoes_realizadas/public/assets/img/guardiao.svg'
    ],
    [
        'nome' => 'public/assets/img/coracao-pixel.svg',
        'titulo' => '12. Sprite SVG: Coração de Vida Cheio',
        'descricao' => 'Ícone pixel art de coração cheio (32px) para a vida do jogador.',
        'caminho' => __DIR__ . '/alteracoes_realizadas/public/assets/img/coracao-pixel.svg'
    ],
    [
        'nome' => 'public/assets/img/coracao-vazio.svg',
        'titulo' => '13. Sprite SVG: Coração de Vida Vazio',
        'descricao' => 'Ícone pixel art de coração vazio/quebrado.',
        'caminho' => __DIR__ . '/alteracoes_realizadas/public/assets/img/coracao-vazio.svg'
    ],
    [
        'nome' => 'public/assets/img/escudo-guardiao.svg',
        'titulo' => '14. Sprite SVG: Escudo Místico Ativo',
        'descricao' => 'Ícone pixel art de gema mística/escudo do guardião ativo.',
        'caminho' => __DIR__ . '/alteracoes_realizadas/public/assets/img/escudo-guardiao.svg'
    ],
    [
        'nome' => 'public/assets/img/escudo-vazio.svg',
        'titulo' => '15. Sprite SVG: Escudo Místico Quebrado',
        'descricao' => 'Ícone pixel art de escudo quebrado do guardião.',
        'caminho' => __DIR__ . '/alteracoes_realizadas/public/assets/img/escudo-vazio.svg'
    ]
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Numéria · Pacote Completo de Alterações</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap">
    <style>
        :root {
            --tinta: #1d2340;
            --papel: #fdf6e3;
            --papel-escuro: #f3e7c3;
            --branco: #ffffff;
            --amarelo: #ffd23f;
            --laranja: #f26b1d;
            --roxo: #6a3fb5;
            --verde: #2e7d4f;
            --pixel: 0 -4px 0 0 var(--tinta), 0 4px 0 0 var(--tinta), -4px 0 0 0 var(--tinta), 4px 0 0 0 var(--tinta);
        }
        * { box-sizing: border-box; }
        body {
            font-family: "Trebuchet MS", Verdana, sans-serif;
            background: var(--papel);
            color: var(--tinta);
            margin: 0;
            padding: 30px 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1050px;
            margin: 0 auto;
        }
        header {
            background: #2f6fb3;
            color: #fff;
            padding: 24px;
            border: 4px solid var(--tinta);
            box-shadow: var(--pixel), 8px 8px 0 4px var(--amarelo);
            margin-bottom: 30px;
        }
        header h1 {
            font-family: 'Press Start 2P', monospace;
            font-size: 1.25rem;
            color: var(--amarelo);
            margin: 0 0 10px;
            text-shadow: 2px 2px 0 var(--tinta);
        }
        .badge {
            background: var(--amarelo);
            color: var(--tinta);
            font-family: 'Press Start 2P', monospace;
            font-size: 0.6rem;
            padding: 4px 8px;
            border: 2px solid var(--tinta);
            display: inline-block;
        }
        .aviso-seguranca {
            background: #e8f5e9;
            border: 3px solid var(--verde);
            padding: 16px;
            margin-bottom: 24px;
            font-size: 0.95rem;
        }
        .card-arquivo {
            background: var(--branco);
            border: 3px solid var(--tinta);
            box-shadow: var(--pixel), 6px 6px 0 2px rgba(0,0,0,0.15);
            padding: 20px;
            margin-bottom: 26px;
        }
        .card-arquivo h3 {
            margin: 0 0 6px;
            color: var(--roxo);
            font-size: 1.2rem;
        }
        .caminho-tag {
            font-family: monospace;
            background: var(--papel-escuro);
            padding: 3px 8px;
            border: 1px solid var(--tinta);
            font-size: 0.9rem;
            display: inline-block;
            margin-bottom: 10px;
        }
        pre {
            background: #1e1e2e;
            color: #cdd6f4;
            padding: 16px;
            border: 2px solid var(--tinta);
            overflow-x: auto;
            font-size: 0.82rem;
            max-height: 420px;
        }
        .btn {
            font-family: 'Press Start 2P', monospace;
            font-size: 0.75rem;
            background: var(--amarelo);
            color: var(--tinta);
            border: 3px solid var(--tinta);
            padding: 12px 20px;
            cursor: pointer;
            box-shadow: 4px 4px 0 var(--tinta);
        }
        .btn:hover {
            background: var(--laranja);
        }
        .msg-status {
            padding: 14px;
            border: 3px solid var(--tinta);
            font-weight: 700;
            margin-bottom: 20px;
        }
        .msg-sucesso { background: #d4edda; color: #155724; border-color: #28a745; }
        .msg-erro { background: #f8d7da; color: #721c24; border-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <span class="badge">PROJETO NUMÉRIA · XGUERTECH</span>
            <h1>PACOTE COMPLETO DE ALTERAÇÕES</h1>
            <p style="margin: 6px 0 0;">Arquivo mestre reunindo todas as modificações solicitadas do Bicó, Poções, Desafio, Dupla e Guardião.</p>
        </header>

        <div class="aviso-seguranca">
            <strong>🔒 Garantia de Segurança:</strong> O seu código em <code>numeriaTcc/</code> foi mantido 100% original e intacto.
            Todos os novos arquivos já estão salvos e prontos na pasta <code>alteracoes_realizadas/</code>.
        </div>

        <?php if (!empty($mensagemAcao)): ?>
            <div class="msg-status <?= ($tipoMensagem === 'sucesso') ? 'msg-sucesso' : 'msg-erro' ?>">
                <?= htmlspecialchars($mensagemAcao, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- BOTÃO DE APLICAÇÃO SEGURA COM BACKUP -->
        <div class="card-arquivo" style="background: #fff8e1; border-color: var(--laranja);">
            <h3>🚀 Deseja aplicar todas as alterações no seu projeto agora?</h3>
            <p>Se você clicar no botão abaixo, o script cria automaticamente uma cópia de segurança completa (backup) da sua pasta <code>numeriaTcc/</code> e copia os arquivos atualizados para ela sem você precisar fazer nada manual.</p>
            <form method="POST" onsubmit="return confirm('Confirmar aplicação das alterações com backup automático?');">
                <button type="submit" name="aplicar_alteracoes" value="1" class="btn">
                    ⚡ APLICAR NO PROJETO COM BACKUP AUTOMÁTICO
                </button>
            </form>
        </div>

        <h2>📂 Conteúdo Completo dos Arquivos Atualizados</h2>

        <?php foreach ($listaArquivos as $arq): ?>
            <article class="card-arquivo">
                <h3><?= htmlspecialchars($arq['titulo'], ENT_QUOTES, 'UTF-8') ?></h3>
                <span class="caminho-tag">📁 <?= htmlspecialchars($arq['nome'], ENT_QUOTES, 'UTF-8') ?></span>
                <p><?= htmlspecialchars($arq['descricao'], ENT_QUOTES, 'UTF-8') ?></p>

                <details>
                    <summary style="cursor: pointer; font-weight: bold; margin-bottom: 8px; color: var(--roxo);">
                        👁️ Clique para ver / copiar o código-fonte deste arquivo
                    </summary>
                    <pre><code><?php
                    if (file_exists($arq['caminho'])) {
                        echo htmlspecialchars(file_get_contents($arq['caminho']), ENT_QUOTES, 'UTF-8');
                    } else {
                        echo "Arquivo não encontrado no caminho: " . htmlspecialchars($arq['caminho']);
                    }
                    ?></code></pre>
                </details>
            </article>
        <?php endforeach; ?>

        <footer style="margin-top: 40px; text-align: center; font-size: 0.85rem; color: #666;">
            Numéria · Plataforma Gamificada de Aprendizagem de Matemática · Ensino Fundamental II
        </footer>
    </div>
</body>
</html>
