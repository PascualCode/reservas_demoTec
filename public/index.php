<?php

declare(strict_types=1);

use App\Http\HttpException;
use App\Http\Router;

require_once __DIR__ . '/../vendor/autoload.php';

$router = new Router();

$router->add('GET', '/api/reservas', function (): void {
    echo "Liistado de reservas (pendiente)";
});

$router->add('GET', '/api/reservas/{id}', function (string $id): void {
    echo "Detalle de la reserva {$id} (pendiente)";
});

$router->add('POST', '/api/reservas', function (): void {
    echo "Creando una nueva reserva (pendiente)";
});

$router->add('PATCH', '/api/reservas/{id}/estado', function (string $id): void {
    echo "Actualizando la reserva {$id} (pendiente)";
});

$router->add('GET', '/reservas', function (): void {
    echo "Listado de reservas (pendiente)";
});

$router->add('GET', '/reservas/{id}', function (string $id): void {
    echo "Detalle de la reserva {$id} (pendiente)";
});

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (HttpException $e) {
    http_response_code($e->getStatusCode());
    foreach ($e->getHeaders() as $name => $value) {
        header("{$name}: {$value}");
    }
    echo $e->getMessage();
}
