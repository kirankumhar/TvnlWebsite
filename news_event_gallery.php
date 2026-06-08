<?php
$encryption_key = 'af7af6d2f08c8e7cdc4cc2d03046453c139c09ed7a5d98ec73ac9c230ec0a2f8';

require_once __DIR__ . '/cd-admin/src/database/Database.php';

$database = new Database();
$pdo = $database->getConnection();

if (!isset($_GET['album'])) {
    header("Location: news_event.php?msg=missing_parameter");
    exit;
}

try {

    $encrypted_data = urldecode($_GET['album']);
    $encrypted_data = base64_decode($encrypted_data);
    if (!$encrypted_data || !str_contains($encrypted_data, '::')) {
        throw new Exception("Invalid encrypted format");
    }

    [$encrypted_id, $encoded_iv] = explode('::', $encrypted_data);
    $iv = base64_decode($encoded_iv);

    $uniq_id = openssl_decrypt(
        $encrypted_id,
        'AES-256-CBC',
        $encryption_key,
        0,
        $iv
    );

    if ($uniq_id === false) {
        throw new Exception("Decryption failed");
    }

    $query = "SELECT *
              FROM news
              WHERE is_deleted = 0
              AND is_hide = 'N'
              AND uniq_id = :uniqId";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':uniqId', $uniq_id, PDO::PARAM_STR);
    $stmt->execute();

    $album = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$album) {
        header("Location: news_event.php");
        exit;
    }

    $en_title = htmlspecialchars($album['news_title'] ?? '');
    $en_description = $album['news_description'] ?? '';
    $dateOfEvent = htmlspecialchars($album['news_event_date'] ?? '');
    $location = htmlspecialchars($album['location'] ?? '');

    $titleImage = '';
    if (!empty($album['news_pic1'])) {
        $originalPath = "cd-admin/src/" . $album['news_pic1'];
        $encryptedPath = base64_encode($originalPath);
        $titleImage = 'enc_image.php?img=' . urlencode($encryptedPath);
    }

    $photos = [];
    if (!empty($album['news_pic2'])) {
        $photos = explode(',', $album['news_pic2']);
    }

} catch (Exception $e) {
    header("Location: events_activities.php?msg=invalid_request");
    exit;
}
?>

<?php include "header1.php"; ?>

<section class="tvnl-banner">
    <img src="assets/images/banner/news-b.jpg" alt="Media - News & Events, Tenughat Vidyut Nigam Limited" title="Media - News & Events, Tenughat Vidyut Nigam Limited"
        class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">News & Events Gallery</h2>
        </div>
    </div>
</section>

<section class="gallery-contant">
    <a href="news_event_gallery.php" class="btn btn-primary btn-sm mb-3 mt-3 d-block d-md-none" style="width:150px;">
        ← Back To News & Events
    </a>

    <div class="all-session">
        <h2 class="section-title text-primary" title="News & Events Gallery">📰 News & Events Gallery</h2>
        <div class="session-box">
            <a href="news_event_gallery.php" class="btn btn-primary btn-sm mb-3 mt-3 d-none d-md-inline-block" title="News & Events">
                ← Back To News & Events
            </a>
        </div>
    </div>

    <div class="photo-title-gallery">
        <h2 class=""><?= $en_title ?></h2>
        <h2><?= date("d M Y", strtotime($dateOfEvent)) ?></h2>
        <p align="justify"><?= $en_description ?></p>
    </div>

    <div class="gallery-container" id="gallery">
        <?php foreach ($photos as $photo):
            $originalPath = "cd-admin/src/" . trim($photo);
            $encryptedPath = base64_encode($originalPath);
            $img = 'enc_image.php?img=' . urlencode($encryptedPath);
            ?>
            <div class="gallery-item">
                <img src="<?= $img ?>" alt="News & Events Gallery"
                    title="News & Events Gallery">
            </div>
        <?php endforeach; ?>
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