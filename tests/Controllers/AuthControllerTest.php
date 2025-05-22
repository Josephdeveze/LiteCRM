<?php

namespace Tests\Controllers;

require_once dirname(__DIR__) . '/config.php';

use PHPUnit\Framework\TestCase;
use Controllers\AuthController;
use Tests\Utils\TestDatabase;  // Changement ici
use PDO;

class AuthControllerTest extends TestCase
{
    private $db;
    private $authController;

    protected function setUp(): void
    {
        try {
            $this->db = TestDatabase::getInstance()->getConnection();
            $this->authController = new AuthController($this->db);
            
            // Réinitialiser la base de données
            TestDatabase::getInstance()->resetDatabase();
            
            // Nettoyer la session
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION = [];
            
            // Créer l'utilisateur admin pour les tests
            $this->createTestAdmin();
        } catch (\PDOException $e) {
            $this->markTestSkipped('Impossible de se connecter à la base de données : ' . $e->getMessage());
        }
    }

    private function createTestAdmin()
    {
        $sql = "INSERT INTO Utilisateur (Nom, Prenom, Email, Password, Role) 
                VALUES (:nom, :prenom, :email, :password, :role)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nom' => 'Admin',
            'prenom' => 'Test',
            'email' => 'admin@litecrm.fr',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin'
        ]);
    }

    /**
     * @test
     */
    public function testLoginWithValidCredentials()
    {
        echo "\nTest de connexion avec identifiants valides\n";
        $_POST['email'] = 'admin@litecrm.fr';
        $_POST['password'] = 'admin123';
        
        ob_start(); // Capture la sortie
        $this->authController->authenticate();
        $output = ob_get_clean();
        
        $this->assertNotEmpty($_SESSION['user_id'], "L'ID utilisateur devrait être défini");
        $this->assertEquals('admin', $_SESSION['role'], "Le rôle devrait être 'admin'");
    }

    /**
     * @test
     */
    public function testLoginWithInvalidCredentials()
    {
        echo "\nTest de connexion avec identifiants invalides\n";
        $_POST['email'] = 'wrong@email.com';
        $_POST['password'] = 'wrongpassword';
        
        ob_start();
        $this->authController->authenticate();
        $output = ob_get_clean();
        
        $this->assertEmpty($_SESSION['user_id'] ?? null, "L'ID utilisateur ne devrait pas être défini");
        $this->assertNotEmpty($_SESSION['error'], "Un message d'erreur devrait être présent");
    }

    /**
     * @test
     */
    public function testLogout()
    {
        echo "\nTest de déconnexion\n";
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';
        
        $this->authController->logout();
        
        $this->assertEmpty($_SESSION['user_id'] ?? null, "L'ID utilisateur devrait être supprimé");
        $this->assertEmpty($_SESSION['role'] ?? null, "Le rôle devrait être supprimé");
    }
}