<?php

declare(strict_types=1);

namespace App\Http;

use ErrorException;
use Throwable;

final class ErrorHandler
{
    public function __construct(private bool $debug)
    {
    }

    // Maneja una excepción y envía una respuesta JSON con el código de estado y las cabeceras indicadas
    public function handle(Throwable $e): void
    {
        if ($e instanceof HttpException) {
            JsonResponse::send(
                ['error' => $e->getMessage()],
                $e->getStatusCode(),
                $e->getHeaders(),
            );
            return;
        }
        error_log((string) $e);
        $body = ['error' => 'Error interno del servidor'];
        if ($this->debug) {
            $body['detalle'] = $e->getMessage();
        }
        JsonResponse::send($body, 500);
    }

    // Convierte los warnings de PHP en excepciones que se pueden gestionar por el manejador de errores
    public static function convertWarningsToExceptions(): void
    {
        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
    }
}
