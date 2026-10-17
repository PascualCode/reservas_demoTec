<?php

declare(strict_types=1);

namespace App\Domain;

use RuntimeException;

final class ReservaNotFoundException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("No existe la reserva {$id}");
    }
}
