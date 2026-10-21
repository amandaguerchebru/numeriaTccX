<?php

declare(strict_types=1);

const APP_NAME = 'Numéria';
const APP_VERSION = '0.1.0';

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
