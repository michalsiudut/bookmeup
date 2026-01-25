<?php

require_once 'Repository.php';

class AppointmentRepository extends Repository
{
    public function runSchemaUpdate()
    {
        try {
            $this->database->connect()->exec("ALTER TABLE appointments ADD COLUMN service_id INTEGER REFERENCES services(id);");
            echo "Column added.";
        } catch (PDOException $e) {
            echo "Info (ignore if column exists): " . $e->getMessage();
        }
    }

    public function addAppointment(int $userId, int $businessId, int $serviceId, string $date, string $status = 'pending', string $type = 'standard'): void
    {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO appointments (user_id, business_id, service_id, appointment_date, status, type, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ');

        $stmt->execute([
            $userId,
            $businessId,
            $serviceId,
            $date,
            $status,
            $type
        ]);
    }
}
