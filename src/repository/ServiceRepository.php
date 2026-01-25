<?php

require_once 'Repository.php';

class ServiceRepository extends Repository
{
    public function addService(int $businessId, string $name, float $price, int $duration, string $description)
    {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO services (business_id, name, price, duration_minutes, description)
            VALUES (:business_id, :name, :price, :duration_minutes, :description)
        ');

        $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':duration_minutes', $duration, PDO::PARAM_INT);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);

        $stmt->execute();
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
