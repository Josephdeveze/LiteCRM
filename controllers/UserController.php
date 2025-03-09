<?php
namespace Controllers;

use Models\UserModel;
use Models\ClientModel;
use Models\RdvModel;
use PDO;
use Database\Database;

class UserController extends Controller
{
    private $userModel;
    private $clientModel;
    private $rdvModel;

    public function __construct($database)
    {
        if ($database instanceof Database) {
            parent::__construct($database->getConnection());
        } else {
            parent::__construct($database);
        }
        
        $this->userModel = new UserModel($this->db);
        $this->clientModel = new ClientModel($this->db);
        $this->rdvModel = new RdvModel($this->db);
    }

    /**
     * Afficher la liste des utilisateurs.
     */
    public function index()
    {
        $this->checkAdmin();
        $users = $this->userModel->getAllUsers();
        $data = [
            "title" => "Liste des utilisateurs",
            "h1" => "Gestion des utilisateurs",
            "users" => $users
        ];
        $this->render('users/list.html.twig', $data);
    }

    /**
     * Créer un nouvel utilisateur.
     */
    public function create()
    {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'Nom' => $_POST['nom'] ?? '',
                'Prenom' => $_POST['prenom'] ?? '',
                'Email' => $_POST['email'] ?? '',
                'Password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'Role' => $_POST['role'] ?? 'user'
            ];

            if ($this->validateUserData($userData)) {
                $this->userModel->createUser($userData);
                $this->addFlashMessage('success', 'Utilisateur créé avec succès');
                header('Location: /litecrm/users');
                exit();
            }
        }

        $data = [
            "title" => "Création d'utilisateur",
            "h1" => "Nouvel utilisateur",
            "roles" => ['user' => 'Utilisateur', 'admin' => 'Administrateur']
        ];
        $this->render('users/form.html.twig', $data);
    }

    /**
     * Modifier un utilisateur.
     *
     * @param int $id Identifiant de l'utilisateur
     */
    public function edit($id)
    {
        // Vérification des droits
        if (!$this->canEditUser($id)) {
            $this->addFlashMessage('error', 'Accès non autorisé');
            header('Location: /litecrm/');
            exit();
        }

        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $this->addFlashMessage('error', 'Utilisateur non trouvé');
            header('Location: /litecrm/users');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'id_utilisateur' => $id,
                'Nom' => $_POST['nom'] ?? $user['Nom'],
                'Prenom' => $_POST['prenom'] ?? $user['Prenom'],
                'Email' => $_POST['email'] ?? $user['Email'],
                'Role' => $_SESSION['role'] === 'admin' ? ($_POST['role'] ?? $user['Role']) : $user['Role']
            ];

            if (!empty($_POST['password'])) {
                $userData['Password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }

            if ($this->validateUserData($userData)) {
                $this->userModel->updateUser($userData);
                $this->addFlashMessage('success', 'Utilisateur modifié avec succès');
                header('Location: /litecrm/users');
                exit();
            }
        }

        $data = [
            "title" => "Modification d'utilisateur",
            "h1" => "Modifier l'utilisateur",
            "user" => $user,
            "roles" => ['user' => 'Utilisateur', 'admin' => 'Administrateur']
        ];
        $this->render('users/form.html.twig', $data);
    }

    /**
     * Supprimer un utilisateur.
     *
     * @param int $id Identifiant de l'utilisateur
     */
    public function delete($id)
    {
        $this->checkAdmin();
        
        if ($id == $_SESSION['user_id']) {
            $this->addFlashMessage('error', 'Vous ne pouvez pas supprimer votre propre compte');
            header('Location: /litecrm/users');
            exit();
        }

        $this->userModel->deleteUser($id);
        $this->addFlashMessage('success', 'Utilisateur supprimé avec succès');
        header('Location: /litecrm/users');
        exit();
    }

    /**
     * Afficher/Modifier le profil personnel.
     */
    public function profile()
    {
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'id_utilisateur' => $_SESSION['user_id'],
                'Nom' => $_POST['nom'] ?? $user['Nom'],
                'Prenom' => $_POST['prenom'] ?? $user['Prenom'],
                'Email' => $_POST['email'] ?? $user['Email'],
                'Role' => $user['Role'] // Le rôle ne peut pas être modifié ici
            ];

            if (!empty($_POST['password'])) {
                $userData['Password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }

            if ($this->validateUserData($userData)) {
                $this->userModel->updateUser($userData);
                $this->addFlashMessage('success', 'Profil mis à jour avec succès');
                header('Location: /litecrm/users/profile');
                exit();
            }
        }

        $data = [
            "title" => "Mon profil",
            "h1" => "Gérer mon profil",
            "user" => $user
        ];
        $this->render('users/profile.html.twig', $data);
    }

    /**
     * Afficher la page de connexion.
     */
    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /litecrm/');
            exit();
        }

        $data = [
            "title" => "Connexion",
            "h1" => "Connexion à LiteCRM"
        ];
        $this->render('auth/login.html.twig', $data);
    }

    /**
     * Traiter la connexion.
     */
    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /litecrm/login');
            exit();
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs');
            header('Location: /litecrm/login');
            exit();
        }

        try {
            $user = $this->userModel->authenticate($email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id_utilisateur'];
                $_SESSION['user_name'] = $user['Nom'] . ' ' . $user['Prenom'];
                $_SESSION['role'] = $user['Role'];
                $this->addFlashMessage('success', 'Connexion réussie');
                header('Location: ' . BASE_URL . '/dashboard');
                exit();
            } else {
                $this->addFlashMessage('error', 'Email ou mot de passe incorrect');
                header('Location: /litecrm/login');
            }
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur est survenue');
            header('Location: /litecrm/login');
        }
        exit();
    }

    /**
     * Se déconnecter.
     */
    public function logout()
    {
        session_unset();
        session_destroy();
        header('Location: /litecrm/login');
        exit();
    }

    /**
     * Afficher le tableau de bord
     */
    public function dashboard()
    {
        // Récupération des rendez-vous
        $rdvModel = new \Models\RdvModel($this->db);
        $rdvs = $rdvModel->getAllRdv();
        $totalRdv = $rdvModel->getTotalRdv(); // Ajout du total des rendez-vous
        
        // Récupération des clients
        $clientModel = new \Models\ClientModel($this->db);
        $totalClients = $clientModel->getTotalClients();
        $latestClients = $clientModel->getLatestClients(5);

        // Formatage des rendez-vous pour FullCalendar
        $events = array_map(function($rdv) {
            return [
                'id' => $rdv['id_rdv'],
                'title' => $rdv['Nom'] . ' ' . $rdv['Prenom'],
                'start' => $rdv['date'] . 'T' . $rdv['heure_debut'],
                'end' => $rdv['date'] . 'T' . $rdv['heure_fin'],
                'extendedProps' => [
                    'lieu' => $rdv['lieu'],
                    'status' => $rdv['status'],
                    'notes' => $rdv['notes']
                ],
                'backgroundColor' => $this->getStatusColor($rdv['status'])
            ];
        }, $rdvs);

        return $this->render('dashboard.html.twig', [
            'events' => json_encode($events),
            'userName' => $_SESSION['user_name'] ?? 'Utilisateur',
            'totalClients' => $totalClients,
            'totalRdv' => $totalRdv,
            'latestClients' => $latestClients
        ]);
    }

    /**
     * Obtenir la couleur en fonction du statut du rendez-vous
     */
    private function getStatusColor($status)
    {
        switch ($status) {
            case 'Confirmé':
                return '#28a745';
            case 'Annulé':
                return '#dc3545';
            default:
                return '#ffc107';
        }
    }

    /**
     * Vérifier si l'utilisateur est administrateur.
     */
    private function checkAdmin()
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $this->addFlashMessage('error', 'Accès non autorisé');
            header('Location: /litecrm/');
            exit();
        }
    }

    /**
     * Vérifier si l'utilisateur peut modifier un profil.
     *
     * @param int $userId Identifiant de l'utilisateur à modifier
     * @return bool
     */
    private function canEditUser($userId)
    {
        return $_SESSION['role'] === 'admin' || $_SESSION['user_id'] == $userId;
    }

    /**
     * Valider les données utilisateur.
     *
     * @param array $userData Données à valider
     * @return bool
     */
    private function validateUserData($userData)
    {
        if (empty($userData['Nom']) || empty($userData['Prenom']) || empty($userData['Email'])) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs obligatoires');
            return false;
        }

        if (!filter_var($userData['Email'], FILTER_VALIDATE_EMAIL)) {
            $this->addFlashMessage('error', 'Adresse email invalide');
            return false;
        }

        return true;
    }
}
