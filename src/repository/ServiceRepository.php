<?php

require_once 'Repository.php';

class ServiceRepository extends Repository
{
    public function addService(int $businessId, string $name, float $price, int $duration, string $description, string $startHour, string $endHour)
    {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO services (business_id, name, price, duration_minutes, description, start_hour, end_hour)
            VALUES (:business_id, :name, :price, :duration_minutes, :description, :start_hour, :end_hour)
        ');

        $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':duration_minutes', $duration, PDO::PARAM_INT);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':start_hour', $startHour, PDO::PARAM_STR);
        $stmt->bindParam(':end_hour', $endHour, PDO::PARAM_STR);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            // Check for undefined column error (Postgres code 42703) or generic message
            if ($e->getCode() == '42703' || strpos($e->getMessage(), 'start_hour') !== false) {
                try {
                    $pdo = $this->database->connect();
                    $pdo->exec("ALTER TABLE services ADD COLUMN start_hour TIME DEFAULT '09:00'");
                    $pdo->exec("ALTER TABLE services ADD COLUMN end_hour TIME DEFAULT '17:00'");

                    // Retry execution
                    $stmt->execute();
                    return;
                } catch (Exception $ex) {
                    throw $e; // Throw original error if fix fails
                }
            }
            throw $e;
        }
    }

    public function getServicesByBusinessId(int $businessId): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM services WHERE business_id = :business_id ORDER BY id DESC
        ');
        $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
