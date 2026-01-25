<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/BusinessRepository.php';
require_once __DIR__ . '/../repository/ServiceRepository.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class BusinessController extends AppController
{

    private $businessRepository;
    private $serviceRepository;
    private $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->businessRepository = new BusinessRepository();
        $this->serviceRepository = new ServiceRepository();
        $this->userRepository = new UserRepository();
    }

    public function addService()
    {
        if (!$this->isPost()) {
            return $this->render('business-dashboard');
        }

        if (!$this->isLoggedIn()) {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/login");
            exit();
        }

        $email = $_SESSION['user_id'];
        $user = $this->userRepository->getUserByEmail($email);
        $business = $this->businessRepository->getBusinessByUserId($user['id']);

        if (!$business) {
            // Error handling if user has no business
            $this->render('business-dashboard', ['messages' => ['Nie znaleziono firmy przypisanej do konta.']]);
            return;
        }

        $name = $_POST['name'];
        $price = (float) $_POST['price'];
        $duration = (int) $_POST['duration_minutes'];
        $description = $_POST['description'];

        $this->serviceRepository->addService($business['id'], $name, $price, $duration, $description);

        $url = "http://$_SERVER[HTTP_HOST]";
        // Redirect back to dashboard or profile
        header("Location: {$url}/business_dashboard?status=service_added");
        exit();
    }
}
