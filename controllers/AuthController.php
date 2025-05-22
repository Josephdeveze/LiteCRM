<?php
namespace Controllers;

use Models\UserModel;
use PDO;

class AuthController extends Controller {
    private $userModel;

    public function __construct(PDO $database) {
        parent::__construct($database);
        $this->userModel = new UserModel($database);
    }

    public function inscription() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Validation
            $errors = [];
            if (empty($nom)) $errors[] = "Le nom est requis";
            if (empty($prenom)) $errors[] = "Le prénom est requis";
            if (empty($email)) $errors[] = "L'email est requis";
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'email n'est pas valide";
            if (empty($password)) $errors[] = "Le mot de passe est requis";
            if ($password !== $confirm_password) $errors[] = "Les mots de passe ne correspondent pas";
            
            // Vérifier si l'email existe déjà
            if ($this->userModel->emailExists($email)) {
                $errors[] = "Cet email est déjà utilisé";
            }

            if (empty($errors)) {
                // Création de l'utilisateur
                $userData = [
                    'Nom' => $nom,
                    'Prenom' => $prenom,
                    'Email' => $email,
                    'Password' => password_hash($password, PASSWORD_DEFAULT),
                    'Role' => 'user'  // Par défaut, les nouveaux inscrits sont des utilisateurs normaux
                ];

                try {
                    if ($this->userModel->createUser($userData)) {
                        // Connexion automatique après inscription
                        $user = $this->userModel->authenticate($email, $password);
                        if ($user) {
                            $_SESSION['user_id'] = $user['id_utilisateur'];
                            $_SESSION['user_name'] = $user['Nom'] . ' ' . $user['Prenom'];
                            $_SESSION['role'] = $user['Role'];
                            header('Location: ' . BASE_URL . '/dashboard');
                            exit();
                        }
                    } else {
                        throw new \Exception("Erreur lors de la création du compte");
                    }
                } catch (\Exception $e) {
                    $this->render('inscription.html.twig', [
                        'error' => "Une erreur est survenue lors de l'inscription : " . $e->getMessage()
                    ]);
                    return;
                }
            } else {
                $this->render('inscription.html.twig', [
                    'error' => implode("<br>", $errors),
                    'old' => [
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'email' => $email
                    ]
                ]);
                return;
            }
        }

        $this->render('inscription.html.twig');
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            // Debug détaillé
            error_log("=== Début login ===");
            error_log("Email: " . $email);

            $user = $this->userModel->getUserByEmail($email);
            error_log("Utilisateur trouvé: " . print_r($user, true));

            if ($user && password_verify($password, $user['Password'])) {
                $_SESSION['user_id'] = $user['id_utilisateur'];
                $_SESSION['user_name'] = $user['Prenom'] . ' ' . $user['Nom'];
                $_SESSION['role'] = $user['Role'];

                error_log("Session créée: " . print_r($_SESSION, true));
                error_log("Rôle défini: " . $_SESSION['role']);

                header('Location: ' . BASE_URL . '/dashboard');
                exit();
            }
            error_log("Échec de l'authentification");
        }
        return $this->render('login.html.twig');
    }

    public function authenticate()
    {
        try {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = $this->userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['Password'])) {
                $_SESSION['user_id'] = $user['id_utilisateur'];
                $_SESSION['user_name'] = $user['Prenom'] . ' ' . $user['Nom'];
                $_SESSION['role'] = $user['Role'];
                
                header('Location: ' . BASE_URL . '/dashboard');
                exit();
            }

            $_SESSION['error'] = 'Identifiants incorrects';
            header('Location: ' . BASE_URL . '/login');
            exit();
        } catch (\Exception $e) {
            $_SESSION['error'] = "Une erreur est survenue";
            header('Location: ' . BASE_URL . '/login');
            exit();
        }
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit();
    }

    public function register()
    {
        // Redirection vers login si quelqu'un essaie d'accéder à register
        header('Location: ' . BASE_URL . '/login');
        exit();
    }
}