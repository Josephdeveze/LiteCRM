<?php

namespace Database;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;
    private static array $config;

    private function __construct()
    {
        self::$config = require __DIR__ . '/config.php';
        
        try {
            $this->connection = new PDO(
                "mysql:host=" . self::$config['host'] . 
                ";dbname=" . self::$config['dbname'] . 
                ";charset=" . self::$config['charset'],
                self::$config['username'],
                self::$config['password'],
                self::$config['options'] ?? [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch (PDOException $e) {
            throw new PDOException("Erreur de connexion : " . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
