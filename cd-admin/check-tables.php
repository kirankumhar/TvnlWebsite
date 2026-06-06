<?php
// check-tables.php - Run this to verify tables exist

require_once __DIR__ . '/src/database/Database.php';

$database = new Database();
$pdo = $database->getConnection();

$tables = ['tender_notice', 'tender_corrigendum', 'tender_extension', 'tender_cancellation', 'tender_attachments'];

echo "<h2>Table Status Check</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Table Name</th><th>Status</th><th>Record Count</th></tr>";

foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<tr>";
        echo "<td>$table</td>";
        echo "<td style='color:green'>✓ Exists</td>";
        echo "<td>$count records</td>";
        echo "</tr>";
    } catch (PDOException $e) {
        echo "<tr>";
        echo "<td>$table</td>";
        echo "<td style='color:red'>✗ Missing - Run SQL to create</td>";
        echo "<td>-</td>";
        echo "</tr>";
    }
}
echo "</table>";
?>