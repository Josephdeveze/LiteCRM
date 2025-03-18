<?php
// Démarrage de la session
session_start();

// Affichage des erreurs en développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définition des constantes
define('ROOT_PATH', __DIR__);
define('BASE_URL', '/litecrm');

// Autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

// Import des classes nécessaires
use Database\Database;
use Controllers\UserController;
use Controllers\ClientController;
use Controllers\RdvController;
use Controllers\AuthController;

// Initialisation de la base de données
try {
    $database = Database::getInstance();
    $db = $database->getConnection();
} catch (Exception $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Configuration de Twig
$loader = new \Twig\Loader\FilesystemLoader(ROOT_PATH . '/views');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
    'debug' => true,
    'auto_reload' => true
]);

// Extensions Twig
$twig->addExtension(new \Twig\Extension\DebugExtension());

// Fonctions Twig personnalisées
$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
    return BASE_URL . '/public/' . $path;
}));

$twig->addFunction(new \Twig\TwigFunction('path', function ($route, $params = []) {
    $url = BASE_URL . '/' . $route;
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    return $url;
}));

// Variables globales Twig
$twig->addGlobal('session', $_SESSION);
$twig->addGlobal('base_url', BASE_URL);

// Récupération de l'URL
$url = $_GET['url'] ?? '';
$url = trim($url, '/');
$urlParts = explode('/', $url);

// Définition des paramètres de routage
$page = !empty($urlParts[0]) ? $urlParts[0] : 'dashboard';
$action = !empty($urlParts[1]) ? $urlParts[1] : 'index';
$id = !empty($urlParts[2]) ? $urlParts[2] : null;

// Liste des routes publiques
$public_routes = ['login', 'authenticate', 'logout'];

// Vérifier l'authentification pour toutes les routes sauf les routes publiques
if (!in_array($page, $public_routes)) {
    require_once ROOT_PATH . '/middlewares/AuthMiddleware.php';
    \Middlewares\AuthMiddleware::checkAuth();
}

try {
    switch ($page) {
        case 'login':
        case 'authenticate':
            $controller = new AuthController($db);
            if ($page === 'login') {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->authenticate();
                } else {
                    $controller->login();
                }
            } else {
                $controller->authenticate();
            }
            exit();

        case 'logout':
            $controller = new AuthController($db);
            $controller->logout();
            exit();

        case 'clients':
            if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/clients') {
                $controller = new \Controllers\ClientController($db);
                $controller->index();
                exit();
            }

            if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/client/create') {
                $controller = new \Controllers\ClientController($db);
                $controller->create();
                exit();
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/client/store') {
                $controller = new \Controllers\ClientController($db);
                $controller->store();
                exit();
            }

            $controller = new ClientController($db);
            switch ($action) {
                case 'create':
                    $controller->create();
                    break;
                case 'store':
                    $controller->store();
                    break;
                case 'edit':
                    if (!$id) throw new Exception('ID client manquant');
                    $controller->edit($id);
                    break;
                case 'update':
                    if (!$id) throw new Exception('ID client manquant');
                    $controller->update($id);
                    break;
                case 'delete':
                    if (!$id) throw new Exception('ID client manquant');
                    $controller->delete($id);
                    break;
                default:
                    $controller->index();
            }
            break;

        case 'rdv':
            $controller = new RdvController($db);
            switch ($action) {
                case 'calendar':
                    $controller->calendar();
                    break;
                case 'create':
                    $controller->create();
                    break;
                case 'store':
                    $controller->store();
                    break;
                case 'edit':
                    if (!$id) throw new Exception('ID rendez-vous manquant');
                    $controller->edit($id);
                    break;
                case 'update':
                    if (!$id) throw new Exception('ID rendez-vous manquant');
                    $controller->update($id);
                    break;
                case 'delete':
                    if (!$id) throw new Exception('ID rendez-vous manquant');
                    $controller->delete($id);
                    break;
                default:
                    $controller->index(); // Cette méthode affichera rdv.html.twig
                    break;
            }
            break;

        case 'users':
            // Vérification du rôle admin pour toutes les routes users
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $_SESSION['error'] = "Accès non autorisé";
                header('Location: ' . BASE_URL . '/dashboard');
                exit();
            }
            
            $controller = new UserController($db);
            switch ($action) {
                case 'create':
                    $controller->create();
                    break;
                case 'store':
                    $controller->store();
                    break;
                case 'edit':
                    if (!$id) throw new Exception('ID utilisateur manquant');
                    $controller->edit($id);
                    break;
                case 'update':
                    if (!$id) throw new Exception('ID utilisateur manquant');
                    $controller->update($id);
                    break;
                case 'delete':
                    if (!$id) throw new Exception('ID utilisateur manquant');
                    $controller->delete($id);
                    break;
                default:
                    $controller->index();
                    break;
            }
            break;

        case 'profile':
            $controller = new UserController($db);
            $controller->profile();
            break;

        case 'profil':
            $controller = new UserController($db);
            switch ($action) {
                case 'update':
                    $controller->updateProfil();
                    break;
                default:
                    $controller->profil();
                    break;
            }
            break;

        case 'register':
        case 'inscription':
            // Rediriger vers la page de connexion
            header('Location: ' . BASE_URL . '/login');
            exit();

        case 'dashboard':
            $controller = new UserController($db);
            $controller->dashboard();
            break;

        default:
            throw new Exception('Page non trouvée');
    }
} catch (Exception $e) {
    // En développement : afficher les erreurs
    if (true) { // Remplacer par une constante de configuration en production
        echo '<h1>Erreur</h1>';
        echo '<p>' . $e->getMessage() . '</p>';
        if ($e->getCode() !== 404) {
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
        }
    } else {
        // En production : rediriger vers une page d'erreur générique
        header('Location: ' . BASE_URL . '/error');
    }
}
