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
Routing::get('dashboard', ['controller' => 'NavigationController', 'action' => 'dashboard']);
Routing::get('calendar', ['controller' => 'NavigationController', 'action' => 'calendar']);
Routing::get('appointments', ['controller' => 'NavigationController', 'action' => 'appointments']);
Routing::get('profile', ['controller' => 'NavigationController', 'action' => 'profile']);
Routing::get('logout', ['controller' => 'SecurityController', 'action' => 'logout']);

$router->run($path);