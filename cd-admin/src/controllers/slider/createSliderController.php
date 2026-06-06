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
    header('Location: ../../../create-slider.php');
    exit;
}

// Store form data in session for repopulation if error occurs
$_SESSION['form_data'] = [
    'title' => $_POST['title'] ?? '',
    'subtitle' => $_POST['subtitle'] ?? '',
    'make_slider' => isset($_POST['make_slider']) ? 1 : 0,
    'status' => $_POST['status'] ?? 'A'
];

// Validate required fields
$title = trim($_POST['title'] ?? '');
if (empty($title)) {
    $_SESSION['error'] = 'Title is required.';
    header('Location: ../../../create-slider.php');
    exit;
}

// Validate image upload
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Slider image is required.';
    header('Location: ../../../create-slider.php');
    exit;
}

// Validate image type and size
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$maxFileSize = 2 * 1024 * 1024; // 2MB

$fileType = mime_content_type($_FILES['image']['tmp_name']);
if (!in_array($fileType, $allowedTypes)) {
    $_SESSION['error'] = 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP';
    header('Location: ../../../create-slider.php');
    exit;
}

if ($_FILES['image']['size'] > $maxFileSize) {
    $_SESSION['error'] = 'File size exceeds 2MB limit.';
    header('Location: ../../../create-slider.php');
    exit;
}

try {
    $database = new Database();
    $pdo = $database->getConnection();
    $sliderModel = new SliderModel($pdo);
    
    // Prepare data for insertion (removed location, event_date, url)
    $data = [
        'title' => $title,
        'subtitle' => $_POST['subtitle'] ?? '',
        'make_slider' => isset($_POST['make_slider']) ? 1 : 0,
        'status' => $_POST['status'] ?? 'A',
        'image' => $_FILES['image']
    ];
    
    if ($sliderModel->createSlider($data)) {
        unset($_SESSION['form_data']);
        $_SESSION['message'] = 'Slider created successfully!';
        header('Location: ../../../manage-sliders.php');
        exit;
    } else {
        $_SESSION['error'] = 'Failed to create slider. Please try again.';
        header('Location: ../../../create-slider.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("CreateSlider Error: " . $e->getMessage());
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
    header('Location: ../../../create-slider.php');
    exit;
}
?>