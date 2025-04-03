<?php

namespace Controllers;

// Définitions globales d'abord
$rootPath = realpath(__DIR__ . '/..');
define('ROOT_PATH', $rootPath);
define('BASE_URL', '/litecrm');
define('TEMPLATE_PATH', $rootPath . '/views');

// Créer une constante globale pour le namespace Controllers
define('Controllers\\ROOT_PATH', $rootPath);

// Chargement de l'autoloader
require_once $rootPath . '/vendor/autoload.php';

// Configuration de la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Nettoyer la session avant les tests
$_SESSION = [];