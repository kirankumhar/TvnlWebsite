<?php
// For development only - at the beginning of the file

// Enable error reporting for debugging
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - POST: " . print_r($_POST['action'], true) . "\n\nFILES: " . print_r($_FILES, true) . "\n\n", FILE_APPEND);

session_start();

header('Content-Type: application/json');
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'AlbumController.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if action is set in the FormData request
    if (!isset($_POST['action'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing action parameter."]);
        exit;
    }

    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo json_encode(["error" => "Invalid CSRF token."]);
        exit;
    }

    // Controller
    $controller = new AlbumController();

    switch ($_POST['action']) {
        case "editPhotos":
            if (!isset($_POST['album_id']) || !isset($_POST['photosData'])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required parameters."]);
                exit;
            }

            $photosData = json_decode($_POST['photosData'], true);
            if (!is_array($photosData)) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid photos data."]);
                exit;
            }

            $albumId = $_POST['album_id'];
            $newPhotos = $_FILES['newPhotos'] ?? [];
            $replacePhotos = $_FILES['replacedPhotos'] ?? [];

            $controller->editPhotos($albumId, $photosData, $newPhotos, $replacePhotos);

            break;

        case "editAlbum":
            $controller->editAlbums($_POST);
            break;

        case "deleteAlbum":
            $controller->deleteAlbums($_POST);
            break;

        case "addVideos":
            if (!isset($_POST['albumId']) || !isset($_POST['Video']) || !isset($_POST['caption_en'])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required parameters."]);
                exit;
            }

            $albumId = $_POST['albumId'];
            $videoURL = $_POST['Video'];
            $caption = $_POST['caption_en'];

            $controller->addVideos($albumId, $videoURL, $caption);

            break;

        case "deleteVideoSet":
            if (!isset($_POST['videoId'])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required parameters."]);
                exit;
            }

            $videoId = $_POST['videoId'];

            $controller->deleteVideoSet($videoId);

            break;

        case "updateVideoSet":
            if (!isset($_POST['videoId']) || !isset($_POST['url']) || !isset($_POST['caption'])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required parameters."]);
                exit;
            }

            $videoId = $_POST['videoId'];
            $url = $_POST['url'];
            $caption = $_POST['caption'];

            $controller->updateVideoSet($videoId, $url, $caption);

            break;

        case "updateVideoCoverPic":
            if (!isset($_FILES['coverPic']) || !isset($_POST['albumID'])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required parameters."]);
                exit;
            }

            $coverPic = $_FILES['coverPic'];
            $albumId = $_POST['albumID'];

            $controller->updateVideoCoverPic($albumId, $coverPic);

            break;
        default:
            http_response_code(400);
            echo json_encode(["error" => "Invalid action."]);
            exit;
    }

    // Regenerate CSRF Token
    unset($_SESSION['csrf_token']);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>