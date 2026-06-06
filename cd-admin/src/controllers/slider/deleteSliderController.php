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

if (empty($sliderId)) {
    echo json_encode(['success' => false, 'message' => 'Slider ID is required']);
    exit;
}

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Get image path before deleting
    $stmt = $pdo->prepare("SELECT image_path FROM sliders WHERE uniq_id = :slider_id AND is_deleted = 0");
    $stmt->execute([':slider_id' => $sliderId]);
    $slider = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$slider) {
        echo json_encode(['success' => false, 'message' => 'Slider not found']);
        exit;
    }
    
    // Soft delete the slider
    $stmt = $pdo->prepare("UPDATE sliders SET is_deleted = 1, updated_at = NOW() WHERE uniq_id = :slider_id");
    $result = $stmt->execute([':slider_id' => $sliderId]);
    
    if ($result) {
        // Delete the physical image file from src/uploads/sliders/
        if (!empty($slider['image_path'])) {
            $imagePath = __DIR__ . '/../../' . $slider['image_path'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Slider deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete slider']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>