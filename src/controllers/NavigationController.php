<?php

require_once  'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';

class NavigationController extends AppController{


    public function dashboard(?int $id = null){

        $userRepository = new UserRepository();
        $users = $userRepository->getUsers();

        return $this->render("dashboard");
    }

    public function calendar() {
        // DATA FETCH HERE
        return $this->render("calendar");
    }

    // Dodaj tę metodę
    public function appointments() {
        // DATA FETCH HERE
        return $this->render("appointments");
    }

    public function profile() {
        $this->render('profile', ['email' => $_SESSION['user_id']]);
    }

}