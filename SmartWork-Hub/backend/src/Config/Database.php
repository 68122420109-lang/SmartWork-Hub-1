<?php
declare(strict_types=1);

namespace SmartWorkHub\Config;

use PDO;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = $_ENV['DB_HOST'] ?? '';
        $port = $_ENV['DB_PORT'] ?? '4000';
        $database = $_ENV['DB_DATABASE'] ?? '';
        $username = $_ENV['DB_USERNAME'] ?? '';
        $password = $_ENV['DB_PASSWORD'] ?? '';

        if ($host === '' || $database === '' || $username === '') {
            throw new RuntimeException('Database configuration is incomplete.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $host,
            $port,
            $database
        );
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $caFile = $_ENV['DB_SSL_CA'] ?? '';
        if ($caFile !== '') {
            if (!is_file($caFile)) {
                throw new RuntimeException('Configured database CA file does not exist.');
            }
            $options[PDO::MYSQL_ATTR_SSL_CA] = $caFile;
        }

        self::$connection = new PDO($dsn, $username, $password, $options);
        return self::$connection;
    }
}
