<?php

require_once 'Repository.php';

class ReviewRepository extends Repository
{
    public function runSchemaUpdate()
    {
        try {
            $pdo = $this->database->connect();
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS reviews (
                    id SERIAL PRIMARY KEY,
                    business_id INTEGER REFERENCES businesses(id) ON DELETE CASCADE,
                    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
                    rating INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 5),
                    comment TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            echo "Table 'reviews' created or already exists.<br>";
        } catch (PDOException $e) {
            echo "Error creating reviews table: " . $e->getMessage() . "<br>";
        }
    }

    public function getReviewsByBusinessId(int $businessId): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT r.*, u.firstname, u.lastname, u.image_url 
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.business_id = :business_id
            ORDER BY r.created_at DESC
        ');
        $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBusinessRatingStats(int $businessId, ?PDO $pdo = null): array
    {
        $connection = $pdo ?: $this->database->connect();
        $stmt = $connection->prepare('
            SELECT 
                COALESCE(AVG(rating), 0) as average_rating,
                COUNT(*) as review_count
            FROM reviews
            WHERE business_id = :business_id
        ');
        $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addReview(int $userId, int $businessId, int $rating, string $comment, int $appointmentId): void
    {
        $pdo = $this->database->connect();
        try {
            $pdo->beginTransaction();

            // 1. Insert review
            $stmt = $pdo->prepare('
                INSERT INTO reviews (user_id, business_id, rating, comment, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ');
            $stmt->execute([$userId, $businessId, $rating, $comment]);

            // 2. Mark appointment as reviewed
            $stmt = $pdo->prepare('UPDATE appointments SET is_reviewed = TRUE WHERE id = ?');
            $stmt->execute([$appointmentId]);

            // 3. Update business rating and count
            $stats = $this->getBusinessRatingStats($businessId, $pdo);
            $stmt = $pdo->prepare('
                UPDATE businesses 
                SET rating = ?, review_count = ? 
                WHERE id = ?
            ');
            $stmt->execute([
                round($stats['average_rating'], 2),
                $stats['review_count'],
                $businessId
            ]);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
