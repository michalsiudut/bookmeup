<?php

require_once 'Repository.php';

class BusinessRepository extends Repository
{

    public function getBusinesses(?string $search = null): array
    {
        $query = 'SELECT * FROM businesses';
        $params = [];

        if ($search) {
            $query .= ' WHERE LOWER(name) LIKE :search OR LOWER(category) LIKE :search OR LOWER(city) LIKE :search';
            $params[':search'] = '%' . strtolower($search) . '%';
        }

        $query .= ' ORDER BY created_at DESC';

        $stmt = $this->database->connect()->prepare($query);
        $stmt->execute($params);

        $businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $businesses ?: [];
    }

    public function searchBusinesses(string $searchString): array
    {
        $searchString = '%' . strtolower($searchString) . '%';

        $stmt = $this->database->connect()->prepare('
            SELECT * FROM businesses 
            WHERE LOWER(name) LIKE :search 
            OR LOWER(category) LIKE :search 
            OR LOWER(city) LIKE :search
        ');
        $stmt->bindParam(':search', $searchString);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkIfOwner(int $userId): bool
    {
        $stmt = $this->database->connect()->prepare('
            SELECT 1 FROM user_business WHERE user_id = :user_id
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }
}