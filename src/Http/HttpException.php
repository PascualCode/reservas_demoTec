<?php

declare(strict_types=1);

namespace App\Http;

use RuntimeException;

class HttpException extends RuntimeException
{
    // Código de estado HTTP, mensaje y cabeceras opcionales para la respuesta
    /** @param array<string, string> $headers */
    public function __construct(
        private int $statusCode,
        string $message,
        private array $headers = [],
    ) {
        parent::__construct($message);
    }

    // Devuelve el código de estado HTTP
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    // Devuelve las cabeceras opcionales para la respuesta
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
