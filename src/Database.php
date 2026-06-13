<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

/**
 * Handles a single reusable PDO connection to the MySQL database.
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    /**
     * Returns the singleton PDO connection.
     * Connection parameters are read from environment variables so that no
     * credentials are ever stored in source code.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = (string) ($_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost');
            $port = (int)   ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306);
            $name = (string) ($_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: '');
            $user = (string) ($_ENV['DB_USER'] ?? getenv('DB_USER') ?: '');
            $pass = (string) ($_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '');

            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $name);

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // Do not expose connection details in the error message.
                throw new PDOException('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
