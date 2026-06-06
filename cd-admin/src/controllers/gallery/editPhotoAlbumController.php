<?php
session_start();

header('Content-Type: application/json');

if (isset($_SESSION['login']) && $_SESSION['login'] == true) {
    require_once "../../database/Database.php";

    $database = new Database();
    $pdo = $database->getConnection();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo "Buddy! You are in wrong way.";
        exit;
    }

    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    // print_r($data);
    if (!$data) {
        echo json_encode(['error' => 'Invalid data sent in request.']);
        exit;
    }

    $sub_cat_id = $data['sub_cat_id'];
    $albumId = $data['albumId'];
    $enAlbumTitle = $data['enAlbumTitle'];
    $enAlbumDescription = $data['enAlbumDescription'];
    $dateOfEvent = $data['dateOfEvent'];
    $location = $data['location'];
    $photos = $data['photos'];

    $albumQuery = $pdo->prepare('SELECT * FROM albums WHERE uniq_id = ?');
    $albumQuery->execute([$albumId]);
    $albumRecord = $albumQuery->fetch(PDO::FETCH_ASSOC);

    $albumId = $albumRecord['id'];

    if ($albumRecord) {
        $pdo->beginTransaction();

        try {
            $albumUpdateQuery = $pdo->prepare('
                UPDATE albums
                SET name_en = ?, description_en = ?, event_date = ?, location = ?
                WHERE id = ?
            ');
            $albumUpdateQuery->execute([
                $enAlbumTitle,
                $enAlbumDescription,
                $dateOfEvent,
                $location,
                $albumId
            ]);

            foreach ($photos as $photo) {

                $photoId = $photo['id'];
                $captionEn = $photo['captionEn'];
                $isCover = $photo['isCover'];
                $position = $photo['position'];

                $photoUpdateQuery = $pdo->prepare('
                        UPDATE photos
                        SET caption_en = ?, position = ?
                        WHERE id = ? AND album_id = ?
                    ');
                $photoUpdateQuery->execute([
                    $captionEn,
                    $photoId,
                    $albumId
                ]);

                // var_dump($isCover);

                if ($isCover) {
                    $albumCoverUpdateQuery = $pdo->prepare('
                            UPDATE albums
                            SET cover_photo_id = ?
                            WHERE id = ?
                        ');
                    $albumCoverUpdateQuery->execute([$photoId, $albumId]);
                }
            }

            $pdo->commit();

            echo json_encode(['success' => 'Changes saved successfully.']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['error' => 'Failed to save changes: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Invalid Album.']);
    }

} else {
    echo json_encode(['error' => 'Invalid session.']);
}
