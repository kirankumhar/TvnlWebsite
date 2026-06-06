<?php
// debug_columns.php - Check table structures

require_once 'cd-admin/src/database/Database.php';

$database = new Database();
$pdo = $database->getConnection();

// Check notices table columns
echo "<h3>Notices Table Columns:</h3>";
$stmt = $pdo->query("DESCRIBE notices");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($columns);
echo "</pre>";

// Check if there's a title column in notices
$stmt = $pdo->query("SHOW COLUMNS FROM notices LIKE '%title%'");
$titleColumn = $stmt->fetchAll();
echo "<h3>Title column exists? " . (count($titleColumn) > 0 ? "Yes" : "No") . "</h3>";

// Check sample data from notices
echo "<h3>Sample Notice Data:</h3>";
$stmt = $pdo->query("SELECT * FROM notices LIMIT 5");
$sample = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($sample);
echo "</pre>";
?>