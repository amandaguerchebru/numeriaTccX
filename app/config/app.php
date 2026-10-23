<?php

declare(strict_types=1);

const APP_NAME = 'Numéria';
const APP_VERSION = '0.1.0';

// Duração do modo Desafio, em segundos (2 minutos).
const DESAFIO_DURACAO = 120;

// Código que o professor digita ao criar a conta (evita que alunos se cadastrem como professor).
// TROQUE por um código só da escola antes de apresentar ou publicar o sistema.
const CODIGO_PROFESSOR = 'NUMERIA-PROF';

function asset(string $path): string
{
    return app_url($path);
}

function app_url(string $path = ''): string
{
    // Descobre sozinho a pasta pública (funciona em qualquer pasta do Laragon/XAMPP)
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

    return $base . ($path !== '' ? '/' . ltrim($path, '/') : '');
}
