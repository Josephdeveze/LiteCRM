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
        // Vérification du rôle admin
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = "Accès non autorisé";
            header('Location: ' . BASE_URL . '/dashboard');
            exit();
        }

        try {
            $users = $this->userModel->getAllUsers();
            return $this->render('userlist.html.twig', [
                'users' => $users,
                'success' => $_SESSION['success'] ?? null,
                'error' => $_SESSION['error'] ?? null
            ]);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue";
            header('Location: ' . BASE_URL . '/dashboard');
            exit();
        }
    }

    /**
     * Créer un nouvel utilisateur.
     */
    public function create()
    {
        // Vérification du rôle admin
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $this->addFlashMessage('error', 'Accès non autorisé');
            header('Location: ' . BASE_URL . '/dashboard');
            exit();
        }

        return $this->render('userform.html.twig', [
            'title' => 'Créer un utilisateur',
            'h1' => 'Nouvel utilisateur',
            'user' => [],
            'roles' => ['user' => 'Utilisateur', 'admin' => 'Administrateur'],
            'action_url' => BASE_URL . '/users/store'
        ]);
    }

    public function store()
    {
        try {
            // Vérification du rôle admin
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                throw new \Exception("Accès non autorisé");
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception("Méthode non autorisée");
            }

            $userData = [
                'Nom' => $_POST['nom'],
                'Prenom' => $_POST['prenom'],
                'Email' => $_POST['email'],
                'Role' => $_POST['role'],
                'Password' => password_hash($_POST['password'], PASSWORD_DEFAULT)
            ];

            if ($this->validateUserData($userData)) {
                if ($this->userModel->createUser($userData)) {
                    $this->addFlashMessage('success', 'Utilisateur créé avec succès');
                    header('Location: ' . BASE_URL . '/users');
                    exit();
                }
            }

            throw new \Exception("Erreur lors de la création de l'utilisateur");

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->addFlashMessage('error', $e->getMessage());
            header('Location: ' . BASE_URL . '/users/create');
            exit();
        }
    }

    /**
     * Modifier un utilisateur.
     *
     * @param int $id Identifiant de l'utilisateur
     */
    public function edit($id)
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = "Accès non autorisé";
            header('Location: ' . BASE_URL . '/dashboard');
            exit();
        }

        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé";
            header('Location: ' . BASE_URL . '/users');
            exit();
        }

        return $this->render('userform.html.twig', [
            'title' => "Modifier l'utilisateur",
            'h1' => "Modifier l'utilisateur",
            'user' => $user,
            'roles' => ['user' => 'Utilisateur', 'admin' => 'Administrateur'],
            'action_url' => BASE_URL . '/users/update/' . $id
        ]);
    }

    /**
     * Mettre à jour un utilisateur
     * 
     * @param int $id Identifiant de l'utilisateur
     */
    public function update($id)
    {
        try {
            // Vérification du rôle admin
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                throw new \Exception("Accès non autorisé");
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception("Méthode non autorisée");
            }

            $user = $this->userModel->getUserById($id);
            if (!$user) {
                throw new \Exception("Utilisateur non trouvé");
            }

            $userData = [
                'id_utilisateur' => $id,
                'Nom' => $_POST['nom'],
                'Prenom' => $_POST['prenom'],
                'Email' => $_POST['email'],
                'Role' => $_POST['role']
            ];

            // Mise à jour du mot de passe si fourni
            if (!empty($_POST['password'])) {
                $userData['Password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }

            if ($this->validateUserData($userData)) {
                if ($this->userModel->updateUser($userData)) {
                    $this->addFlashMessage('success', 'Utilisateur modifié avec succès');
                    header('Location: ' . BASE_URL . '/users');
                    exit();
                }
            }

            throw new \Exception("Erreur lors de la mise à jour");

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->addFlashMessage('error', $e->getMessage());
            header('Location: ' . BASE_URL . '/users/edit/' . $id);
            exit();
        }
    }

    /**
     * Supprimer un utilisateur.
     *
     * @param int $id Identifiant de l'utilisateur
     */
    public function delete($id)
    {
        try {
            // Vérification du rôle admin
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                throw new \Exception("Accès non autorisé");
            }

            // Empêcher la suppression de son propre compte
            if ($id == $_SESSION['user_id']) {
                throw new \Exception("Vous ne pouvez pas supprimer votre propre compte");
            }

            if ($this->userModel->deleteUser($id)) {
                $_SESSION['success'] = "Utilisateur supprimé avec succès";
            } else {
                throw new \Exception("Erreur lors de la suppression de l'utilisateur");
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/users');
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
        // Vérification de l'authentification
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

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

    private function addFlashMessage($type, $message)
    {
        $_SESSION[$type] = $message;
    }

    public function profil()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        $user = $this->userModel->getUserById($_SESSION['user_id']);
        
        return $this->render('profil.html.twig', [
            'user' => $user
        ]);
    }

    public function updateProfil()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/profil');
            exit();
        }

        try {
            $userId = $_SESSION['user_id'];
            $data = [
                'id_utilisateur' => $userId,
                'Nom' => $_POST['Nom'],
                'Prenom' => $_POST['Prenom'],
                'Email' => $_POST['Email'],
                'Telephone' => $_POST['Telephone']
            ];

            // Vérification du mot de passe si changement demandé
            if (!empty($_POST['current_password'])) {
                if ($_POST['new_password'] !== $_POST['confirm_password']) {
                    throw new \Exception('Les nouveaux mots de passe ne correspondent pas');
                }
                
                if (!$this->userModel->verifyPassword($userId, $_POST['current_password'])) {
                    throw new \Exception('Mot de passe actuel incorrect');
                }
                
                $data['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            }

            if ($this->userModel->updateUser($data)) {
                $_SESSION['success'] = 'Profil mis à jour avec succès';
            } else {
                throw new \Exception('Erreur lors de la mise à jour du profil');
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/profil');
        exit();
    }

    public function inscription()
    {
        // Redirection vers la page de connexion
        header('Location: ' . BASE_URL . '/login');
        exit();
    }

    public function register()
    {
        // Redirection vers la page de connexion
        header('Location: ' . BASE_URL . '/login');
        exit();
    }
}
