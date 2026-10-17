<?php

declare(strict_types=1);

namespace App\Validation;

use DateTimeImmutable;

final class Fecha
{
    /** Comprueba que la fecha tiene formato AAAA-MM-DD y que existe. */
    public static function esValida(string $fecha): bool
    {
        $objeto = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        return $objeto !== false && $objeto->format('Y-m-d') === $fecha;
    }
}
