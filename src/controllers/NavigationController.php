<?php

require_once  'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';
require_once __DIR__.'/../repository/BusinessRepository.php';

class NavigationController extends AppController{


    public function dashboard() {

        // DATA FETCH HERE
        $businessRepository = new BusinessRepository();
        
        $businesses = $businessRepository->getBusinesses();

        return $this->render("dashboard", ['businesses' => $businesses]);
    }

    public function calendar() {
        // DATA FETCH HERE
        return $this->render("calendar");
    }

    public function appointments() {
        // DATA FETCH HERE
        return $this->render("appointments");
    }

    public function profile() {
        $this->render('profile', ['email' => $_SESSION['user_id']]);
    }

}