<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définition des constantes
define('ROOT_PATH', __DIR__);
define('BASE_URL', '/litecrm');

require 'vendor/autoload.php';

use Controllers\HomeController;
use Controllers\UserController;
use Controllers\ClientController;
use Controllers\RdvController;
use Controllers\AuthController;
use Database\Database;
use Middlewares\AuthMiddleware;

// Créer une instance du routeur
$router = new AltoRouter();

// Définir le chemin de base
$router->setBasePath('/litecrm');

// Configuration de Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/views');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
    'debug' => true,
    'auto_reload' => true
]);

$twig->addExtension(new \Twig\Extension\DebugExtension());
$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
    return '/litecrm/public/' . $path;
}));
$twig->addFunction(new \Twig\TwigFunction('path', function ($route, $params = []) {
    $url = '/litecrm/' . $route;
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    return $url;
}));

// Variables globales Twig
$twig->addGlobal('session', $_SESSION);
$twig->addGlobal('base_url', '/litecrm');

// Initialisation de la base de données
try {
    $database = Database::getInstance();
    $db = $database->getConnection();
} catch (Exception $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Routes d'authentification
$router->map('GET', '/login', function() use ($db) {
    $controller = new AuthController($db);
    $controller->login();
});

$router->map('POST', '/authenticate', function() use ($db) {
    $controller = new AuthController($db);
    $controller->authenticate();
});

$router->map('GET', '/logout', function() use ($db) {
    $controller = new AuthController($db);
    $controller->logout();
});

$router->map('GET', '/inscription', function() use ($db) {
    $controller = new AuthController($db);
    $controller->inscription();
});

$router->map('POST', '/inscription', function() use ($db) {
    $controller = new AuthController($db);
    $controller->inscription();
});

// Routes du dashboard
$router->map('GET', '/', function() use ($db) {
    AuthMiddleware::checkAuth();
    $controller = new UserController($db);
    $controller->dashboard();
});

$router->map('GET', '/dashboard', function() use ($db) {
    AuthMiddleware::checkAuth();
    $controller = new UserController($db);
    $controller->dashboard();
});

// Routes des clients
$router->map('GET', '/clients', function() use ($db) {
    AuthMiddleware::checkAuth();
    $controller = new ClientController($db);
    $controller->index();
});

$router->map('GET', '/clients/create', function() use ($db) {
    AuthMiddleware::checkAuth();
    $controller = new ClientController($db);
    $controller->create();
});

$router->map('POST', '/clients/store', function() use ($db) {
    AuthMiddleware::checkAuth();
    $controller = new ClientController($db);
    $controller->store();
});

$router->map('GET', '/clients/edit/[i:id]', function($id) use ($db) {
    AuthMiddleware::checkAuth();
    $controller = new ClientController($db);
    $controller->edit($id);
});

$router->map('POST', '/clients/update/[i:id]', function($id) use ($db) {
    AuthMiddleware::checkAuth();
    $controller = new ClientController($db);
    $controller->update($id);
});

$router->map('POST', '/clients/delete/[i:id]', function($id) use ($db) {
    AuthMiddleware::checkAuth();
    $controller = new ClientController($db);
    $controller->delete($id);
});

// Routes des rendez-vous
$router->map('GET', '/rdv', function() use ($db) {
    AuthMiddleware::checkAuth();
    $controller = new RdvController($db);
    $controller->index();
});

$router->map('GET', '/rdv/create', function() use ($db) {
    AuthMiddleware::checkAuth();
    $controller = new RdvController($db);
    $controller->create();
});

$router->map('POST', '/rdv/store', function() use ($db) {
    AuthMiddleware::checkAuth();
    $controller = new RdvController($db);
    $controller->store();
});

$router->map('GET', '/rdv/edit/[i:id]', function($id) use ($db) {
    AuthMiddleware::checkAuth();
    $controller = new RdvController($db);
    $controller->edit($id);
});

$router->map('POST', '/rdv/update/[i:id]', function($id) use ($db) {
    AuthMiddleware::checkAuth();
    $controller = new RdvController($db);
    $controller->update($id);
});

$router->map('POST', '/rdv/delete/[i:id]', function($id) use ($db) {
    AuthMiddleware::checkAuth();
    $controller = new RdvController($db);
    $controller->delete($id);
});

// Routes des utilisateurs (admin)
$router->map('GET', '/users', function() use ($db) {
    AuthMiddleware::checkAuth();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['error'] = "Accès non autorisé";
        header('Location: /litecrm/dashboard');
        exit();
    }
    $controller = new UserController($db);
    $controller->index();
});

$router->map('GET', '/users/create', function() use ($db) {
    AuthMiddleware::checkAuth();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['error'] = "Accès non autorisé";
        header('Location: /litecrm/dashboard');
        exit();
    }
    $controller = new UserController($db);
    $controller->create();
});

$router->map('POST', '/users/store', function() use ($db) {
    AuthMiddleware::checkAuth();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['error'] = "Accès non autorisé";
        header('Location: /litecrm/dashboard');
        exit();
    }
    $controller = new UserController($db);
    $controller->store();
});

$router->map('GET', '/users/edit/[i:id]', function($id) use ($db) {
    AuthMiddleware::checkAuth();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['error'] = "Accès non autorisé";
        header('Location: /litecrm/dashboard');
        exit();
    }
    $controller = new UserController($db);
    $controller->edit($id);
});

$router->map('POST', '/users/update/[i:id]', function($id) use ($db) {
    AuthMiddleware::checkAuth();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['error'] = "Accès non autorisé";
        header('Location: /litecrm/dashboard');
        exit();
    }
    $controller = new UserController($db);
    $controller->update($id);
});

$router->map('POST', '/users/delete/[i:id]', function($id) use ($db) {
    AuthMiddleware::checkAuth();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['error'] = "Accès non autorisé";
        header('Location: /litecrm/dashboard');
        exit();
    }
    $controller = new UserController($db);
    $controller->delete($id);
});

// Routes du profil
$router->map('GET', '/profile', function() use ($db) {
    AuthMiddleware::checkAuth();
    $controller = new UserController($db);
    $controller->profile();
});

$router->map('POST', '/profile/update', function() use ($db) {
    AuthMiddleware::checkAuth();
    $controller = new UserController($db);
    $controller->updateProfil();
});

// Matcher la route actuelle
$match = $router->match();

// Gérer la route
if ($match && is_callable($match['target'])) {
    call_user_func_array($match['target'], $match['params']);
} else {
    // Page non trouvée
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    echo 'Page introuvable.';
}
