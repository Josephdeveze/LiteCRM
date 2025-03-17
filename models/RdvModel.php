<?php
namespace Models;

class RdvModel extends Model
{
    /**
     * Récupérer tous les rendez-vous
     * 
     * @param int|null $userId ID du commercial (facultatif)
     * @return array
     */
    public function getAllRdv($userId = null)
    {
        try {
            $sql = "SELECT r.*, c.Nom, c.Prenom 
                    FROM `Rendez-vous` r 
                    LEFT JOIN Client c ON r.id_client = c.id_client 
                    ORDER BY r.date ASC, r.heure_debut ASC";
                    
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur lors de la récupération des rendez-vous : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer les rendez-vous d'une période donnée
     * 
     * @param string $debut Date de début (Y-m-d)
     * @param string $fin Date de fin (Y-m-d)
     * @return array
     */
    public function getRdvPeriode($debut, $fin)
    {
        $sql = "SELECT *
                FROM `Rendez-vous`
                WHERE date BETWEEN :debut AND :fin
                ORDER BY date, heure_debut";

        $stmt = $this->db->prepare($sql);
        $params = ['debut' => $debut, 'fin' => $fin];
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Récupérer un rendez-vous par son ID
     * 
     * @param int $id
     * @return array|false
     */
    public function getRdvById($id)
    {
        try {
            $sql = "SELECT r.*, c.Nom, c.Prenom 
                    FROM `Rendez-vous` r
                    LEFT JOIN Client c ON r.id_client = c.id_client
                    WHERE r.id_rdv = :id";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur lors de la récupération du rendez-vous : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Créer un nouveau rendez-vous
     * 
     * @param array $data Les données du rendez-vous
     * @return bool
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO `Rendez-vous` (date, heure_debut, heure_fin, lieu, status, notes, id_client) 
                    VALUES (:date, :heure_debut, :heure_fin, :lieu, :status, :notes, :id_client)";

            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                'date' => $data['date'],
                'heure_debut' => $data['heure_debut'],
                'heure_fin' => $data['heure_fin'],
                'lieu' => $data['lieu'],
                'status' => $data['status'],
                'notes' => $data['notes'],
                'id_client' => $data['client_id'] // Modification ici pour correspondre au nom de la colonne
            ]);
        } catch (\PDOException $e) {
            error_log("Erreur lors de la création du rendez-vous : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Créer un nouveau rendez-vous
     * 
     * @param array $data
     * @return bool|int
     */
    public function createRdv($data)
    {
        try {
            $sql = "INSERT INTO `Rendez-vous` (
                        date, 
                        heure_debut, 
                        heure_fin, 
                        lieu, 
                        status, 
                        notes, 
                        id_client
                    ) VALUES (
                        :date, 
                        :heure_debut, 
                        :heure_fin, 
                        :lieu, 
                        :status, 
                        :notes, 
                        :id_client
                    )";
            
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'date' => $data['date'],
                'heure_debut' => $data['heure_debut'],
                'heure_fin' => $data['heure_fin'],
                'lieu' => $data['lieu'],
                'status' => $data['status'],
                'notes' => $data['notes'],
                'id_client' => $data['id_client']
            ]);

            if ($success) {
                return $this->db->lastInsertId();
            }
            return false;

        } catch (\PDOException $e) {
            error_log("Erreur lors de la création du rendez-vous : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour un rendez-vous
     * 
     * @param array $rdvData
     * @return bool
     */
    public function updateRdv($rdvData)
    {
        $sql = "UPDATE `Rendez-vous` 
                SET date = :date,
                    heure_debut = :heure_debut,
                    heure_fin = :heure_fin,
                    lieu = :lieu,
                    status = :status,
                    notes = :notes,
                    id_client = :id_client,
                WHERE id_rdv = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $rdvData['id_rdv'],
            'date' => $rdvData['date'],
            'heure_debut' => $rdvData['heure_debut'],
            'heure_fin' => $rdvData['heure_fin'],
            'lieu' => $rdvData['lieu'],
            'status' => $rdvData['status'],
            'notes' => $rdvData['notes'],
            'id_client' => $rdvData['id_client'],
        ]);
    }

    /**
     * Mettre à jour un rendez-vous
     * 
     * @param array $data
     * @return bool
     */
    public function update($data)
    {
        try {
            $sql = "UPDATE `Rendez-vous` 
                    SET date = :date,
                        heure_debut = :heure_debut,
                        heure_fin = :heure_fin,
                        lieu = :lieu,
                        status = :status,
                        notes = :notes,
                        id_client = :id_client
                    WHERE id_rdv = :id_rdv";

            $params = [
                'id_rdv' => $data['id_rdv'],
                'date' => $data['date'],
                'heure_debut' => $data['heure_debut'],
                'heure_fin' => $data['heure_fin'],
                'lieu' => $data['lieu'],
                'status' => $data['status'],
                'notes' => $data['notes'],
                'id_client' => $data['id_client']
            ];

            error_log("SQL: " . $sql);
            error_log("Paramètres: " . print_r($params, true));

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if (!$result) {
                error_log("Erreur PDO: " . print_r($stmt->errorInfo(), true));
            }

            return $result;
        } catch (\PDOException $e) {
            error_log("Exception PDO: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer un rendez-vous
     * 
     * @param int $id
     * @return bool
     */
    public function deleteRdv($id)
    {
        $sql = "DELETE FROM `Rendez-vous` WHERE id_rdv = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Vérifier la disponibilité d'un commercial sur une plage horaire
     * 
     * @param string $dateHeure Date et heure du rendez-vous
     * @param int $duree Durée en minutes
     * @param int $userId ID du commercial
     * @param int|null $excludeRdvId ID du rendez-vous à exclure (pour modification)
     * @return bool
     */
    public function isCommercialDisponible($dateHeure, $duree, $userId, $excludeRdvId = null)
    {
        $debutRdv = $dateHeure;
        $finRdv = date('Y-m-d H:i:s', strtotime($dateHeure . " +$duree minutes"));

        $sql = "SELECT COUNT(*) FROM `Rendez-vous`
                WHERE id_utilisateur = :userId
                AND (
                    (date BETWEEN :debut AND :fin)
                    OR (DATE_ADD(date, INTERVAL durée MINUTE) BETWEEN :debut AND :fin)
                )";

        if ($excludeRdvId) {
            $sql .= " AND id_rdv != :excludeId";
        }

        $stmt = $this->db->prepare($sql);
        $params = [
            'userId' => $userId,
            'debut' => $debutRdv,
            'fin' => $finRdv
        ];

        if ($excludeRdvId) {
            $params['excludeId'] = $excludeRdvId;
        }

        $stmt->execute($params);
        return $stmt->fetchColumn() == 0;
    }

    /**
     * Obtenir les statistiques des rendez-vous d'un commercial
     * 
     * @param int $userId
     * @param string $debut Date de début (Y-m-d)
     * @param string $fin Date de fin (Y-m-d)
     * @return array
     */
    public function getStatsCommercial($userId, $debut, $fin)
    {
        $sql = "SELECT 
                COUNT(*) as total_rdv,
                SUM(CASE WHEN status = 'Planifié' THEN 1 ELSE 0 END) as rdv_planifies,
                SUM(CASE WHEN status = 'Terminé' THEN 1 ELSE 0 END) as rdv_termines,
                SUM(CASE WHEN status = 'Annulé' THEN 1 ELSE 0 END) as rdv_annules
                FROM `Rendez-vous`
                WHERE id_utilisateur = :userId
                AND date BETWEEN :debut AND :fin";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'userId' => $userId,
            'debut' => $debut,
            'fin' => $fin
        ]);
        return $stmt->fetch();
    }

    /**
     * Récupérer le nombre total de rendez-vous
     * 
     * @return int
     */
    public function getTotalRdv()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM `Rendez-vous`";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int) $result['total'];
        } catch (\PDOException $e) {
            error_log("Erreur lors du comptage des rendez-vous : " . $e->getMessage());
            return 0;
        }
    }
}
?>


