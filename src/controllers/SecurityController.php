<?php

require_once 'AppController.php';
require_once 'supabase.php';

class SecurityController extends AppController
{


    private $userRepository;
    public function __construct()
    {
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

        if (!$user) {
            return $this->render('login', ['messages' => 'user doesnt exists']);
        }

        if (!password_verify($password, $user['password'])) {
            return $this->render('login', ['messages' => 'Wrong password']);
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user['email'];
        $_SESSION['user_avatar'] = $user['image_url'];

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
        $imageUrl = 'https://www.w3schools.com/howto/img_avatar.png';

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadedUrl = $this->uploadToSupabase($_FILES['avatar']);
            if ($uploadedUrl) {
                $imageUrl = $uploadedUrl;
            }
        }

        if ($password != $password2) {
            return $this->render('register', ['messages' => 'Hasła muszą być identyczne']);
        }

        if ($this->userRepository->getUserByEmail($email)) {
            return $this->render('register', ['messages' => 'Ten email jest już zajęty']);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $this->userRepository->createUser(
            $email,
            $hashedPassword,
            $firstName,
            $lastName,
            $imageUrl
        );

        return $this->render("login", ["messages" => "Zarejestrowano pomyślnie. Zaloguj się!"]);
    }

    public function preRegister()
    {
        return $this->render('preRegister');
    }

    public function registerBusiness()
    {
        if (!$this->isPost()) {
            return $this->render('registerBusiness');
        }
        $imageUrl = "http://default-image.com/default.png";
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

    public function logout()
    {
        session_destroy();
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/login");
    }

    // SUPBASE API TO STORAGE IMAGES
    private function uploadToSupabase($file): ?string
    {
        $supabaseUrl = rtrim(SUPABASE_URL, '/');
        $token = SUPABASE_KEY;
        $bucket = SUPABASE_BUCKET;

        $fileName = time() . '_' . basename($file['name']);

        $filePath = "public/" . $fileName;
        $url = $supabaseUrl . "/storage/v1/object/" . $bucket . "/" . $filePath;

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
            return $supabaseUrl . "/storage/v1/object/public/" . $bucket . "/" . $filePath;
        }

        return null;
    }

    public function updateSettings()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);

        if (is_array($decoded)) {
            $email = $_SESSION['user_id'];
            $emailNotif = filter_var($decoded['email_notifications'], FILTER_VALIDATE_BOOLEAN);
            $smsNotif = filter_var($decoded['sms_notifications'], FILTER_VALIDATE_BOOLEAN);

            $result = $this->userRepository->updateSettings($email, $emailNotif, $smsNotif);

            header('Content-Type: application/json');
            echo json_encode(['status' => $result ? 'success' : 'error']);
            exit;
        }
    }

}