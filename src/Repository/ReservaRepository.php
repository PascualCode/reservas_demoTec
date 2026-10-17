<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\ReservaFiltros;

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
}
