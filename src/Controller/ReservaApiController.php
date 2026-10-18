<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\ReservaNotFoundException;
use App\Http\HttpException;
use App\Http\JsonResponse;
use App\Service\ReservaService;
use App\Validation\Fecha;
use App\Domain\ReservaFiltros;
use App\Domain\EstadoReserva;
use App\Http\ValidationException;
use JsonException;

final class ReservaApiController
{
    public function __construct(private ReservaService $service)
    {
    }

    // Devuelve la informacion de una reserva en formato JSON
    public function detalle(string $id): void
    {
        try {
            $reserva = $this->service->getById((int) $id);
        } catch (ReservaNotFoundException $e) {
            throw new HttpException(404, 'Reserva no encontrada');
        }

        JsonResponse::send($reserva);
    }

    // Devuelve todas las reservas en JSON junto con el total: {"reservas": [...], "total": N}
    public function listado(): void
    {
        $filtros = $this->crearFiltros($_GET);
        $reservas = $this->service->getAll($filtros);
        $response = [
            'reservas' => $reservas,
            'total' => count($reservas)
        ];
        JsonResponse::send($response);
    }

    // Crea una reserva a partir del JSON del cuerpo. Responde 201 con la reserva y la cabecera Location.
    public function crear(): void
    {
        $datos = $this->leerJson();
        $reserva = $this->service->crear($datos);
        JsonResponse::send($reserva, 201, ['Location' => '/api/reservas/' . $reserva['id']]);
    }

    // Devuelve el parametro como texto sin espacios, o null si no viene o esta vacio.
    private function texto(array $query, string $clave): ?string
    {
        $valor = $query[$clave] ?? null;
        if ($valor === null) {
            return null;
        }
        if (!is_string($valor)) {
            throw new ValidationException([$clave => 'Valor no válido'], 'Filtros no válidos');
        }
        $valor = trim($valor);
        return $valor === '' ? null : $valor;
    }

    // Crea un objeto ReservaFiltros a partir de los parametros de la query string, validando los valores y lanzando ValidationException si hay errores.
    private function crearFiltros(array $query): ReservaFiltros
    {
        $errores = [];

        $estado = null;
        $estadoTexto = $this->texto($query, 'estado');
        if ($estadoTexto !== null) {
            $estado = EstadoReserva::tryFrom($estadoTexto);   // convierte 'pendiente' en EstadoReserva::Pendiente
            if ($estado === null) {
                $errores['estado'] = 'Estado no válido. Valores permitidos: pendiente, confirmada, cancelada';
            }
        }

        $desde = $this->texto($query, 'desde');
        if ($desde !== null && !Fecha::esValida($desde)) {
            $errores['desde'] = 'Fecha no válida, usa el formato AAAA-MM-DD';
        }

        $hasta = $this->texto($query, 'hasta');
        if ($hasta !== null && !Fecha::esValida($hasta)) {
            $errores['hasta'] = 'Fecha no válida, usa el formato AAAA-MM-DD';
        }

        if ($desde !== null && $hasta !== null && Fecha::esValida($desde) && Fecha::esValida($hasta)) {
            if ($desde > $hasta) {
                $errores['hasta'] = 'No puede ser anterior a la fecha desde';
            }
        }

        $q = $this->texto($query, 'q');
        if ($q !== null && mb_strlen($q) > 100) {
            $errores['q'] = 'La búsqueda no puede superar los 100 caracteres';
        }

        if (!empty($errores)) {
            throw new ValidationException($errores, 'Filtros no válidos');
        }

        return new ReservaFiltros($estado, $desde, $hasta, $q);
    }

    /** Lee el cuerpo de la petición como JSON. Lanza 400 si no es un objeto JSON válido. */
    private function leerJson(): array
    {
        $cuerpo = file_get_contents('php://input');

        try {
            $datos = json_decode($cuerpo, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new HttpException(400, 'El cuerpo de la petición no es un JSON válido');
        }

        if (!is_array($datos))
            throw new HttpException(400, 'Se esperaba un objeto JSON');

        return $datos;
    }

}
