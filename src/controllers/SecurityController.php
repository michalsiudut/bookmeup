<?php

require_once  'AppController.php';

class SecurityController extends AppController{

    public function login(){
        // TODO zwroc html logowania, przetworz dane

        return $this->render("login", ["messages" => "Bledne haslo!"]);
    }
}