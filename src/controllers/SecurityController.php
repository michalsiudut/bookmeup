<?php

require_once  'AppController.php';

class SecurityController extends AppController{


    private $userRepository;
    public function __construct(){
        parent::__construct();
        $this->userRepository = new UserRepository();
    }


    public function login()
    {
        if (!$this->isPost()) {
            return $this->render('login');
        }

        $email = $_POST["email"] ?? '';
        $password = $_POST["password"] ?? '';

        $user = $this->userRepository->getUserByEmail($email);

        if (empty($email) || empty($password)) {
            return $this->render('login', ['messages' => 'Fill all fields']);
        }

        if(!$user){
            return $this->render('login', ['messages' => 'user doesnt exists']);
        }

        if (!password_verify($password, $user['password'])) {
            return $this->render('login', ['messages' => 'Wrong password']);
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['user_id'] = $user['email']; 

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/dashboard");
    }
    public function register()
    {

        if (!$this->isPost()) {
            return $this->render('register');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        $firstName = $_POST['firstName'] ?? '';
        $lastName = $_POST['lastName'] ?? '';

        if($password != $password2){
            return $this->render('register', ['messages' => 'Passwords should be the same']);
        }

        if($this->userRepository->getUserByEmail($email)){
            return $this->render('register', ['messages' => 'This email is in use']);
        }
        if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
            return $this->render('register', ['messages' => 'Fill all fields']);
        }
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $this->userRepository->createUser(
            $email,
            $hashedPassword,
            $firstName,
            $lastName
        );
        return $this->render("login", ["messages"=>"User register successfully.Please login!"]);
    }

    public function logout() {
        session_destroy();
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/login");
    }
}