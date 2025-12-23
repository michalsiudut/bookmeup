<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';


// TODO musimty zapewnic ze utworzony oboekt ma tylko jedna iinstacje - singleton

//  TODO /dashboard - wszystkie dane
//  /dashboiard/12234 - wyciagnie nam jakis elemtn o konkretnym id 12234
//  regex
//  sesja uzytkownika
//  singletony 
class Routing {

    public static $routes= [
        "login" => [
            "controller" => "SecurityController",
            "action" => "login",
        ],
        "register" => [
            "controller" => "SecurityController",
            "action" => "register",
        ],
        "dashboard" => [
            "controller" => "DashboardController",
            "action" => "dashboard",
        ],
        "calendar" => [
            "controller" => "DashboardController",
            "action" => "calendar",
        ],
        "appointments" => [
            "controller" => "DashboardController",
            "action" => "appointments",
        ],
        "profile" => [
            "controller" => "ProfileController",
            "action" => "profile",
        ],
        "logout" => [
            "controller" => "SecurityController",
            "action" => "logout",
        ]
    ];

    public static function run(string $path)
    {
        $path = trim($path, '/'); 
        if (array_key_exists($path, self::$routes)) {
            $controllerName = self::$routes[$path]["controller"];
            $actionName = self::$routes[$path]["action"];
            $controllerObj = new $controllerName();
            $controllerObj->$actionName();
            
            return; 
        }
        if ($path === "") {
            if (isset($_SESSION['user_id'])) {
                $path = "dashboard";
            } else {
                $path = "login";     
            }
        }

        switch ($path) {
            case 'test':
                include 'public/views/test.html';
                break;
            case 'profile':
                include 'public/views/profile.html';
                break;
            case 'dashboard':
                include 'public/views/dashboard.html';
                break;
            case 'login':
                include 'public/views/login.html';
                break;
            default:
                include 'public/views/404.html';
                break;
        }
    }
}