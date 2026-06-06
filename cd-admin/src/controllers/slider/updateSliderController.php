<?php
session_start();

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: ../../../index.php');
    exit;
}

require_once __DIR__ . '/../../database/Database.php';
require_once __DIR__ . '/../../models/SliderModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ../../../manage-sliders.php');
    exit;
}

// Store form data in session for repopulation if error occurs
$_SESSION['form_data'] = [
    'title' => $_POST['title'] ?? '',
    'subtitle' => $_POST['subtitle'] ?? '',
    'make_slider' => isset($_POST['make_slider']) ? true : false,
    'status' => $_POST['status'] ?? 'A'
];

$slider_id = intval($_POST['slider_id'] ?? 0);
if (empty($slider_id)) {
    $_SESSION['error'] = 'Invalid slider ID.';
    header('Location: ../../../manage-sliders.php');
    exit;
}

// Validate required fields
$title = trim($_POST['title'] ?? '');
if (empty($title)) {
    $_SESSION['error'] = 'Title is required.';
    header('Location: ../../../edit-slider.php?id=' . $slider_id);
    exit;
}

// Validate replacement image if one was uploaded.
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = 'Image upload failed. Please try again.';
        header('Location: ../../../edit-slider.php?id=' . $slider_id);
        exit;
    }

    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB
    $fileType = mime_content_type($_FILES['image']['tmp_name']);

    if (!in_array($fileType, $allowedTypes)) {
        $_SESSION['error'] = 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP';
        header('Location: ../../../edit-slider.php?id=' . $slider_id);
        exit;
    }

    if ($_FILES['image']['size'] > $maxFileSize) {
        $_SESSION['error'] = 'File size exceeds 2MB limit.';
        header('Location: ../../../edit-slider.php?id=' . $slider_id);
        exit;
    }
}

try {
    $database = new Database();
    $pdo = $database->getConnection();
    $sliderModel = new SliderModel($pdo);
    
    // Get existing slider data
    $stmt = $pdo->prepare("SELECT * FROM sliders WHERE id = :id");
    $stmt->execute([':id' => $slider_id]);
    $existingSlider = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingSlider) {
        $_SESSION['error'] = 'Slider not found.';
        header('Location: ../../../manage-sliders.php');
        exit;
    }
    
    // Prepare data for update
    $data = [
        'title' => $title,
        'subtitle' => $_POST['subtitle'] ?? '',
        'make_slider' => isset($_POST['make_slider']) ? 1 : 0,
        'status' => $_POST['status'] ?? 'A',
        'existing_image_path' => $existingSlider['image_path'],
        'image' => $_FILES['image'] ?? null
    ];
    
    if ($sliderModel->updateSlider($slider_id, $data)) {
        unset($_SESSION['form_data']);
        $_SESSION['message'] = 'Slider updated successfully!';
        header('Location: ../../../manage-sliders.php');
        exit;
    } else {
        $_SESSION['error'] = 'Failed to update slider. Please try again.';
        header('Location: ../../../edit-slider.php?id=' . $slider_id);
        exit;
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
    header('Location: ../../../edit-slider.php?id=' . $slider_id);
    exit;
}
?>
