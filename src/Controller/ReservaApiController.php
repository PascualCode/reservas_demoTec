<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\ReservaNotFoundException;
use App\Http\HttpException;
use App\Http\JsonResponse;
use App\Service\ReservaService;

final class ReservaApiController
{
    public function __construct(private ReservaService $service)
    {
    }

    // Devuelve la informacion de una reserva en formato JSON
    public function detalle(string $id): void
    {
        try {
            $reserva = $this->service->getById((int)$id);
        } catch (ReservaNotFoundException $e) {
            throw new HttpException(404, 'Reserva no encontrada');
        }
        
        JsonResponse::send($reserva);
    }

    // Devuelve todas las reservas en JSON junto con el total: {"reservas": [...], "total": N}
    public function listado(): void
    {
        $reservas = $this->service->getAll();
        $response = [
            'reservas' => $reservas,
            'total' => count($reservas)
        ];
        JsonResponse::send($response);
    }
}
