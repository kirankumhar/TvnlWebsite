<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['login']) && $_SESSION['login'] != true) {
    echo "Invalid session.";
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Buddy! You are in wrong way.";
    exit;
}


require_once "../../database/Database.php";

$database = new Database();
$pdo = $database->getConnection();

if (isset($_POST['photoId'])) {
    $photoId = $_POST['photoId'];

    $albumQuery = $pdo->prepare('SELECT * FROM albums WHERE cover_photo_id = ? AND is_deleted <> 1');
    $albumQuery->execute([$photoId]);
    $albumRecord = $albumQuery->fetch(PDO::FETCH_ASSOC);

    if ($albumRecord) {
        echo "Cannot delete the cover photo. Please change the cover photo first.";
        exit;
    }

    $photoQuery = $pdo->prepare('SELECT * FROM photos WHERE id = ?');
    $photoQuery->execute([$photoId]);
    $photoRecord = $photoQuery->fetch(PDO::FETCH_ASSOC);

    if (is_dir($photoRecord['file_path'])) {
        if (unlink($photoRecord['file_path'])) {
            $Query = $pdo->prepare('DELETE FROM photos WHERE id = ?');
            $Query->execute([$photoId]);

            $result = $db->query("SELECT id FROM photos WHERE album_id = ? ORDER BY position", [$albumId]);
            $position = 1;
            
            foreach($result as $row) {
                $db->query("UPDATE photos SET position = ? WHERE id = ?", [$position, $row['id']]);
                $position++;
            }

            echo json_encode(['success' => 'Photo deleted successfully.']);
        } else {
            echo "Error deleting photo file.";
        }
    } else {
        $Query = $pdo->prepare('DELETE FROM photos WHERE id = ?');
        $Query->execute([$photoId]);

        $result = $db->query("SELECT id FROM photos WHERE album_id = ? ORDER BY position", [$albumId]);
        $position = 1;
        
        foreach($result as $row) {
            $db->query("UPDATE photos SET position = ? WHERE id = ?", [$position, $row['id']]);
            $position++;
        }

        echo json_encode(['success' => 'Photo deleted successfully.']);
    }
} else {
    echo "Photo ID not provided.";
}

?>