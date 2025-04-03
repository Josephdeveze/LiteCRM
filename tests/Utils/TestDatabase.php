<?php

namespace Tests\Utils;

use PDO;
use PDOException;

class TestDatabase {
    private static $instance = null;
    private $db;

    private function __construct() {
        try {
            $this->db = new PDO(
                'mysql:host=127.0.0.1;dbname=litecrm;charset=utf8mb4',
                'root',
                '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            throw new \Exception("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->db;
    }

    public function resetDatabase() {
        try {
            // Sauvegarder les données importantes
            $this->backupImportantData();
            
            $this->db->beginTransaction();

            // Vider les tables de test
            $this->db->exec('SET FOREIGN_KEY_CHECKS=0');
            $this->db->exec('DELETE FROM `Rendez-vous` WHERE id_rdv > 1');
            $this->db->exec('DELETE FROM `Client` WHERE id_client > 1');
            $this->db->exec('DELETE FROM `Utilisateur` WHERE id_utilisateur > 1');
            $this->db->exec('SET FOREIGN_KEY_CHECKS=1');

            // Créer l'utilisateur de test si nécessaire
            $this->createTestUser();

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new \Exception("Erreur lors de la réinitialisation: " . $e->getMessage());
        }
    }

    private function createTestUser() {
        $sql = "INSERT INTO Utilisateur (Nom, Prenom, Email, Password, Role) 
                SELECT :nom, :prenom, :email, :password, :role 
                WHERE NOT EXISTS (
                    SELECT 1 FROM Utilisateur WHERE Email = :email
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nom' => 'Admin',
            'prenom' => 'Test',
            'email' => 'admin@litecrm.fr',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin'
        ]);
    }

    private function backupImportantData() {
        // Ajoutez ici la logique pour sauvegarder les données importantes si nécessaire
    }
}