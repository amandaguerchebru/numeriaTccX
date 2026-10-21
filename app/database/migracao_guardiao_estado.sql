CREATE TABLE IF NOT EXISTS guardiao_estado (
    id_participante INT NOT NULL,
    id_partida INT NOT NULL,
    id_dificuldade INT NOT NULL,
    id_questao_atual INT DEFAULT NULL,
    vidas_jogador TINYINT UNSIGNED NOT NULL DEFAULT 3,
    vidas_guardiao TINYINT UNSIGNED NOT NULL DEFAULT 3,
    questoes_respondidas TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id_participante),
    KEY idx_guardiao_estado_partida (id_partida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
