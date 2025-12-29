<?php

require_once 'Repository.php';

class BusinessRepository extends Repository {

    public function getBusinesses(): array {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM businesses ORDER BY created_at DESC
        ');
        $stmt->execute();

        $businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Jeśli baza jest pusta, zwróć pustą tablicę
        return $businesses ?: [];
    }

    // Dodatkowa metoda, która przyda się później do wyszukiwarki
    public function searchBusinesses(string $searchString): array {
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
}