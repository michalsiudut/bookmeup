<?php

require_once 'Repository.php';

class AppointmentRepository extends Repository
{
    public function runSchemaUpdate()
    {
        try {
            $pdo = $this->database->connect();
            $pdo->exec("ALTER TABLE appointments ADD COLUMN service_id INTEGER REFERENCES services(id);");
            echo "Column service_id added.<br>";
        } catch (PDOException $e) {
            echo "Info (appointments): " . $e->getMessage() . "<br>";
        }

        try {
            $pdo = $this->database->connect();
            $pdo->exec("ALTER TABLE services ADD COLUMN start_hour TIME DEFAULT '09:00'");
            $pdo->exec("ALTER TABLE services ADD COLUMN end_hour TIME DEFAULT '17:00'");
            echo "Columns start_hour and end_hour added.<br>";
        } catch (PDOException $e) {
            echo "Info (services): " . $e->getMessage() . "<br>";
        }

        try {
            $pdo = $this->database->connect();
            $pdo->exec("ALTER TABLE appointments ADD COLUMN is_reviewed BOOLEAN DEFAULT FALSE");
            echo "Column is_reviewed added.<br>";
        } catch (PDOException $e) {
            echo "Info (is_reviewed): " . $e->getMessage() . "<br>";
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

    public function getAppointmentsByUserId(int $userId): array
    {
        // Auto-update status for past appointments
        $this->updatePastAppointments();

        $stmt = $this->database->connect()->prepare('
            SELECT 
                a.id,
                a.appointment_date,
                a.status,
                a.is_reviewed,
                b.name as business_name,
                s.name as service_name,
                s.price
            FROM appointments a
            LEFT JOIN businesses b ON a.business_id = b.id
            LEFT JOIN services s ON a.service_id = s.id
            WHERE a.user_id = :user_id
            ORDER BY a.appointment_date DESC
        ');

        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAppointmentById(int $id): ?array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM appointments WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
        return $appointment ?: null;
    }

    public function updateStatus(int $appointmentId, string $status): void
    {
        $stmt = $this->database->connect()->prepare('
            UPDATE appointments SET status = :status WHERE id = :id
        ');
        $stmt->bindParam(':id', $appointmentId, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
    }

    private function updatePastAppointments()
    {
        try {
            // Mark as completed if date is in past and status is pending or confirmed
            $stmt = $this->database->connect()->prepare("
                UPDATE appointments 
                SET status = 'completed' 
                WHERE appointment_date < NOW() 
                AND status IN ('pending', 'confirmed')
            ");
            $stmt->execute();
        } catch (PDOException $e) {
            // Silently fail or log error, so we don't block fetching list
            // error_log($e->getMessage());
        }
    }

    public function getBookedSlots(int $businessId, string $date): array
    {
        $stmt = $this->database->connect()->prepare("
            SELECT appointment_date::time as slot_time
            FROM appointments
            WHERE business_id = :business_id 
            AND appointment_date::date = :date
            AND status NOT IN ('cancelled')
        ");

        $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
