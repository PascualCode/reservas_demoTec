<?php

declare(strict_types=1);

namespace App\Http;

final class HtmlResponse
{
    // Envía una respuesta JSON con el código de estado y las cabeceras indicadas
    /** @param array<string, string> $headers */
    public static function send(string $html, int $status = 200, array $headers = []): void
    {
        // Código de estado
        http_response_code($status);

        // Cabeceras fijas
        header('Content-Type: text/html; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: same-origin');
        header('Cache-Control: no-store');

        // Cabeceras extra ($headers)
        foreach ($headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // Cuerpo en JSON
        echo $html;
    }
}
