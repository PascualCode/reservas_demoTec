<?php

declare(strict_types=1);

namespace App\Domain;

enum EstadoReserva: string
{
    case Pendiente = 'pendiente';
    case Confirmada = 'confirmada';
    case Cancelada = 'cancelada';
}
