<?php

declare(strict_types=1);

use App\Config\Config;
use App\Database\ConnectionFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$config = Config::fromFile(__DIR__ . '/../.env');

$pdo = ConnectionFactory::createConnection($config);

$count = $pdo->query('SELECT COUNT(*) FROM reserva')->fetchColumn();
$sqlMode = $pdo->query('SELECT @@SESSION.sql_mode')->fetchColumn();

//Mostrar el resultado de la consulta

echo "Número de reservas: $count\n";
echo "SQL Mode: $sqlMode\n";
