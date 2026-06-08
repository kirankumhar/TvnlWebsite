<?php

require_once __DIR__ . '/cd-admin/src/database/Database.php';

$database = new Database();
$pdo = $database->getConnection();

$query = "SELECT DISTINCT(a.session_year)
          FROM news a
          WHERE a.is_deleted = '0'
          AND a.is_hide = 'N'
          ORDER BY a.session_year DESC";

// Prepare and execute the statement
$stmt = $pdo->prepare($query);
$stmt->execute();
$sessionYears = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include "header1.php"; ?>

<section class="tvnl-banner">
    <img src="assets/images/banner/news-b.jpg" alt="Media - News & Events, Tenughat Vidyut Nigam Limited" title="Media - News & Events, Tenughat Vidyut Nigam Limited"
        class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">News & Events</h2>
        </div>
    </div>
</section>

<section class="gallery-contant">
    <div class="all-session">
        <h2 class="section-title text-primary" title="News & Events">📰 News & Events</h2>
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
            url: 'cd-admin/ajax_call/fetch_news.php',
            type: 'POST',
            data: {
                data: 'photo',
                session_year: sessionYear
            },
            dataType: 'json',
            success: function (response) {

                $('#albums-container').empty();

                if (response.success && response.albums.length > 0) {

                    $.each(response.albums, function (index, album) {

                        // ----- Indian Date Format -----
                        let day = '';
                        let monthYear = '';

                        if (album.event_date) {
                            let dateObj = new Date(album.event_date);

                            day = dateObj.getDate();

                            let options = { month: 'short', year: 'numeric' };
                            monthYear = dateObj.toLocaleDateString('en-IN', options);
                        }

                        let albumHTML = `
                        <div class="photos-gallery-card">
                            <a href="news_event_gallery.php?album=${album.id}">
                                <img src="${album.cover_image}" alt="${album.title}" title="${album.title}">
                                <h5>${album.title}</h5>
                            </a>
                        </div>
                    `;

                        $('#albums-container').append(albumHTML);
                    });

                    // Reinitialize SAL animation (if using)
                    if (typeof sal !== 'undefined') {
                        sal();
                    }

                } else {
                    $('#albums-container').html(
                        '<div class="col-12 text-center"><p>No albums found for this session year.</p></div>'
                    );
                }
            },
            error: function (xhr, status, error) {
                console.error('Error fetching albums:', error);
                $('#albums-container').html(
                    '<div class="col-12 text-center"><p>Error loading albums. Please try again later.</p></div>'
                );
            }
        });
    }

    $(document).ready(function () {

        let session = $('#event_yr').val();
        fetchAlbumsBySession(session);

        $('#event_yr').on('change', function () {
            fetchAlbumsBySession($(this).val());
        });

    });
</script>
