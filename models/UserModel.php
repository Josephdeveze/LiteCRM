<?php
namespace Models;

class UserModel extends Model
{
    /**
     * Authentifier un utilisateur
     *
     * @param string $email
     * @param string $password
     * @return array|false
     */
    public function authenticate($email, $password)
    {
        $sql = "SELECT * FROM Utilisateur WHERE Email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['Password'])) {
            unset($user['Password']); // Ne pas renvoyer le mot de passe
            return $user;
        }
        return false;
    }

    /**
     * Récupérer tous les utilisateurs
     *
     * @param string|null $role Filtrer par rôle (optionnel)
     * @return array
     */
    public function getAllUsers($role = null)
    {
        $sql = "SELECT id_utilisateur, Nom, Prenom, Email, Role FROM Utilisateur";
        $params = [];

        if ($role) {
            $sql .= " WHERE Role = :role";
            $params['role'] = $role;
        }

        $sql .= " ORDER BY Nom, Prenom";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Récupérer un utilisateur par son ID
     *
     * @param int $id
     * @return array|false
     */
    public function getUserById($id)
    {
        $sql = "SELECT id_utilisateur, Nom, Prenom, Email, Password, Role FROM Utilisateur WHERE id_utilisateur = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Vérifier le mot de passe d'un utilisateur
     *
     * @param int $userId
     * @param string $password
     * @return bool
     */
    public function verifyPassword($userId, $password) {
        $user = $this->getUserById($userId);
        if (!$user) {
            return false;
        }
        return password_verify($password, $user['Password']);
    }

    /**
     * Créer un nouvel utilisateur
     *
     * @param array $userData
     * @return bool
     */
    public function createUser($userData)
    {
        $sql = "INSERT INTO Utilisateur (Nom, Prenom, Email, Password, Role) 
                VALUES (:nom, :prenom, :email, :password, :role)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'nom' => $userData['Nom'],
            'prenom' => $userData['Prenom'],
            'email' => $userData['Email'],
            'password' => $userData['Password'], // Doit être déjà hashé
            'role' => $userData['Role']
        ]);
    }

    /**
     * Mettre à jour un utilisateur
     *
     * @param array $userData
     * @return bool
     */
    public function updateUser($userData)
    {
        // Construction dynamique de la requête pour gérer le cas où le mot de passe n'est pas modifié
        $sql = "UPDATE Utilisateur SET 
                Nom = :nom,
                Prenom = :prenom,
                Email = :email,
                Role = :role";
        
        $params = [
            'id' => $userData['id_utilisateur'],
            'nom' => $userData['Nom'],
            'prenom' => $userData['Prenom'],
            'email' => $userData['Email'],
            'role' => $userData['Role']
        ];

        // Ajout du mot de passe uniquement s'il est fourni
        if (isset($userData['Password'])) {
            $sql .= ", Password = :password";
            $params['password'] = $userData['Password'];
        }

        $sql .= " WHERE id_utilisateur = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Supprimer un utilisateur
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser($id)
    {
        try {
            $sql = "DELETE FROM Utilisateur WHERE id_utilisateur = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            error_log("Erreur lors de la suppression de l'utilisateur : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si un email existe déjà
     *
     * @param string $email
     * @param int|null $excludeId ID de l'utilisateur à exclure
     * @return bool
     */
    public function emailExists($email, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) FROM Utilisateur WHERE Email = :email";
        $params = ['email' => $email];

        if ($excludeId) {
            $sql .= " AND id_utilisateur != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Changer le mot de passe d'un utilisateur
     *
     * @param int $userId
     * @param string $newPassword Mot de passe déjà hashé
     * @return bool
     */
    public function changePassword($userId, $newPassword)
    {
        $sql = "UPDATE Utilisateur SET Password = :password WHERE id_utilisateur = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $userId,
            'password' => $newPassword
        ]);
    }

    /**
     * Obtenir les statistiques d'un commercial
     *
     * @param int $userId
     * @param string $debut Date de début (Y-m-d)
     * @param string $fin Date de fin (Y-m-d)
     * @return array
     */
    public function getUserStats($userId, $debut, $fin)
    {
        $sql = "SELECT 
                (SELECT COUNT(*) FROM `Rendez-vous` 
                 WHERE id_utilisateur = :userId 
                 AND date BETWEEN :debut AND :fin) as total_rdv,
                
                (SELECT COUNT(DISTINCT id_client) FROM `Rendez-vous` 
                 WHERE id_utilisateur = :userId 
                 AND date BETWEEN :debut AND :fin) as clients_rencontres,
                
                (SELECT COUNT(*) FROM `Rendez-vous` 
                 WHERE id_utilisateur = :userId 
                 AND status = 'Terminé'
                 AND date BETWEEN :debut AND :fin) as rdv_termines";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'userId' => $userId,
            'debut' => $debut,
            'fin' => $fin
        ]);
        return $stmt->fetch();
    }

    /**
     * Récupérer un utilisateur par son email
     *
     * @param string $email
     * @return array|false
     */
    public function getUserByEmail($email)
    {
        try {
            $sql = "SELECT id_utilisateur, Nom, Prenom, Email, Password, Role 
                    FROM Utilisateur 
                    WHERE Email = :email";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return false;
        }
    }
}