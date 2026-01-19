<?php
require_once 'Database.php';
$db = new Database();
$conn = $db->connect();
$stmt = $conn->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = 'businesses'");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($columns);
