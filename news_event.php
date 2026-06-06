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
            <select class="session-photo" title="Year">
                <option>2026</option>
                <option>2025</option>
                <option>2024</option>
            </select>
        </div>
    </div>


    <div class="photos-gallery-grid">
        <div class="photos-gallery-card">
            <a href="news_event_gallery.php">
                <img src="assets/images/gallery/activities/actv1.png" alt="News & Events TVNL" title="News & Events TVNL">
                <h5>TVNL is the only government-owned thermal power plant in Jharkhand with an installed capacity.</h5>
            </a>
        </div>
        <div class="photos-gallery-card">
            <a href="news_event_gallery.php">
                <img src="assets/images/gallery/activities/news-b.jpg" alt="News & Events TVNL" title="News & Events TVNL">
                <h5>TVNL is the only government-owned thermal power plant in Jharkhand with an installed capacity.</h5>
            </a>
        </div>
        <div class="photos-gallery-card">
            <a href="news_event_gallery.php">
                <img src="assets/images/gallery/activities/sustainability.jpg" alt="News & Events TVNL" title="News & Events TVNL">
                <h5>TVNL is the only government-owned thermal power plant in Jharkhand with an installed capacity.</h5>
            </a>
        </div>
        <div class="photos-gallery-card">
            <a href="news_event_gallery.php">
                <img src="assets/images/gallery/activities/news-b.jpg" alt="News & Events TVNL" title="News & Events TVNL">
                <h5>TVNL is the only government-owned thermal power plant in Jharkhand with an installed capacity.</h5>
            </a>
        </div>
    </div>

    <ul class="pagination">
        <li class="page-item disabled"><a class="page-link" href="#" title="Previous">Previous</a></li>
        <li class="page-item active"><a class="page-link" href="#">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item"><a class="page-link" href="#" title="Next">Next</a></li>
    </ul>
</section>

<?php include("footer_top.php"); ?>
<?php include "footer1.php"; ?>