<?php

namespace Models;

use PDO;
use Database\Database;

class Model
{
    protected $db;

    public function __construct($database)
    {
        if ($database instanceof Database) {
            $this->db = $database->getConnection();
        } elseif ($database instanceof PDO) {
            $this->db = $database;
        } else {
            throw new \InvalidArgumentException('La connexion à la base de données doit être de type PDO ou Database');
        }
    }
}
?>