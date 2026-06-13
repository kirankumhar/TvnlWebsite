<?php
session_start();
require_once "../../database/Database.php";
require_once __DIR__ . '../../../utils/ActivityLogger.php';

class NewsController
{
    private $pdo;
    private $logger;
    private $loggedId;

    public function __construct()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['login'] == 0) {
            header('Location: index.php');
            exit;
        }

        $database = new Database();
        $this->pdo = $database->getConnection();

        $this->logger = new ActivityLogger($this->pdo);
        $this->loggedId = $_SESSION['login_id'];
    }

    public function getSessionYear($event_date)
    {
        // Extract the year and month from the event date
        $year = date('Y', strtotime($event_date));
        return $year;
    }

    private function generateUniqueTenderID($length = 6)
    {
        do {
            $newsID = substr(str_shuffle(str_repeat('0123456789', $length)), 0, $length);
        } while ($this->newsIDExists($newsID));
        return $newsID;
    }

    private function newsIDExists($newsID)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM news WHERE uniq_id = ?");
        $stmt->execute([$newsID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }

    private function createDirectoryIfNotExists($baseDir)
    {
        $baseDir = '../../' . rtrim($baseDir, '/') . '/';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }
        return $baseDir;
    }

    private function getUploadErrorMessage($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
            case UPLOAD_ERR_FORM_SIZE:
                return "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.";
            case UPLOAD_ERR_PARTIAL:
                return "The uploaded file was only partially uploaded.";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Missing a temporary folder.";
            case UPLOAD_ERR_CANT_WRITE:
                return "Failed to write file to disk.";
            case UPLOAD_ERR_EXTENSION:
                return "A PHP extension stopped the file upload.";
            default:
                return "Unknown upload error.";
        }
    }

    private function pdfUpload($fieldName, $path, $allowedExtensions)
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] == UPLOAD_ERR_NO_FILE) {
            return null; // No file uploaded
        }

        $redirectPage = (isset($_POST['post']) && !empty($_POST['post'])) 
            ? 'edit-news.php?id=' . urlencode($_POST['post']) 
            : 'post-news.php';

        if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error_message'] = "Error uploading " . $fieldName . ": " . $this->getUploadErrorMessage($_FILES[$fieldName]['error']);
            header("Location: ../../../$redirectPage");
            exit;
        }

        $fileName = $_FILES[$fieldName]["name"];
        if (!empty($fileName)) {
            $file_ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $file_size_mb = $_FILES[$fieldName]['size'] / (1024 * 1024); // size in MB

            if (!in_array($file_ext, $allowedExtensions)) {
                $_SESSION['error_message'] = "Invalid file type for " . $fieldName . ". Allowed extensions: " . implode(', ', $allowedExtensions);
                header("Location: ../../../$redirectPage");
                exit;
            }

            if ($file_size_mb > 1) {
                $_SESSION['error_message'] = "File size for " . $fieldName . " exceeds 1MB limit.";
                header("Location: ../../../$redirectPage");
                exit;
            }

            $uploads_dir = $this->createDirectoryIfNotExists($path);
            $uniqueName = uniqid() . "_" . $fileName;
            $file_path = $uploads_dir . $uniqueName;

            // Remove the extra '../../' so that the correct file path is used
            if (move_uploaded_file($_FILES[$fieldName]["tmp_name"], $file_path)) {
                return $path . $uniqueName;
            } else {
                return null;
            }
        }
        return null;
    }

    public function insertNews()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../../post-news.php');
            exit;
        }

        $errors = [];
        $_SESSION['post'] = $_POST; // Store previous input values

        // print_r($_POST); die();

        // Basic validations
        if (empty($_POST['news_date'])) {
            $errors['news_date'] = "News Date is required";
        }
        if (empty($_POST['news_title'])) {
            $errors['news_title'] = "News Title is required";
        }
        if (empty($_POST['news_description'])) {
            $errors['news_description'] = "News Description is required";
        }
        if (!isset($_FILES['picture1']) || $_FILES['picture1']['error'] == UPLOAD_ERR_NO_FILE) {
            $_SESSION['error_message'] = "Title Pic is mandatory.";
            header("Location: ../../../post-news.php");
            exit();
        }

        // Validate PDF attachments (example for pdf_attachement1)
        if ($_FILES['pdf_attachement1']['size'] == 0 && empty($_POST['pdf_attachement_title1'])) {
            $pdf_attachement1 = null;
            $pdf_attachement_title1 = null;
        } elseif ($_FILES['pdf_attachement1']['size'] != 0 && !empty($_POST['pdf_attachement_title1'])) {
            $pdf_attachement_title1 = $_POST['pdf_attachement_title1'];
        } else {
            $errors['pdf_attachement1'] = "Pdf Attachment 1 or Pdf Attachment Title 1 is missing";
        }

        if (($_FILES['pdf_attachement2']['size'] == 0) && empty($_POST['pdf_attachement_title2'])) {
            $pdf_attachement2 = null;
            $pdf_attachement_title2 = null;
        } else if ((($_FILES['pdf_attachement2']['size'] != 0) || !empty($_POST['pdf_attachement_title2']))) {
            $pdf_attachement_title2 = $_POST['pdf_attachement_title2'];
        } else {
            $errors['pdf_attachement2'] = "Pdf Attachment 2 or Pdf Attachment Title 2 is missing";
            // $pdf_attachement2 = $_POST['pdf_attachement2'];
        }

        if (($_FILES['pdf_attachement3']['size'] == 0) && empty($_POST['pdf_attachement_title3'])) {
            $pdf_attachement3 = null;
            $pdf_attachement_title3 = null;
        } else if ((($_FILES['pdf_attachement3']['size'] != 0) && !empty($_POST['pdf_attachement_title3']))) {
            $pdf_attachement_title3 = $_POST['pdf_attachement_title3'];
        } else {
            $errors['pdf_attachement3'] = "Pdf Attachment 3 or Pdf Attachment 3 Title is missing";
        }

        if (($_FILES['pdf_attachement4']['size'] == 0) && empty($_POST['pdf_attachement_title4'])) {
            $pdf_attachement4 = null;
            $pdf_attachement_title4 = null;
        } else if (($_FILES['pdf_attachement4']['size'] != 0) && !empty($_POST['pdf_attachement_title4'])) {
            $pdf_attachement_title4 = $_POST['pdf_attachement_title4'];
        } else {
            $errors['pdf_attachement4'] = "Pdf Attachment 4 or Pdf Attachment 4 Title is missing";
        }

        if (($_FILES['pdf_attachement5']['size'] == 0) && empty($_POST['pdf_attachement_title5'])) {
            $pdf_attachement5 = null;
            $pdf_attachement_title5 = null;
        } else if ((($_FILES['pdf_attachement5']['size'] != 0) && !empty($_POST['pdf_attachement_title5']))) {
            $pdf_attachement_title5 = $_POST['pdf_attachement_title5'];
        } else {
            $errors['pdf_attachement5'] = "Pdf Attachment 5 or Pdf Attachment 5 Title is missing";
        }

        if (($_FILES['pdf_attachement6']['size'] == 0) && empty($_POST['pdf_attachement_title6'])) {
            $pdf_attachement6 = null;
            $pdf_attachement_title6 = null;
        } else if ((($_FILES['pdf_attachement6']['size'] != 0) && !empty($_POST['pdf_attachement_title6']))) {
            $pdf_attachement_title6 = $_POST['pdf_attachement_title6'];
        } else {
            $errors['pdf_attachement6'] = "Pdf Attachment 6 or Pdf Attachment 6 Title is missing";
        }

        if (($_FILES['pdf_attachement7']['size'] == 0) && empty($_POST['pdf_attachement_title7'])) {
            $pdf_attachement7 = null;
            $pdf_attachement_title7 = null;
        } else if ((($_FILES['pdf_attachement7']['size'] != 0) && !empty($_POST['pdf_attachement_title7']))) {
            $pdf_attachement_title7 = $_POST['pdf_attachement_title7'];
        } else {
            $errors['pdf_attachement7'] = "Pdf Attachment 7 or Pdf Attachment 7 Title is missing";
        }

        if (($_FILES['pdf_attachement8']['size'] == 0) && empty($_POST['pdf_attachement_title8'])) {
            $pdf_attachement8 = null;
            $pdf_attachement_title8 = null;
        } else if ((($_FILES['pdf_attachement8']['size'] != 0) && !empty($_POST['pdf_attachement_title8']))) {
            $pdf_attachement_title8 = $_POST['pdf_attachement_title8'];
        } else {
            $errors['pdf_attachement8'] = "Pdf Attachment 8 or Pdf Attachment 8 Title is missing";
        }

        // Validate multiple images (picture2)
        $totalFiles = count($_FILES['picture2']['name']);
        $maxFiles = 6;
        if ($totalFiles > $maxFiles) {
            $errors['picture2'] = "Maximum 6 files allowed for other pictures.";
        }

        // If errors exist, store them and redirect back
        if (!empty($errors)) {
            $_SESSION['req_error_msg'] = $errors;
            $_SESSION['error_message'] = "Please fix the errors and try again.";
            header("Location: ../../../post-news.php");
            exit();
        }

        // Generate unique ID and prepare paths
        $uniqueTenderID = $this->generateUniqueTenderID();
        $news_date = $_POST['news_date'];
        $news_title = $_POST['news_title'];
        $news_title_hin = $_POST['news_title_hin'];
        $year = date("Y", strtotime($news_date));
        $mon = date("m", strtotime($news_date));
        $session_year = $this->getSessionYear($news_date);

        // Sanitize news description
        $allowed_tags = '<p></p><a></a><b></b><u></u><strong></strong><em></em><ul></ul><ol></ol><li></li><i></i><table></table>';
        $news_description = strip_tags($_POST['news_description'], $allowed_tags);
        $location = $_POST['location'] ?? null;
        $hash_tag = $_POST['hashTag'] ?? null;

        $img_allowed_extensions = ["jpg", "jpeg", "gif", "png"];
        $max_allowed_file_size = 500; // in KB
        $uploads_dir = "uploads/News/$year/$mon/$uniqueTenderID/";

        // Upload main picture
        $news_picture = $this->pdfUpload('picture1', $uploads_dir, $img_allowed_extensions);

        if (!$news_picture) {
            $_SESSION['error_message'] = "Error uploading the title picture.";
            header("Location: ../../../post-news.php");
            exit();
        }

        // Upload PDF attachments (example shown for pdf_attachement1; do similar for others)
        $pdf_attachement1 = $this->pdfUpload('pdf_attachement1', $uploads_dir, ['pdf']);
        $pdf_attachement2 = $this->pdfUpload('pdf_attachement2', $uploads_dir, ['pdf']);
        $pdf_attachement3 = $this->pdfUpload('pdf_attachement3', $uploads_dir, ['pdf']);
        $pdf_attachement4 = $this->pdfUpload('pdf_attachement4', $uploads_dir, ['pdf']);
        $pdf_attachement5 = $this->pdfUpload('pdf_attachement5', $uploads_dir, ['pdf']);
        $pdf_attachement6 = $this->pdfUpload('pdf_attachement6', $uploads_dir, ['pdf']);
        $pdf_attachement7 = $this->pdfUpload('pdf_attachement7', $uploads_dir, ['pdf']);
        $pdf_attachement8 = $this->pdfUpload('pdf_attachement8', $uploads_dir, ['pdf']);


        // Upload additional images for picture2       
        $this->createDirectoryIfNotExists($uploads_dir);
        $image_names = [];

        foreach ($_FILES['picture2']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['picture2']['error'][$key] === UPLOAD_ERR_OK && !empty($_FILES['picture2']['name'][$key])) {
                $fileSizeKB = $_FILES['picture2']['size'][$key] / 1024;
                if ($fileSizeKB <= $max_allowed_file_size) {
                    $file_extension = strtolower(pathinfo($_FILES['picture2']['name'][$key], PATHINFO_EXTENSION));
                    if (in_array($file_extension, $img_allowed_extensions)) {
                        $image_name = uniqid() . "_other_pic-" . $_FILES['picture2']['name'][$key];
                        $destination = $uploads_dir . $image_name;
                        if (move_uploaded_file($_FILES['picture2']['tmp_name'][$key], "../../$destination")) {
                            $image_names[] = $destination;
                        } else {
                            $errors[] = "Error copying one of the additional images.";
                        }
                    } else {
                        $errors[] = "Invalid file extension for additional images.";
                    }
                } else {
                    $errors[] = "One of the additional images exceeds the maximum allowed size (500KB).";
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['error_message'] = implode(" ", $errors);
            header("Location: ../../../post-news.php");
            exit();
        }

        $other_images = implode(",", $image_names);

        // Get video attachments (if any)
        $video1 = $_POST['videoAttach1'] ?? null;
        $video2 = $_POST['videoAttach2'] ?? null;
        $video3 = $_POST['videoAttach3'] ?? null;
        $video4 = $_POST['videoAttach4'] ?? null;
        $video_title1 = $_POST['videoAttach_title1'] ?? null;
        $video_title2 = $_POST['videoAttach_title2'] ?? null;
        $video_title3 = $_POST['videoAttach_title3'] ?? null;
        $video_title4 = $_POST['videoAttach_title4'] ?? null;

        // Insert into the database inside a transaction
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare(
                "INSERT INTO news
                (uniq_id, news_title, news_title_hin, news_event_date, news_description, hashtag, news_pic1, news_pic2, created_by, 
                 video_attach_1, video_attach_title1, 
                 video_attach_2, video_attach_title2, 
                 video_attach_3, video_attach_title3, 
                 video_attach_4, video_attach_title4,
                 pdf_attachement1, pdf_attachement_title1, 
                 pdf_attachement2, pdf_attachement_title2, 
                 pdf_attachement3, pdf_attachement_title3, 
                 pdf_attachement4, pdf_attachement_title4, 
                 pdf_attachement5, pdf_attachement_title5, 
                 pdf_attachement6, pdf_attachement_title6, 
                 pdf_attachement7, pdf_attachement_title7, 
                 pdf_attachement8, pdf_attachement_title8, 
                 `location`, session_year) 
                VALUES
                (:uniq_id, :news_title, :news_title_hin, :news_event_date, :news_description, :hashtag, :news_pic1, :news_pic2, :created_by, 
                 :video_attach_1, :video_attach_title1, 
                 :video_attach_2, :video_attach_title2, 
                 :video_attach_3, :video_attach_title3, 
                 :video_attach_4, :video_attach_title4,
                 :pdf_attachement1, :pdf_attachement_title1, 
                 :pdf_attachement2, :pdf_attachement_title2, 
                 :pdf_attachement3, :pdf_attachement_title3, 
                 :pdf_attachement4, :pdf_attachement_title4, 
                 :pdf_attachement5, :pdf_attachement_title5, 
                 :pdf_attachement6, :pdf_attachement_title6, 
                 :pdf_attachement7, :pdf_attachement_title7, 
                 :pdf_attachement8, :pdf_attachement_title8, 
                 :location, :session_year)"
            );

            $stmt->execute([
                ':uniq_id' => $uniqueTenderID,
                ':news_title' => $news_title,
                ':news_title_hin' => $news_title_hin,
                ':news_event_date' => $news_date,
                ':news_description' => $news_description,
                ':hashtag' => $hash_tag,
                ':news_pic1' => $news_picture,
                ':news_pic2' => $other_images,
                ':created_by' => $_SESSION['user_id'],
                ':video_attach_1' => $video1,
                ':video_attach_title1' => $video_title1,
                ':video_attach_2' => $video2,
                ':video_attach_title2' => $video_title2,
                ':video_attach_3' => $video3,
                ':video_attach_title3' => $video_title3,
                ':video_attach_4' => $video4,
                ':video_attach_title4' => $video_title4,
                ':pdf_attachement1' => $pdf_attachement1,
                ':pdf_attachement_title1' => $pdf_attachement_title1,
                ':pdf_attachement2' => $pdf_attachement2,
                ':pdf_attachement_title2' => $pdf_attachement_title2,
                ':pdf_attachement3' => $pdf_attachement3,
                ':pdf_attachement_title3' => $pdf_attachement_title3,
                ':pdf_attachement4' => $pdf_attachement4,
                ':pdf_attachement_title4' => $pdf_attachement_title4,
                ':pdf_attachement5' => $pdf_attachement5,
                ':pdf_attachement_title5' => $pdf_attachement_title5,
                ':pdf_attachement6' => $pdf_attachement6,
                ':pdf_attachement_title6' => $pdf_attachement_title6,
                ':pdf_attachement7' => $pdf_attachement7,
                ':pdf_attachement_title7' => $pdf_attachement_title7,
                ':pdf_attachement8' => $pdf_attachement8,
                ':pdf_attachement_title8' => $pdf_attachement_title8,
                ':location' => $location,
                ':session_year' => $session_year
            ]);

            $data = [
                'uniq_id'                => $uniqueTenderID,
                'news_title'             => $news_title,
                'news_title_hin'         => $news_title_hin,
                'news_event_date'        => $news_date,
                'news_description'       => $news_description,
                'hashtag'                => $hash_tag,
                'news_pic1'              => $news_picture,
                'news_pic2'              => $other_images,
                'created_by'             => $_SESSION['user_id'],

                'video1'        => $video1,
                'video_title1'  => $video_title1,
                'video2'        => $video2,
                'video_title2'  => $video_title2,
                'video3'        => $video3,
                'video_title3'  => $video_title3,
                'video4'        => $video4,
                'video_title4'  => $video_title4,

                'pdf1' => $pdf_attachement1,
                'pdf_title1' => $pdf_attachement_title1,
                'pdf2' => $pdf_attachement2,
                'pdf_title2' => $pdf_attachement_title2,
                'pdf3' => $pdf_attachement3,
                'pdf_title3' => $pdf_attachement_title3,
                'pdf4' => $pdf_attachement4,
                'pdf_title4' => $pdf_attachement_title4,
                'pdf5' => $pdf_attachement5,
                'pdf_title5' => $pdf_attachement_title5,
                'pdf6' => $pdf_attachement6,
                'pdf_title6' => $pdf_attachement_title6,
                'pdf7' => $pdf_attachement7,
                'pdf_title7' => $pdf_attachement_title7,
                'pdf8' => $pdf_attachement8,
                'pdf_title8' => $pdf_attachement_title8,

                'location'      => $location,
                'session_year'  => $session_year
            ];


            $this->logger->log('news', $uniqueTenderID, 'INSERT', null, null, json_encode($data), $_SESSION['user_id'], $this->loggedId);
            $this->pdo->commit();

            unset($_SESSION['post']);
            unset($_SESSION['req_error_msg']);
            $_SESSION['success_message'] = "News has been posted successfully.";
            header('Location: ../../../post-news.php');
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            header('Location: ../../../post-news.php');
            exit;
        }
    }

    public function updateNews()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../../manage-news.php');
            exit;
        }

        $newsId = $_POST['post'];

        // Fetch existing news record to get the current data
        $stmt = $this->pdo->prepare("SELECT * FROM news WHERE uniq_id = ?");
        $stmt->execute([$newsId]);
        $existingNews = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingNews) {
            $_SESSION['error_message'] = "News record not found.";
            header("Location: ../../../manage-news.php");
            exit();
        }

        $errors = [];

        // Basic validations
        if (empty($_POST['news_date'])) {
            $errors['news_date'] = "News Date is required";
        }
        if (empty($_POST['news_title'])) {
            $errors['news_title'] = "News Title is required";
        }
        if (empty($_POST['news_description'])) {
            $errors['news_description'] = "News Description is required";
        }

        // Validate PDF attachments for pdf_attachement1 with existing file consideration
        if ($_FILES['pdf_attachement1']['size'] == 0 && empty($_POST['pdf_attachement_title1'])) {
            // If no new file and no title, keep existing if available
            $pdf_attachement1 = $existingNews['pdf_attachement1'];
            $pdf_attachement_title1 = $existingNews['pdf_attachement_title1'];
        } elseif ($_FILES['pdf_attachement1']['size'] != 0 && !empty($_POST['pdf_attachement_title1'])) {
            // New file and title provided
            $pdf_attachement_title1 = $_POST['pdf_attachement_title1'];
        } elseif ($existingNews['pdf_attachement1'] && !empty($_POST['pdf_attachement_title1'])) {
            // Keeping existing file but updating title
            $pdf_attachement1 = $existingNews['pdf_attachement1'];
            $pdf_attachement_title1 = $_POST['pdf_attachement_title1'];
        } elseif ($existingNews['pdf_attachement1'] && empty($_POST['pdf_attachement_title1'])) {
            // Existing file but title removed - use existing title
            $pdf_attachement1 = $existingNews['pdf_attachement1'];
            $pdf_attachement_title1 = $existingNews['pdf_attachement_title1'];
        } else {
            $errors['pdf_attachement1'] = "Pdf Attachment 1 or Pdf Attachment Title 1 is missing";
        }

        // Validate remaining PDF attachments (2-8)
        for ($i = 2; $i <= 8; $i++) {
            $pdf_field = 'pdf_attachement' . $i;
            $title_field = 'pdf_attachement_title' . $i;

            if ($_FILES[$pdf_field]['size'] == 0 && empty($_POST[$title_field])) {
                // If no new file and no title, keep existing if available
                ${'pdf_attachement' . $i} = $existingNews[$pdf_field];
                ${'pdf_attachement_title' . $i} = $existingNews[$title_field];
            } elseif ($_FILES[$pdf_field]['size'] != 0 && !empty($_POST[$title_field])) {
                // New file and title provided
                ${'pdf_attachement_title' . $i} = $_POST[$title_field];
            } elseif ($existingNews[$pdf_field] && !empty($_POST[$title_field])) {
                // Keeping existing file but updating title
                ${'pdf_attachement' . $i} = $existingNews[$pdf_field];
                ${'pdf_attachement_title' . $i} = $_POST[$title_field];
            } elseif ($existingNews[$pdf_field] && empty($_POST[$title_field])) {
                // Existing file but title removed - use existing title
                ${'pdf_attachement' . $i} = $existingNews[$pdf_field];
                ${'pdf_attachement_title' . $i} = $existingNews[$title_field];
            } else {
                $errors['pdf_attachement' . $i] = "Pdf Attachment $i or Pdf Attachment Title $i is missing";
            }
        }

        // Validate multiple images (picture2)
        if (isset($_FILES['picture2']['name']) && is_array($_FILES['picture2']['name'])) {
            $totalFiles = count(array_filter($_FILES['picture2']['name']));
            $maxFiles = 6;
            if ($totalFiles > $maxFiles) {
                $errors['picture2'] = "Maximum 6 files allowed for other pictures.";
            }
        }

        // If errors exist, store them and redirect back
        if (!empty($errors)) {
            $_SESSION['req_error_msg'] = $errors;
            $_SESSION['error_message'] = "Please fix the errors and try again.";
            header("Location: ../../../edit-news.php?id=" . $newsId);
            exit();
        }

        $uniqueTenderID = $existingNews['uniq_id'];
        $news_date = $_POST['news_date'];
        $news_title = $_POST['news_title'];
        $news_title_hin = $_POST['news_title_hin'];
        $year = date("Y", strtotime($news_date));
        $mon = date("m", strtotime($news_date));

        $session_year = $this->getSessionYear($news_date);

        // Sanitize news description

        $allowed_tags = '<p></p><a></a><b></b><u></u><strong></strong><em></em><ul></ul><ol></ol><li></li><i></i><table></table>';
        $news_description = strip_tags($_POST['news_description'], $allowed_tags);
        $location = $_POST['location'] ?? null;

        $hash_tag = $_POST['hashTag'] ?? null;

        $img_allowed_extensions = ["jpg", "jpeg", "gif", "png"];
        $max_allowed_file_size = 500; // in KB
        $uploads_dir = "uploads/News/$year/$mon/$uniqueTenderID/";

        // Update main picture if a new one is uploaded
        $news_picture = $existingNews['news_pic1'];
        if (isset($_FILES['picture1']) && $_FILES['picture1']['error'] != UPLOAD_ERR_NO_FILE) {
            $news_picture = $this->pdfUpload('picture1', $uploads_dir, $img_allowed_extensions);
            if (!$news_picture) {
                $_SESSION['error_message'] = "Error uploading the title picture.";
                header("Location: ../../../edit-news.php?id=" . $newsId);
                exit();
            }
        }

        // Upload PDF attachments (update if new ones are provided)
        $pdf_files = [];
        for ($i = 1; $i <= 8; $i++) {
            $pdf_field = 'pdf_attachement' . $i;
            $pdf_files[$pdf_field] = $existingNews[$pdf_field];

            if (isset($_FILES[$pdf_field]) && $_FILES[$pdf_field]['error'] == UPLOAD_ERR_OK) {
                $pdf_files[$pdf_field] = $this->pdfUpload($pdf_field, $uploads_dir, ['pdf']);
            }
        }

        // Upload additional images for picture2
        $image_names = [];

        // If existing images exist, start with those
        if (!empty($existingNews['news_pic2'])) {
            $image_names = explode(',', $existingNews['news_pic2']);
        }

        // Handle new uploaded images
        if (isset($_FILES['picture2']['tmp_name']) && is_array($_FILES['picture2']['tmp_name'])) {
            $this->createDirectoryIfNotExists($uploads_dir);

            foreach ($_FILES['picture2']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['picture2']['error'][$key] === UPLOAD_ERR_OK && !empty($_FILES['picture2']['name'][$key])) {
                    $fileSizeKB = $_FILES['picture2']['size'][$key] / 1024;
                    if ($fileSizeKB <= $max_allowed_file_size) {
                        $file_extension = strtolower(pathinfo($_FILES['picture2']['name'][$key], PATHINFO_EXTENSION));
                        if (in_array($file_extension, $img_allowed_extensions)) {
                            $image_name = uniqid() . "_other_pic-" . $_FILES['picture2']['name'][$key];
                            $destination = $uploads_dir . $image_name;
                            if (move_uploaded_file($_FILES['picture2']['tmp_name'][$key], "../../$destination")) {
                                $image_names[] = $destination;
                            } else {
                                $errors[] = "Error copying one of the additional images.";
                            }
                        } else {
                            $errors[] = "Invalid file extension for additional images.";
                        }
                    } else {
                        $errors[] = "One of the additional images exceeds the maximum allowed size (500KB).";
                    }
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['error_message'] = implode(" ", $errors);
            header("Location: ../../../edit-news.php?id=" . $newsId);
            exit();
        }

        $other_images = implode(",", $image_names);

        // Get video attachments (if any)
        $video1 = $_POST['videoAttach1'] ?? $existingNews['video_attach_1'];
        $video2 = $_POST['videoAttach2'] ?? $existingNews['video_attach_2'];
        $video3 = $_POST['videoAttach3'] ?? $existingNews['video_attach_3'];
        $video4 = $_POST['videoAttach4'] ?? $existingNews['video_attach_4'];
        $video_title1 = $_POST['videoAttach_title1'] ?? null;
        $video_title2 = $_POST['videoAttach_title2'] ?? null;
        $video_title3 = $_POST['videoAttach_title3'] ?? null;
        $video_title4 = $_POST['videoAttach_title4'] ?? null;

        // Update the database inside a transaction
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare(
                "UPDATE news SET
            news_title = ?, 
            news_title_hin = ?, 
            news_event_date = ?, 
            news_description = ?,
            hashtag = ?, 
            news_pic1 = ?, 
            news_pic2 = ?, 
            updated_by = ?,
            updated_at = NOW(),
            video_attach_1 = ?, 
            video_attach_title1 = ?,
            video_attach_2 = ?, 
            video_attach_title2 = ?,
            video_attach_3 = ?, 
            video_attach_title3 = ?,
            video_attach_4 = ?, 
            video_attach_title4 = ?,
            pdf_attachement1 = ?, 
            pdf_attachement_title1 = ?, 
            pdf_attachement2 = ?, 
            pdf_attachement_title2 = ?, 
            pdf_attachement3 = ?, 
            pdf_attachement_title3 = ?, 
            pdf_attachement4 = ?, 
            pdf_attachement_title4 = ?, 
            pdf_attachement5 = ?, 
            pdf_attachement_title5 = ?, 
            pdf_attachement6 = ?, 
            pdf_attachement_title6 = ?, 
            pdf_attachement7 = ?, 
            pdf_attachement_title7 = ?, 
            pdf_attachement8 = ?, 
            pdf_attachement_title8 = ?, 
            `location` = ?,
            session_year = ?
            WHERE uniq_id = ?"
            );

            $stmt->execute([
                $news_title,
                $news_title_hin,
                $news_date,
                $news_description,
                $hash_tag,
                $news_picture,
                $other_images,
                $_SESSION['user_id'],
                $video1,
                $video_title1,
                $video2,
                $video_title2,
                $video3,
                $video_title3,
                $video4,
                $video_title4,
                $pdf_files['pdf_attachement1'],
                $pdf_attachement_title1,
                $pdf_files['pdf_attachement2'],
                $pdf_attachement_title2,
                $pdf_files['pdf_attachement3'],
                $pdf_attachement_title3,
                $pdf_files['pdf_attachement4'],
                $pdf_attachement_title4,
                $pdf_files['pdf_attachement5'],
                $pdf_attachement_title5,
                $pdf_files['pdf_attachement6'],
                $pdf_attachement_title6,
                $pdf_files['pdf_attachement7'],
                $pdf_attachement_title7,
                $pdf_files['pdf_attachement8'],
                $pdf_attachement_title8,
                $location,
                $session_year,
                $newsId
            ]);

            $data = [
                'news_title'               => $news_title,
                'news_title_hin'           => $news_title_hin,
                'news_event_date'          => $news_date,
                'news_description'         => $news_description,
                'hashtag'                  => $hash_tag,

                'news_pic1'                => $news_picture,
                'news_pic2'                => $other_images,

                'created_by'               => $_SESSION['user_id'],

                'video_attach_1'           => $video1,
                'video_attach_title1'      => $video_title1,
                'video_attach_2'           => $video2,
                'video_attach_title2'      => $video_title2,
                'video_attach_3'           => $video3,
                'video_attach_title3'      => $video_title3,
                'video_attach_4'           => $video4,
                'video_attach_title4'      => $video_title4,

                'pdf_attachement1'         => $pdf_files['pdf_attachement1'] ?? null,
                'pdf_attachement_title1'   => $pdf_attachement_title1,
                'pdf_attachement2'         => $pdf_files['pdf_attachement2'] ?? null,
                'pdf_attachement_title2'   => $pdf_attachement_title2,
                'pdf_attachement3'         => $pdf_files['pdf_attachement3'] ?? null,
                'pdf_attachement_title3'   => $pdf_attachement_title3,
                'pdf_attachement4'         => $pdf_files['pdf_attachement4'] ?? null,
                'pdf_attachement_title4'   => $pdf_attachement_title4,
                'pdf_attachement5'         => $pdf_files['pdf_attachement5'] ?? null,
                'pdf_attachement_title5'   => $pdf_attachement_title5,
                'pdf_attachement6'         => $pdf_files['pdf_attachement6'] ?? null,
                'pdf_attachement_title6'   => $pdf_attachement_title6,
                'pdf_attachement7'         => $pdf_files['pdf_attachement7'] ?? null,
                'pdf_attachement_title7'   => $pdf_attachement_title7,
                'pdf_attachement8'         => $pdf_files['pdf_attachement8'] ?? null,
                'pdf_attachement_title8'   => $pdf_attachement_title8,

                'location'                 => $location,
                'session_year'             => $session_year,

                'news_id'                  => $newsId
            ];

            $newData = json_encode($data);

            $this->logger->log(
                'news',
                $newsId,
                'UPDATE',
                NULL,
                json_encode($existingNews),
                $newData,
                $_SESSION['user_id'],
                $this->loggedId
            );

            $this->pdo->commit();

            unset($_SESSION['post']);
            unset($_SESSION['req_error_msg']);
            $_SESSION['success_message'] = "News has been updated successfully.";
            header('Location: ../../../edit-news.php?id=' . $newsId);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            header('Location: ../../../edit-news.php?id=' . $newsId);
            exit;
        }
    }

    public function softDelete()
    {
        session_start();

        $post = filter_input(INPUT_POST, 'ed');

        $stmt = $this->pdo->prepare("SELECT * FROM news WHERE uniq_id = :id AND is_deleted = '0'");
        $stmt->bindParam(':id', $post, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $sql = "UPDATE news SET is_deleted='1' WHERE uniq_id= :id";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':id', $post, PDO::PARAM_INT);

            $data = $stmt->execute();

            $this->logger->log(
                'news',
                $post,
                'DELETE',
                'is_deleted',
                '0',
                '1',
                $_SESSION['user_id'],
                $this->loggedId
            );

            return true;

            header('Content-Type: application/json');

            if ($data) {
                $_SESSION['message'] = "News deleted successfully.";
                echo json_encode([
                    'success' => true,
                    'message' => 'Data deleted successfully.'
                ]);
            } else {
                $_SESSION['error'] = "Failed to delete data.";
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to delete data.'
                ]);
            }
            exit;
        } else {
            $_SESSION['error'] = "News Record not found!.";
            echo json_encode([
                'success' => false,
                'message' => 'Failed data.'
            ]);
        }
    }

    public function hideNews()
    {
        session_start();

        $post = filter_input(INPUT_POST, 'ed');

        $stmt = $this->pdo->prepare("SELECT * FROM news WHERE uniq_id = :id AND is_deleted = '0'");
        $stmt->bindParam(':id', $post, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $hideValue = $result['is_hide'] == 'N' ? 'Y' : 'N';
            // die($result['is_hide']);
            $sql = "UPDATE news SET is_hide='$hideValue' WHERE uniq_id= :id";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':id', $post, PDO::PARAM_INT);

            $data = $stmt->execute();

            $this->logger->log(
                'news',
                $post,
                'UPDATE',
                'is_hide',
                'N',
                'Y',
                $_SESSION['user_id'],
                $this->loggedId
            );

            header('Content-Type: application/json');

            if ($data) {
                $_SESSION['message'] = "News " . ($result['is_hide'] == 'N' ? 'Hide' : 'Unhide') . " successfully.";
                echo json_encode([
                    'success' => true,
                    'message' => 'Data hide successfully.'
                ]);
            } else {
                $_SESSION['error'] = "Failed to hide data.";
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to hide data.'
                ]);
            }
            exit;
        } else {
            $_SESSION['error'] = "News Record not found!.";
            echo json_encode([
                'success' => false,
                'message' => 'Failed data.'
            ]);
        }
    }
}

$controller = new NewsController();

$postAction = $_POST['action'];

if (isset($_POST['post']) && $_POST['post']) {
    $controller->updateNews();
} elseif (isset($_POST['ed']) && $_POST['ed'] && $postAction == "deleteNews") {
    $controller->softDelete();
} elseif (isset($_POST['ed']) && $_POST['ed'] && $postAction == "hideNews") {
    $controller->hideNews();
} else {
    $controller->insertNews();
}
