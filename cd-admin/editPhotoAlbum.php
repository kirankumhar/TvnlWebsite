<?php
session_start();

if (isset($_SESSION['login']) && $_SESSION['login'] == true) {

    include 'PDOsettings.php';

    $albumId = isset($_GET['album_id']) ? $_GET['album_id'] : null;

    if (!$albumId) {
        die('Invalid request: missing album ID.');
    }

    try {
        $sql = "SELECT * FROM news_sub_category where category_master_id='2'";
        $Category_result = mysqli_query($link, $sql);

        $subCatQuery = $pdo->prepare('SELECT * FROM news_sub_category where category_master_id=?');
        $subCatQuery->execute(['2']);
        $subCat = $subCatQuery->fetchAll(PDO::FETCH_ASSOC);

        $albumQuery = $pdo->prepare('SELECT * FROM Albums WHERE id = ?');
        $albumQuery->execute([$albumId]);
        $album = $albumQuery->fetch(PDO::FETCH_ASSOC);

        $photosQuery = $pdo->prepare('SELECT * FROM Photos WHERE album_id = ?');
        $photosQuery->execute([$albumId]);
        $photos = $photosQuery->fetchAll(PDO::FETCH_ASSOC);

        if (!$album || !$photos) {
            die('Album not found or unable to fetch photos.');
        }
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
    }

    $sub_cat_id = $album['sub_category_id'];
    $en_title = htmlspecialchars($album['name_en']);
    $hn_title = htmlspecialchars($album['name_hn']);
    $en_description = htmlspecialchars($album['description_en']);
    $hn_description = htmlspecialchars($album['description_hn']);
    $dateOfEvent = htmlspecialchars($album['event_date']);
    $location = htmlspecialchars($album['location']);
    $coverId = $album['cover_photo_id'];

    $title = 'Admin: Edit Photo Album';
    require 'main_layout/header.php'; ?>

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

    <section id="content" class="my-3">
        <div class="container">

            <div class="card my-3">
                <div class="card-body p-0">
                    <div class="card-header-modern d-flex align-items-center justify-content-between">
                        Edit Photo Album
                        <a href="javascript:history.back()" class="btn btn-danger btn-sm">
                            ← Back
                        </a>
                    </div>

                    <div class="p-2">
                        <!-- rest form / content -->
                    </div>
                    <div id="msg"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Date of Event -->
                            <div class="form-group">
                                <label for="dateOfEvent">Date of Event:</label>
                                <input type="date" id="dateOfEvent" class="form-control" value="<?= $dateOfEvent ?>"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-control" name="category" id="category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($subCat as $row): ?>
                                        <option value="<?= htmlspecialchars($row['id']); ?>" <?= $sub_cat_id == $row['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($row['eng_sub_category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <?php if (isset($err['category'])) { ?>
                                    <div class="form-text text-danger"><?php echo $err['category']; ?></div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="enalbumTitle">Engligh Album Title:</label>
                        <input type="text" id="enalbumTitle" class="form-control" value="<?= $en_title ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="hnalbumTitle">Hindi Album Title:</label>
                        <input type="text" id="hnalbumTitle" class="form-control" value="<?= $hn_title ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="enalbumDescription">English Album Description:</label>
                                <textarea id="enalbumDescription" name="enalbumDescription" class="form-control"
                                    required><?= $en_description ?></textarea>
                                <script>
                                    CKEDITOR.replace('enalbumDescription');
                                </script>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="hnalbumDescription">Hindi Album Description:</label>
                                <textarea id="hnalbumDescription" name="hnalbumDescription" class="form-control"
                                    required><?= $hn_description ?></textarea>
                                <script>
                                    CKEDITOR.replace('hnalbumDescription');
                                </script>
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-12">
                            <!-- Venue/Location -->
                            <div class="form-group">
                                <label for="location">Venue/Location:</label>
                                <input type="text" id="location" class="form-control" value="<?= $location ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Manage Existing Photos -->
                    <div id="photoList">
                        <h4>Manage Photos</h4>
                        <div class="row">

                            <?php $setIndex = 1;
                            foreach ($photos as $photo) {
                                $photoId = htmlspecialchars($photo['id']);
                                $photoURL = htmlspecialchars($photo['file_path']);
                                $photoCaptionEn = htmlspecialchars($photo['caption_en']);
                                $photoCaptionHn = htmlspecialchars($photo['caption_hn']);

                                echo ' <div class="col-md-4"><div class="photo-row" id="photo-' . $photoId . '">
                            <img src="' . $photoURL . '" class="preview-image" alt="Photo Preview">
                            <input type="text" value="' . $photoCaptionEn . '" class="form-control mt-2" placeholder="Enter English caption">
                            <input type="text" value="' . $photoCaptionHn . '" class="form-control mt-2" placeholder="Enter Hindi caption">
                            <div class="d-flex align-items-center">
                            <button class="btn btn-danger me-2" onclick="deletePhoto(\'photo-' . $photoId . '\')">Delete</button>
                            <div class="form-check">'; ?>
                                <input type="radio" class="form-check-input" name="coverPhoto" value="<?= $photoId ?>"
                                    <?= $photoId == $coverId ? 'checked' : '' ?>>

                            <?php echo ' <label for="coverPhoto1" class="form-check-label">Mark as Cover Photo</label>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div></div>';
                                $setIndex++;
                            } ?>
                        </div>
                    </div>

                    <!-- Add New Photo -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <h4>Add New Photo</h4>
                                <input type="file" id="newPhotoInput" class="form-control" accept="image/*">
                                <input type="text" id="newPhotoCaptionEn" class="form-control mt-2"
                                    placeholder="Enter English caption for new photo">
                                <input type="text" id="newPhotoCaptionHn" class="form-control mt-2"
                                    placeholder="Enter Hindi caption for new photo">
                                <div id="newPhotoPreview" class="mt-2"></div>
                                <div id="newPhotoError" class="error-message"></div>
                                <button class="btn btn-primary mt-2" onclick="addNewPhoto()">Add Photo</button>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-success mt-4 mb-5" onclick="saveChanges()">Save Changes</button>

                </div>
            </div>
        </div>
    </section>
    <br>
    <footer id="footer" class="mt-3">
        <div class="container">
            <div class="row">
                <div class="grid_12 copyright">
                    <pre>© <span id="copyright-year"></span> saryuroy.in</pre>
                </div>
            </div>
        </div>
    </footer>
    <script>
        setTimeout(function() {
            $('.cke_notifications_area').remove();
        }, 1000);

        const maxFileSizeKB = 500;
        const allowedExtensions = ["jpg", "jpeg", "png"];

        function addNewPhoto() {

            const albumId = new URLSearchParams(window.location.search).get("album_id");
            const photoList = document.getElementById("photoList");
            const currentPhotoCount = photoList.getElementsByClassName("photo-row").length;

            if (currentPhotoCount >= 20) {
                alert("You cannot add more than 20 photos.");
                return;
            }

            const photoInput = $("#newPhotoInput")[0];
            const photoFile = photoInput.files[0];
            const captionEn = $("#newPhotoCaptionEn").val();
            const captionHn = $("#newPhotoCaptionHn").val();

            if (!photoFile) {
                alert("Photo Attachment are required.");
                return;
            }

            if (!validateFile(photoFile)) {
                return;
            }

            const formData = new FormData();
            formData.append('albumId', albumId);
            formData.append('photo', photoFile);
            formData.append('caption_en', captionEn);
            formData.append('caption_hn', captionHn);

            $.ajax({
                url: 'controllers/editInsertPhotoAlbum.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error('Error adding photo:', error);
                    // location.reload();
                }
            });
        }

        function validateFile(file) {
            const errorMessageElement = $("#newPhotoError");
            errorMessageElement.text("");

            const maxFileSizeBytes = maxFileSizeKB * 1024;
            if (file.size > maxFileSizeBytes) {
                errorMessageElement.text(`File size exceeds the allowed limit of ${maxFileSizeKB} KB.`);
                return false;
            }

            const fileExtension = file.name.split(".").pop().toLowerCase();
            if (!allowedExtensions.includes(fileExtension)) {
                errorMessageElement.text(`File format not supported. Allowed extensions: ${allowedExtensions.join(", ")}.`);
                return false;
            }
            return true;
        }

        function deletePhoto(photoId) {
            if (!confirm("Are you sure you want to delete this photo?")) {
                return;
            }
            const isCover = $(`#${photoId}`).find("input[type='radio']").prop("checked");

            if (isCover) {
                alert("Cannot delete the cover photo. Please change the cover photo first.");
                return;
            }
            const photo = photoId.split("-")[1];

            $.ajax({
                url: 'controllers/deletePhotoAlbumPics.php',
                type: 'POST',
                data: {
                    photoId: photo
                },
                success: function(response) {
                    const alertType = response.includes("Success!") ? "success" : "danger";
                    const messageTemplate = `
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <strong>${response}</strong>
                            <button type="button"
                                class="btn btn-sm btn-primary ml-3"
                                aria-label="Close"
                                onclick="closeAlert(this)">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>`;

                    $("#msg").append(messageTemplate);
                    $('html, body').animate({
                        scrollTop: 0
                    }, 'fast');
                    $(`#${photoId}`).remove();
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting photo:', error);
                }
            });
        }

        function saveChanges() {
            const albumId = new URLSearchParams(window.location.search).get("album_id");
            const sub_cat = $("#category").val();
            const enAlbumTitle = $("#enalbumTitle").val();
            const hnAlbumTitle = $("#hnalbumTitle").val();
            const enAlbumDescription = CKEDITOR.instances.enalbumDescription.getData(); // Get CKEditor content
            const hnAlbumDescription = CKEDITOR.instances.hnalbumDescription.getData(); // Get CKEditor content
            const dateOfEvent = $("#dateOfEvent").val();
            const location = $("#location").val();

            const photosData = [];
            $("#photoList .photo-row").each(function() {
                const photoId = $(this).attr("id").split("-")[1];
                const captionEn = $(this).find("input[type='text']").first().val();
                const captionHn = $(this).find("input[type='text']").last().val();
                // const isCover = $(this).find("input[type='radio']").val();
                const isCover = $(this).find("input[type='radio']").is(':checked');

                photosData.push({
                    id: photoId,
                    captionEn: captionEn,
                    captionHn: captionHn,
                    isCover: isCover
                });
            });

            $.ajax({
                enctype: 'multipart/form-data',
                url: "controllers/editPhotoAlbumController.php",
                type: "POST",
                processData: false,
                contentType: false,
                data: JSON.stringify({
                    sub_cat_id: sub_cat,
                    albumId: albumId,
                    enAlbumTitle: enAlbumTitle,
                    hnAlbumTitle: hnAlbumTitle,
                    enAlbumDescription: enAlbumDescription,
                    hnAlbumDescription: hnAlbumDescription,
                    dateOfEvent: dateOfEvent,
                    location: location,
                    photos: photosData
                }),
                // contentType: "application/json",
                success: function(response) {
                    // console.log("Changes saved:", response);
                    const success = `<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                                                                                                                                                <strong>Success!</strong> Changes saved successfully.
                                                                                                                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                                                                                                    <span aria-hidden="true">&times;</span>
                                                                                                                                                                </button>
                                                                                                                                                            </div>`;

                    $("#msg").append(success);
                    $('html, body').animate({
                        scrollTop: 0
                    }, 'fast');
                },
                error: function(xhr, status, error) {

                    const errorMsg = `<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                                                                                                                                                <strong>Error!</strong> Something went wrong!.
                                                                                                                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                                                                                                    <span aria-hidden="true">&times;</span>
                                                                                                                                                                </button>
                                                                                                                                                            </div>`;

                    $("#msg").append(errorMsg);
                    $('html, body').animate({
                        scrollTop: 0
                    }, 'slow');
                    // console.error("Error saving changes:", error);
                }
            });
        }
    </script>
<?php } else {
    echo "Invalid session, <a href='index.php'>click here</a> to login.";
}
