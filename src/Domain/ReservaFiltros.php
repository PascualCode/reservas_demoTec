<?php

declare(strict_types=1);

namespace App\Domain;

final class ReservaFiltros
{
    public function __construct(
        public readonly ?EstadoReserva $estado = null,
        public readonly ?string $desde = null,
        public readonly ?string $hasta = null,
        public readonly ?string $q = null,
    ) {
    }
}
