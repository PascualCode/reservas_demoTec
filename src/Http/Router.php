<?php

declare(strict_types=1);

namespace App\Http;

final class Router
{
    // Array de rutas, cada ruta es un array con 'method', 'regex' y 'handler'
    /** @var array<int, array{method: string, regex: string, handler: callable}> */
    private array $routes = [];

    // Añade una ruta al router con su método, path y handler
    public function add(string $method, string $path, callable $handler): void
    {
        // Convierte la ruta en una expresión regular y guárdala con su método y su handler
        $regex = preg_replace('#\{\w+\}#', '(\d+)', $path);
        $regex = '#^' . $regex . '$#';
        $this->routes[] = [
            'method' => $method,
            'regex' => $regex,
            'handler' => $handler
        ];
    }

    // Dispara la ruta correspondiente según el método y la URI, o lanza una excepción si no hay coincidencia
    public function dispatch(string $method, string $uri): void
    {
        // Quita la query string de la URI
        $uri = parse_url($uri, PHP_URL_PATH);
        if (!is_string($uri)) {
            throw new HttpException(400, 'Petición no válida');
        }
        // Recorre las rutas: si la ruta encaja y el método también → llama al handler con los parámetros y termina
        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $uri, $matches) && $route['method'] === $method) {
                array_shift($matches); // Elimina el primer elemento que es la cadena completa 
                ($route['handler'])(...$matches);
                return;
            }
        }
        // Si la ruta encaja pero el método no → apunta el método como permitido
        $allowedMethods = [];
        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $uri)) {
                $allowedMethods[] = $route['method'];
            }
        }
        // Al terminar: si hay métodos permitidos → 405 con cabecera Allow; si no → 404
        if ($allowedMethods !== []) {
            throw new HttpException(405, 'Método no permitido', ['Allow' => implode(', ', $allowedMethods)]);
        } else {
            throw new HttpException(404, 'Recurso no encontrado');
        }
    }
}
