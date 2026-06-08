<?php
require_once __DIR__ . '/cd-admin/src/database/Database.php';

$database = new Database();
$pdo = $database->getConnection();

$type = 'Videos';
$query = "SELECT DISTINCT(a.session_year)
          FROM albums a
          WHERE a.is_deleted = 0
          AND a.is_hide = 0
          AND a.type = :type
          ORDER BY a.session_year DESC";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':type', $type, PDO::PARAM_STR);
$stmt->execute();
$sessionYears = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch videos for the selected session (default to first available)
$selectedSession = isset($_GET['session']) ? $_GET['session'] : ($sessionYears[0]['session_year'] ?? '');
?>
<?php include "header1.php"; ?>

<section class="tvnl-banner">
    <img src="assets/images/banner/news-b.jpg" alt="Media - Videos, Tenughat Vidyut Nigam Limited" title="Media - Videos, Tenughat Vidyut Nigam Limited"
        class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">Videos</h2>
        </div>
    </div>
</section>

<section class="gallery-contant">
    <div class="all-session">
        <h2 class="section-title text-primary" title="Videos">▶️ Videos</h2>
        <div class="session-box">
            <label class="session-taxt text-primary">Year</label>
            <select class="session-photo" title="Year" id="event_yr">
                <?php foreach ($sessionYears as $sessionYear): ?>
                    <option value="<?= htmlspecialchars($sessionYear['session_year']) ?>" 
                        <?= ($selectedSession == $sessionYear['session_year']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sessionYear['session_year']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="videos-gallery-grid" id="albums-container">

    </div>

    <ul class="pagination" id="pagination">

    </ul>
</section>

<?php include("footer_top.php"); ?>
<?php include "footer1.php"; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
    let currentPage = 1;
    let totalPages = 1;
    let currentSession = '';

    function fetchAlbumsBySession(sessionYear, page = 1) {
        currentSession = sessionYear;
        
        $('#albums-container').html(`
            <div class="col-12" style="width: 100%;">
                <div class="loading-spinner">
                    <div class="spinner-border" role="status"></div>
                    <p>Loading videos...</p>
                </div>
            </div>
        `);
        
        $.ajax({
            url: 'cd-admin/ajax_call/fetch_albums.php',
            type: 'POST',
            data: {
                data: 'video',
                session_year: sessionYear,
                page: page,
                limit: 8
            },
            dataType: 'json',
            success: function (response) {
                if (response.success && response.albums && response.albums.length > 0) {
                    displayAlbums(response.albums);
                    if (response.pagination) {
                        totalPages = response.pagination.total_pages;
                        currentPage = response.pagination.current_page;
                        updatePagination(response.pagination);
                    }
                    updateURL(sessionYear, page);
                } else {
                    $('#albums-container').html('<div class="col-12 text-center" style="width: 100%;"><p>No videos found for this session year.</p></div>');
                    $('#pagination').html('');
                }
            },
            error: function () {
                $('#albums-container').html('<div class="col-12 text-center" style="width: 100%;"><p>Error loading videos.</p></div>');
                $('#pagination').html('');
            }
        });
    }

    function displayAlbums(albums) {
        let container = $('#albums-container');
        container.empty();
        
        $.each(albums, function (index, album) {
            let album_title = album.name_en || 'Untitled';
            let albumId = album.id || '';
            let videoLink = album.cover_image || '';
            let videoId = extractYouTubeId(videoLink);
            let embedUrl = videoId ? 'https://www.youtube.com/embed/' + videoId : '';
            
            let albumHTML = `
                <div class="videos-gallery-card">
                    <a href="videos-gallery.php?album=${albumId}" title="Videos Gallery">
                        ${embedUrl ? `
                        <iframe class="images-videos"
                            src="${embedUrl}"
                            title="${escapeHtml(album_title)}"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                        ` : `
                        <img src="assets/images/default-video.jpg" alt="${escapeHtml(album_title)}" style="width: 100%; height: 320px; object-fit: cover;">
                        `}
                        <h5 align="justify">${escapeHtml(album_title)}</h5>
                    </a>
                </div>
            `;
            container.append(albumHTML);
        });
    }

    // Helper function to extract YouTube ID
    function extractYouTubeId(url) {
        if (!url) return '';
        const patterns = [
            /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\?\/]+)/,
            /youtube\.com\/watch\?.*v=([^&]+)/,
            /youtube\.com\/embed\/([^?]+)/
        ];
        for (let pattern of patterns) {
            let match = url.match(pattern);
            if (match && match[1]) {
                return match[1];
            }
        }
        return url;
    }

    function updatePagination(pagination) {
        let current = pagination.current_page;
        let total = pagination.total_pages;
        
        if (total <= 1) {
            $('#pagination').html('');
            return;
        }
        
        let paginationHtml = '';
        
        // Previous button
        if (current > 1) {
            paginationHtml += `<li class="page-item"><a class="page-link" onclick="changePage(${current - 1}); return false;">Previous</a></li>`;
        } else {
            paginationHtml += `<li class="page-item disabled"><span class="page-link">Previous</span></li>`;
        }
        
        // Page numbers
        let startPage = Math.max(1, current - 2);
        let endPage = Math.min(total, current + 2);
        
        if (startPage > 1) {
            paginationHtml += `<li class="page-item"><a class="page-link" onclick="changePage(1); return false;">1</a></li>`;
            if (startPage > 2) paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === current) {
                paginationHtml += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else {
                paginationHtml += `<li class="page-item"><a class="page-link" onclick="changePage(${i}); return false;">${i}</a></li>`;
            }
        }
        
        if (endPage < total) {
            if (endPage < total - 1) paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            paginationHtml += `<li class="page-item"><a class="page-link" onclick="changePage(${total}); return false;">${total}</a></li>`;
        }
        
        // Next button
        if (current < total) {
            paginationHtml += `<li class="page-item"><a class="page-link" onclick="changePage(${current + 1}); return false;">Next</a></li>`;
        } else {
            paginationHtml += `<li class="page-item disabled"><span class="page-link">Next</span></li>`;
        }
        
        $('#pagination').html(paginationHtml);
    }

    function changePage(page) {
        if (page < 1 || page > totalPages || page === currentPage) {
            return;
        }
        currentPage = page;
        fetchAlbumsBySession($('#event_yr').val(), page);
        $('html, body').animate({ scrollTop: $('#albums-container').offset().top - 50 }, 500);
    }

    function updateURL(session, page) {
        let url = new URL(window.location.href);
        if (session) url.searchParams.set('session', session);
        if (page > 1) url.searchParams.set('page', page);
        else url.searchParams.delete('page');
        window.history.pushState({}, '', url);
    }

    function getURLParams() {
        let urlParams = new URLSearchParams(window.location.search);
        return {
            session: urlParams.get('session'),
            page: parseInt(urlParams.get('page')) || 1
        };
    }

    function decodeHtml(html) {
        if (!html) return '';
        const txt = document.createElement("textarea");
        txt.innerHTML = html;
        return txt.value;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const decoded = decodeHtml(text);
        const div = document.createElement('div');
        div.textContent = decoded;
        return div.innerHTML;
    }

    $(document).ready(function () {
        let params = getURLParams();
        
        if (params.session && $('#event_yr option[value="' + params.session + '"]').length) {
            $('#event_yr').val(params.session);
        }
        
        currentPage = params.page;
        let session = $('#event_yr').val();
        
        if (session) {
            fetchAlbumsBySession(session, currentPage);
        }

        $('#event_yr').on('change', function () {
            currentPage = 1;
            fetchAlbumsBySession($(this).val(), 1);
            updateURL($(this).val(), 1);
        });
        
        window.addEventListener('popstate', function () {
            let params = getURLParams();
            if (params.session && $('#event_yr option[value="' + params.session + '"]').length) {
                $('#event_yr').val(params.session);
            }
            currentPage = params.page;
            fetchAlbumsBySession(params.session || $('#event_yr').val(), params.page);
        });
    });
</script>