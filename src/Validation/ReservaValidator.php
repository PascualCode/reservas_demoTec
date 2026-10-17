<?php

declare(strict_types=1);

namespace App\Validation;

use App\Http\ValidationException;
use DateTimeImmutable;

final class ReservaValidator
{
    private string $hoy;

    /** @var array<string, string> */
    private array $errores = [];

    public function __construct(?DateTimeImmutable $hoy = null)
    {
        $this->hoy = ($hoy ?? new DateTimeImmutable('today'))->format('Y-m-d');
    }

    /**
     * Valida los datos de una reserva nueva y los devuelve limpios.
     *
     * @param array<string, mixed> $datos
     * @return array<string, string|null>
     * @throws ValidationException si algún campo no es válido
     */
    public function validar(array $datos): array
    {
        $this->errores = [];

        $nombreHuesped = $this->obligatorio($datos, 'nombre_huesped', 100);
        $nombreAlojamiento = $this->obligatorio($datos, 'nombre_alojamiento', 254);
        $notas = $this->opcional($datos, 'notas', 1000);

        $emailHuesped = $this->obligatorio($datos, 'email_huesped', 254);
        if ($emailHuesped !== null && filter_var($emailHuesped, FILTER_VALIDATE_EMAIL) === false) {
            $this->errores['email_huesped'] = 'El email no es válido';
        }

        $fechaEntrada = $this->obligatorio($datos, 'fecha_entrada', 10);
        if ($fechaEntrada !== null) {
            if (!Fecha::esValida($fechaEntrada)) {
                $this->errores['fecha_entrada'] = 'Fecha no válida, usa el formato AAAA-MM-DD';
            } elseif ($fechaEntrada < $this->hoy) {
                $this->errores['fecha_entrada'] = 'No puede ser anterior a hoy';
            }
        }

        $fechaSalida = $this->obligatorio($datos, 'fecha_salida', 10);
        if ($fechaSalida !== null) {
            if (!Fecha::esValida($fechaSalida)) {
                $this->errores['fecha_salida'] = 'Fecha no válida, usa el formato AAAA-MM-DD';
            } elseif ($fechaEntrada < $this->hoy) {
                $this->errores['fecha_salida'] = 'No puede ser anterior a hoy';
            }
        }

        if (
            $fechaEntrada !== null && $fechaSalida !== null
            && !isset($this->errores['fecha_entrada']) && !isset($this->errores['fecha_salida'])
            && $fechaSalida <= $fechaEntrada
        ) {
            $this->errores['fecha_salida'] = 'Debe ser posterior a la fecha de entrada';
        }

        // 4. Importe (ver abajo)
        $importe = $this->importe($datos);

        if ($this->errores !== []) {
            throw new ValidationException($this->errores);
        }

        return [
            'nombre_huesped' => $nombreHuesped,
            'email_huesped' => $emailHuesped,
            'nombre_alojamiento' => $nombreAlojamiento,
            'fecha_entrada' => $fechaEntrada,
            'fecha_salida' => $fechaSalida,
            'importe' => $importe,
            'notas' => $notas,
        ];
    }

    /** Lee un campo de texto obligatorio. Si falla, apunta el error y devuelve null. */
    private function obligatorio(array $datos, string $campo, int $maximo): ?string
    {
        $valor = $datos[$campo] ?? null;
        if ($valor !== null && !is_string($valor)) {
            $this->errores[$campo] = 'Debe ser un texto';
            return null;
        }
        $valor = trim((string) $valor);
        if ($valor === '') {
            $this->errores[$campo] = 'Es obligatorio';
            return null;
        }
        if (mb_strlen($valor) > $maximo) {
            $this->errores[$campo] = "No puede superar los {$maximo} caracteres";
            return null;
        }
        return $valor;
    }

    /** Lee un campo de texto opcional. Devuelve null si no viene o está vacío. */
    private function opcional(array $datos, string $campo, int $maximo): ?string
    {
        $valor = $datos[$campo] ?? null;
        if ($valor === null) {
            return null;
        }
        if (!is_string($valor)) {
            $this->errores[$campo] = 'Debe ser un texto';
            return null;
        }
        $valor = trim($valor);
        if ($valor === '') {
            return null;
        }
        if (mb_strlen($valor) > $maximo) {
            $this->errores[$campo] = "No puede superar los {$maximo} caracteres";
            return null;
        }
        return $valor;
    }

    /** Lee el importe: número ≥ 0 con hasta 2 decimales. Lo devuelve como texto, o null si no es válido. */
    private function importe(array $datos): ?string
    {
        $valor = $datos['importe'] ?? null;

        if (is_int($valor) || is_float($valor)) {
            $texto = (string) $valor;
        } elseif (is_string($valor)) {
            $texto = trim($valor);
        } elseif ($valor === null) {
            $texto = '';
        } else {
            $this->errores['importe'] = 'Debe ser un número';
            return null;
        }

        if ($texto === '') {
            $this->errores['importe'] = 'Es obligatorio';
            return null;
        }

        if (preg_match('/^\d{1,8}(\.\d{1,2})?$/', $texto) !== 1) {
            $this->errores['importe'] = 'Importe no válido: número positivo con punto decimal y hasta 2 decimales';
            return null;
        }

        return $texto;
    }
}
