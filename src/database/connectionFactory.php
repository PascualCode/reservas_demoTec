<?php

declare(strict_types=1);

namespace App\Database;

use App\Config\Config;
use PDO;

final class ConnectionFactory
{

    public static function createConnection(Config $config): PDO
    {
        $host = $config->get('DB_HOST');
        $port = $config->get('DB_PORT');
        $dbname = $config->get('DB_NAME');
        $user = $config->get('DB_USER');
        $password = $config->get('DB_PASS');

        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,       // Activa el modo de error de PDO para lanzar excepciones en caso de errores.
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Establece el modo de obtención de resultados predeterminado como un array asociativo.
            PDO::ATTR_EMULATE_PREPARES => false                // Consultas preparadas reales: el SQL y los datos viajan por separado (protege de inyección SQL)
        ];

        $pdo = new PDO($dsn, $user, $password, $options);
        $pdo->exec("SET SESSION sql_mode = 'TRADITIONAL'");    // No depender de la configuración del servidor: Wamp viene sin modo estricto y MySQL guardaría datos inválidos sin avisar
        return $pdo;
    }
}
