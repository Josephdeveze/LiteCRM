<?php

namespace Controllers;

use PDO;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Controller
{
    protected PDO $db;
    protected Environment $twig;

    public function __construct(PDO $database)
    {
        $this->db = $database;

        // Initialisation de Twig
        $loader = new FilesystemLoader(ROOT_PATH . '/views');
        $this->twig = new Environment($loader, [
            'debug' => true,
            'cache' => false,
            'auto_reload' => true
        ]);

        // Extensions Twig
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());

        // Fonctions personnalisées
        $this->twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
            return BASE_URL . '/public/' . $path;
        }));

        $this->twig->addFunction(new \Twig\TwigFunction('path', function ($route, $params = []) {
            $url = BASE_URL . '/' . $route;
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }
            return $url;
        }));

        // Variables globales
        $this->twig->addGlobal('session', $_SESSION);
        $this->twig->addGlobal('base_url', BASE_URL);
    }

    protected function render(string $template, array $data = [])
    {
        try {
            echo $this->twig->render($template, $data);
        } catch (\Twig\Error\Error $e) {
            echo "Error rendering template: " . $e->getMessage();
            error_log($e->getMessage());
        }
    }

    protected function setSession(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    protected function getSession(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    protected function deleteSession(string $key): void
    {
        unset($_SESSION[$key]);
    }

}
