<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class ReservaRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    //Busca una reserva por su ID y devuelve un array asociativo con los datos de la reserva o null si no se encuentra.
    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, nombre_huesped, email_huesped, nombre_alojamiento, fecha_entrada, fecha_salida, estado, importe, notas, fecha_creacion FROM reserva WHERE id = :id');
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
}
