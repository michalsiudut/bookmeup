<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../repository/BusinessRepository.php';

require_once __DIR__ . '/../../supabase.php';

class NavigationController extends AppController
{


    private $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = new UserRepository();
    }

    public function dashboard()
    {
        if (!$this->isLoggedIn()) {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/login");
            exit();
        }

        $search = $_GET['search'] ?? null;
        $businessRepository = new BusinessRepository();

        $businesses = $businessRepository->getBusinesses($search);

        // Fetch user data for header
        $email = $_SESSION['user_id'] ?? null;
        $user = null;
        if ($email) {
            $user = $this->userRepository->getUserByEmail($email);
        }

        return $this->render("dashboard", [
            'businesses' => $businesses,
            'search' => $search,
            'user' => $user
        ]);
    }

    public function calendar()
    {
        if (!$this->isLoggedIn()) {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/login");
            exit();
        }
        // DATA FETCH HERE
        return $this->render("calendar");
    }

    public function appointments()
    {
        if (!$this->isLoggedIn()) {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/login");
            exit();
        }
        // DATA FETCH HERE
        return $this->render("appointments");
    }

    public function profile()
    {
        if (!$this->isLoggedIn()) {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/login");
            exit();
        }

        $email = $_SESSION['user_id'];
        $user = $this->userRepository->getUserByEmail($email);

        // Check if user is business owner
        $businessRepository = new BusinessRepository();
        $isOwner = $businessRepository->checkIfOwner($user['id']);

        return $this->render('profile', [
            'user' => $user,
            'isOwner' => $isOwner
        ]);
    }

    public function business_dashboard()
    {
        if (!$this->isLoggedIn()) {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/login");
            exit();
        }

        $email = $_SESSION['user_id'];
        $user = $this->userRepository->getUserByEmail($email);

        $businessRepository = new BusinessRepository();
        $isOwner = $businessRepository->checkIfOwner($user['id']);

        if (!$isOwner) {
            // Redirect non-owners back to profile or dashboard
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/profile");
            exit();
        }

        // TODO: specific data for dashboard could be fetched here

        return $this->render('business-dashboard', [
            'user' => $user
        ]);
    }

    public function business_profile()
    {
        $serviceId = $_GET['id'] ?? null;

        $selectedService = null;
        if ($serviceId) {
            # TODO FECTH DATA FROM REPO
        }

        return $this->render('business-profile', [
            'selectedService' => $selectedService,
            'serviceId' => $serviceId
        ]);
    }

    public function editProfile()
    {
        if (!$this->isLoggedIn()) {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/login");
            exit();
        }

        $email = $_SESSION['user_id'];
        $user = $this->userRepository->getUserByEmail($email);

        if ($this->isPost()) {
            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $email = $_POST['email'];
            $bio = $_POST['bio'];
            $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;
            $imageUrl = null;

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->render('editProfile', [
                    'user' => $user,
                    'error' => 'Nieprawidłowy adres email.'
                ]);
            }

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                // Use Supabase upload logic
                $uploadedUrl = $this->uploadToSupabase($_FILES['avatar']);
                if ($uploadedUrl) {
                    $imageUrl = $uploadedUrl;
                }
            }

            $this->userRepository->updateUserDetails($user['id'], $firstname, $lastname, $email, $password, $bio, $imageUrl);

            // Update session if email changed
            if ($email !== $_SESSION['user_id']) {
                $_SESSION['user_id'] = $email;
            }

            // Update session if avatar changed
            if ($imageUrl) {
                $_SESSION['user_avatar'] = $imageUrl;
            }

            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/profile?status=updated");
            exit();
        }

        $this->render('editProfile', [
            'user' => $user
        ]);
    }

    private function uploadToSupabase($file): ?string
    {
        $supabaseUrl = trim(SUPABASE_URL, " \n\r\t\v\x00/");
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
        if ($response === false) {
            error_log('Curl error: ' . curl_error($ch));
        }
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info['http_code'] === 200) {
            return $supabaseUrl . "/storage/v1/object/public/" . $bucket . "/" . $filePath;
        }

        error_log("Supabase upload failed with code " . $info['http_code'] . ": " . $response);

        return null;
    }

}