<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\ReservaNotFoundException;
use App\Repository\ReservaRepository;
use App\Domain\ReservaFiltros;
use App\Validation\ReservaValidator;
use App\Domain\EstadoReserva;
use App\Domain\TransicionNoPermitidaException;

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
     * @param array<string, mixed> $datos
     * @return array<string, mixed>
     */
    public function crear(array $datos): array
    {

        $datosLimpios = $this->validator->validar($datos);
        $fechaActual = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        $id = $this->repository->enTransaccion(function () use ($datosLimpios, $fechaActual): int {
            $datosLimpios['estado'] = EstadoReserva::Pendiente->value;
            $datosLimpios['fecha_creacion'] = $fechaActual;
            $id = $this->repository->insert($datosLimpios);
            $this->repository->insertEvento($id, 'creada', 'Reserva creada', $fechaActual);
            return $id;
        });

        return $this->getById($id);
    }

    /**
     * Cambia el estado de una reserva y añade el evento al historial, todo en una transacción.
     * @return array<string, mixed>
     */
    public function cambiarEstado(int $id, EstadoReserva $nuevo): array
    {

        $fechaActual = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        $this->repository->enTransaccion(function() use ($id, $nuevo, $fechaActual): void{
            $actual = $this->repository->findEstadoParaActualizar($id);
            if ($actual === null) {
                throw new ReservaNotFoundException($id);
            }
            if (!$actual->puedeCambiarA($nuevo)) {
                throw new TransicionNoPermitidaException($actual, $nuevo);
            }
            $this->repository->updateEstado($id, $nuevo);
            $this->repository->insertEvento($id, $nuevo->value, "Estado cambiado de {$actual->value} a {$nuevo->value}", $fechaActual);

        });

        return $this->getById($id);
    }
}
