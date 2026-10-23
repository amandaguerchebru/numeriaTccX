<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <meta name="description" content="Plataforma educacional Numéria">
    
    <!-- FAVICON DO BICÓ FELIZ AQUI: -->
     <link rel="icon" type="image/png" href="<?php echo asset('assets/img/bico-favicon.png'); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap">
    <link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>">
</head>

<body<?php if (in_array($pagina ?? '', ['batalha', 'desafio', 'guardiao', 'dupla'], true)): ?> class="tema-batalha"<?php endif; ?>>
    <a href="#conteudo" class="pular-conteudo">Pular para o conteúdo</a>
    <header>
        <div class="container brand">
            <div class="logo-area">
                <a href="?pagina=home" class="logo-link" aria-label="Numéria Início">
                    <span class="logo-icone" aria-hidden="true"></span>
                    <span class="logo-texto"><?php echo APP_NAME; ?></span>
                </a>
                <span class="slogan-header">Reino Mágico da Matemática · XGuerTech</span>
            </div>

            <nav aria-label="Navegação principal" class="nav-principal">
                <a href="?pagina=home">Início</a>
                <a href="?pagina=home#jogos">Jogos</a>
                <?php if (isset($_SESSION['aluno_id'])): ?>
                    <?php if (($_SESSION['aluno_tipo'] ?? 'aluno') === 'professor'): ?>
                        <a href="?pagina=professor">Painel do Professor</a>
                    <?php else: ?>
                        <a href="?pagina=historico">Meu Desempenho</a>
                    <?php endif; ?>
                    <a href="?pagina=ranking">Ranking</a>
                    <span class="nav-aluno-ola">Olá, <strong><?php echo htmlspecialchars($_SESSION['aluno_nome'] ?? 'Aventureiro', ENT_QUOTES, 'UTF-8'); ?></strong><?php if (($_SESSION['aluno_tipo'] ?? 'aluno') === 'professor'): ?> <span class="tag-professor">Professor</span><?php endif; ?></span>
                    <a href="?pagina=sair" class="nav-conta nav-sair">Sair</a>
                <?php else: ?>
                    <a href="?pagina=login" class="nav-conta">Entrar</a>
                    <a href="?pagina=cadastro" class="nav-conta nav-cadastro">
                        Criar conta
                    </a>
                <?php endif; ?>
                <div class="acess-fonte" role="group" aria-label="Tamanho do texto">
                    <button type="button" data-fonte="normal" aria-label="Texto normal" aria-pressed="true">A</button>
                    <button type="button" data-fonte="grande" aria-label="Texto grande" aria-pressed="false">A</button>
                    <button type="button" data-fonte="enorme" aria-label="Texto muito grande" aria-pressed="false">A</button>
                </div>
            </nav>
        </div>
    </header>