<?php

require_once  'AppController.php';
require_once 'supabase.php';

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

    public function preRegister()
    {
        return $this->render('preRegister');
    }

    public function registerBusiness() {
        if (!$this->isPost()) {
            return $this->render('registerBusiness');
        }
        $imageUrl = "http://default-image.com/default.png"; // Domyślne zdjęcie
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (isset($_FILES['bussines_photos']) && $_FILES['bussines_photos']['error'] === UPLOAD_ERR_OK) {
            $uploadedUrl = $this->uploadToSupabase($_FILES['bussines_photos']);
            if ($uploadedUrl) {
                $imageUrl = $uploadedUrl;
            }
        }

        $userData = [
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'firstname' => $_POST['businessName'] ?? '',
            'lastname' => 'Owner'
        ];

        $businessData = [
            'name' => $_POST['businessName'] ?? '',
            'nip' => $_POST['nip'] ?? '',
            'category' => $_POST['category'] ?? '',
            'image_url' => $imageUrl,
            'city' => $_POST['city'] ?? '',
            'street' => $_POST['street'] ?? '',
            'house_number' => $_POST['houseNumber'] ?? '',
            'postal_code' => $_POST['postalCode'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'email' => $email,
            'description' => $_POST['description'] ?? ''
        ];

        $success = $this->userRepository->registerBusinessWithUser($userData, $businessData);

        if ($success) {
            return $this->render('login', ['messages' => ['Firma i użytkownik zarejestrowani!']]);
        } else {
            return $this->render('registerBusiness', ['messages' => ['Błąd rejestracji. Upewnij się, że dane są poprawne.']]);
        }
    }

    public function logout() {
        session_destroy();
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/login");
    }

    // SUPBASE API TO STORAGE IMAGES
    private function uploadToSupabase($file): ?string {
        $supabaseUrl = SUPABASE_URL;
        $token = SUPABASE_KEY;
        $bucket = SUPABASE_BUCKET;

        $fileName = time() . '_' . $file['name'];
        $filePath = "/public/" . $fileName; // Ścieżka wewnątrz bucketu
        $url = $supabaseUrl . "/storage/v1/object/" . $bucket . $filePath;

        $ch = curl_init($url);
        $fileData = file_get_contents($file['tmp_name']);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $token,
            "Content-Type: " . $file['type']
        ]);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info['http_code'] === 200) {
            // Zwracamy publiczny URL do pliku
            return $supabaseUrl . "/storage/v1/object/public/" . $bucket . $filePath;
        }

        return null;
    }
    
}