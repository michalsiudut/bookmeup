<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/NavigationController.php';

class Routing {
    private static $instance;
    private static $routes = [];

    private function __construct() {}

    public static function getInstance(): Routing {
        if (self::$instance === null) {
            self::$instance = new Routing();
        }
        return self::$instance;
    }

    public static function get($url, $config) {
        self::$routes[$url] = $config;
    }

    public static function post($url, $config) {
        self::$routes[$url] = $config;
    }

    public static function run(string $path) {
        $path = trim($path, '/');
        $path = explode("/", $path)[0];

        if (empty($path)) {
            if (isset($_SESSION['user_id'])) {
                self::redirect('dashboard');
            } else {
                self::redirect('login');
            }
        }

        if (!array_key_exists($path, self::$routes)) {
            include 'public/views/404.html';
            die();
        }

        $controllerName = self::$routes[$path]['controller'];
        $action = self::$routes[$path]['action'];

        $object = new $controllerName;
        $object->$action();
    }

    private static function redirect($path) {
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/{$path}");
        exit();
    }
}