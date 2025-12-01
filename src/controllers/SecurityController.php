<?php

require_once  'AppController.php';

class SecurityController extends AppController{

        private static array $users = [
        [
            'email' => 'anna@example.com',
            'password' => '$2y$10$VljUCkQwxrsULVbZovCaF.UfkeqVNcdz8SRFQptFS/Hr8QnUgsf5G', // test123
            'first_name' => 'Anna'
        ],
        [
            'email' => 'bartek@example.com',
            'password' => '$2y$10$fK9rLobZK2C6rJq6B/9I6u6Udaez9CaRu7eC/0zT3pGq5piVDsElW', // haslo456
            'first_name' => 'Bartek'
        ],
        [
            'email' => 'celina@example.com',
            'password' => '$2y$10$Cq1J6YMGzRKR6XzTb3fDF.6sC6CShm8kFgEv7jJdtyWkhC1GuazJa', // qwerty
            'first_name' => 'Celina'
        ],
    ];

    private $userRepository;
    public function __construct(){
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

       //  TODO możemy przechowywać sesje użytkowika lub token
        // setcookie("username", $userRow['email'], time() + 3600, '/');

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
}