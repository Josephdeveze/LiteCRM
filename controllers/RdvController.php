<?php

namespace Controllers;

use Models\RdvModel;
use Models\ClientModel; // Ajout de l'import
use PDO;

class RdvController extends Controller
{
    private $rdvModel;
    private $clientModel; // Ajout de la propriété

    public function __construct(PDO $database)
    {
        parent::__construct($database);
        $this->rdvModel = new RdvModel($database);
        $this->clientModel = new ClientModel($database); // Initialisation
    }

    /**
     * Affiche la liste des rendez-vous
     */
    public function index()
    {
        // Récupérer tous les rendez-vous avec les informations des clients
        $rdvs = $this->rdvModel->getAllRdv();
        
        return $this->render('rdv.html.twig', [
            'rdvs' => $rdvs
        ]);
    }

    /**
     * Affiche le formulaire de création de rendez-vous
     */
    public function create()
    {
        // Récupérer la date sélectionnée si elle est passée en paramètre
        $selectedDate = $_GET['date'] ?? date('Y-m-d');
        
        // Récupérer la liste des clients
        $clients = $this->clientModel->getAllClients();
        
        return $this->render('createrdv.html.twig', [
            'selectedDate' => $selectedDate,
            'clients' => $clients,
            'statuses' => ['Prévu', 'Confirmé', 'Annulé', 'Terminé']
        ]);
    }

    /**
     * Traite la soumission du formulaire de création
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'date' => $_POST['date'],
                'heure_debut' => $_POST['heure_debut'],
                'heure_fin' => $_POST['heure_fin'],
                'lieu' => $_POST['lieu'],
                'status' => $_POST['status'],
                'notes' => $_POST['notes'] ?? '',
                'id_client' => $_POST['client_id']
            ];

            if ($this->rdvModel->createRdv($data)) {
                header('Location: ' . BASE_URL . '/rdv');
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de la création du rendez-vous";
                header('Location: ' . BASE_URL . '/rdv/create');
                exit();
            }
        }
        
        header('Location: ' . BASE_URL . '/rdv/create');
        exit();
    }

    public function edit($id)
    {
        $rdv = $this->rdvModel->getRdvById($id);
        if (!$rdv) {
            throw new \Exception('Rendez-vous non trouvé');
        }

        // Récupération de la liste des clients pour le select
        $clientModel = new \Models\ClientModel($this->db);
        $clients = $clientModel->getAllClients();

        return $this->render('editrdv.html.twig', [
            'rdv' => $rdv,
            'clients' => $clients
        ]);
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_rdv' => $id,
                'date' => $_POST['date'],
                'heure_debut' => $_POST['heure_debut'],
                'heure_fin' => $_POST['heure_fin'],
                'lieu' => $_POST['lieu'],
                'status' => $_POST['status'],
                'notes' => $_POST['notes'] ?? '',
                'id_client' => $_POST['client_id']
            ];

            if ($this->rdvModel->update($data)) {
                header('Location: ' . BASE_URL . '/rdv');
                exit();
            }
        }
        header('Location: ' . BASE_URL . '/rdv/edit/' . $id);
        exit();
    }

    public function delete($id)
    {
        try {
            if ($this->rdvModel->deleteRdv($id)) {
                $_SESSION['success'] = "Le rendez-vous a été supprimé avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression du rendez-vous";
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erreur lors de la suppression du rendez-vous";
        }
        
        header('Location: ' . BASE_URL . '/rdv');
        exit();
    }

}