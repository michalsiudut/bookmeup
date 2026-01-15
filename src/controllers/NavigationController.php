<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../repository/BusinessRepository.php';

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

        // DATA FETCH HERE
        $businessRepository = new BusinessRepository();

        $businesses = $businessRepository->getBusinesses();

        return $this->render("dashboard", ['businesses' => $businesses]);
    }

    public function calendar()
    {
        // DATA FETCH HERE
        return $this->render("calendar");
    }

    public function appointments()
    {
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

        $this->render('editProfile', [
            'user' => $user
        ]);
    }

}