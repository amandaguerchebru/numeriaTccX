<?php

require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../helpers/bicoHelper.php';

// ==================================================
// 1. VERIFICAR SE O ALUNO ESTÁ CONECTADO
// ==================================================
if (empty($_SESSION['aluno_id'])) {
    header('Location: ?pagina=login');
    exit;
}

$tempoDesafioTotal = 60; // Tempo reduzido para 60 segundos (1 minuto) para mais dinamismo

// ==================================================
// 2. INICIAR OU REINICIAR A PARTIDA
// ==================================================
if (
    !isset($_SESSION['desafio_inicio']) ||
    !isset($_SESSION['desafio_partida_id']) ||
    isset($_GET['novo'])
) {
    // Busca o modo Desafio no banco.
    $consultaModo = $conexao->prepare(
        'SELECT id_modo_jogo
         FROM modo_jogo
         WHERE nome = :nome
         LIMIT 1'
    );
    $consultaModo->execute(['nome' => 'Desafio']);
    $idModo = $consultaModo->fetchColumn();

    if ($idModo === false) {
        exit('Erro: o modo Desafio não foi encontrado no banco de dados.');
    }

    // Cria a partida com tempo de 60 segundos.
    $consultaPartida = $conexao->prepare(
        'INSERT INTO partida
            (id_jogo, id_modo_jogo, tempo_segundos, status)
         VALUES
            (:jogo, :modo, :tempo, :status)'
    );
    $consultaPartida->execute([
        'jogo' => 1,
        'modo' => (int) $idModo,
        'tempo' => $tempoDesafioTotal,
        'status' => 'em_andamento'
    ]);
    $idPartida = (int) $conexao->lastInsertId();

    // Registra o aluno participante.
    $consultaParticipante = $conexao->prepare(
        'INSERT INTO partida_participante
            (id_partida, id_aluno, xp_total, acertos, erros)
         VALUES
            (:partida, :aluno, 0, 0, 0)'
    );
    $consultaParticipante->execute([
        'partida' => $idPartida,
        'aluno' => (int) $_SESSION['aluno_id']
    ]);

    // Guarda identificadores na sessão.
    $_SESSION['desafio_partida_id'] = $idPartida;
    $_SESSION['desafio_participante_id'] = (int) $conexao->lastInsertId();
    $_SESSION['desafio_inicio'] = time();
    $_SESSION['desafio_xp'] = 0;
    $_SESSION['desafio_acertos'] = 0;
    $_SESSION['desafio_erros'] = 0;
    unset($_SESSION['desafio_feedback']);

    if (isset($_GET['novo'])) {
        header('Location: ?pagina=desafio');
        exit;
    }
}

$_SESSION['desafio_xp'] = (int) ($_SESSION['desafio_xp'] ?? 0);
$_SESSION['desafio_acertos'] = (int) ($_SESSION['desafio_acertos'] ?? 0);
$_SESSION['desafio_erros'] = (int) ($_SESSION['desafio_erros'] ?? 0);

// ==================================================
// 3. CALCULAR TEMPO RESTANTE
// ==================================================
$tempoRestante = max(0, $tempoDesafioTotal - (time() - (int) $_SESSION['desafio_inicio']));

$mensagem = '';
$mensagemTipo = '';
$explicacao = '';
$questao = null;
$alternativas = [];

// ==================================================
// 3.1. FINALIZAR PARTIDA NO BANCO SE TEMPO ESGOTADO
// ==================================================
if ($tempoRestante <= 0 && !empty($_SESSION['desafio_partida_id'])) {
    $consultaFim = $conexao->prepare(
        "UPDATE partida
         SET status = 'finalizada',
             data_fim = NOW()
         WHERE id_partida = :partida
           AND status = 'em_andamento'"
    );
    $consultaFim->execute(['partida' => (int) $_SESSION['desafio_partida_id']]);
}

// ==================================================
// 4. PROCESSAR A RESPOSTA
// ==================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tempoRestante > 0 && isset($_POST['responder_desafio'])) {
    $idQuestao = filter_input(INPUT_POST, 'id_questao', FILTER_VALIDATE_INT);
    $idAlternativa = filter_input(INPUT_POST, 'id_alternativa', FILTER_VALIDATE_INT);
    $idParticipante = (int) ($_SESSION['desafio_participante_id'] ?? 0);
    $idPartida = (int) ($_SESSION['desafio_partida_id'] ?? 0);

    if ($idQuestao && $idAlternativa && $idParticipante && $idPartida) {
        $consulta = $conexao->prepare(
            'SELECT a.correta, q.enunciado, q.explicacao_curta
             FROM alternativa a
             JOIN questao q ON q.id_questao = a.id_questao
             WHERE a.id_alternativa = :alternativa
               AND a.id_questao = :questao
               AND q.id_jogo = 1
               AND q.ativo = 1
             LIMIT 1'
        );
        $consulta->execute([
            'alternativa' => $idAlternativa,
            'questao' => $idQuestao
        ]);
        $dadosResposta = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($dadosResposta) {
            $consultaValida = $conexao->prepare(
                "SELECT id_participante
                 FROM partida_participante
                 WHERE id_participante = :participante
                   AND id_partida = :partida
                   AND id_aluno = :aluno
                   AND EXISTS (
                       SELECT 1
                       FROM partida
                       WHERE partida.id_partida = :partida_verificacao
                         AND partida.status = 'em_andamento'
                   )
                 LIMIT 1"
            );
            $consultaValida->execute([
                'participante' => $idParticipante,
                'partida' => $idPartida,
                'aluno' => (int) $_SESSION['aluno_id'],
                'partida_verificacao' => $idPartida
            ]);

            if ($consultaValida->fetchColumn()) {
                $acertou = (int) $dadosResposta['correta'] === 1;
                $xpQuestao = 0;

                if ($acertou) {
                    $consultaXP = $conexao->prepare(
                        'SELECT xp_por_acerto
                         FROM dificuldade
                         WHERE id_dificuldade = (
                             SELECT id_dificuldade
                             FROM questao
                             WHERE id_questao = :questao
                               AND id_jogo = 1
                               AND ativo = 1
                         )
                         LIMIT 1'
                    );
                    $consultaXP->execute(['questao' => $idQuestao]);
                    $xpQuestao = (int) $consultaXP->fetchColumn();
                }

                // Registra resposta
                $consultaRegistro = $conexao->prepare(
                    'INSERT INTO resposta (id_participante, id_questao, id_alternativa, acertou, xp_ganho)
                     VALUES (:participante, :questao, :alternativa, :acertou, :xp)'
                );
                $consultaRegistro->execute([
                    'participante' => $idParticipante,
                    'questao' => $idQuestao,
                    'alternativa' => $idAlternativa,
                    'acertou' => $acertou ? 1 : 0,
                    'xp' => $xpQuestao
                ]);

                // Atualiza estatísticas
                if ($acertou) {
                    $consultaAtualiza = $conexao->prepare(
                        'UPDATE partida_participante
                         SET xp_total = xp_total + :xp,
                             acertos = acertos + 1
                         WHERE id_participante = :participante'
                    );
                    $consultaAtualiza->execute([
                        'xp' => $xpQuestao,
                        'participante' => $idParticipante
                    ]);

                    $_SESSION['desafio_xp'] += $xpQuestao;
                    $_SESSION['desafio_acertos']++;

                    $_SESSION['desafio_feedback'] = [
                        'tipo' => 'acerto',
                        'texto' => "✨ Feitiço perfeito! Você acertou e ganhou {$xpQuestao} XP!",
                        'explicacao' => $dadosResposta['explicacao_curta'] ?? ''
                    ];
                } else {
                    $consultaAtualiza = $conexao->prepare(
                        'UPDATE partida_participante
                         SET erros = erros + 1
                         WHERE id_participante = :participante'
                    );
                    $consultaAtualiza->execute(['participante' => $idParticipante]);

                    $_SESSION['desafio_erros']++;

                    $dicaErro = gerarDicaBico($dadosResposta['enunciado'], $dadosResposta['explicacao_curta'] ?? '');

                    $_SESSION['desafio_feedback'] = [
                        'tipo' => 'erro',
                        'texto' => "🦉 Ops, não foi desta vez! Mas tudo bem errar, o Bicó te ajuda a calcular:",
                        'explicacao' => $dicaErro
                    ];
                }
            }
        }
    }

    $tempoRestante = max(0, $tempoDesafioTotal - (time() - (int) $_SESSION['desafio_inicio']));
    header('Location: ?pagina=desafio');
    exit;
}

if (!empty($_SESSION['desafio_feedback'])) {
    $mensagem = $_SESSION['desafio_feedback']['texto'];
    $mensagemTipo = $_SESSION['desafio_feedback']['tipo'];
    $explicacao = $_SESSION['desafio_feedback']['explicacao'];
    unset($_SESSION['desafio_feedback']);
}

// ==================================================
// 5. BUSCAR NOVA QUESTÃO
// ==================================================
if ($tempoRestante > 0) {
    $consulta = $conexao->prepare(
        'SELECT id_questao, enunciado, explicacao_curta
         FROM questao
         WHERE id_jogo = 1
           AND ativo = 1
         ORDER BY RAND()
         LIMIT 1'
    );
    $consulta->execute();
    $questao = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($questao) {
        $consultaAlternativas = $conexao->prepare(
            'SELECT id_alternativa, texto
             FROM alternativa
             WHERE id_questao = :questao
             ORDER BY id_alternativa'
        );
        $consultaAlternativas->execute(['questao' => $questao['id_questao']]);
        $alternativas = $consultaAlternativas->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<main class="container">
    <section class="hero">
        <span class="badge">Batalha na Floresta · Modo Desafio</span>
        <h2>Desafio Relâmpago (60 Segundos)</h2>
        <p>Responda o máximo de contas de adição e subtração antes que o tempo acabe!</p>

        <!-- CRONÔMETRO E STATUS -->
        <div class="painel-status desafio-painel-status">
            <div class="desafio-hud-grid">
                <div>
                    <span class="label-hud">Tempo restante:</span>
                    <strong id="cronometro" class="cronometro-destaque">01:00</strong>
                </div>
                <div>
                    <span class="label-hud">XP Conquistado:</span>
                    <strong id="pontuacaoXP">⭐ <?= (int) $_SESSION['desafio_xp'] ?> XP</strong>
                </div>
                <div>
                    <span class="label-hud">Acertos:</span>
                    <strong id="pontuacaoAcertos" style="color: var(--amarelo);">✅ <?= (int) $_SESSION['desafio_acertos'] ?></strong>
                </div>
                <div>
                    <span class="label-hud">Erros:</span>
                    <strong id="pontuacaoErros" style="color: #ffb4b4;">❌ <?= (int) $_SESSION['desafio_erros'] ?></strong>
                </div>
            </div>
            <progress id="barraTempo" value="<?= $tempoRestante ?>" max="<?= $tempoDesafioTotal ?>" style="width: 100%; margin-top: 14px;" aria-label="Tempo restante"></progress>
        </div>

        <!-- FEEDBACK DO BICÓ (ACERTO / ERRO) -->
        <?php if ($mensagem !== ''): ?>
            <?= renderBicoFeedbackHtml($mensagem, $mensagemTipo, $explicacao) ?>
        <?php endif; ?>

        <!-- ÁREA FINAL DO DESAFIO (QUANDO O TEMPO ACABA) -->
        <article
            id="areaFim"
            class="card resultado-card"
            <?= $tempoRestante > 0 ? 'hidden' : '' ?>
            style="margin-top: 24px;">

            <h3>⏱️ Desafio Relâmpago Encerrado!</h3>
            <p>O tempo de 60 segundos terminou! Veja seu desempenho nesta aventura com o Bicó:</p>

            <?php
            $acertosDesafio = (int) ($_SESSION['desafio_acertos'] ?? 0);
            $errosDesafio = (int) ($_SESSION['desafio_erros'] ?? 0);
            $totalRespondidasDesafio = $acertosDesafio + $errosDesafio;
            $xpDesafio = (int) ($_SESSION['desafio_xp'] ?? 0);
            ?>

            <div class="grid" style="margin: 20px 0;">
                <article class="card">
                    <h4>Questões Respondidas</h4>
                    <p class="destaque-numero"><?= $totalRespondidasDesafio ?></p>
                </article>

                <article class="card">
                    <h4>Acertos Rápidos</h4>
                    <p class="destaque-numero" style="color: var(--cor-acerto) !important;">✅ <?= $acertosDesafio ?></p>
                </article>

                <article class="card">
                    <h4>Tentativas / Erros</h4>
                    <p class="destaque-numero" style="color: var(--cor-erro) !important;">❌ <?= $errosDesafio ?></p>
                </article>

                <article class="card">
                    <h4>XP Total Conquistado</h4>
                    <p class="destaque-numero" style="color: var(--cor-ouro-escuro) !important;">⭐ <?= $xpDesafio ?> XP</p>
                </article>
            </div>

            <div style="margin-top: 20px;">
                <a href="?pagina=desafio&novo=1" class="btn">
                    ⚡ Jogar novamente (Mais 60s)
                </a>
                <a href="?pagina=batalha" class="btn btn-secondary">
                    Voltar aos modos de Batalha
                </a>
            </div>
        </article>

        <!-- ÁREA DA QUESTÃO ATIVA -->
        <div id="areaQuestao" <?= $tempoRestante <= 0 ? 'hidden' : '' ?>>
            <?php if ($questao): ?>
                <?php
                $dicaAtiva = gerarDicaBico($questao['enunciado'], $questao['explicacao_curta'] ?? '');
                ?>
                <article class="card">
                    <span class="badge">Desafio de Cálculo Rápido</span>
                    <h3 style="font-size: 1.4rem; margin: 14px 0;">
                        <?= htmlspecialchars($questao['enunciado'], ENT_QUOTES, 'UTF-8') ?>
                    </h3>

                    <!-- DICA MÁGICA DO BICÓ SOB DEMANDA -->
                    <?= renderBicoDicaBotaoHtml($dicaAtiva) ?>

                    <?php if (count($alternativas) > 0): ?>
                        <form method="POST" id="formResposta" style="margin-top: 18px;">
                            <input type="hidden" name="id_questao" value="<?= (int) $questao['id_questao'] ?>">

                            <fieldset>
                                <legend class="sr-only">Selecione a resposta correta</legend>
                                <?php foreach ($alternativas as $alternativa): ?>
                                    <label>
                                        <input
                                            type="radio"
                                            name="id_alternativa"
                                            value="<?= (int) $alternativa['id_alternativa'] ?>"
                                            required>
                                        <strong><?= htmlspecialchars($alternativa['texto'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    </label>
                                    <br>
                                <?php endforeach; ?>
                            </fieldset>

                            <div style="margin-top: 18px;">
                                <button type="submit" name="responder_desafio" value="1" class="btn">
                                    Confirmar Resposta ⚡
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <p>Esta questão ainda não possui alternativas.</p>
                    <?php endif; ?>
                </article>

                <!-- BOTÃO DE ABANDONAR BATALHA -->
                <p style="margin-top: 24px;">
                    <a href="?pagina=abandonar_desafio" class="btn btn-secondary" onclick="return confirm('Tem certeza que deseja abandonar este desafio relâmpago?');">
                        🏳️ Abandonar desafio e voltar aos modos
                    </a>
                </p>

            <?php else: ?>
                <p>Não foi encontrada uma questão ativa para este jogo.</p>
            <?php endif; ?>
        </div>

    </section>
</main>

<!-- JAVASCRIPT DO CRONÔMETRO -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let segundos = <?= (int) $tempoRestante ?>;
        const tempoTotal = <?= (int) $tempoDesafioTotal ?>;

        const cronometro = document.getElementById('cronometro');
        const barraTempo = document.getElementById('barraTempo');
        const formulario = document.getElementById('formResposta');
        const areaQuestao = document.getElementById('areaQuestao');
        const areaFim = document.getElementById('areaFim');

        let intervaloCronometro = null;

        function atualizarExibicao() {
            const minutos = Math.floor(segundos / 60);
            const resto = segundos % 60;
            cronometro.textContent = String(minutos).padStart(2, '0') + ':' + String(resto).padStart(2, '0');
            if (barraTempo) {
                barraTempo.value = segundos;
            }
        }

        function finalizarDesafio() {
            if (intervaloCronometro !== null) {
                clearInterval(intervaloCronometro);
                intervaloCronometro = null;
            }
            segundos = 0;
            cronometro.textContent = '00:00';
            if (barraTempo) barraTempo.value = 0;

            if (formulario) {
                formulario.querySelectorAll('input, button').forEach(function(el) {
                    el.disabled = true;
                });
            }

            if (areaQuestao) areaQuestao.hidden = true;
            if (areaFim) areaFim.hidden = false;

            fetch('?pagina=finalizar_desafio', {
                method: 'POST',
                credentials: 'same-origin'
            }).catch(function(err) {
                console.error(err);
            });
        }

        atualizarExibicao();

        if (segundos <= 0) {
            finalizarDesafio();
            return;
        }

        intervaloCronometro = setInterval(function() {
            segundos--;
            if (segundos <= 0) {
                finalizarDesafio();
                return;
            }
            atualizarExibicao();
        }, 1000);
    });
</script>
