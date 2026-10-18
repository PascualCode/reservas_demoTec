<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\ReservaNotFoundException;
use App\Repository\ReservaRepository;
use App\Domain\ReservaFiltros;
use APP\Validation\ReservaValidator;
use App\Domain\EstadoReserva;

use DateTimeImmutable;

final class ReservaService
{
    public function __construct(
        private ReservaRepository $repository,
        private ReservaValidator $validator
    ) {
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

    /**
     * Valida y crea una reserva nueva (siempre pendiente) con su evento "creada".
     *
     * @param array<string, mixed> $datos
     * @return array<string, mixed>
     */
    public function crear(array $datos): array
    {

        $datosLimpios = $this->validator->validar($datos);
        $fechaActual = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        $id = $this->repository->enTransaccion(function() use ($datosLimpios, $fechaActual): int {
            $datosLimpios['estado'] = EstadoReserva::Pendiente->value;
            $datosLimpios['fecha_creacion'] = $fechaActual;
            $id = $this->repository->insert($datosLimpios);
            $this->repository->insertEvento($id, 'creada', 'Reserva creada', $fechaActual);
            return $id;
        });

        return $this->getById($id);
    }
}
