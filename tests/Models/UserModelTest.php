<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use Models\UserModel;
use Tests\Utils\TestDatabase;  // Modification ici
use PDO;

class UserModelTest extends TestCase
{
    private $db;
    private $userModel;

    protected function setUp(): void
    {
        try {
            $this->db = TestDatabase::getInstance()->getConnection();
            $this->userModel = new UserModel($this->db);
            
            // Réinitialiser la base de données
            TestDatabase::getInstance()->resetDatabase();
            
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
    public function testGetAllUsers()
    {
        $users = $this->userModel->getAllUsers();
        $this->assertIsArray($users);
        $this->assertGreaterThan(0, count($users), "La liste des utilisateurs devrait contenir au moins un utilisateur");
    }

    /**
     * @test
     */
    public function testGetUserById()
    {
        $admin = $this->userModel->getUserByEmail('admin@litecrm.fr');
        $user = $this->userModel->getUserById($admin['id_utilisateur']);
        
        $this->assertIsArray($user);
        $this->assertEquals('Admin', $user['Nom']);
        $this->assertEquals('admin', $user['Role']);
    }

    /**
     * @test
     */
    public function testGetUserByEmail()
    {
        $user = $this->userModel->getUserByEmail('admin@litecrm.fr');
        
        $this->assertIsArray($user);
        $this->assertEquals('Admin', $user['Nom']);
        $this->assertEquals('admin', $user['Role']);
    }

    /**
     * @test
     */
    public function testCreateUser()
    {
        $userData = [
            'Nom' => 'Nouveau',
            'Prenom' => 'User',
            'Email' => 'nouveau@test.com',
            'Password' => password_hash('test123', PASSWORD_DEFAULT),
            'Role' => 'user'
        ];

        $result = $this->userModel->createUser($userData);
        $this->assertTrue($result);

        $newUser = $this->userModel->getUserByEmail('nouveau@test.com');
        $this->assertEquals('Nouveau', $newUser['Nom']);
        $this->assertEquals('user', $newUser['Role']);
    }

    /**
     * @test
     */
    public function testUpdateUser()
    {
        // Créer un utilisateur de test
        $userData = [
            'Nom' => 'ToUpdate',
            'Prenom' => 'User',
            'Email' => 'update@test.com',
            'Password' => password_hash('password123', PASSWORD_DEFAULT),
            'Role' => 'user'
        ];
        
        $this->userModel->createUser($userData);
        $user = $this->userModel->getUserByEmail('update@test.com');
        
        $updateData = [
            'id_utilisateur' => $user['id_utilisateur'],
            'Nom' => 'Updated',
            'Prenom' => 'UserTest',
            'Email' => 'update@test.com',
            'Role' => 'user'
        ];
        
        $result = $this->userModel->updateUser($updateData);
        $this->assertTrue($result);

        $updatedUser = $this->userModel->getUserByEmail('update@test.com');
        $this->assertEquals('Updated', $updatedUser['Nom']);
        $this->assertEquals('UserTest', $updatedUser['Prenom']);
    }

    /**
     * @test
     */
    public function testDeleteUser()
    {
        // Créer un utilisateur à supprimer
        $userData = [
            'Nom' => 'ToDelete',
            'Prenom' => 'User',
            'Email' => 'delete@test.com',
            'Password' => password_hash('password123', PASSWORD_DEFAULT),
            'Role' => 'user'
        ];
        
        $this->userModel->createUser($userData);
        $user = $this->userModel->getUserByEmail('delete@test.com');
        
        $result = $this->userModel->deleteUser($user['id_utilisateur']);
        $this->assertTrue($result);

        $deletedUser = $this->userModel->getUserByEmail('delete@test.com');
        $this->assertFalse($deletedUser);
    }

    /**
     * @test
     */
    public function testVerifyPassword()
    {
        // Créer un utilisateur avec un mot de passe connu
        $password = 'testpassword123';
        $userData = [
            'Nom' => 'Test',
            'Prenom' => 'Password',
            'Email' => 'test.password@test.com',
            'Password' => password_hash($password, PASSWORD_DEFAULT),
            'Role' => 'user'
        ];
        
        $this->userModel->createUser($userData);
        $user = $this->userModel->getUserByEmail('test.password@test.com');
        
        // Test avec le bon mot de passe
        $result = $this->userModel->verifyPassword($user['id_utilisateur'], $password);
        $this->assertTrue($result, "Le bon mot de passe devrait être vérifié");
        
        // Test avec un mauvais mot de passe
        $result = $this->userModel->verifyPassword($user['id_utilisateur'], 'wrongpassword');
        $this->assertFalse($result, "Le mauvais mot de passe ne devrait pas être vérifié");
    }
}