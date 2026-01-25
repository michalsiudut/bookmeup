<?php

require_once 'Repository.php';

class CategoryRepository extends Repository
{
    private $categoriesList = [
        "Usługi fryzjerskie",
        "Usługi kosmetyczne",
        "Medycyna estetyczna",
        "Masaż i fizjoterapia",
        "Fitness i trening personalny",
        "Zdrowie i rehabilitacja",
        "Stomatologia",
        "Usługi medyczne prywatne",
        "Usługi IT i serwis komputerowy",
        "Marketing i reklama",
        "Fotografia i wideo",
        "Edukacja i korepetycje",
        "Szkolenia i coaching",
        "Usługi prawne",
        "Usługi księgowe i podatkowe",
        "Nieruchomości i doradztwo mieszkaniowe",
        "Usługi remontowo-budowlane",
        "Sprzątanie i utrzymanie czystości",
        "Motoryzacja i serwis pojazdów",
        "Eventy i organizacja wydarzeń"
    ];

    public function getAllCategories(): array
    {
        $this->ensureCategoriesExist();

        $stmt = $this->database->connect()->prepare('
            SELECT * FROM categories ORDER BY name ASC
        ');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function ensureCategoriesExist()
    {
        // 1. Create table if not exists
        $this->database->connect()->exec('
            CREATE TABLE IF NOT EXISTS categories (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE
            )
        ');

        // 2. Check content
        $stmt = $this->database->connect()->prepare('SELECT COUNT(*) FROM categories');
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            // 3. Seed data
            $insertStmt = $this->database->connect()->prepare('
                INSERT INTO categories (name) VALUES (:name)
            ');

            foreach ($this->categoriesList as $category) {
                try {
                    $insertStmt->execute([':name' => $category]);
                } catch (PDOException $e) {
                    // Ignore duplicates or errors during seed
                    continue;
                }
            }
        }
    }
}
