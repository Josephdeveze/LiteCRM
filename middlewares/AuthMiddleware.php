<?php

namespace Middlewares;

class AuthMiddleware {
    public static function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Veuillez vous connecter pour accéder à cette page";
            header('Location: /litecrm/login');
            exit();
        }
    }
}
