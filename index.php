<?php

require_once 'Routing.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = parse_url($path, PHP_URL_PATH);

$router = Routing::getInstance();

Routing::get('login', ['controller' => 'SecurityController', 'action' => 'login']);
Routing::post('login', ['controller' => 'SecurityController', 'action' => 'login']);
Routing::get('register', ['controller' => 'SecurityController', 'action' => 'register']);
Routing::post('register', ['controller' => 'SecurityController', 'action' => 'register']);
Routing::get('dashboard', ['controller' => 'NavigationController', 'action' => 'dashboard']);
Routing::get('calendar', ['controller' => 'NavigationController', 'action' => 'calendar']);
Routing::get('appointments', ['controller' => 'NavigationController', 'action' => 'appointments']);
Routing::get('profile', ['controller' => 'NavigationController', 'action' => 'profile']);
Routing::get('logout', ['controller' => 'SecurityController', 'action' => 'logout']);
Routing::get('preRegister', ['controller' => 'SecurityController', 'action' => 'preRegister']);
Routing::get('registerBusiness', ['controller' => 'SecurityController', 'action' => 'registerBusiness']);
Routing::post('registerBusiness', ['controller' => 'SecurityController', 'action' => 'registerBusiness']);

$router->run($path);