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
        // Errores esperados (400, 404, 405…): su mensaje está escrito para el cliente
        if ($e instanceof HttpException) {
            $body = ['error' => $e->getMessage()];
            if ($e instanceof ValidationException) {
                $body['errores'] = $e->getErrores();
            }
            JsonResponse::send($body, $e->getStatusCode(), $e->getHeaders());
            return;
        }

        // Errores inesperados: el detalle va al log y nunca al cliente, salvo en desarrollo
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
