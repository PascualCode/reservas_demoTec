<?php

declare(strict_types=1);

namespace App\Http;

final class JsonResponse
{
    /** @param array<string, string> $headers */
    public static function send(mixed $data, int $status = 200, array $headers = []): void
    {
        // Código de estado
        http_response_code($status);

        // Cabeceras fijas
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');

        // Cabeceras extra ($headers)
        foreach ($headers as $name => $value) {
            header("{$name}: {$value}");
        }
        
        // Cuerpo en JSON
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
