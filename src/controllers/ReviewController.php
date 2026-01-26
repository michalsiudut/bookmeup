<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/ReviewRepository.php';
require_once __DIR__ . '/../repository/AppointmentRepository.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class ReviewController extends AppController
{
    private $reviewRepository;
    private $appointmentRepository;
    private $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->reviewRepository = new ReviewRepository();
        $this->appointmentRepository = new AppointmentRepository();
        $this->userRepository = new UserRepository();
    }

    public function addReview()
    {
        if (!$this->isPost()) {
            http_response_code(405);
            exit();
        }

        if (!$this->isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $appointmentId = $input['appointment_id'] ?? null;
        $rating = $input['rating'] ?? null;
        $comment = $input['comment'] ?? '';

        if (!$appointmentId || !$rating) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing data']);
            return;
        }

        $email = $_SESSION['user_id'];
        $user = $this->userRepository->getUserByEmail($email);
        $userId = $user['id'];

        try {
            $appointment = $this->appointmentRepository->getAppointmentById((int) $appointmentId);

            if (!$appointment || (int) $appointment['user_id'] !== (int) $userId) {
                http_response_code(404);
                echo json_encode(['error' => 'Appointment not found or unauthorized']);
                return;
            }

            if ($appointment['status'] !== 'completed') {
                http_response_code(400);
                echo json_encode(['error' => 'Appointment not completed']);
                return;
            }

            if ($appointment['is_reviewed']) {
                http_response_code(400);
                echo json_encode(['error' => 'Already reviewed']);
                return;
            }

            $businessId = $appointment['business_id'];

            $this->reviewRepository->addReview($userId, $businessId, (int) $rating, $comment, (int) $appointmentId);

            echo json_encode(['message' => 'Review added successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }
}
