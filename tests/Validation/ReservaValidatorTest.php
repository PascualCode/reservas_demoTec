<?php

declare(strict_types=1);

namespace App\Tests\Validation;

use App\Http\ValidationException;
use App\Validation\ReservaValidator;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReservaValidatorTest extends TestCase
{
    /** "Hoy" fijo: el resultado no depende del dia en que se ejecuten los tests */
    private function validador(): ReservaValidator
    {
        return new ReservaValidator(new DateTimeImmutable('2026-10-17'));
    }

    /** Datos que pasan la validacion. Cada test cambia solo lo que quiere probar. */
    private function datosValidos(array $cambios = []): array
    {
        return array_merge([
            'nombre_huesped' => "Seán O'Brien",
            'email_huesped' => 'sean.obrien@example.com',
            'nombre_alojamiento' => 'Hotel Neptuno',
            'fecha_entrada' => '2026-11-01',
            'fecha_salida' => '2026-11-04',
            'importe' => '360.00',
        ], $cambios);
    }

    /** Valida esperando que falle y devuelve los errores. Si no lanza la excepcion, el test falla. */
    private function erroresDe(array $datos): array
    {
        try {
            $this->validador()->validar($datos);
        } catch (ValidationException $e) {
            return $e->getErrores();
        }

        $this->fail('Se esperaba una ValidationException');
    }

    // PROVIDERS:
    public static function salidasNoPosteriores(): array
    {
        return [
            'mismo día que la entrada' => ['2026-11-01'],
            'antes de la entrada' => ['2026-10-30'],
        ];
    }

    public static function fechasNoValidas(): array
    {
        return [
            'dia del mes incorrecto' => ['2026-11-31'],
            'mes del año incorrecto' => ['2026-13-01'],
            'formato de fecha incorrecto' => ['01/11/2026'],
            'formato de fecha incompleto' => ['2026-1-5']
        ];
    }

    public static function importesNoValidos(): array
    {
        $formato = 'Importe no válido: número positivo con punto decimal y hasta 2 decimales';

        return [
            'negativo en texto' => ['-10', $formato],
            'negativo como número' => [-5, $formato],
            'coma decimal' => ['12,50', $formato],
            'tres decimales' => ['10.999', $formato],
            'letras' => ['abc', $formato],
            'más de 8 cifras' => ['123456789', $formato],
            'array' => [['10'], 'Debe ser un número'],
            'booleano' => [true, 'Debe ser un número'],
            'vacío' => ['', 'Es obligatorio'],
        ];
    }

    public static function importesValidos(): array
    {
        return [
            'texto con dos decimales' => ['360.00', '360.00'],
            'número entero' => [360, '360'],
            'número con decimales' => [99.5, '99.5'],
            'cero' => ['0', '0'],
            'con espacios alrededor' => ['  85.5 ', '85.5'],
        ];
    }


    // TESTS:
    public function testEmailNoValidoDaError(): void
    {
        $errores = $this->erroresDe($this->datosValidos(['email_huesped' => 'esto-no-es-un-email']));
        $this->assertSame(['email_huesped' => 'El email no es válido'], $errores);
    }

    public function testDatosValidosSeDevuelvenLimpios(): void
    {
        $resultado = $this->validador()->validar($this->datosValidos([
            'nombre_huesped' => ' Ana Ruiz ',
            'notas' => '  ',
            'estado' => 'confirmada',
            'id' => 99,
        ]));

        $this->assertSame([
            'nombre_huesped' => 'Ana Ruiz',
            'email_huesped' => 'sean.obrien@example.com',
            'nombre_alojamiento' => 'Hotel Neptuno',
            'fecha_entrada' => '2026-11-01',
            'fecha_salida' => '2026-11-04',
            'importe' => '360.00',
            'notas' => null,
        ], $resultado);
    }

    public function testCamposObligatoriosVacios(): void
    {
        $errores = $this->erroresDe([]);
        $this->assertSame([
            'nombre_huesped' => 'Es obligatorio',
            'nombre_alojamiento' => 'Es obligatorio',
            'email_huesped' => 'Es obligatorio',
            'fecha_entrada' => 'Es obligatorio',
            'fecha_salida' => 'Es obligatorio',
            'importe' => 'Es obligatorio'
        ], $errores);
    }

    #[DataProvider('salidasNoPosteriores')]
    public function testFechaSalidaDebeSerPosteriorEntrada(string $salida): void
    {
        $errores = $this->erroresDe($this->datosValidos(['fecha_salida' => $salida]));
        $this->assertSame(['fecha_salida' => 'Debe ser posterior a la fecha de entrada'], $errores);
    }

    public function testFechaEntradaAnteriorHoy(): void
    {
        $errores = $this->erroresDe($this->datosValidos(['fecha_entrada' => '2026-10-16']));
        $this->assertSame(['fecha_entrada' => 'No puede ser anterior a hoy'], $errores);
    }

    public function testFechaEntradaHoy(): void
    {
        $resultado = $this->validador()->validar($this->datosValidos(['fecha_entrada' => '2026-10-17']));
        $this->assertSame('2026-10-17', $resultado['fecha_entrada']);
    }

    #[DataProvider('fechasNoValidas')]
    public function testFechaNoValidaError(string $fecha): void
    {
        $errores = $this->erroresDe($this->datosValidos(['fecha_entrada' => $fecha]));
        $this->assertSame(['fecha_entrada' => 'Fecha no válida, usa el formato AAAA-MM-DD'], $errores);
    }

    #[DataProvider('importesValidos')]
    public function testImportesValidos(mixed $importe, string $esperado): void
    {
        $resultado = $this->validador()->validar($this->datosValidos(['importe' => $importe]));
        $this->assertSame($esperado, $resultado['importe']);
    }

    #[DataProvider('importesNoValidos')]
    public function testImportesNoValidos(mixed $importe, string $mensaje): void
    {
        $errores = $this->erroresDe($this->datosValidos(['importe' => $importe]));
        $this->assertSame(['importe' => $mensaje], $errores);
    }

    public function testNombreDe100Caracteres(): void
    {
        $nombre = str_repeat('ñ', 100);

        $resultado = $this->validador()->validar($this->datosValidos(['nombre_huesped' => $nombre]));
        $this->assertSame($nombre, $resultado['nombre_huesped']);
    }

    public function testNombreMas100Caracteres(): void
    {
        $errores = $this->erroresDe($this->datosValidos(['nombre_huesped' => str_repeat('a', 101)]));
        $this->assertSame(['nombre_huesped' => 'No puede superar los 100 caracteres'], $errores);
    }
}
