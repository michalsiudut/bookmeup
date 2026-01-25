<?php

require_once 'Database.php';

try {
    $database = new Database();
    $conn = $database->connect();

    // Check if column exists first to avoid error on re-run (PostgreSQL specific check, adapting for general)
    // Or just try raw ALTER and catch error

    echo "Attempting to add service_id column to appointments table...\n";
    try {
        $conn->exec("ALTER TABLE appointments ADD COLUMN service_id INTEGER REFERENCES services(id)");
        echo "Success: Column service_id added.\n";
    } catch (PDOException $e) {
        echo "Info: " . $e->getMessage() . "\n";
    }

    echo "Attempting to add column is_reviewed to appointments table...\n";
    try {
        $conn->exec("ALTER TABLE appointments ADD COLUMN is_reviewed BOOLEAN DEFAULT FALSE");
        echo "Success: Column is_reviewed added.\n";
    } catch (PDOException $e) {
        echo "Info: " . $e->getMessage() . "\n";
    }

    echo "Attempting to create reviews table...\n";
    try {
        $conn->exec("
            CREATE TABLE IF NOT EXISTS reviews (
                id SERIAL PRIMARY KEY,
                business_id INTEGER REFERENCES businesses(id) ON DELETE CASCADE,
                user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
                rating INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 5),
                comment TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "Success: Table 'reviews' created or already exists.\n";
    } catch (PDOException $e) {
        echo "Error creating reviews table: " . $e->getMessage() . "\n";
    }

    echo "Attempting to add start_hour and end_hour to services table...\n";
    try {
        $conn->exec("ALTER TABLE services ADD COLUMN start_hour TIME DEFAULT '09:00'");
        $conn->exec("ALTER TABLE services ADD COLUMN end_hour TIME DEFAULT '17:00'");
        echo "Success: Columns start_hour and end_hour added.\n";
    } catch (PDOException $e) {
        echo "Info: " . $e->getMessage() . "\n";
    }

    echo "Recalculating all business ratings and review counts...\n";
    try {
        $conn->exec("
            UPDATE businesses b
            SET 
                rating = COALESCE((SELECT ROUND(AVG(rating), 2) FROM reviews r WHERE r.business_id = b.id), 0),
                review_count = (SELECT COUNT(*) FROM reviews r WHERE r.business_id = b.id)
        ");
        echo "Success: Business stats recalculated.\n";
    } catch (PDOException $e) {
        echo "Error recalculating stats: " . $e->getMessage() . "\n";
    }
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), 'already exists') !== false) {
        echo "Info: Column service_id already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
