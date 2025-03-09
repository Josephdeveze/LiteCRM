<?php
namespace Middlewares;



class AuthMiddleware{
   public static function auth(){
    if (!isset($_SESSION["mail"]) && !isset($_SESSION["username"])) {
        header('Location: /litemvc/connection');
        exit();
        
    }
   }
}
