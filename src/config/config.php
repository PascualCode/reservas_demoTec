<?php

declare(strict_types=1);

namespace App\Config;

use RuntimeException;

final class Config
{
    private function __construct(private array $values)
    {
    }

    public static function fromFile(string $path): self
    {
        if (!is_readable($path)) {
            throw new RuntimeException("El archivo de configuración no existe o no se puede leer: $path");
        }
        $config = parse_ini_file($path, false, INI_SCANNER_RAW);
        if ($config === false) {
            throw new RuntimeException("Error al analizar el archivo de configuración: $path");
        }
        return new self($config);
    }

    public function get(string $key): string
    {
        if (!array_key_exists($key, $this->values)) {
            throw new RuntimeException("Falta la variable de configuración {$key}");
        }
        return $this->values[$key];
    }
}
