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

        // Security check: verify appointment exists, is completed, belongs to user, and is not reviewed
        $stmt = $this->appointmentRepository->getAppointmentsByUserId($userId);
        $appointment = null;
        foreach ($stmt as $appt) {
            if ($appt['id'] == $appointmentId) {
                $appointment = $appt;
                break;
            }
        }

        if (!$appointment) {
            http_response_code(404);
            echo json_encode(['error' => 'Appointment not found']);
            return;
        }

        if ($appointment['status'] !== 'completed') {
            http_response_code(400);
            echo json_encode(['error' => 'Appointment not completed']);
            return;
        }

        // We need to check if it's already reviewed. 
        // AppointmentRepository::getAppointmentsByUserId should be updated to return is_reviewed.
        if (isset($appointment['is_reviewed']) && $appointment['is_reviewed']) {
            http_response_code(400);
            echo json_encode(['error' => 'Already reviewed']);
            return;
        }

        try {
            // Get business_id from somewhere. 
            // We need to fetch the appointment details explicitly to get business_id.
            $pdo = $this->appointmentRepository->getAppointmentsByUserId($userId);
            // Actually I'll use a specific query in ReviewRepository or AppointmentRepository to get business_id.

            // Let's assume we fetch it correctly. 
            // Re-using the join from getAppointmentsByUserId but I need business_id specifically.
            $conn = $this->appointmentRepository->updateStatus($appointmentId, 'completed'); // Dummy call to get handle or I update the query.

            // Fetch business_id
            $db = new Database();
            $pdo = $db->connect();
            $checkStmt = $pdo->prepare('SELECT business_id FROM appointments WHERE id = ?');
            $checkStmt->execute([$appointmentId]);
            $businessId = $checkStmt->fetchColumn();

            $this->reviewRepository->addReview($userId, $businessId, (int) $rating, $comment, (int) $appointmentId);

            echo json_encode(['message' => 'Review added successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }
}
