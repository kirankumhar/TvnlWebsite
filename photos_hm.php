<?php
$userAgent = $_SERVER['HTTP_USER_AGENT'];

$badBots = ['HTTrack', 'WebZIP', 'Teleport', 'wget', 'curl'];
foreach ($badBots as $bot) {
	if (stripos($userAgent, $bot) !== false) {
		header('HTTP/1.0 403 Forbidden');
		exit('Access denied.');
	}
}

require_once __DIR__ . '/cd-admin/src/database/Database.php';

$database = new Database();
$pdo = $database->getConnection();

// Encryption Keys ============>
$encryption_key = 'af7af6d2f08c8e7cdc4cc2d03046453c139c09ed7a5d98ec73ac9c230ec0a2f8';
$iv = openssl_random_pseudo_bytes(16);

// ============ OUR GALLERY ==============

// ***** PHOTOS *****
$query = "SELECT a.*, p.file_path
          FROM albums a
          INNER JOIN photos p ON a.cover_photo_id = p.id
          WHERE a.is_deleted = '0'
          AND a.is_hide = '0'
          AND a.type = 'Photos'
          ORDER BY a.event_date desc LIMIT 4";

$stmt = $pdo->prepare($query);
$stmt->execute();
$photo_albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function to encrypt album ID for URLs
function getEncryptedUrl($albumId, $encryption_key, $iv) {
    $encrypted_id = openssl_encrypt($albumId, 'AES-256-CBC', $encryption_key, 0, $iv);
    $encrypted_data = base64_encode($encrypted_id . '::' . base64_encode($iv));
    return urlencode($encrypted_data);
}

// Helper function to get encrypted image path
function getEncryptedImagePath($filePath) {
    $originalPath = "cd-admin/src/" . $filePath;
    $encryptedPath = base64_encode($originalPath);
    return 'enc_image.php?img=' . urlencode($encryptedPath);
}

// Helper function to format date
function formatDateForDisplay($date) {
    return date("d M Y", strtotime($date));
}

// Get the featured album (first one) and remaining albums
$featuredAlbum = !empty($photo_albums) ? $photo_albums[0] : null;
$sideAlbums = array_slice($photo_albums, 1);
?>

<section class="blog-style2-area" style="padding:20px;">
    <div class="container">
        <div class="blog-style2_top" style="padding:20px;">
            <div class="sec-title style2 with_text pull-left" style="margin-bottom:0px;margin-top:0px!important;">
                <div class="sub-title clr2">
                    <span class="border-box"></span>
                    <h5>Media@TVNL</h5>
                </div>
            </div>
        </div>

        <div class="row text-right-rtl">
            <!-- Left Side - Featured Album (Large) -->
            <div class="col-xl-6 col-lg-6">
                <?php if ($featuredAlbum): 
                    $featuredDate = date("d", strtotime($featuredAlbum['event_date']));
                    $featuredMonth = strtoupper(date("M", strtotime($featuredAlbum['event_date'])));
                    $featuredImg = getEncryptedImagePath($featuredAlbum['file_path']);
                    $featuredUrl = getEncryptedUrl($featuredAlbum['uniq_id'], $encryption_key, $iv);
                ?>
                <div class="single-blog-style2 wow fadeInUp" data-wow-duration="1500ms">
                    <div class="img-holder">
                        <div class="inner" title="<?= htmlspecialchars($featuredAlbum['name_en']) ?>">
                            <img src="<?= $featuredImg ?>"
                                alt="<?= htmlspecialchars($featuredAlbum['name_en']) ?>" 
                                style="width:100%; height: 350px; object-fit: cover;">

                            <div class="overlay-icon">
                                <a href="gallery-details.php?album=<?= $featuredUrl ?>">
                                    <span class="flaticon-plus"></span>
                                </a>
                            </div>
                        </div>
                        <div class="date-box bgclr2">
                            <h2><?= $featuredDate ?></h2>
                            <span><?= $featuredMonth ?></span>
                        </div>
                    </div>
                    <div class="text-holder">
                        <h3 class="blog-title">
                            <a href="gallery-details.php?album=<?= $featuredUrl ?>">
                                <?= htmlspecialchars($featuredAlbum['name_en']) ?>
                            </a>
                        </h3>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info">No featured album available</div>
                <?php endif; ?>
            </div>

            <!-- Right Side - Side Albums List -->
            <div class="col-xl-6 col-lg-6">
                <?php if (!empty($sideAlbums)): ?>
                    <?php foreach ($sideAlbums as $index => $album): 
                        $formatted_date = formatDateForDisplay($album['event_date']);
                        $albumImg = getEncryptedImagePath($album['file_path']);
                        $albumUrl = getEncryptedUrl($album['uniq_id'], $encryption_key, $iv);
                        $animationDelay = 1200 + ($index * 200);
                    ?>
                    <div class="single-blog-style2 wow fadeInUp mb-3" data-wow-duration="<?= $animationDelay ?>ms">
                        <div class="row align-items-start">
                            <div class="col-4 col-md-4">
                                <div class="img-holder">
                                    <div class="inner" title="<?= htmlspecialchars($album['name_en']) ?>">
                                        <img src="<?= $albumImg ?>" 
                                            alt="<?= htmlspecialchars($album['name_en']) ?>"
                                            class="img-fluid rounded"
                                            style="width: 100%; height: 100px; object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-8 col-md-8">
                                <div class="text-holder" style="padding:0px;">
                                    <a href="gallery-details.php?album=<?= $albumUrl ?>">
                                        <div class="card-text mb-0 text-muted small">
                                            <time datetime="<?= $album['event_date'] ?>"><?= $formatted_date ?></time>
                                        </div>
                                        <h3 class="blog-title mb-2">
                                            <?= htmlspecialchars($album['name_en']) ?>
                                        </h3>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- If no side albums, show a message -->
                    <div class="alert alert-info">No more albums available</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="service-style4_more_service_button text-center">
                    <a class="btn-one" href="photos.php" title="Media@TVNL">
                        <span class="txt">
                            <i class="left flaticon-login"></i>View All
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>