<?php
require_once __DIR__ . '/cdgps/src/database/Database.php';

$database = new Database();
$pdo = $database->getConnection();

// Define the same encryption key (store this securely)
$encryption_key = 'af7af6d2f08c8e7cdc4cc2d03046453c139c09ed7a5d98ec73ac9c230ec0a2f8';

// Get and process the encrypted data
if (isset($_GET['album'])) {
    try {
        // Decode the URL parameter
        $encrypted_data = urldecode($_GET['album']);
        $encrypted_data = base64_decode($encrypted_data);

        // Split the data to get encrypted ID and IV
        list($encrypted_id, $encoded_iv) = explode('::', $encrypted_data);
        $iv = base64_decode($encoded_iv);

        // Decrypt the ID
        $uniq_id = openssl_decrypt($encrypted_id, 'AES-256-CBC', $encryption_key, 0, $iv);

        if ($uniq_id === false) {
            throw new Exception("Decryption failed");
        }

        $albumId = $_GET['album'] ?? 0;

        $type = 'Press Clips';
        $query = "SELECT *
                    FROM albums a
                    WHERE a.is_deleted = 0
                    AND a.is_hide = 0
                    AND a.type = :type
                    AND a.uniq_id = :uniqId";

        // Prepare and execute the statement
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt->bindParam(':uniqId', $uniq_id, PDO::PARAM_STR);
        $stmt->execute();
        $album = $stmt->fetch(PDO::FETCH_ASSOC);

        $album_id = $album['id'];

        $photoQuery = "SELECT *
                  FROM photos a
                  WHERE a.album_id = :albumId
                  ORDER BY position";

        // Prepare and execute the statement
        $stmt1 = $pdo->prepare($photoQuery);
        $stmt1->bindParam(':albumId', $album_id, PDO::PARAM_INT);
        $stmt1->execute();
        $photos = $stmt1->fetchAll(PDO::FETCH_ASSOC);

        if (!$album || !$photos) {
            header("Location: press_clips.php");
            exit;
        }

        $en_title = htmlspecialchars($album['name_en']);
        $en_description = $album['description_en'];
        if (!empty($album['event_date'])) {
            $dateObj = new DateTime($album['event_date']);
            $dateOfEvent = $dateObj->format('d M Y'); // 21 Feb 2025
        } else {
            $dateOfEvent = '';
        }
        
        $location = htmlspecialchars($album['location']);
        $coverId = $album['cover_photo_id'];

        // ********* Others Album **********

        $type = 'Press Clips';
        $query = "SELECT a.name_en, p.file_path, a.event_date, a.uniq_id
                    FROM albums a
                    INNER JOIN photos p ON a.cover_photo_id = p.id
                    WHERE a.is_deleted = 0
                    AND a.is_hide = 0
                    AND a.type = :type
                    AND a.id <> :al_id
                    Order BY a.event_date desc";

        // Prepare and execute the statement
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt->bindParam(':al_id', $album_id, PDO::PARAM_STR);
        $stmt->execute();
        $oth_album = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        // Handle decryption errors
        header("Location: press_clips.php");
        exit;
    }
} else {
    // No album parameter provided
    header("Location: press_clips.php");
    exit;
}
?>

<?php include "header1.php"; ?>
<section class="tvnl-banner">
    <img src="assets/images/banner/news-b.jpg" alt="Media - Media Coverage Gallery, Tenughat Vidyut Nigam Limited" title="Media - Media Coverage Gallery, Tenughat Vidyut Nigam Limited"
        class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">Media Coverage Gallery</h2>
        </div>
    </div>
</section>

<section class="gallery-contant">
    <a href="news_event_gallery.php" class="btn btn-primary btn-sm mb-3 mt-3 d-block d-md-none" style="width:150px;">
        ← Back To Media Coverage
    </a>

    <div class="all-session">
        <h2 class="section-title text-primary" title="Media Coverage Gallery Gallery">📰 Media Coverage Gallery</h2>
        <div class="session-box">
            <a href="news_event_gallery.php" class="btn btn-primary btn-sm mb-3 mt-3 d-none d-md-inline-block" title="Media Coverage">
                ← Back To Media Coverage
            </a>

            <label class="session-taxt text-primary">Year:</label>
            <select class="session-photo" title="year">
                <option>2026</option>
                <option>2025</option>
                <option>2024</option>
            </select>
        </div>
    </div>
    <div class="photo-title-gallery">
        <h2 class=""> This is Media Coverage Gallery</h2>
        <h2>23 January 2026</h2>
        <p align="justify">
            Tenughat Vidyut Nigam Limited (TVNL) is the only government-owned thermal power plant in Jharkhand with an installed capacity of 210×2 MW. The plant is located at Lalpania in Bokaro District, providing reliable and sustainable energy for the state.</p>
    </div>

    <div class="gallery-container" id="gallery">

        <div class="gallery-item">
            <img src="assets/images/gallery/activities/actv1.png" alt="Media Coverage Gallery"
                title="Media Coverage Gallery">
        </div>

        <div class="gallery-item">
            <img src="assets/images/gallery/activities/news-b.jpg" alt="Media Coverage Gallery"
                title="Media Coverage Gallery">
        </div>

        <div class="gallery-item">
            <img src="assets/images/gallery/activities/sustainability.jpg" alt="Media Coverage Gallery"
                title="Media Coverage Gallery">
        </div>

        <div class="gallery-item">
            <img src="assets/images/gallery/activities/actv1.png" alt="Media Coverage Gallery"
                title="Media Coverage Gallery">
        </div>
        <div class="gallery-item">
            <img src="assets/images/gallery/activities/news-b.jpg" alt="Media Coverage Gallery"
                title="Media Coverage Gallery">
        </div>
        <div class="gallery-item">
            <img src="assets/images/gallery/activities/news-b.jpg" alt="Media Coverage Gallery"
                title="Media Coverage Gallery">
        </div>

        <div class="gallery-item">
            <img src="assets/images/gallery/activities/sustainability.jpg" alt="Media Coverage Gallery"
                title="Media Coverage Gallery">
        </div>
        <div class="gallery-item">
            <img src="assets/images/gallery/activities/actv1.png" alt="Media Coverage Gallery"
                title="Media Coverage Gallery">
        </div>
        <div class="gallery-item">
            <img src="assets/images/gallery/activities/sustainability.jpg" alt="Media Coverage Gallery"
                title="Media Coverage Gallery">
        </div>
        <div class="gallery-item">
            <img src="assets/images/gallery/activities/actv1.png" alt="Media Coverage Gallery"
                title="Media Coverage Gallery">
        </div>
        <div class="gallery-item">
            <img src="assets/images/gallery/activities/sustainability.jpg" alt="Media Coverage Gallery"
                title="Media Coverage Gallery">
        </div>
        <div class="gallery-item">
            <img src="assets/images/gallery/activities/news-b.jpg" alt="Media Coverage Gallery "
                title="Media Coverage Gallery">
        </div>

    </div>
    <div class="lightbox" id="lightbox">
        <span class="close" id="closeBtn">&times;</span>
        <span class="prev" id="prevBtn">&#10094;</span>
        <img id="lightboxImg">
        <span class="next" id="nextBtn">&#10095;</span>
    </div>
</section>

<?php include("footer_top.php"); ?>
<?php include "footer1.php"; ?>