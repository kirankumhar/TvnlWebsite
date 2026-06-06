<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
if (isset($_SESSION['user_id'])) {
    
    require_once __DIR__ . '/layouts/header.php';
    $albumId = $_GET['album_id'] ?? 0;

    $albumQuery = $pdo->prepare('SELECT * FROM albums WHERE uniq_id = ? AND is_deleted=0');
    $albumQuery->execute([$albumId]);
    $album = $albumQuery->fetch(PDO::FETCH_ASSOC);

    $album_id = $album['id'];

    $photosQuery = $pdo->prepare('SELECT * FROM photos WHERE album_id = ? ORDER BY position');
    $photosQuery->execute([$album_id]);
    $photos = $photosQuery->fetchAll(PDO::FETCH_ASSOC);

    if (!$album || !$photos) {
        die('Album not found or unable to fetch photos.');
    }

    $en_title = htmlspecialchars($album['name_en']);
    $en_description = $album['description_en'];
    $dateOfEvent = htmlspecialchars($album['event_date']);
    $location = htmlspecialchars($album['location']);
    $coverId = $album['cover_photo_id'];
    ?>

    <style>
        /* Custom styles */
        .error-message {
            color: red;
        }

        .preview-image {
            max-width: 100px;
            margin-right: 10px;
        }

        .photo-row {
            margin-bottom: 10px;
        }
    </style>
    <script src="https://cdn.ckeditor.com/4.9.2/standard/ckeditor.js"></script>

    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0">
                <div class="card-header-modern d-flex align-items-center justify-content-between">
                    Photo Album View
                </div>

                <div class="p-2">
                    <!-- rest form / content -->
                </div>
                <div class="row">

                    <div class="col-md-12">
                        <div class="form-group row">
                            <label for="" class="col-sm-2 col-form-label"><strong>Album Title :</strong></label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control-plaintext" value="<?= $en_title; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group row">
                            <label for="news_date" class="col-sm-2 col-form-label"><strong>Event Date
                                    :</strong></label>
                            <div class="col-sm-10">
                                <input type="date" class="form-control-plaintext" value="<?= $dateOfEvent; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row my-2">
                    <div class="col-md-12">
                        <label for=""><strong>Album Decription : </strong></label>

                        <textarea name="" id="editor" rows="7"
                            class="form-control-plaintext"><?= $en_description; ?></textarea>
                        <script>
                            CKEDITOR.replace('editor');
                            setTimeout(function () {
                                $('.cke_notifications_area').remove();
                            }, 1000);
                        </script>
                    </div>
                </div>
                <div class="row">

                    <?php $setIndex = 1;
                    foreach ($photos as $index => $photo) {
                        $photoId = htmlspecialchars($photo['id']);
                        $photoURL = htmlspecialchars($photo['file_path']);
                        $photoCaptionEn = !empty($photo['caption_en']) ? htmlspecialchars($photo['caption_en']) : '';

                        echo '<div class="col-md-4 mb-3">
                                            <div class="photo-row card" id="photo-' . $photoId . '">
                                                <div class="image-container d-flex justify-content-center align-items-center bg-light" 
                                                     style="height: 200px; overflow: hidden;">
                                                    <img src="' . $base_url . '/src/' . $photoURL . '" class="preview-image" 
                                                         style="max-width: 100%; max-height: 180px; object-fit: contain;" alt="Photo Preview">
                                                </div>
                                                
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <label class="me-2 mb-0 fw-bold" style="width: 70px;">Position:</label>
                                                        <input type="text" readonly value="' . ($index + 1) . '" class="form-control-plaintext form-control-sm" placeholder="Position">
                                                    </div>
                                                    
                                                    <textarea class="form-control-plaintext mb-3" readonly placeholder="Photo Caption" name="captionEn">' . $photoCaptionEn . '</textarea>
                                                    
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="form-check">'; ?>
                        <?php if ($photoId == $coverId) {
                            echo "<span class='text-success'>Cover Photo</span>";
                        } ?>
                        <?php echo '
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>';
                        $setIndex++;
                    } ?>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label for="">Location : </label>
                        <input type="text" class="form-control-plaintext" value="<?= $album['location']; ?>">
                    </div>
                    <!-- <div class="col-md-8">
                        <label for="">Hash Tag : </label>
                        <input type="text" class="form-control-plaintext" value="<?= $album['location']; ?>">
                    </div> -->
                </div>
            </div>
        </div>
    </div>

    <?php

    require_once __DIR__ . '/layouts/footer.php'; ?>

<?php } else {
    echo "Invalid session, <a href='index.php'>click here</a> to login";
}
?>