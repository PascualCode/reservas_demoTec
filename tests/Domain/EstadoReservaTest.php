<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\EstadoReserva;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EstadoReservaTest extends TestCase
{
    /** @return array<string, array{EstadoReserva, EstadoReserva, bool}> */
    public static function transiciones(): array
    {
        return [
            'pendiente → confirmada' => [EstadoReserva::Pendiente, EstadoReserva::Confirmada, true],
            'pendiente → cancelada' => [EstadoReserva::Pendiente, EstadoReserva::Cancelada, true],
            'confirmada → cancelada' => [EstadoReserva::Confirmada, EstadoReserva::Cancelada, true],
            'pendiente → pendiente' => [EstadoReserva::Pendiente, EstadoReserva::Pendiente, false],
            'confirmada → confirmada' => [EstadoReserva::Confirmada, EstadoReserva::Confirmada, false],
            'cancelada → cancelada' => [EstadoReserva::Cancelada, EstadoReserva::Cancelada, false],
            'confirmada → pendiente' => [EstadoReserva::Confirmada, EstadoReserva::Pendiente, false],
            'cancelada → confirmada' => [EstadoReserva::Cancelada, EstadoReserva::Confirmada, false],
            'cancelada → pendiente' => [EstadoReserva::Cancelada, EstadoReserva::Pendiente, false]
        ];
    }

    #[DataProvider('transiciones')]
    public function testPuedeCambiarA(EstadoReserva $actual, EstadoReserva $nuevo, bool $esperado): void
    {
        $this->assertSame($esperado, $actual->puedeCambiarA($nuevo));
    }
}
