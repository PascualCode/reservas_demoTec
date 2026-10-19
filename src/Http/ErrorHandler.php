<?php

declare(strict_types=1);

namespace App\Http;

use ErrorException;
use Throwable;
use Twig\Environment;

final class ErrorHandler
{
    public function __construct(private bool $debug, private bool $esApi, private ?Environment $twig = null)
    {
    }

    // Maneja una excepción y envía una respuesta JSON con el código de estado y las cabeceras indicadas
    public function handle(Throwable $e): void
    {

        if ($e instanceof HttpException) {
            $status = $e->getStatusCode();
            $mensaje = $e->getMessage();
            $headers = $e->getHeaders();

        } else {
            error_log((string) $e);
            $status = 500;
            $mensaje = 'Error interno del servidor';
            $headers = [];
        }

        $detalle = ($status === 500 && $this->debug) ? $e->getMessage() : null;
        
        if ($this->esApi) {
            $body = ['error' => $mensaje];
            if ($e instanceof ValidationException) {
                $body['errores'] = $e->getErrores();
            }
            if ($detalle !== null) {
                $body['detalle'] = $detalle;
            }
            JsonResponse::send($body, $status, $headers);
            return;
        }

        HtmlResponse::send($this->paginaDeError($status, $mensaje, $detalle), $status, $headers);
    }

    // Convierte los warnings de PHP en excepciones que se pueden gestionar por el manejador de errores
    public static function convertWarningsToExceptions(): void
    {
        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            if ($severity === E_DEPRECATED || $severity === E_USER_DEPRECATED) {
                error_log("Deprecated: {$message} en {$file}:{$line}");
                return true;
            }
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
    }

    /** Renderiza la página de error. Si Twig no está disponible o falla, devuelve un HTML mínimo. */
    private function paginaDeError(int $status, string $mensaje, ?string $detalle): string
    {
        if ($this->twig !== null) {
            try {
                return $this->twig->render('error.html.twig', [
                    'status' => $status,
                    'mensaje' => $mensaje,
                    'detalle' => $detalle,
                ]);
            } catch (Throwable $e) {
                error_log((string) $e);
            }
        }

        return '<!DOCTYPE html><html lang="es"><meta charset="utf-8"><title>Error</title><h1>Error ' . $status . '</h1>';
    }
}
