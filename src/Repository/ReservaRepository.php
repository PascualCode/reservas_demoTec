<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\ReservaFiltros;
use App\Domain\EstadoReserva;

use Throwable;
use PDO;

final class ReservaRepository
{

    private const COLUMNAS = 'id, nombre_huesped, email_huesped, nombre_alojamiento, fecha_entrada, fecha_salida, estado, importe, notas, fecha_creacion';

    public function __construct(private PDO $pdo)
    {
    }

    //Busca una reserva por su ID y devuelve un array asociativo con los datos de la reserva o null si no se encuentra.
    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT ' . self::COLUMNAS . ' FROM reserva WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }
        return $row;
    }

    //Busca todos los eventos asociados a una reserva por su ID y devuelve un array de arrays asociativos con los datos de los eventos.
    /** @return array<int, array<string, mixed>> */
    public function findEventos(int $reservaId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, tipo, descripcion, fecha_creacion FROM reserva_evento WHERE reserva_id = :reserva_id ORDER BY fecha_creacion, id');
        $stmt->execute(['reserva_id' => $reservaId]);
        return $stmt->fetchAll();
    }

    /**
     * Busca todas las reservas y devuelve un array de arrays asociativos con los datos de las reservas.
     * @return array<int, array<string, mixed>>
     */
    public function findAll(ReservaFiltros $filtros): array
    {
        $condiciones = [];
        $parametros = [];

        if ($filtros->estado !== null) {
            $condiciones[] = 'estado = :estado';
            $parametros['estado'] = $filtros->estado->value;
        }

        if ($filtros->desde !== null) {
            $condiciones[] = 'fecha_salida > :desde';
            $parametros['desde'] = $filtros->desde;
        }

        if ($filtros->hasta !== null) {
            $condiciones[] = 'fecha_entrada <= :hasta';
            $parametros['hasta'] = $filtros->hasta;
        }

        if ($filtros->q !== null) {
            $condiciones[] = 'nombre_huesped LIKE :q';
            $parametros['q'] = '%' . addcslashes($filtros->q, '%_\\') . '%';
        }

        $sql = 'SELECT ' . self::COLUMNAS . ' FROM reserva';
        if ($condiciones !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $condiciones);
        }
        $sql .= ' ORDER BY fecha_entrada, id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    /** Inserta una reserva y devuelve su id. */
    public function insert(array $datos): int
    {
        $sql = 'INSERT INTO reserva (nombre_huesped, email_huesped, nombre_alojamiento, fecha_entrada,
                                fecha_salida, estado, importe, notas, fecha_creacion)
                VALUES (:nombre_huesped, :email_huesped, :nombre_alojamiento, :fecha_entrada,
                                :fecha_salida, :estado, :importe, :notas, :fecha_creacion)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nombre_huesped' => $datos['nombre_huesped'],
            'email_huesped' => $datos['email_huesped'],
            'nombre_alojamiento' => $datos['nombre_alojamiento'],
            'fecha_entrada' => $datos['fecha_entrada'],
            'fecha_salida' => $datos['fecha_salida'],
            'estado' => $datos['estado'],
            'importe' => $datos['importe'],
            'notas' => $datos['notas'],
            'fecha_creacion' => $datos['fecha_creacion']
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** Añade un evento al historial de una reserva. */
    public function insertEvento(int $reservaId, string $tipo, string $descripcion, string $fechaCreacion): void
    {
        $sql = 'INSERT INTO reserva_evento (reserva_id, tipo, descripcion, fecha_creacion) 
                VALUES (:reserva_id, :tipo, :descripcion, :fecha_creacion)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'reserva_id' => $reservaId,
            'tipo' => $tipo,
            'descripcion' => $descripcion,
            'fecha_creacion' => $fechaCreacion
        ]);
    }

    /**
     * Ejecuta $operacion dentro de una transacción: si algo falla, se deshace todo.
     *
     * @template T
     * @param callable(): T $operacion
     * @return T
     */
    public function enTransaccion(callable $operacion): mixed
    {
        $this->pdo->beginTransaction();
        try {
            $resultado = $operacion();
            $this->pdo->commit();
            return $resultado;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /** Devuelve el estado actual de la reserva y BLOQUEA su fila hasta el final de la transacción. */
    public function findEstadoParaActualizar(int $id): ?EstadoReserva
    {

        $sql = 'SELECT estado FROM reserva WHERE id = :id FOR UPDATE';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $id
        ]);

        $valor = $stmt->fetchColumn();
        if ($valor === false) {
            return null;
        }
        return EstadoReserva::from($valor);

    }

    /** Cambia el estado de una reserva. */
    public function updateEstado(int $id, EstadoReserva $estado): void
    {
        $sql = 'UPDATE reserva SET estado = :estado WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'estado' => $estado->value,
            'id' => $id
        ]);
    }
}
