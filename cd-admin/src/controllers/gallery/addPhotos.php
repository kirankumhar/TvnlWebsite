<?php
session_start();

if (!isset($_SESSION['login']) && $_SESSION['login'] != true) {
    echo "Invalid session.";
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Buddy! You are in wrong way.";
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once "../../database/Database.php";
define('MAX_FILE_SIZE', 500 * 1024);
define('BASE_UPLOAD_DIR', 'uploads/Photos');

$database = new Database();
$pdo = $database->getConnection();

$albumId = $_POST['albumId'];

$albumQuery = $pdo->prepare('SELECT * FROM albums WHERE uniq_id = ?');
$albumQuery->execute([$albumId]);
$albumRecord = $albumQuery->fetch(PDO::FETCH_ASSOC);

if ($albumRecord) {
    if (isset($_FILES['photo'])) {
        $photoFile = $_FILES['photo'];
        $fileName = basename($photoFile['name']);

        $year = date('Y', strtotime($albumRecord['event_date']));
        $mon = date("m", strtotime($albumRecord['event_date']));

        $album_folder_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($albumRecord['name_en']));

        // $target_folder_path = BASE_UPLOAD_DIR . $year . '/Photos/' . $album_folder_name;
        $target_folder_path = BASE_UPLOAD_DIR . '/' . $year . '/'. $mon .'/'. $album_folder_name;

        if (!is_dir('../../'.$target_folder_path)) {
            if (!mkdir("../../$target_folder_path", 0777, true)) {
                $errors[] = "Failed to create the folder: $target_folder_path";
                // die;
            } else {
                echo "Folder created successfully: $target_folder_path";
                // die;
            }
        } else {
            echo "Folder already exists: $target_folder_path";
            // die;
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo "<p style='color:red;'>$error</p>";
            }
        }

        $unique_file_name = uniqid() . '_' . $fileName;

        $upload_path = "$target_folder_path/$unique_file_name";
        // die($upload_path);

        if (move_uploaded_file($photoFile['tmp_name'], "../../$upload_path")) {
            $captionEn = $_POST['caption_en'];

            $photoInsertQuery = $pdo->prepare('
                                    INSERT INTO photos (album_id, file_path, caption_en)
                                    VALUES (?, ?, ?)
                                ');
            $photoInsertQuery->execute([
                $albumRecord['id'],
                $upload_path,
                $captionEn
            ]);
            echo json_encode(['success' => 'Image Store Successfully!']);
            exit;
        } else {
            $pdo->rollBack();
            echo json_encode(['error' => 'Failed to upload photo file.']);
            exit;
        }
    } else {
        echo "Please select a file to upload.";
    }
} else {
    echo json_encode(['error' => 'Invalid Album.']);
}


