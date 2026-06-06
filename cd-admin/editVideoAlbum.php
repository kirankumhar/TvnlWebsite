<?php
session_start();

if (isset($_SESSION['login']) && $_SESSION['login'] == true) {

    include 'PDOsettings.php';

    $albumId = isset($_GET['album_id']) ? $_GET['album_id'] : null;

    if (!$albumId) {
        die('Invalid request: missing album ID.');
    }

    try {
        $subCatQuery = $pdo->prepare('SELECT * FROM news_sub_category where category_master_id=?');
        $subCatQuery->execute(['3']);
        $subCat = $subCatQuery->fetchAll(PDO::FETCH_ASSOC);

        $albumQuery = $pdo->prepare('SELECT * FROM Albums WHERE id = ?');
        $albumQuery->execute([$albumId]);
        $album = $albumQuery->fetch(PDO::FETCH_ASSOC);

        $VideosQuery = $pdo->prepare('SELECT * FROM Videos WHERE Albums_id = ?');
        $VideosQuery->execute([$albumId]);
        $Videos = $VideosQuery->fetchAll(PDO::FETCH_ASSOC);

        if (!$album || !$Videos) {
            die('Album not found or unable to fetch videos.');
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
    $coverId = $album['cover_video_id'];

    $title = 'Admin: Edit Video Album';
    include 'main_layout/header.php'; ?>

    <style>
        /* Custom styles */
        .error-message {
            color: red;
        }

        .preview-image {
            max-width: 100px;
            margin-right: 10px;
        }

        .Video-row {
            margin-bottom: 10px;
        }
    </style>
    <script src="https://cdn.ckeditor.com/4.9.2/standard/ckeditor.js"></script>
    <div class="container">

        <div class="card my-3">
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
                    <div class="col-md-6">
                        <!-- Date of Event -->
                        <div class="form-group">
                            <label for="dateOfEvent">Date of Event:</label>
                            <input type="date" id="dateOfEvent" class="form-control" value="<?= $dateOfEvent ?>" required>
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

                <!-- Manage Existing Videos -->
                <div id="VideoList">
                    <h4>Manage Video Link Albums</h4>
                    <div class="row">

                        <?php $setIndex = 1;
                        foreach ($Videos as $Video) {
                            $videoId = htmlspecialchars($Video['id']);
                            $videoLink = htmlspecialchars(trim($Video['video_link']), ENT_QUOTES, 'UTF-8');
                            $videoCaptionEn = htmlspecialchars($Video['en_video_title'], ENT_QUOTES, 'UTF-8');
                            $videoCaptionHn = htmlspecialchars($Video['hn_video_title'], ENT_QUOTES, 'UTF-8');

                            echo '<div class="col-md-4">
                                    <div class="Video-row" id="Video-' . $videoId . '">
                                        <iframe width="215" height="160"
                                             src="https://www.youtube.com/embed/' . $videoLink . '"
                                             title="' . $videoCaptionEn . '"
                                             frameborder="0"
                                             allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                             allowfullscreen>
                                        </iframe>
                                        <input type="text" value="' . $videoLink . '" class="form-control mt-2" placeholder="Enter Video Link">
                                        <input type="text" value="' . $videoCaptionEn . '" class="form-control mt-2" placeholder="Enter English caption">
                                        <input type="text" value="' . $videoCaptionHn . '" class="form-control mt-2" placeholder="Enter Hindi caption">
                                        <div class="d-flex align-items-center">
                                            <button class="btn btn-danger me-2" onclick="deleteVideo(\'Video-' . $videoId . '\')">Delete</button>
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input" name="coverVideo" value="' . $videoId . '"';

                            if ($videoId == $coverId) {
                                echo ' checked';
                            }
                            echo '>';
                            echo ' <label for="coverVideo1" class="form-check-label">Mark as Cover Video</label>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div></div>';
                            $setIndex++;
                        } ?>
                    </div>
                </div>

                <!-- Add New Video -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <h4>Add New Video Link</h4>
                            <input type="url" id="newVideoInput" class="form-control" placeholder="Enter Youtube Link">
                            <input type="text" id="newVideoCaptionEn" class="form-control mt-2"
                                placeholder="Enter English caption for new Video">
                            <input type="text" id="newVideoCaptionHn" class="form-control mt-2"
                                placeholder="Enter Hindi caption for new Video">
                            <div id="newVideoPreview" class="mt-2"></div>
                            <div id="newVideoError" class="error-message"></div>
                            <button class="btn btn-primary mt-2" onclick="addNewVideo()">Add Video</button>
                        </div>
                    </div>
                </div>


                <!-- Save Changes Button -->
                <button class="btn btn-success mt-4 mb-5" onclick="saveChanges()">Save Changes</button>
                <!-- </form> -->
            </div>
        </div>
    </div>

    <script>
        setTimeout(function() {
            $('.cke_notifications_area').remove();
        }, 1000);

        function addNewVideo() {

            const albumId = new URLSearchParams(window.location.search).get("album_id");
            const VideoList = document.getElementById("VideoList");
            const currentVideoCount = VideoList.getElementsByClassName("Video-row").length;

            if (currentVideoCount >= 12) {
                alert("You cannot add more than 20 Videos.");
                return;
            }

            const VideoInput = $("#newVideoInput").val();
            const captionEn = $("#newVideoCaptionEn").val();
            const captionHn = $("#newVideoCaptionHn").val();

            if (!captionEn || !VideoInput) {
                alert("English Caption and Youtube Video Link are required.");
                return;
            }

            const formData = new FormData();
            formData.append('albumId', albumId);
            formData.append('Video', VideoInput);
            formData.append('caption_en', captionEn);
            formData.append('caption_hn', captionHn);

            $.ajax({
                url: 'editInsertVideoAlbum.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // location.reload();
                },
                error: function(xhr, status, error) {
                    console.error('Error adding Video:', error);
                    // location.reload();
                }
            });
        }

        function deleteVideo(videoId) {
            if (!confirm("Are you sure you want to delete this Video?")) {
                return;
            }
            const isCover = $(`#${videoId}`).find("input[type='radio']").prop("checked");

            if (isCover) {
                alert("Cannot delete the cover Video. Please change the cover Video first.");
                return;
            }
            const Video = videoId.split("-")[1];

            $.ajax({
                url: 'deleteVideoAlnumPics.php',
                type: 'POST',
                data: {
                    videoId: Video
                },
                success: function(response) {
                    const alertType = response.includes("Success!") ? "success" : "danger";
                    const messageTemplate = `
                                                                                                    <div class="alert alert-${alertType} alert-dismissible fade show mt-3" role="alert">
                                                                                                        <strong>${response}</strong>
                                                                                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                                            <span aria-hidden="true">&times;</span>
                                                                                                        </button>
                                                                                                    </div>`;

                    $("#msg").append(messageTemplate);
                    $('html, body').animate({
                        scrollTop: 0
                    }, 'fast');
                    $(`#${videoId}`).remove();
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting Video:', error);
                }
            });
        }

        function saveChanges() {
            const albumId = new URLSearchParams(window.location.search).get("album_id");
            const sub_cat = $("#category").val();
            const enAlbumTitle = $("#enalbumTitle").val();
            const hnAlbumTitle = $("#hnalbumTitle").val();
            // const enAlbumDescription = $("#enalbumDescription").val();
            // const hnAlbumDescription = $("#hnalbumDescription").text();
            const enAlbumDescription = CKEDITOR.instances.enalbumDescription.getData(); // Get CKEditor content
            const hnAlbumDescription = CKEDITOR.instances.hnalbumDescription.getData(); // Get CKEditor content
            const dateOfEvent = $("#dateOfEvent").val();
            const location = $("#location").val();

            const VideosData = [];
            $("#VideoList .Video-row").each(function() {
                const videoId = $(this).attr("id").split("-")[1];
                const videoLink = $(this).find("input[type='text']").eq(0).val(); // First input
                const captionEn = $(this).find("input[type='text']").eq(1).val(); // Middle input
                const captionHn = $(this).find("input[type='text']").eq(2).val(); // Last input

                const isCover = $(this).find("input[type='radio']:checked").val();

                VideosData.push({
                    id: videoId,
                    videoLink: videoLink,
                    captionEn: captionEn,
                    captionHn: captionHn,
                    isCover: isCover
                });
            });

            $.ajax({
                enctype: 'multipart/form-data',
                url: "editVideoAlbumController.php",
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
                    Videos: VideosData
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
