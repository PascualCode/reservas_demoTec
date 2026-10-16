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
