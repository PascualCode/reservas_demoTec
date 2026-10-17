<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\ReservaNotFoundException;
use App\Repository\ReservaRepository;

final class ReservaService
{
    public function __construct(private ReservaRepository $repository)
    {
    }

    //Busca la reserva por su id y la devuelve si existe, si no lanza una excepción de tipo ReservaNotFoundException
    /** @return array<string, mixed> */
    public function getById(int $id): array
    {
        $reserva = $this->repository->findById($id);

        if ($reserva === null) {
            throw new ReservaNotFoundException($id);
        }

        $reserva['eventos'] = $this->repository->findEventos($id);
        return $reserva;
    }
}
