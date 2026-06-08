<?php

require_once __DIR__ . '/cd-admin/src/database/Database.php';

$database = new Database();
$pdo = $database->getConnection();

$type = 'Press Clips';
$query = "SELECT DISTINCT(a.session_year)
          FROM albums a
          WHERE a.is_deleted = 0
          AND a.is_hide = 0
          AND a.type = :type
          ORDER BY a.session_year DESC";

// Prepare and execute the statement
$stmt = $pdo->prepare($query);
$stmt->bindParam(':type', $type, PDO::PARAM_STR);
$stmt->execute();
$sessionYears = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<?php include "header1.php"; ?>

<section class="tvnl-banner">
    <img src="assets/images/banner/news-b.jpg" alt="Media - Media Coverage, Tenughat Vidyut Nigam Limited" title="Media - Media Coverage, Tenughat Vidyut Nigam Limited"
        class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">Media Coverage</h2>
        </div>
    </div>
</section>

<section class="gallery-contant">
    <div class="all-session">
        <h2 class="section-title text-primary" title="Media Coverage">📰 Media Coverage</h2>
        <div class="session-box">
            <label class="session-taxt text-primary">Year</label>
            <select class="session-photo" title="Year" id="event_yr">
                <?php
                    foreach ($sessionYears as $sessionYear) {
                        echo "<option value='" . $sessionYear['session_year'] . "'>" . $sessionYear['session_year'] . "</option>";
                    }
                    ?>
            </select>
        </div>
    </div>

    <div class="photos-gallery-grid" id="albums-container">
        
    </div>

    <!-- <ul class="pagination">
        <li class="page-item disabled"><a class="page-link" href="#" title="Previous">Previous</a></li>
        <li class="page-item active"><a class="page-link" href="#">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item"><a class="page-link" href="#" title="Next">Next</a></li>
    </ul> -->
</section>

<?php include("footer_top.php"); ?>
<?php include "footer1.php"; ?>


<script>
    function fetchAlbumsBySession(sessionYear) {
        $.ajax({
            url: 'cd-admin/ajax_call/fetch_albums.php',
            type: 'POST',
            data: {
                data: 'press',
                session_year: sessionYear
            },
            dataType: 'json',
            beforeSend: function () {
                // Show loading spinner
                $('#albums-container').html(`
                <div class="col-12 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
            },
            success: function (response) {
                // Clear existing albums
                $('#albums-container').empty();

                if (response.success && response.albums.length > 0) {
                    // Loop through albums and append them to container
                    $.each(response.albums, function (index, album) {
                        let album_title = album.name_en;
                        let album_date = album.event_date || '';

                        // Format date if exists
                        let formattedDate = '';
                        if (album_date) {
                            let date = new Date(album_date);
                            let day = date.getDate();
                            let month = date.toLocaleString('default', { month: 'short' });
                            let year = date.getFullYear();
                            formattedDate = `${day}<sup>${getDaySuffix(day)}</sup> ${month} ${year}`;
                        }

                        let albumHTML = ` 
                        <div class="photos-gallery-card">
                            <a href="media-coverage-gallery.php?album=${album.uniq_id || album.id}">
                                <img src="${album.cover_image}" alt="Media Coverage,  TVNL" title="Media Coverage,  TVNL">
                                <h5>${album_title}</h5>
                            </a>
                        </div>
                    `;

                        $('#albums-container').append(albumHTML);
                    });

                    // Re-initialize SAL animations if available
                    if (typeof sal !== 'undefined') {
                        sal.reset();
                    }
                } else {
                    // Show no records found message
                    $('#albums-container').html(`
                    <div class="col-12 text-center py-5">
                        <div class="no-data-found">
                            <i class="icon-61" style="font-size: 48px; color: #ccc;"></i>
                            <p class="mt-3" style="color: #666; font-size: 16px;">No press clips found for this session year.</p>
                        </div>
                    </div>
                `);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error fetching albums:', error);
                $('#albums-container').html(`
                <div class="col-12 text-center py-5">
                    <div class="error-message">
                        <i class="fa fa-exclamation-circle" style="font-size: 48px; color: #dc3545;"></i>
                        <p class="mt-3" style="color: #666; font-size: 16px;">Error loading albums. Please try again later.</p>
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="fetchAlbumsBySession($('#event_yr').val())">
                            <i class="fa fa-refresh"></i> Retry
                        </button>
                    </div>
                </div>
            `);
            }
        });
    }

    // Helper function to get day suffix (st, nd, rd, th)
    function getDaySuffix(day) {
        if (day >= 11 && day <= 13) {
            return 'th';
        }
        switch (day % 10) {
            case 1: return 'st';
            case 2: return 'nd';
            case 3: return 'rd';
            default: return 'th';
        }
    }

    $(document).ready(function () {
        // Get initial session year
        let session = $('#event_yr').val();

        // Fetch albums for current year initially
        if (session) {
            fetchAlbumsBySession(session);
        }

        // Handle session year selection
        $('#event_yr').on('change', function () {
            fetchAlbumsBySession($(this).val());
        });
    });
</script>