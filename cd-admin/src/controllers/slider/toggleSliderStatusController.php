<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

require_once __DIR__ . '/../../database/Database.php';

$sliderId = $_POST['slider_id'] ?? 0;
$makeSlider = $_POST['make_slider'] ?? 0;

if (empty($sliderId)) {
    echo json_encode(['success' => false, 'message' => 'Slider ID is required']);
    exit;
}

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $stmt = $pdo->prepare("UPDATE sliders SET make_slider = :make_slider, updated_at = NOW() WHERE uniq_id = :slider_id");
    $result = $stmt->execute([
        ':make_slider' => $makeSlider,
        ':slider_id' => $sliderId
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Slider homepage status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update slider status']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>