<?php
require_once "../../database/Database.php";

class AlbumController
{
    private $pdo;
    private $user_id;

    public function __construct()
    {
        // session_start();

        if (!isset($_SESSION['user_id']) || $_SESSION['login'] == 0) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized access.']);
            exit;
        }

        $this->user_id = $_SESSION['user_id'];
        $database = new Database();
        $this->pdo = $database->getConnection();
    }

    public function getSessionYear($event_date)
    {
        // Extract the year and month from the event date
        $year = date('Y', strtotime($event_date));
        $month = date('m', strtotime($event_date));

        // Check if the month is before or after April
        if ($month >= 4) {
            // If the month is April or later, the session year is the current year to the next year
            return $year . '-' . ($year + 1);
        } else {
            // If the month is before April, the session year is the previous year to the current year
            return ($year - 1) . '-' . $year;
        }
    }

    public function editAlbums($data)
    {
        if (!$data || !isset($data['domainId'], $data['albumId'], $data['enAlbumTitle'], $data['dateOfEvent'], $data['location'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data format.']);
            exit;
        }


        $domainId = filter_var($data['domainId'], FILTER_SANITIZE_STRING);
        $albumId = filter_var($data['albumId'], FILTER_SANITIZE_STRING);
        $enAlbumTitle = htmlspecialchars($data['enAlbumTitle'], ENT_QUOTES, 'UTF-8');
        $hiAlbumTitle = htmlspecialchars($data['hiAlbumTitle'], ENT_QUOTES, 'UTF-8');
        $enAlbumDescription = $data['enAlbumDescription'];
        $dateOfEvent = filter_var($data['dateOfEvent'], FILTER_SANITIZE_STRING);
        $location = htmlspecialchars($data['location'], ENT_QUOTES, 'UTF-8');

        $session_year = $this->getSessionYear($dateOfEvent);

        // print_r($session_year);
        // die;

        $stmt = $this->pdo->prepare("SELECT id FROM albums WHERE uniq_id = ?");
        $stmt->execute([$albumId]);
        $albumRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$albumRecord) {
            http_response_code(404);
            echo json_encode(['error' => 'Album not found.']);
            exit;
        }

        if (empty($enAlbumTitle) || empty($dateOfEvent)) {
            $_SESSION['error'] = "Fill all the mandatory fields.";
            exit();
        }

        $albumId = $albumRecord['id'];
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare("
                UPDATE albums SET domain_id = ?, name_en = ?, name_hi = ?, description_en = ?, event_date = ?, location = ?, session_year = ?
                WHERE id = ?
            ");
            $stmt->execute([$domainId, $enAlbumTitle, $hiAlbumTitle, $enAlbumDescription, $dateOfEvent, $location, $session_year, $albumId]);

            $this->pdo->commit();
            $_SESSION['message'] = "Album details updated successfully.";
            echo json_encode(['success' => 'Album details updated successfully.']);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update album details: ' . $e->getMessage()]);
            $_SESSION['error'] = 'Failed to update album details: ' . $e->getMessage();
        }
    }

    public function editPhotos($albumId, $photosData, $newPhotos = [], $replacedPhotos = [])
    {
        if (!$albumId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data format.']);
            exit;
        }

        // Validate album ID
        $stmt = $this->pdo->prepare("SELECT id, event_date, uniq_id, type FROM albums WHERE uniq_id = ?");
        $stmt->execute([$albumId]);
        $albumRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$albumRecord) {
            $_SESSION['error'] = "Album not found.";
            header('Location: ../../../edit-photos.php');
            http_response_code(404);
            echo json_encode(['error' => 'Album not found.']);
            exit;
        }

        $albumDbId = $albumRecord['id'];
        $event_date = $albumRecord['event_date'];
        $uniq_id = $albumRecord['uniq_id'];
        $type = $albumRecord['type'];
        $year = date('Y', strtotime($event_date));
        $mon = date('m', strtotime($event_date));
        $album_folder_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($uniq_id));
        $target_folder_path = "uploads/$type/$year/$mon/$album_folder_name/";

        if (!file_exists("../../$target_folder_path")) {
            mkdir("../../$target_folder_path", 0777, true);
        }

        $this->pdo->beginTransaction();

        try {
            foreach ($photosData as $photo) {
                $photoId = $photo['id'] ?? null;
                $captionEn = htmlspecialchars($photo['caption_en'], ENT_QUOTES, 'UTF-8');
                $isCover = (bool) $photo['is_cover'];
                $position = intval($photo['position']);
                $has_replaced_image = (bool) $photo['has_replaced_image'];

                if ($photoId) {
                    $stmt = $this->pdo->prepare("UPDATE photos SET caption_en = ?, position = ? WHERE id = ? AND album_id = ?");
                    $stmt->execute([$captionEn, $position, $photoId, $albumDbId]);
                }

                if ($isCover) {
                    $stmt = $this->pdo->prepare("UPDATE albums SET cover_photo_id = ? WHERE id = ?");
                    $stmt->execute([$photoId, $albumDbId]);
                }

                if (isset($replacedPhotos['name'][$photoId]) && $replacedPhotos['error'][$photoId] === 0) {
                    // Get old file path
                    $stmt = $this->pdo->prepare("SELECT file_path FROM photos WHERE id = ? AND album_id = ?");
                    $stmt->execute([$photoId, $albumDbId]);
                    $oldPhoto = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($oldPhoto && file_exists('../../' . $oldPhoto['file_path'])) {
                        unlink('../../' . $oldPhoto['file_path']); // Remove old image
                    }

                    // Save new file
                    $event_date = $albumRecord['event_date'];
                    $uniq_id = $albumRecord['uniq_id'];
                    $year = date('Y', strtotime($event_date));
                    $mon = date('m', strtotime($event_date));
                    $album_folder_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($uniq_id));
                    $target_folder_path = "uploads/$type/$year/$mon/$album_folder_name/";

                    if (!file_exists("../../$target_folder_path")) {
                        mkdir("../../$target_folder_path", 0777, true);
                    }

                    $fileName = uniqid() . '_' . basename($replacedPhotos['name'][$photoId]);
                    $filePath = $target_folder_path . $fileName;

                    if (move_uploaded_file($replacedPhotos['tmp_name'][$photoId], '../../' . $filePath)) {
                        // Update file path in database
                        $stmt = $this->pdo->prepare("
                            UPDATE photos SET file_path = ? WHERE id = ? AND album_id = ?
                        ");
                        $stmt->execute([$filePath, $photoId, $albumDbId]);
                    }
                }

            }

            if (!empty($newPhotos['name'])) {
                for ($i = 0; $i < count($newPhotos['name']); $i++) {
                    if ($newPhotos['error'][$i] === 0) {
                        $fileName = uniqid() . '_' . basename($newPhotos['name'][$i]);
                        $filePath = $target_folder_path . $fileName;

                        if (move_uploaded_file($newPhotos['tmp_name'][$i], '../../' . $filePath)) {
                            $stmt = $this->pdo->prepare("INSERT INTO photos (album_id, file_path, position) VALUES (?, ?, ?)");
                            $stmt->execute([$albumDbId, $filePath, count($photosData) + $i + 1]);
                        }
                    }
                }
            }

            $this->pdo->commit();
            $_SESSION['message'] = "$type updated successfully.";
            echo json_encode(['success' => 'Photos updated successfully.']);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update photos: ' . $e->getMessage()]);
        }
    }

    public function deleteAlbums($data)
    {
        if (!$data || !isset($data['albumId'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data format.']);
            exit;
        }

        $albumId = filter_var($data['albumId'], FILTER_SANITIZE_STRING);

        $stmt = $this->pdo->prepare("SELECT id FROM albums WHERE uniq_id = ?");
        $stmt->execute([$albumId]);
        $albumRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$albumRecord) {
            http_response_code(404);
            echo json_encode(['error' => 'Album not found.']);
            exit;
        }

        $stmt = $this->pdo->prepare("UPDATE albums SET is_deleted = 1 WHERE uniq_id = ?");
        $stmt->execute([$albumId]);

        $_SESSION['message'] = "Album deleted successfully.";
        echo json_encode(['success' => 'Album deleted successfully.']);
    }

    public function addVideos($albumId, $videoURL, $caption)
    {
        if (!$albumId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data format.']);
            exit;
        }

        // Validate album ID
        $stmt = $this->pdo->prepare("SELECT id, event_date, uniq_id, type FROM albums WHERE uniq_id = ?");
        $stmt->execute([$albumId]);
        $albumRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$albumRecord) {
            $_SESSION['error'] = "Album not found.";
            header('Location: ../../../edit-video-album.php');
            http_response_code(404);
            echo json_encode(['error' => 'Album not found.']);
            exit;
        }

        try {
            $sql = "INSERT INTO videos (albums_id, video_link, video_title) VALUES (:albumId, :videoURL, :caption)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':albumId', $albumRecord['id'], PDO::PARAM_INT);
            $stmt->bindParam(':videoURL', $videoURL, PDO::PARAM_STR);
            $stmt->bindParam(':caption', $caption, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Video Added successfully.";
                echo json_encode(['success' => 'Video added successfully.']);
            } else {
                throw new Exception("Failed to insert video.");
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }

    }

    public function deleteVideoSet($videoId)
    {

        $stmt = $this->pdo->prepare("SELECT id FROM videos WHERE id = ?");
        $stmt->execute([$videoId]);
        $videoRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$videoRecord) {
            http_response_code(404);
            echo json_encode(['error' => 'Video not found.']);
            exit;
        }

        $stmt = $this->pdo->prepare("DELETE FROM videos WHERE id = ?");
        $stmt->execute([$videoId]);

        $_SESSION['message'] = "Video deleted successfully.";
        echo json_encode(['success' => 'Video deleted successfully.']);

    }

    public function updateVideoSet($videoId, $url, $caption)
    {
        if (!$videoId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data format.']);
            exit;
        }

        // Validate album ID
        $stmt = $this->pdo->prepare("SELECT * FROM videos WHERE id = ?");
        $stmt->execute([$videoId]);
        $albumRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$albumRecord) {
            $_SESSION['error'] = "Album not found.";
            header('Location: ../../../edit-video-album.php');
            http_response_code(404);
            echo json_encode(['error' => 'Album not found.']);
            exit;
        }

        try {
            try {
                $sql = "UPDATE videos SET video_link = :video_link, video_title = :caption WHERE id = :videoId";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':video_link', $url, PDO::PARAM_STR);
                $stmt->bindParam(':caption', $caption, PDO::PARAM_STR);
                $stmt->bindParam(':videoId', $videoId, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    if ($stmt->rowCount() > 0) {
                        $_SESSION['message'] = "Video updated successfully.";
                        echo json_encode(['success' => 'Video updated successfully.']);
                    } else {
                        echo json_encode(['info' => 'No changes detected.']);
                    }
                } else {
                    throw new Exception("Failed to update video.");
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function updateVideoCoverPic($albumId, $coverPic)
    {
        // Validate album ID
        $stmt = $this->pdo->prepare("SELECT id, event_date, uniq_id, type FROM albums WHERE uniq_id = ?");
        $stmt->execute([$albumId]);
        $albumRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$albumRecord) {
            $_SESSION['error'] = "Album not found.";
            header('Location: ../../../edit-photos.php');
            http_response_code(404);
            echo json_encode(['error' => 'Album not found.']);
            exit;
        }

        $albumDbId = $albumRecord['id'];
        $event_date = $albumRecord['event_date'];
        $uniq_id = $albumRecord['uniq_id'];
        $type = $albumRecord['type'];
        $year = date('Y', strtotime($event_date));
        $mon = date('m', strtotime($event_date));
        $album_folder_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($uniq_id));
        $target_folder_path = "uploads/$type/$year/$mon/$album_folder_name/";

        if (!file_exists("../../$target_folder_path")) {
            mkdir("../../$target_folder_path", 0777, true);
        }

        if (isset($_FILES['coverPic']) && $_FILES['coverPic']['error'] === 0) {
            $coverPic = $_FILES['coverPic'];

            try {
                // Get old file path
                $stmt = $this->pdo->prepare("SELECT file_path FROM photos WHERE album_id = ?");
                $stmt->execute([$albumDbId]);
                $oldPhoto = $stmt->fetch(PDO::FETCH_ASSOC);

                // Prepare new file path
                $fileName = uniqid() . '_' . basename($coverPic['name']);
                $filePath = $target_folder_path . $fileName;

                if (move_uploaded_file($coverPic['tmp_name'], '../../' . $filePath)) {
                    // Update DB with new file path
                    $stmt = $this->pdo->prepare("UPDATE photos SET file_path = ? WHERE album_id = ?");
                    if (!$stmt->execute([$filePath, $albumDbId])) {
                        throw new Exception("Failed to update database with new file path.");
                    }
                } else {
                    throw new Exception("Failed to move uploaded file to destination.");
                }

            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
                exit;
            }

        } else {
            http_response_code(400);
            echo json_encode(['error' => 'No valid cover photo uploaded or file error occurred.']);
            exit;
        }

        $_SESSION['message'] = "Video deleted successfully.";
        echo json_encode(['success' => 'Video deleted successfully.']);


    }
}
?>