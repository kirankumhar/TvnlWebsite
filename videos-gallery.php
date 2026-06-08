<?php
require_once __DIR__ . '/cd-admin/src/database/Database.php';

$database = new Database();
$pdo = $database->getConnection();

// Define the same encryption key
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

        $type = 'Videos';
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

        if (!$album) {
            header("Location: videos.php");
            exit;
        }

        $album_id = $album['id'];
        $album_title = htmlspecialchars($album['name_en']);
        $album_description = $album['description_en'];

        if (!empty($album['event_date'])) {
            $dateObj = new DateTime($album['event_date']);
            $dateOfEvent = $dateObj->format('d M Y');
        } else {
            $dateOfEvent = '';
        }

        $location = htmlspecialchars($album['location']);

        // Get videos in the album
        $videoQuery = "SELECT *
                      FROM videos 
                      WHERE albums_id = :albumId
                      ORDER BY id ASC";

        $stmt1 = $pdo->prepare($videoQuery);
        $stmt1->bindParam(':albumId', $album_id, PDO::PARAM_INT);
        $stmt1->execute();
        $videos = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        header("Location: videos.php");
        exit;
    }
} else {
    header("Location: videos.php");
    exit;
}

// Helper function to extract YouTube video ID
function extractYouTubeId($url) {
    if (empty($url)) return '';
    
    $patterns = [
        '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\?\/]+)/',
        '/youtube\.com\/watch\?.*v=([^&]+)/',
        '/youtube\.com\/embed\/([^?]+)/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    return $url;
}
?>

<?php include "header1.php"; ?>

<section class="tvnl-banner">
    <img src="assets/images/banner/news-b.jpg" alt="Media - Videos-gallery, Tenughat Vidyut Nigam Limited" title="Media - Videos-gallery, Tenughat Vidyut Nigam Limited"
        class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">Videos</h2>
        </div>
    </div>
</section>

<section class="gallery-contant">
    <a href="videos.php" class="btn btn-primary btn-sm mb-3 mt-3 d-block d-md-none" style="width:150px;" title="videos">
        ← Back To Videos
    </a>

    <div class="all-session">
        <h2 class="section-title text-primary" title="Videos Gallery">▶️ Videos-gallery</h2>
        <div class="session-box">
            <a href="videos.php" class="btn btn-primary btn-sm mb-3 mt-3 d-none d-md-inline-block" title="Videos Gallery">
                ← Back To Videos
            </a>
        </div>
    </div>

    <div class="photo-title-gallery">
        <h2 class=""><?= $album_title ?></h2>
        <h2><?= $dateOfEvent ?></h2>
        <?php if (!empty($location)): ?>
            <h6>Venue/Location: <?= $location ?></h6>
        <?php endif; ?>
        <p align="justify">
            <?= !empty($album_description) ? $album_description : 'Tenughat Vidyut Nigam Limited (TVNL) is the only government-owned thermal power plant in Jharkhand with an installed capacity of 210×2 MW. The plant is located at Lalpania in Bokaro District, providing reliable and sustainable energy for the state.' ?>
        </p>
    </div>

    <div class="gallery-container" id="gallery">
        <?php foreach ($videos as $video): ?>
            <?php 
            $videoId = extractYouTubeId($video['video_link']); 
            $embedUrl = $videoId ? 'https://www.youtube.com/embed/' . $videoId : '';
            ?>
            <?php if (!empty($embedUrl)): ?>
                <div class="gallery-item" style="cursor: default;">
                    <iframe class="images-videos"
                        src="<?= $embedUrl ?>"
                        title="<?= htmlspecialchars($video['video_title'] ?? '') ?>" 
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                    </iframe>
                    <?php if (!empty($video['video_title'])): ?>
                        <h6 style="padding: 12px; margin: 0; font-size: 14px; text-align: justify; color: #333; font-weight: 500;"><?= htmlspecialchars($video['video_title']) ?></h6>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>

<?php include("footer_top.php"); ?>
<?php include "footer1.php"; ?>