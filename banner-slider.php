<?php
// Define base URL dynamically based on current file location
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    
    $script_path = dirname($_SERVER['SCRIPT_NAME']);
    
    $script_path = rtrim($script_path, '/');

    $base_path = $script_path;

    $base_path = str_replace('/cd-admin', '', $base_path);
    
    return $protocol . $host . $base_path;
}

$base_url = getBaseUrl();
?>

<section class="tvnl-slider" role="region" aria-label="TVNL Highlights Slider">
    <div class="slider-wrapper">
        <div class="slider-carousel owl-carousel" aria-live="polite">
            <?php
            // Fetch sliders from database
            require_once __DIR__ . '/cd-admin/src/database/Database.php';
            
            $database = new Database();
            $pdo = $database->getConnection();
            
            $sql = "SELECT * FROM sliders 
                    WHERE status = 'A' 
                    AND make_slider = 1 
                    AND is_deleted = 0 
                    ORDER BY display_order ASC, created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $totalSlides = count($sliders);
            
            if (empty($sliders)) {
                echo '<!-- No active sliders available -->';
            }
            
            foreach ($sliders as $index => $slider):
                $imagePath = $slider['image_path'];
                if (!empty($imagePath) && $imagePath[0] !== '/') {
                    $imagePath = $base_url . '/' . ltrim($imagePath, '/');
                }
                $slideTitle = !empty($slider['title']) ? htmlspecialchars($slider['title']) : 'TVNL Slider Image';
                $slideSubtitle = !empty($slider['subtitle']) ? htmlspecialchars($slider['subtitle']) : $slideTitle;
            ?>
            <div class="slide" role="group" aria-roledescription="slide" aria-label="<?= ($index + 1) ?> of <?= $totalSlides ?>">
                <img src="<?= htmlspecialchars($imagePath) ?>"
                    alt="<?= $slideTitle ?>"
                    title="<?= $slideSubtitle ?>" loading="lazy" width="100%" height="auto">
            </div>
            <?php endforeach; ?>


        </div>
    </div>
</section>