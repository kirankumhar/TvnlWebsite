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


    if (!$album) {
        die('Album not found.');
    }

    $en_title = htmlspecialchars($album['name_en']);
    $hi_title = htmlspecialchars($album['name_hi']);
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
                <div class="col-md-12">
                    <div class="card-header-modern d-flex align-items-center justify-content-between">
                        Edit <?= $album['type'] ?> Album Details
                        <a href="javascript:history.back()" class="btn btn-danger btn-sm">
                            ← Back
                        </a>
                    </div>

                    <div class="p-2">
                        <!-- rest form / content -->
                    </div>

                    <?php if (isset($_SESSION['message'])) { ?>
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <strong>Success!</strong> <?php echo $_SESSION['message']; ?>.
                            <button type="button"
                                class="btn btn-sm btn-primary ml-3"
                                aria-label="Close"
                                onclick="closeAlert(this)">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?php unset($_SESSION['message']); ?>
                        </div>
                    <?php } elseif (isset($_SESSION['error'])) { ?>
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <?php echo $_SESSION['error']; ?>.
                            <button type="button"
                                class="btn btn-sm btn-primary ml-3"
                                aria-label="Close"
                                onclick="closeAlert(this)">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php } ?>

                    <form action="" method="post" enctype='multipart/form-data'>

                        <!-- *********************************** -->
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <!-- Date of Event -->
                                <div class="form-group">
                                    <label for="domainId">Domain: <span class="text-danger">*</span></label>
                                    <select name="domainId" id="domainId" class="form-select" required <?= ($domainId > 0) ? 'disabled' : '' ?>>
                                        <option value="">Choose domain...</option>
                                        <?php foreach ($domains_data as $values): ?>
                                            <option value="<?php echo htmlspecialchars($values['id']); ?>" <?php if (!empty($album['domain_id']) && $album['domain_id'] == $values['id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($values['eng_name']) . ' / ' . htmlspecialchars($values['hin_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($domainId > 0): ?>
                                        <input type="hidden" name="domainId" value="<?= (int)$domainId; ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Date of Event -->
                                <div class="form-group">
                                    <label for="dateOfEvent">Date of Event: <span class="text-danger">*</span></label>
                                    <input type="date" id="dateOfEvent" class="form-control" value="<?= $dateOfEvent ?>"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-2">
                            <label for="enalbumTitle">Album Title (English): <span class="text-danger">*</span></label>
                            <input type="text" id="enalbumTitle" class="form-control" value="<?= $en_title ?>" required>
                        </div>

                        <div class="form-group mt-2">
                            <label for="hialbumTitle">Album Title (Hindi): </label>
                            <input type="text" id="hialbumTitle" class="form-control" value="<?= $hi_title ?>">
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="enalbumDescription">Album Description:</label>
                                    <textarea id="enalbumDescription" name="enalbumDescription" class="form-control"
                                        required><?= $en_description ?></textarea>
                                    <script>
                                        CKEDITOR.replace('enalbumDescription');
                                    </script>
                                </div>
                            </div>

                        </div>

                        <div class="row mt-2">
                            <div class="col-md-12">
                                <!-- Venue/Location -->
                                <div class="form-group">
                                    <label for="location">Venue/Location:</label>
                                    <input type="text" id="location" class="form-control" value="<?= $location ?>" required>
                                </div>
                            </div>
                            <input type="hidden" name="action" value="editAlbums">
                        </div>

                        <button type="button" class="btn btn-primary mt-4" id="btn" onclick="saveChanges()">Update
                            Details</button>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        setTimeout(function() {
            $('.cke_notifications_area').remove();
        }, 1000);

        function saveChanges() {
            $("#btn").prop('disabled', true).text('Please Wait..');
            const albumId = new URLSearchParams(window.location.search).get("album_id");
            const sub_cat = $("#category").val();
            const domainId = $("#domainId").val();
            const enAlbumTitle = $("#enalbumTitle").val();
            const hiAlbumTitle = $("#hialbumTitle").val();
            const enAlbumDescription = CKEDITOR.instances.enalbumDescription.getData(); // Get CKEditor content
            const dateOfEvent = $("#dateOfEvent").val();
            const location = $("#location").val();
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');


            const formData = new FormData();
            formData.append('sub_cat_id', sub_cat);
            formData.append('albumId', albumId);
            formData.append('domainId', domainId);
            formData.append('enAlbumTitle', enAlbumTitle);
            formData.append('hiAlbumTitle', hiAlbumTitle);
            formData.append('enAlbumDescription', enAlbumDescription);
            formData.append('dateOfEvent', dateOfEvent);
            formData.append('location', location);
            formData.append('action', 'editAlbum');
            formData.append('csrf_token', csrfToken);

            $.ajax({
                enctype: 'multipart/form-data',
                url: "src/controllers/gallery/album_api.php",
                type: "POST",
                processData: false,
                contentType: false,
                data: formData,
                // contentType: "application/json",
                success: function(response) {
                    window.location.reload();
                },
                error: function(xhr, status, error) {
                    // alert(error)
                    window.location.reload();
                }
            });
        }
    </script>

    <?php
    $embed_script = "newsForm.js";

    require_once __DIR__ . '/layouts/footer.php'; ?>
<?php } else {
    echo "Invalid session, <a href='index.php'>click here</a> to login";
}
?>