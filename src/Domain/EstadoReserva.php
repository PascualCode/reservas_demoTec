<?php

declare(strict_types=1);

namespace App\Domain;

enum EstadoReserva: string
{
    case Pendiente = 'pendiente';
    case Confirmada = 'confirmada';
    case Cancelada = 'cancelada';

    /** Indica si una reserva en este estado puede pasar a $nuevo. */
    public function puedeCambiarA(self $nuevo): bool
    {
        return match ($this) {
            self::Pendiente => in_array($nuevo, [self::Confirmada, self::Cancelada], true),
            self::Confirmada => $nuevo === self::Cancelada,
            self::Cancelada => false,
        };
    }
}

