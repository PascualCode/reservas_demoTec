<?php

declare(strict_types=1);

use App\Config\Config;
use App\Http\ErrorHandler;
use App\Http\JsonResponse;
use App\Http\Router;
use App\Repository\ReservaRepository;
use App\Service\ReservaService;
use App\Controller\ReservaApiController;
use App\Database\ConnectionFactory;

require_once __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', '0');
ErrorHandler::convertWarningsToExceptions();

$debug = false;

try {
    $config = Config::fromFile(__DIR__ . '/../.env');
    $debug = $config->get('APP_ENV') === 'dev';

    // Crear la conexión a la base de datos, el repositorio, el servicio y el controlador
    $pdo = ConnectionFactory::createConnection($config);
    $repository = new ReservaRepository($pdo);
    $service = new ReservaService($repository);
    $apiController = new ReservaApiController($service);

    $router = new Router();

    $router->add('GET', '/api/reservas', function (): void {
        JsonResponse::send(['mensaje' => 'Listado de reservas (pendiente)']);
    });

    $router->add('POST', '/api/reservas', function (): void {
        JsonResponse::send(['mensaje' => 'Creando una nueva reserva (pendiente)']);
    });

    $router->add('PATCH', '/api/reservas/{id}/estado', function (string $id): void {
        JsonResponse::send(['mensaje' => "Actualizando la reserva {$id} (pendiente)"]);
    });

    $router->add('GET', '/reservas', function (): void {
        JsonResponse::send(['mensaje' => 'Listado de reservas (pendiente)']);
    });

    $router->add('GET', '/api/reservas/{id}', $apiController->detalle(...));

    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

} catch (Throwable $e) {
    (new ErrorHandler($debug))->handle($e);
}
