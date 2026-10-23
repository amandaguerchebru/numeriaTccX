-- Numéria: diferenciação entre aluno e professor.
-- (O sistema também cria esta coluna sozinho, na primeira vez que alguém
-- cadastra ou entra, caso você esqueça de rodar este arquivo.)

ALTER TABLE aluno
    ADD COLUMN tipo ENUM('aluno', 'professor') NOT NULL DEFAULT 'aluno'
    AFTER email;
