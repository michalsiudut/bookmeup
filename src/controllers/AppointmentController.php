<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/AppointmentRepository.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class AppointmentController extends AppController
{
    private $appointmentRepository;
    private $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->appointmentRepository = new AppointmentRepository();
        $this->userRepository = new UserRepository();
    }

    public function bookAppointment()
    {
        // Check if content type is JSON
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if ($contentType === "application/json") {
            $content = trim(file_get_contents("php://input"));
            $decoded = json_decode($content, true);

            if (is_array($decoded)) {
                // Merge decoded JSON with $_POST to support unified access if needed, 
                // but strictly we should use the decoded data
                $_POST = array_merge($_POST, $decoded);
            }
        }

        if (!$this->isPost()) {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'User not logged in']);
            return;
        }

        $userEmail = $_SESSION['user_id']; // session stores email mostly based on other controllers
        $user = $this->userRepository->getUserByEmail($userEmail);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'User not found']);
            return;
        }

        $userId = $user['id'];
        $businessId = $_POST['business_id'] ?? null;
        $serviceId = $_POST['service_id'] ?? null;
        $date = $_POST['date'] ?? null;
        $time = $_POST['time'] ?? null;

        if (!$businessId || !$serviceId || !$date || !$time) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $appointmentDate = $date . ' ' . $time;

        try {
            $this->appointmentRepository->addAppointment($userId, $businessId, $serviceId, $appointmentDate);
            http_response_code(200);
            echo json_encode(['message' => 'Appointment booked successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function updateSchema()
    {
        try {
            // Attempt to add the column. Using raw SQL via repository would be cleaner but direct access here is quick fix.
            // Ideally we use a migration method in repository.
            // Let's use the repository connection helper if available or just instantiate DB.

            // We can reuse appointmentRepository since it extends Repository which has database.
            // But Repository property $database is protected.
            // Let's just create a new Database instance or use a public method if exists.

            // Actually, Repository has protected $database. We can't access it directly from controller easily 
            // unless we add a method to AppointmentRepository.

            $this->appointmentRepository->runSchemaUpdate();

            echo "Schema update attempted. Check if you can book now.";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
