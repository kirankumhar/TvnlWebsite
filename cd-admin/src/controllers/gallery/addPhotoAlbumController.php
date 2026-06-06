<?php
session_start();
require_once "../../database/Database.php";
require_once "../../models/Gallery/AlbumModel.php";

class AlbumController
{
    public function getSessionYear($event_date)
    {
        // Extract the year and month from the event date
        $year = date('Y', strtotime($event_date));
        return $year;
        
    }

    public function insertAlbum()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }

        $database = new Database();
        $pdo = $database->getConnection();
        $albumModel = new AlbumModel($pdo);

        session_start();
        $domainStmt = $pdo->prepare("SELECT domain_id FROM users WHERE id = :userId LIMIT 1");
        $domainStmt->bindValue(':userId', (int)$_SESSION['user_id'], PDO::PARAM_INT);
        $domainStmt->execute();
        $domainId = (int)$domainStmt->fetchColumn();
        $name_en = filter_input(INPUT_POST, 'eng_cat', FILTER_SANITIZE_STRING);
        $name_hi = filter_input(INPUT_POST, 'hin_cat', FILTER_SANITIZE_STRING);
        $description_en = filter_input(INPUT_POST, 'en_albm_desc');
        $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
        $event_date = filter_input(INPUT_POST, 'dt_event', FILTER_SANITIZE_STRING);
        $type = filter_input(INPUT_POST, 'album_type', FILTER_SANITIZE_STRING);
        $captions = $_POST['caption'] ?? [];
        $uid = uniqid();

        switch ($type) {
            case 'Videos':
                if (empty($name_en) || empty($event_date) || empty($type) || empty($_POST['video_link'])) {
                    $_SESSION['error'] = "Fill all the mandatory fields.";
                    header('Location: ../../../post-album.php');
                    exit();
                }
                break;
            default:
                if (empty($name_en) || empty($event_date) || empty($type) || empty($_FILES['albumPic'])) {
                    $_SESSION['error'] = "Fill all the mandatory fields.";
                    header('Location: ../../../post-album.php');
                    exit();
                }

                $year = date('Y', strtotime($event_date));
                $mon = date('m', strtotime($event_date));

                $album_folder_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($uid));
                $target_folder_path = "uploads/$type/$year/$mon/$album_folder_name";

                if (!is_dir("../../$target_folder_path") && !mkdir("../../$target_folder_path", 0777, true)) {
                    $_SESSION['error'] = "Failed to create the folder: $target_folder_path";
                    header('Location: ../../../post-album.php');
                    exit();
                }
                break;
        }

        $session_year = $this->getSessionYear($event_date);


        try {
            $pdo->beginTransaction();
            $album_id = $albumModel->createAlbum($domainId, $name_en, $name_hi, $description_en, $location, $event_date, $type, $uid, $session_year);

            switch ($type) {
                case 'Videos':
                    $video_id = $albumModel->addVideosAlbum($album_id, $description_en, $_POST['video_link']);
                   
                    $coverVideo = $albumModel->setCoverVideo($album_id, $video_id);
                    break;
                default:
                    foreach ($_FILES['albumPic']['name'] as $i => $filename) {
                        $unique_file_name = uniqid() . '_' . basename($filename);
                        $upload_path = "$target_folder_path/$unique_file_name";

                        if (move_uploaded_file($_FILES['albumPic']['tmp_name'][$i], "../../$upload_path")) {
                            $photo_id = $albumModel->addPhotoToAlbum($album_id, $upload_path, $captions[$i] ?? '');

                            $albumModel->setCoverPhoto($album_id, $photo_id);

                        } else {
                            throw new Exception("Failed to upload image $filename");
                        }
                    }
                    break;
            }


            $pdo->commit();
            $_SESSION['message'] = "Album posted successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "An error occurred: " . $e->getMessage();
        }

        header('Location: ../../../post-album.php');
        exit();
    }
}

$controller = new AlbumController();
$controller->insertAlbum();
