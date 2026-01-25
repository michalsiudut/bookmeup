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

    echo "Attempting to add start_hour and end_hour to services table...\n";
    try {
        $conn->exec("ALTER TABLE services ADD COLUMN start_hour TIME DEFAULT '09:00'");
        $conn->exec("ALTER TABLE services ADD COLUMN end_hour TIME DEFAULT '17:00'");
        echo "Success: Columns start_hour and end_hour added.\n";
    } catch (PDOException $e) {
        echo "Info: " . $e->getMessage() . "\n";
    }
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), 'already exists') !== false) {
        echo "Info: Column service_id already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
