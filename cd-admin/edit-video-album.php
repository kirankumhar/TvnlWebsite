<?php
session_start();
include('./timeout.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/layouts/header.php';

if ((isset($_SESSION['login'])) && ($_SESSION['login'] == true)) {

    $albumId = isset($_GET['album_id']) ? $_GET['album_id'] : null;

    if (!$albumId) {
        die('Invalid request: missing album ID.');
    }

    $albumQuery = $pdo->prepare('SELECT a.* FROM albums a WHERE a.uniq_id = ? AND a.is_deleted=0');
    $albumQuery->execute([$albumId]);
    $album = $albumQuery->fetch(PDO::FETCH_ASSOC);

    $album_id = $album['id'];

    $videosQuery = $pdo->prepare('SELECT * FROM videos WHERE albums_id = ? ');
    $videosQuery->execute([$album_id]);
    $videos = $videosQuery->fetchAll(PDO::FETCH_ASSOC);

    if (!$album) {
        die('Album not found or unable to fetch videos.');
    }

    // Check if form data is stored in session
    $form_data = $_SESSION['form_data'] ?? [];

    $title = "Admin - Post Video Album";
?>

    <script src="https://cdn.ckeditor.com/4.9.2/standard/ckeditor.js"></script>
    <script>
        setTimeout(function() {
            $('.cke_notifications_area').remove();
        }, 1000);
    </script>
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="card-header-modern d-flex align-items-center justify-content-between">
                    Edit Video Album
                    <a href="javascript:history.back()" class="btn btn-danger btn-sm">
                        ← Back
                    </a>
                </div>

                <div class="p-2">
                    <!-- rest form / content -->
                </div>

                <div id="msg"></div>

                <div class="row">
                    <?php foreach ($videos as $Video):
                        $videoId = (int)$Video['id'];
                        $videoLink = htmlspecialchars(trim($Video['video_link']), ENT_QUOTES);
                        $videoCaption = htmlspecialchars($Video['video_title'], ENT_QUOTES);
                    ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">

                                    <?php if ($album['cover_video_id'] == $videoId): ?>
                                        <span class="badge bg-success mb-2 d-block text-center p-3">Cover Video</span>
                                    <?php endif; ?>

                                    <iframe class="w-100 rounded"
                                        height="170"
                                        src="<?= $videoLink ?>"
                                        frameborder="0"
                                        allowfullscreen>
                                    </iframe>

                                    <input type="url"
                                        class="form-control mt-3 video-link"
                                        value="<?= $videoLink ?>"
                                        placeholder="YouTube URL">

                                    <input type="text"
                                        class="form-control mt-2 video-caption"
                                        value="<?= $videoCaption ?>"
                                        placeholder="Video Caption">

                                    <div class="d-flex gap-2 mt-3">
                                        <button class="btn btn-danger btn-sm w-50"
                                            onclick="deleteVideo(<?= $videoId ?>)">
                                            Delete
                                        </button>

                                        <button class="btn btn-success btn-sm w-50"
                                            onclick="updateVideo(<?= $videoId ?>, this)">
                                            Update
                                        </button>
                                    </div>

                                    <div class="update-msg mt-2 small"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- ADD NEW VIDEO (NO HEADER) -->
                <div class="card mt-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-3 text-secondary">Add New Video</h5>

                        <input type="url" id="newVideoInput" class="form-control"
                            placeholder="YouTube Video URL">

                        <input type="text" id="newVideoCaptionEn"
                            class="form-control mt-2"
                            placeholder="Video Caption">

                        <button class="btn btn-primary mt-3"
                            onclick="addNewVideo()">
                            Add Video
                        </button>

                        <div id="newVideoError" class="text-danger mt-2"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>


<?php

    $embed_script = "albumForm.js";

    require_once __DIR__ . '/layouts/footer.php';

    unset($_SESSION['form_data']);
} else {
    echo "Invalid session, <a href='index.php'>click here</a> to login";
}
?>