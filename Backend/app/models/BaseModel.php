<?php
require_once(__DIR__ . '/../lib/env.php');  // ✅ Adjust path as needed


class BaseModel
{
    protected static $pdo;

    public function __construct()
    {
        if (!self::$pdo) {
            try {
                $host = $_ENV["DB_HOST"] ?? 'localhost';
                $db = $_ENV["DB_NAME"] ?? 'flexwork_db';
                $user = $_ENV["DB_USER"] ?? 'root';
                $pass = $_ENV["DB_PASSWORD"] ?? 'root';
                $charset = $_ENV["DB_CHARSET"] ?? 'utf8mb4';

                $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                self::$pdo = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
    }
}
