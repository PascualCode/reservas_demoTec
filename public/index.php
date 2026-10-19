<?php

declare(strict_types=1);

use App\Config\Config;
use App\Controller\ReservaWebController;
use App\Http\ErrorHandler;
use App\Http\Router;
use App\Repository\ReservaRepository;
use App\Service\ReservaService;
use App\Controller\ReservaApiController;
use App\Database\ConnectionFactory;
use App\Validation\ReservaValidator;


use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Madrid');

ini_set('display_errors', '0');
ErrorHandler::convertWarningsToExceptions();

$debug = false;
$twig = null;
$esApi = str_starts_with($_SERVER['REQUEST_URI'], '/api/');

try {
    $config = Config::fromFile(__DIR__ . '/../.env');
    $debug = $config->get('APP_ENV') === 'dev';

    $twig = new Environment(new FilesystemLoader(__DIR__ . '/../templates'), [
        'autoescape' => 'html',
        'strict_variables' => true,
        'cache' => $debug ? false : __DIR__ . '/../var/cache/twig',
    ]);

    $pdo = ConnectionFactory::createConnection($config);
    $repository = new ReservaRepository($pdo);
    $service = new ReservaService($repository, new ReservaValidator());
    $apiController = new ReservaApiController($service);
    $webController = new ReservaWebController($service, $twig);

    $router = new Router();

    $router->add('GET', '/', function (): void {
        header('Location: /reservas', true, 302);
    });

    $router->add('GET', '/api/reservas', $apiController->listado(...));

    $router->add('POST', '/api/reservas', $apiController->crear(...));

    $router->add('PATCH', '/api/reservas/{id}/estado', $apiController->cambiarEstado(...));

    $router->add('GET', '/reservas', $webController->listado(...));

    $router->add('GET', '/reservas/{id}', $webController->detalle(...));

    $router->add('GET', '/api/reservas/{id}', $apiController->detalle(...));

    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

} catch (Throwable $e) {
    (new ErrorHandler($debug, $esApi, $twig))->handle($e);
}
