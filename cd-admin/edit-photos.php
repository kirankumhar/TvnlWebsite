<?php
session_start();
include('./timeout.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/layouts/header.php';
if (isset($_SESSION['user_id'])) {

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
    $en_description = htmlspecialchars($album['description_en']);
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

        .change-file {
            display: inline-block;
            background-color: #158983;
            color: white;
            padding: 0.5rem;
            font-family: sans-serif;
            border-radius: 0.3rem;
            cursor: pointer;
            margin-top: 1rem;
        }
    </style>

    <script src="https://cdn.ckeditor.com/4.9.2/standard/ckeditor.js"></script>

    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0">
                <div class="card-header-modern d-flex align-items-center justify-content-between">
                    Edit Photos
                    <a href="javascript:history.back()" class="btn btn-danger btn-sm">
                        ← Back
                    </a>
                </div>

                <div class="p-2">
                    <!-- rest form / content -->
                </div>
                <div id="msg"></div>
                <?php if (isset($_SESSION['message'])) { ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <strong>Success!</strong> <?php echo $_SESSION['message']; ?>.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?php unset($_SESSION['message']); ?>
                    </div>
                <?php } elseif (isset($_SESSION['error'])) { ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <?php echo $_SESSION['error']; ?>.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php } ?>

                <form action="" method="post" enctype='multipart/form-data'>

                    <h5>Title: <?= $album['name_en']; ?></h5>
                    <!-- Manage Existing Photos -->
                    <div id="photoList" class="mt-3">

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
                                                    <img src="' . $base_url . '/src/' . $photoURL . '" class="preview-image" id="img-' . $photoId . '"
                                                         style="max-width: 100%; max-height: 180px; object-fit: contain;" alt="Photo Preview">
                                                </div>
                                                
                                                <div class="card-body">
                                                
                                                    <div class="d-flex align-items-center mb-2">
                                                        <label class="me-2 mb-0 fw-bold" style="width: 70px;">Position:</label>
                                                        <input type="text" value="' . ($index + 1) . '" class="form-control form-control-sm" placeholder="Position">
                                                    </div>
                                                    <input type="file" id="upload" name="replacePic" hidden>
                                                    <label for="upload" class="change-file">Change pic</label>

                                                    <textarea class="form-control my-2" placeholder="Photo Caption" name="captionEn">' . $photoCaptionEn . '</textarea>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <button class="btn btn-sm btn-danger" type="button" onclick="deletePhoto(\'photo-' . $photoId . '\')">Delete</button>
                                                        <div class="form-check">'; ?>
                                <input type="radio" class="form-check-input" id="coverPhoto<?= $photoId ?>" name="coverPhoto"
                                    value="<?= $photoId ?>" <?= $photoId == $coverId ? 'checked' : '' ?>>
                                <label for=" coverPhoto<?= $photoId ?>" class="form-check-label">Cover Photo</label>
                            <?php echo '
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>';
                                $setIndex++;
                            } ?>
                        </div>

                        <?php if (count($photos) < 30) { ?>
                            <div class="form-group row mt-2">
                                <div class="col-md-4">Other Pictures Upload
                                    <div class="hidden" id="fileCountMessage" style="color: red;">Maximum
                                        <?= 30 - count($photos) ?>
                                        files allowed.
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <input type="file" class="form-control" name="picture2[]" id="picture2" placeholder=""
                                        value="" multiple accept="image/png, image/gif,image/jpg, image/jpeg"
                                        onchange="checkFiles(this)" /> <small style="color: red;"> Max. size 400kb (jpg & png
                                        only)</small>

                                </div>

                                <div class="col-md-12" id="previewImage2"></div>
                            </div>
                        <?php } ?>

                        <button type="button" class="btn btn-primary mt-4" id="saveBtn" onclick="saveChanges()">Add
                            Photo</button>

                    </div>
                </form>

            </div>
        </div>
    </div>
    </div>

    <script>
        const MAX_ALLOWED_FILES = <?= 30 - count($photos) ?>;
    </script>

    <?php
    $embed_script = "photos.js";

    require_once __DIR__ . '/layouts/footer.php'; ?>

<?php } else {
    echo "Invalid session, <a href='index.php'>click here</a> to login";
}
?>