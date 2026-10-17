<?php

declare(strict_types=1);

namespace App\Http;

final class ValidationException extends HttpException
{
    /** @param array<string, string> $errores */
    public function __construct(private array $errores, string $message = 'Datos no válidos')
    {
        parent::__construct(400, $message);
    }

    /**
     * Obtiene los errores de validacion.
     * @return array<string, string>
     */
    public function getErrores(): array
    {
        return $this->errores;
    }
}
