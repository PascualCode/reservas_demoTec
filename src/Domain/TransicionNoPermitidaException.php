<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\EstadoReserva;

use RuntimeException;

final class TransicionNoPermitidaException extends RuntimeException
{
    public function __construct(EstadoReserva $actual, EstadoReserva $nuevo)
    {
        parent::__construct("No se puede cambiar una reserva {$actual->value} a {$nuevo->value}");
    }
}
