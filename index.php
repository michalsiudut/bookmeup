<?php

require_once 'Routing.php';
require_once 'src/controllers/BusinessController.php';
require_once 'src/controllers/AppointmentController.php';
require_once 'src/controllers/ReviewController.php';

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
Routing::get('appointments', ['controller' => 'NavigationController', 'action' => 'appointments']);
Routing::get('profile', ['controller' => 'NavigationController', 'action' => 'profile']);
Routing::get('logout', ['controller' => 'SecurityController', 'action' => 'logout']);
Routing::get('preRegister', ['controller' => 'SecurityController', 'action' => 'preRegister']);
Routing::get('registerBusiness', ['controller' => 'SecurityController', 'action' => 'registerBusiness']);
Routing::post('registerBusiness', ['controller' => 'SecurityController', 'action' => 'registerBusiness']);
Routing::get('editProfile', ['controller' => 'NavigationController', 'action' => 'editProfile']);
Routing::post('editProfile', ['controller' => 'NavigationController', 'action' => 'editProfile']);
Routing::post('updateSettings', ['controller' => 'SecurityController', 'action' => 'updateSettings']);
Routing::get('business_dashboard', ['controller' => 'NavigationController', 'action' => 'business_dashboard']);
Routing::get('business_profile', ['controller' => 'NavigationController', 'action' => 'business_profile']);
Routing::post('addService', ['controller' => 'BusinessController', 'action' => 'addService']);
Routing::post('bookAppointment', ['controller' => 'AppointmentController', 'action' => 'bookAppointment']);
Routing::get('update-schema', ['controller' => 'AppointmentController', 'action' => 'updateSchema']);
Routing::post('cancelAppointment', ['controller' => 'AppointmentController', 'action' => 'cancelAppointment']);
Routing::post('addReview', ['controller' => 'ReviewController', 'action' => 'addReview']);
Routing::get('getBookedSlots', ['controller' => 'AppointmentController', 'action' => 'getBookedSlots']);

$router->run($path);