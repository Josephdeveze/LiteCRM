<?php
namespace Models;

use PDO;

class ClientModel extends Model
{
    /**
     * Récupérer tous les clients
     *
     * @return array
     */
    public function getAllClients()
    {
        try {
            $sql = "SELECT * FROM Client ORDER BY Nom ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur lors de la récupération des clients : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer un client par son ID
     *
     * @param int $id
     * @return array|false
     */
    public function getClientById($id)
    {
        $sql = "SELECT * FROM Client WHERE id_client = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Créer un nouveau client
     * 
     * @param array $data Les données du client
     * @return int|false L'ID du nouveau client ou false si échec
     */
    public function create($data)
    {
        $sql = "INSERT INTO Client (Nom, Prenom, Email, Telephone, Entreprise, Adresse) 
                VALUES (:Nom, :Prenom, :Email, :Telephone, :Entreprise, :Adresse)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'Nom' => $data['Nom'],
            'Prenom' => $data['Prenom'],
            'Email' => $data['Email'],
            'Telephone' => $data['Telephone'],
            'Entreprise' => $data['Entreprise'],
            'Adresse' => $data['Adresse']
        ]);
    }

    /**
     * Mettre à jour un client
     *
     * @param array $data
     * @return bool
     */
    public function update($data)
    {
        try {
            $sql = "UPDATE Client 
                    SET Nom = :Nom,
                        Prenom = :Prenom,
                        Email = :Email,
                        Telephone = :Telephone,
                        Entreprise = :Entreprise,
                        Adresse = :Adresse
                    WHERE id_client = :id_client";
    
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                'id_client' => $data['id_client'],
                'Nom' => $data['Nom'],
                'Prenom' => $data['Prenom'],
                'Email' => $data['Email'],
                'Telephone' => $data['Telephone'],
                'Entreprise' => $data['Entreprise'],
                'Adresse' => $data['Adresse']
            ]);
        } catch (\PDOException $e) {
            error_log("Erreur lors de la mise à jour du client : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer un client
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $sql = "DELETE FROM Client WHERE id_client = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Récupérer les derniers clients ajoutés
     *
     * @param int $limit Nombre de clients à récupérer
     * @return array
     */
    public function getLatestClients($limit = 5)
    {
        $sql = "SELECT * FROM Client ORDER BY id_client DESC LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer le nombre total de clients
     *
     * @return int
     */
    public function getTotalClients()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM Client";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int) $result['total'];
        } catch (\PDOException $e) {
            error_log("Erreur lors du comptage des clients : " . $e->getMessage());
            return 0;
        }
    }
}
?>