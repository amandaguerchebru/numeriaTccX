<?php
// O acompanhamento aparece apenas para alunos conectados.
$resumoAluno = null;

if (isset($_SESSION['aluno_id'])) {
    require_once __DIR__ . '/../../config/conexao.php';

    $idAluno = (int) $_SESSION['aluno_id'];

    // Busca o total de XP do aluno.
    $consultaXP = $conexao->prepare(
        'SELECT COALESCE(SUM(xp_total), 0)
         FROM partida_participante
         WHERE id_aluno = :aluno'
    );
    $consultaXP->execute(['aluno' => $idAluno]);
    $totalXP = (int) $consultaXP->fetchColumn();

    // Conta as partidas finalizadas do aluno.
    $consultaPartidas = $conexao->prepare(
        "SELECT COUNT(*)
         FROM partida_participante
         WHERE id_aluno = :aluno
           AND id_partida IN (
               SELECT id_partida
               FROM partida
               WHERE status = 'finalizada'
           )"
    );
    $consultaPartidas->execute(['aluno' => $idAluno]);
    $totalPartidas = (int) $consultaPartidas->fetchColumn();

    // Soma os acertos registrados.
    $consultaAcertos = $conexao->prepare(
        'SELECT COALESCE(SUM(acertos), 0)
         FROM partida_participante
         WHERE id_aluno = :aluno'
    );
    $consultaAcertos->execute(['aluno' => $idAluno]);
    $totalAcertos = (int) $consultaAcertos->fetchColumn();

    // Soma os erros registrados.
    $consultaErros = $conexao->prepare(
        'SELECT COALESCE(SUM(erros), 0)
         FROM partida_participante
         WHERE id_aluno = :aluno'
    );
    $consultaErros->execute(['aluno' => $idAluno]);
    $totalErros = (int) $consultaErros->fetchColumn();

    $resumoAluno = [
        'xp' => $totalXP,
        'partidas' => $totalPartidas,
        'acertos' => $totalAcertos,
        'erros' => $totalErros
    ];
}
?>

<main>
    <div class="container">
        <!-- SEÇÃO HERO DE BOAS-VINDAS -->
        <section class="hero" aria-labelledby="titulo-principal">
            <span class="badge">Reino Encantado da Matemática</span>

            <?php if (isset($_SESSION['aluno_id'])): ?>
                <p>
                    Olá, <strong><?= htmlspecialchars($_SESSION['aluno_nome'], ENT_QUOTES, 'UTF-8') ?></strong>!
                    Que bom ter você de volta a Numéria!
                </p>
            <?php else: ?>
                <p>
                    Entre na sua conta ou cadastre-se pelo menu superior
                    para iniciar sua aventura matemática!
                </p>
            <?php endif; ?>

            <h2 id="titulo-principal">Bem-vindo ao Numéria</h2>
            <p>
                Uma plataforma de aprendizagem matemática gamificada voltada para alunos do Ensino Fundamental II,
                onde cada conta resolvida é um feitiço dominado e cada erro é um passo para a sabedoria.
            </p>
        </section>

        <!-- APRESENTAÇÃO DO BICÓ: TUCANO MÁGICO E MASCOTE OFICIAL -->
        <section class="card bico-apresentacao-card" aria-label="Apresentação do Mascote Bicó">
            <div class="bico-apresentacao-grid">
                <div class="bico-mascote-avatar" role="img" aria-label="Bicó, o Tucano Mágico"></div>
                <div class="bico-mascote-bio">
                    <span class="badge">Mascote Oficial de Numéria</span>
                    <h3 class="bico-titulo">Conheça o Bicó! O Tucano Mágico 🦉✨</h3>
                    <p>
                        Saudações, aventureiro! Eu sou o <strong>Bicó</strong>, um tucano feiticeiro de bico dourado e chapéu estelar.
                        Fui escolhido pelo Reino de Numéria para ser o seu guia oficial em todas as aventuras matemáticas!
                    </p>
                    <p>
                        Aqui no Numéria, <strong>tudo bem errar</strong>! O aprendizado é uma receita que se aprimora a cada tentativa.
                        Em <em>todos os jogos</em> — da Batalha na Floresta ao Laboratório de Poções —, eu estarei ao seu lado sussurrando
                        <strong>dicas especiais</strong>, estratégias de cálculo mental e explicações passo a passo quando você precisar!
                    </p>
                    <div class="bico-dicas-pilulas">
                        <span class="bico-pilula">💡 Dicas em tempo real</span>
                        <span class="bico-pilula">🧪 Mestre das Poções</span>
                        <span class="bico-pilula">🏹 Guia na Floresta</span>
                        <span class="bico-pilula">🛡️ Aliado contra o Guardião</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- CATÁLOGO DE JOGOS DISPONÍVEIS -->
        <section id="jogos" aria-labelledby="jogos-titulo" style="margin-top: 32px;">
            <h2 id="jogos-titulo">Jogos disponíveis</h2>

            <div class="grid">
                <article class="card" aria-label="Batalha na Floresta">
                    <h3>Batalha na Floresta</h3>
                    <p>Desafios rápidos de adição e subtração! Escolha entre o Desafio Relâmpago, o épico Contra o Guardião ou o Duelo em Dupla Local.</p>

                    <?php if (isset($_SESSION['aluno_id'])): ?>
                        <a href="?pagina=batalha">Jogar Batalha</a>
                    <?php else: ?>
                        <a href="?pagina=login">Entrar para jogar</a>
                    <?php endif; ?>
                </article>

                <article class="card" aria-label="Poções Mágicas">
                    <h3>Poções Mágicas</h3>
                    <p>Entre no laboratório arcano para preparar receitas mágicas com frações, identificando partes e comparando poções poderosas com o Bicó.</p>

                    <?php if (isset($_SESSION['aluno_id'])): ?>
                        <a href="?pagina=pocoes">Jogar Poções</a>
                    <?php else: ?>
                        <a href="?pagina=login">Entrar para jogar</a>
                    <?php endif; ?>
                </article>
            </div>
        </section>

        <!-- ACOMPANHAMENTO DO ALUNO -->
        <?php if ($resumoAluno !== null): ?>
            <section id="acompanhamento" aria-labelledby="acompanhamento-titulo" style="margin-top: 32px;">
                <h2 id="acompanhamento-titulo">Meu acompanhamento</h2>
                <p>Acompanhe sua evolução e conquistas no reino de Numéria!</p>

                <div class="grid">
                    <article class="card">
                        <h3>XP acumulado</h3>
                        <p class="destaque-numero">⭐ <?= number_format($resumoAluno['xp'], 0, ',', '.') ?> XP</p>
                    </article>

                    <article class="card">
                        <h3>Partidas finalizadas</h3>
                        <p class="destaque-numero">🎮 <?= $resumoAluno['partidas'] ?></p>
                    </article>

                    <article class="card">
                        <h3>Acertos</h3>
                        <p class="destaque-numero" style="color: var(--cor-acerto) !important;">✅ <?= $resumoAluno['acertos'] ?></p>
                    </article>

                    <article class="card">
                        <h3>Erros (Tentativas)</h3>
                        <p class="destaque-numero" style="color: var(--roxo) !important;">📚 <?= $resumoAluno['erros'] ?></p>
                    </article>
                </div>
            </section>
        <?php endif; ?>

        <?php if (isset($_SESSION['aluno_id'])): ?>
            <p style="margin-top: 20px;">
                <a href="?pagina=historico" class="btn">
                    📊 Ver meu histórico detalhado de partidas
                </a>
            </p>
        <?php endif; ?>

        <!-- SOBRE A PLATAFORMA -->
        <section id="sobre" aria-labelledby="sobre-titulo" style="margin-top: 32px;">
            <h2 id="sobre-titulo">Objetivo da plataforma</h2>
            <p>
                O Numéria é uma plataforma educacional desenvolvida para
                apoiar alunos do Ensino Fundamental II no aprendizado
                de matemática por meio de jogos interativos.
            </p>

            <p>
                A plataforma busca tornar o aprendizado mais envolvente,
                registrar o progresso dos estudantes e ajudar a identificar
                conteúdos que precisam de mais atenção com o apoio do mascote Bicó.
            </p>

            <p>
                O projeto é desenvolvido pela XGuerTech, com a proposta
                de transformar desafios matemáticos em oportunidades
                de exploração, prática e descoberta.
            </p>
        </section>
    </div>
</main>
