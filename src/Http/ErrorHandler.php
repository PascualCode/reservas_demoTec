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

    public function handle(Throwable $e): void
    {
        // 1. Si es una HttpException → JSON con su código, su mensaje y sus cabeceras
        if ($e instanceof HttpException) {
            JsonResponse::send(
                ['error' => $e->getMessage()],
                $e->getStatusCode(),
                $e->getHeaders(),
            );
            return;
        } else {
            error_log((string) $e);
            $body = ['error' => 'Error interno del servidor'];
            if ($this->debug) {
                $body['detalle'] = $e->getMessage();
            }
            JsonResponse::send($body, 500);
        }
        // 2. Si es cualquier otra cosa:
        //    a) Escribe el error completo en el log
        //    b) Prepara un 500 con el mensaje "Error interno del servidor"
        //    c) Solo si $this->debug es true, añade el mensaje real en una clave 'detalle'
    }

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
