<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\ReservaNotFoundException;
use App\Repository\ReservaRepository;
use App\Domain\ReservaFiltros;

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

    /**
     * Busca todas las reservas y devuelve un array de arrays asociativos con los datos de las reservas.
     * @return array<int, array<string, mixed>>
     */
    public function getAll(ReservaFiltros $filtros): array
    {
        return $this->repository->findAll($filtros);
    }
}
