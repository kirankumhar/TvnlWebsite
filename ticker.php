<?php
// Fetch ONLY Ticker Notices from database using dynamic category matching
require_once __DIR__ . '/cd-admin/src/database/Database.php';

$database = new Database();
$pdo = $database->getConnection();

// Dynamic query - no hardcoded IDs
$sql = "SELECT n.* 
        FROM notices n
        INNER JOIN category_master cm ON cm.id = n.notice_category
        INNER JOIN sub_category sc ON sc.id = n.notice_subcategory
        INNER JOIN child_sub_category csc ON csc.id = n.notice_childsubcategory
        WHERE n.status = 'A' 
          AND cm.category_name = 'Notice'
          AND sc.sub_category_name = 'Notice'
          AND csc.child_sub_category_name = 'Ticker'
        ORDER BY n.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$tickerNotices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php if (!empty($tickerNotices)): ?>
<div class="notice-bar" role="region" aria-label="Latest News Updates">
    <button id="toggleBtn" aria-pressed="false" aria-controls="noticeTrack" title="Pause or Play News Updates"
        aria-label="Pause or Play scrolling news updates">
        ⏸
    </button>
    <div class="notice-wrapper" role="marquee" aria-live="polite">
        <div class="notice-track" id="noticeTrack">
            <ul>
                <?php foreach ($tickerNotices as $notice): ?>
                    <li tabindex="0" role="listitem" title="<?= htmlspecialchars($notice['notice'] ?? '') ?>">
                        <?= htmlspecialchars($notice['notice_title'] ?? '') ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>