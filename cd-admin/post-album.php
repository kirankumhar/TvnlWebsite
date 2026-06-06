<?php
session_start();
if ((isset($_SESSION['login'])) && ($_SESSION['login'] == true)) {
    // Check if form data is stored in session
    $form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];

    // Use the form data to populate the form fields
    $eng_cat = isset($form_data['eng_cat']) ? htmlspecialchars($form_data['eng_cat']) : '';
    $hin_cat = isset($form_data['hin_cat']) ? htmlspecialchars($form_data['hin_cat']) : '';
    $en_albm_desc = isset($form_data['en_albm_desc']) ? htmlspecialchars($form_data['en_albm_desc']) : '';
    $location = isset($form_data['location']) ? htmlspecialchars($form_data['location']) : '';
    $dt_event = isset($form_data['dt_event']) ? htmlspecialchars($form_data['dt_event']) : '';
    $captions = isset($form_data['caption']) ? $form_data['caption'] : [];


    $title = "Admin - Add Photo Album";
    require_once __DIR__ . '/layouts/header.php';
?>
    <script src="https://cdn.ckeditor.com/4.9.2/standard/ckeditor.js"></script>

    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0">
                <div class="card-header-modern d-flex align-items-center justify-content-between">
                    Post Album
                </div>

                <div class="p-2">
                    <!-- rest form / content -->
                </div>

                <?php if (isset($_SESSION['message'])) { ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <strong>Success!</strong> <?= $_SESSION['message']; ?>.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?php unset($_SESSION['message']); ?>
                    </div>
                <?php } elseif (isset($_SESSION['error'])) { ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <?= $_SESSION['error']; ?>.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php } ?>

                <form action="<?= $base_url ?>/src/controllers/gallery/addPhotoAlbumController.php" method="post"
                    enctype="multipart/form-data">
                    <div class="form-group row mt-3">
                        <label for="album_type" class="col-form-label col-md-4">Album Type <span
                                class="text-danger">*</span></label>
                        <div class="col-md-8">
                            <select name="album_type" id="album_type" class="form-select">
                                <?php if (canCreate($pdo, $userId, 'mediaPhoto')) : ?>
                                    <option value="Photos">Photos</option>
                                <?php endif; ?>
                                <?php if (canCreate($pdo, $userId, 'mediavideo')) : ?>
                                    <option value="Videos">Videos</option>
                                <?php endif; ?>
                                <?php if (canCreate($pdo, $userId, 'mediaPressclip')) : ?>
                                    <option value="Press Clips">Press Clips</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mt-3">
                        <label for="dt_event" class="col-md-4 col-form-label">Date of Event <span
                                class="text-danger">*</span></label>
                        <div class="col-md-8">
                            <input type="date" name="dt_event" id="dt_event" class="form-control" value="<?= $dt_event; ?>"
                                required max="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="form-group row mt-3">
                        <label for="eng_cat" class="col-md-4 col-form-label">Album Name (English)<span
                                class="text-danger">*</span></label>
                        <div class="col-md-8">
                            <input type="text" name="eng_cat" id="eng_cat" class="form-control" value="<?= $eng_cat ?>" placeholder="Album Name"
                                required>
                        </div>
                    </div>

                    <div class="form-group row mt-3">
                        <label for="hin_cat" class="col-md-4 col-form-label">Album Name (Hindi)</label>
                        <div class="col-md-8">
                            <input type="text" name="hin_cat" id="hin_cat" class="form-control" value="<?= $hin_cat ?>" placeholder="एल्बम का नाम">
                        </div>
                    </div>

                    <div class="form-group row mt-3">
                        <label for="en_albm_desc" class="col-md-4 col-form-label">Album
                            Description</label>
                        <div class="col-md-8">
                            <textarea name="en_albm_desc" id="editor" placeholder="Max 15000 chars (Optional)"
                                maxlength="15000" required cols="50"><?= $en_albm_desc; ?></textarea>
                            <script>
                                CKEDITOR.replace('editor', {});
                            </script>
                        </div>
                    </div>

                    <div class="form-group row mt-3">
                        <label for="location" class="col-md-4 col-form-label">Venue/Location</label>
                        <div class="col-md-8">
                            <input type="text" name="location" id="location" class="form-control" value="<?= $location; ?>">
                        </div>
                    </div>

                    <div class="form-group row mt-3 image-caption-set" id="image_set">
                        <label for="albumPic1" class="col-md-4 col-form-label">Album Cover Photo <span
                                class="text-danger">*</span></label>
                        <div class="col-md-8 d-flex align-items-center">
                            <div class="flex-grow-1">
                                <input type="file" name="albumPic[]" id="albumPic1" class="form-control" accept="image/*"
                                    onchange="previewImage(this, 'previewImage1')" required>
                                <input type="text" name="caption[]" id="caption1" class="form-control mt-2"
                                    placeholder="Photo Caption">
                                <div id="previewImage1" class="mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mt-3 d-none" id="video_set">
                        <label for="albumPic1" class="col-md-4 col-form-label">Video Album Cover URL <span
                                class="text-danger">*</span></label>
                        <div class="col-md-8 d-flex align-items-center">
                            <div class="flex-grow-1">
                                <input type="url" pattern="https?://.+" name="video_link" class="form-control"
                                    id="video_link" disabled>
                                <input type="text" name="caption" id="video_caption" class="form-control mt-2"
                                    placeholder="Video Caption">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="text-center">
                        <input type="submit" class="btn btn-outline-success" value="Post Album">
                        <!-- <input type="button" class="btn btn-outline-danger" value="Cancel"
                            onclick="javascript:history.back();"> -->
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the album type select element
            const albumTypeSelect = document.getElementById('album_type');

            // Get the image and video set divs
            const imageSet = document.getElementById('image_set');
            const videoSet = document.getElementById('video_set');

            // Get the form elements
            const albumPic = document.getElementById('albumPic1');
            const caption = document.getElementById('caption1');
            const videoLink = document.getElementById('video_link');
            const videoCaption = document.getElementById('video_caption');

            // Add event listener to the album type select
            albumTypeSelect.addEventListener('change', function() {
                // Get the selected value
                const selectedType = this.value;

                if (selectedType === 'Videos') {
                    // For Video albums
                    // Show video set and hide image set
                    videoSet.classList.remove('d-none');
                    imageSet.classList.add('d-none');

                    // Enable and make video fields required
                    videoLink.disabled = false;
                    videoLink.required = true;
                    videoCaption.disabled = false;

                    // Disable and remove required from image fields
                    albumPic.disabled = true;
                    albumPic.required = false;
                    caption.disabled = true;
                } else {
                    // For Photos and Press Clips
                    // Show image set and hide video set
                    imageSet.classList.remove('d-none');
                    videoSet.classList.add('d-none');

                    // Enable and make image fields required
                    albumPic.disabled = false;
                    albumPic.required = true;
                    caption.disabled = false;

                    // Disable and remove required from video fields
                    videoLink.disabled = true;
                    videoLink.required = false;
                    videoCaption.disabled = true;
                }
            });

            // Trigger the change event on page load to set initial state
            albumTypeSelect.dispatchEvent(new Event('change'));
        });
    </script>
    <?php

    require_once __DIR__ . '/layouts/footer.php'; ?>

<?php
} else {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}
unset($_SESSION['form_data']);
?>
