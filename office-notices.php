<?php
// office-notice.php - Dynamic Office Orders/Notices Page

require_once 'cd-admin/src/database/Database.php';

$database = new Database();
$pdo = $database->getConnection();

// Function to fix PDF path
function getCorrectPdfPath($notice_path) {
    if (empty($notice_path)) {
        return '';
    }
    
    // Remove leading slash if exists
    $path = ltrim($notice_path, '/');
    
    // If path already starts with cd-admin/src, return as is
    if (strpos($path, 'cd-admin/src/') === 0) {
        return '/' . $path;
    }
    
    // Add the correct base path
    return '/tvnl-website/cd-admin/src/' . $path;
}

define('BASE_URL', '/tvnl-website');
define('ADMIN_URL', BASE_URL . '/cd-admin/src');

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$selectedYear = isset($_GET['year']) && !empty($_GET['year']) ? (int)$_GET['year'] : null;

try {
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total 
                   FROM notices n
                   LEFT JOIN child_sub_category csc ON n.notice_childsubcategory = csc.id
                   WHERE n.is_deleted = '0'
                   AND n.status = 'A'
                   AND csc.child_sub_category_name IN ('Circulars/Office Orders', 'Office Order', 'Circulars')";
    
    if ($selectedYear) {
        $countQuery .= " AND YEAR(n.notice_dated) = :year";
    }
    
    $countStmt = $pdo->prepare($countQuery);
    if ($selectedYear) $countStmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
    $countStmt->execute();
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRecords / $limit);
    
    // Fetch office notices with pagination
    $query = "SELECT 
                n.id,
                n.domain_id,
                n.uniq_id,
                n.notice_category,
                n.notice_subcategory,
                n.notice_childsubcategory,
                n.session_year,
                n.notice_type,
                n.notice_ref_no,
                n.notice_dated,
                n.notice_title,
                n.notice_path,
                n.notice_url,
                n.url_tab_open,
                n.notice_new_tag,
                n.status,
                n.is_deleted,
                n.created_at,
                cm.category_name as main_category_name,
                sc.sub_category_name as sub_category_name,
                csc.child_sub_category_name as child_category_name
              FROM notices n
              LEFT JOIN category_master cm ON n.notice_category = cm.id
              LEFT JOIN sub_category sc ON n.notice_subcategory = sc.id
              LEFT JOIN child_sub_category csc ON n.notice_childsubcategory = csc.id
              WHERE n.is_deleted = '0'
              AND n.status = 'A'
              AND csc.child_sub_category_name IN ('Circulars/Office Orders', 'Office Order', 'Circulars')";

    if ($selectedYear) {
        $query .= " AND YEAR(n.notice_dated) = :year";
    }

    $query .= " ORDER BY n.notice_dated DESC, n.id DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    if ($selectedYear) $stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $officeNotices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fix paths for each notice
    foreach ($officeNotices as &$notice) {
        if (!empty($notice['notice_path'])) {
            $notice['pdf_url'] = getCorrectPdfPath($notice['notice_path']);
            $notice['download_url'] = getCorrectPdfPath($notice['notice_path']);
        } else {
            $notice['pdf_url'] = '';
            $notice['download_url'] = '';
        }
    }
    
    // Get recent notices for sidebar
    $recentQuery = "SELECT 
                      n.id, 
                      n.notice_title,
                      n.notice_dated,
                      n.uniq_id,
                      n.notice_path,
                      csc.child_sub_category_name as category_name
                    FROM notices n
                    LEFT JOIN child_sub_category csc ON n.notice_childsubcategory = csc.id
                    WHERE n.is_deleted = '0'
                    AND n.status = 'A'
                    ORDER BY n.notice_dated DESC 
                    LIMIT 5";
    
    $recentStmt = $pdo->prepare($recentQuery);
    $recentStmt->execute();
    $recentNotices = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fix paths for recent notices
    foreach ($recentNotices as &$recent) {
        if (!empty($recent['notice_path'])) {
            $recent['pdf_url'] = getCorrectPdfPath($recent['notice_path']);
        }
    }
    
    // Get year filter options
    $yearQuery = "SELECT DISTINCT YEAR(notice_dated) as year 
                  FROM notices n
                  LEFT JOIN child_sub_category csc ON n.notice_childsubcategory = csc.id
                  WHERE n.is_deleted = '0'
                  AND n.status = 'A'
                  AND csc.child_sub_category_name IN ('Circulars/Office Orders', 'Office Order', 'Circulars')
                  ORDER BY year DESC";
    
    $yearStmt = $pdo->prepare($yearQuery);
    $yearStmt->execute();
    $availableYears = $yearStmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch(PDOException $e) {
    echo "Database Error: " . $e->getMessage();
    $officeNotices = [];
    $recentNotices = [];
    $availableYears = [];
    $totalPages = 0;
    $page = 1;
}
?>

<?php include "header1.php"; ?>

<style>


/* Layout */
.row-flex {
    display: flex;
    gap: 40px;
    flex-wrap: wrap;
}

.main-content {
    flex: 2;
    min-width: 250px;
}

.sidebar {
    flex: 1;
    min-width: 200px;
}

/* Section Titles */
.section-title {
    font-size: 1.3rem;
    font-weight: normal;
    border-bottom: 2px solid #8b0000;
    padding-bottom: 8px;
    margin-bottom: 25px;
    letter-spacing: 0.5px;
}

/* Notice Items */
.notice-item {
    border-bottom: 1px solid #ddd;
    padding: 20px 0;
}

.notice-item:last-child {
    border-bottom: none;
}

.notice-date {
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 5px;
}

.notice-title {
    font-size: 1.1rem;
    font-weight: 500;
    margin-bottom: 10px;
}

.notice-title a {
    color: #222;
    text-decoration: none;
}

.notice-title a:hover {
    color: #8b0000;
    text-decoration: underline;
}

.notice-meta {
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 12px;
}

.notice-meta span {
    margin-right: 15px;
}

/* Badge */
.badge-new {
    background: #8b0000;
    color: #fff;
    font-size: 0.65rem;
    padding: 2px 8px;
    margin-left: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Buttons */
.btn-group {
    margin-top: 12px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn {
    font-family: inherit;
    font-size: 0.8rem;
    padding: 6px 14px;
    text-decoration: none;
    background: none;
    border: 1px solid #999;
    color: #333;
    cursor: pointer;
    display: inline-block;
    transition: all 0.2s;
}

.btn:hover {
    background: #8b0000;
    border-color: #8b0000;
    color: #fff;
}

.btn-pdf {
    border-color: #8b0000;
    color: #8b0000;
}

.btn-pdf:hover {
    background: #8b0000;
    color: #fff;
}

/* Pagination */
.pagination {
    margin-top: 30px;
    text-align: center;
}

.pagination a, .pagination span {
    display: inline-block;
    padding: 8px 14px;
    margin: 0 3px;
    border: 1px solid #ddd;
    text-decoration: none;
    color: #333;
    font-size: 0.85rem;
}

.pagination a:hover {
    background: #8b0000;
    border-color: #8b0000;
    color: #fff;
}

.pagination .active {
    background: #8b0000;
    border-color: #8b0000;
    color: #fff;
}

.pagination .disabled {
    color: #ccc;
    cursor: not-allowed;
}

/* Sidebar */
.sidebar-box {
    margin-bottom: 30px;
}

.sidebar-title {
    font-size: 1.1rem;
    font-weight: normal;
    border-bottom: 1px solid #ddd;
    padding-bottom: 6px;
    margin-bottom: 15px;
}

/* Search Box */
.search-box {
    margin-bottom: 30px;
}

.search-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ccc;
    font-family: inherit;
    font-size: 0.9rem;
}

.search-input:focus {
    outline: none;
    border-color: #8b0000;
}

.search-btn {
    margin-top: 8px;
    width: 100%;
    background: #2c2c2c;
    border-color: #2c2c2c;
    color: #fff;
}

.search-btn:hover {
    background: #8b0000;
    border-color: #8b0000;
}

/* Year Filter */
.year-filter {
    margin-bottom: 30px;
}

.year-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.year-link {
    display: inline-block;
    padding: 5px 12px;
    border: 1px solid #ddd;
    text-decoration: none;
    color: #333;
    font-size: 0.8rem;
}

.year-link:hover, .year-link.active {
    background: #8b0000;
    border-color: #8b0000;
    color: #fff;
}

/* Recent List */
.recent-list {
    list-style: none;
}

.recent-list li {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.recent-list li:last-child {
    border-bottom: none;
}

.recent-list a {
    color: #333;
    text-decoration: none;
    font-size: 0.85rem;
}

.recent-list a:hover {
    color: #8b0000;
    text-decoration: underline;
}

.recent-date {
    font-size: 0.7rem;
    color: #888;
    display: block;
    margin-top: 4px;
}

/* Info Box */
.info-box {
    background: #f9f9f5;
    border: 1px solid #eee;
    padding: 15px;
}

.info-box ul {
    list-style: none;
    padding-left: 0;
}

.info-box li {
    padding: 6px 0;
    font-size: 0.85rem;
    color: #555;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #888;
    font-style: italic;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    display: block;
}

/* Footer */
.footer-note {
    text-align: center;
    padding: 30px 0;
    margin-top: 40px;
    border-top: 1px solid #ddd;
    font-size: 0.75rem;
    color: #888;
}

/* Responsive */
@media (max-width: 768px) {
    .row-flex {
        flex-direction: column;
        gap: 20px;
    }
    
    .page-banner h1 {
        font-size: 1.5rem;
    }
    
    .btn-group {
        gap: 8px;
    }
}
</style>

<!-- Banner Section -->
<section class="tvnl-banner">
    <img src="assets/images/banner/board-banner.jpg" alt="Public Notices" title="Public Notices"
        class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">Public Notices</h2>
        </div>
    </div>
</section>

<!-- Main Content -->

    <div class="container my-5">
        <div class="row-flex">
            <!-- Left Column - Office Notices List -->
            <div class="main-content">
                <h2 class="section-title">Office Orders / Circulars</h2>
                
                <?php if (empty($officeNotices)): ?>
                    <div class="empty-state">
                        <i>📄</i>
                        <p>No office orders or circulars available at this time.</p>
                        <p style="font-size: 0.8rem; margin-top: 10px;">Please check back later for updates.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($officeNotices as $notice): ?>
                        <div class="notice-item">
                            <div class="notice-date">
                                <?= date('d F Y', strtotime($notice['notice_dated'])) ?>
                                <?php if (!empty($notice['notice_ref_no'])): ?>
                                    | Ref No: <?= htmlspecialchars($notice['notice_ref_no']) ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="notice-title">
                                <a href="notice-detail.php?id=<?= $notice['id'] ?>">
                                    <?= htmlspecialchars($notice['notice_title']) ?>
                                </a>
                                <?php if ($notice['notice_new_tag'] == 'Y'): ?>
                                    <span class="badge-new">NEW</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="notice-meta">
                                <?php if (!empty($notice['child_category_name'])): ?>
                                    <span>📌 <?= htmlspecialchars($notice['child_category_name']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($notice['session_year'])): ?>
                                    <span>📅 Session: <?= htmlspecialchars($notice['session_year']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($notice['uniq_id'])): ?>
                                    <span>🆔 ID: <?= htmlspecialchars($notice['uniq_id']) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($notice['pdf_url'])): ?>
                                <div class="btn-group">
                                    <a href="<?= $notice['pdf_url'] ?>" class="btn btn-pdf" target="_blank">📖 View PDF</a>
                                    <a href="<?= $notice['download_url'] ?>" class="btn btn-pdf" download>⬇️ Download PDF</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?><?= $selectedYear ? '&year=' . $selectedYear : '' ?>">&laquo; Previous</a>
                        <?php else: ?>
                            <span class="disabled">&laquo; Previous</span>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php $paginationUrl = "?page=$i" . ($selectedYear ? "&year=$selectedYear" : ""); ?>
                            <?php if ($i == $page): ?>
                                <span class="active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= $paginationUrl ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?><?= $selectedYear ? '&year=' . $selectedYear : '' ?>">Next &raquo;</a>
                        <?php else: ?>
                            <span class="disabled">Next &raquo;</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Right Column - Sidebar -->
            <div class="sidebar">
                <!-- Search Box -->
                <div class="search-box">
                    <h3 class="sidebar-title">Search</h3>
                    <input type="text" id="searchInput" class="search-input" placeholder="Search by title or reference...">
                    <button onclick="searchNotices()" class="btn search-btn">Search</button>
                </div>
                
                <!-- Year Filter -->
                <?php if (!empty($availableYears)): ?>
                <div class="year-filter">
                    <h3 class="sidebar-title">Filter by Year</h3>
                    <div class="year-list">
                        <a href="office-notices.php" class="year-link <?= !isset($_GET['year']) ? 'active' : '' ?>">All</a>
                        <?php foreach ($availableYears as $year): ?>
                            <a href="office-notices.php?year=<?= $year ?>" class="year-link <?= (isset($_GET['year']) && $_GET['year'] == $year) ? 'active' : '' ?>"><?= $year ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Recent Notices -->
                <div class="sidebar-box">
                    <h3 class="sidebar-title">Recent Updates</h3>
                    <?php if (empty($recentNotices)): ?>
                        <p style="color: #888; font-size: 0.8rem;">No recent notices</p>
                    <?php else: ?>
                        <ul class="recent-list">
                            <?php foreach ($recentNotices as $recent): ?>
                                <li>
                                    <a href="notice-detail.php?id=<?= $recent['id'] ?>">
                                        <?= htmlspecialchars(substr($recent['notice_title'], 0, 55)) ?>
                                        <?php if (strlen($recent['notice_title']) > 55): ?>...<?php endif; ?>
                                    </a>
                                    <span class="recent-date"><?= date('d M Y', strtotime($recent['notice_dated'])) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                
                <!-- Information Box -->
                <div class="sidebar-box info-box">
                    <h3 class="sidebar-title" style="border-bottom-color: #ccc;">Information</h3>
                    <ul>
                        <li>✓ All office orders are official</li>
                        <li>✓ PDF documents available for download</li>
                        <li>✓ Updates are published regularly</li>
                        <li>✓ For queries: info@tvnl.com</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php include "footer_top.php"; ?>
<?php include "footer1.php"; ?>

<script>
// Search functionality
function searchNotices() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
    const noticeItems = document.querySelectorAll('.notice-item');
    
    if (searchTerm === '') {
        // Show all if search is empty
        noticeItems.forEach(item => {
            item.style.display = 'block';
        });
        return;
    }
    
    let foundCount = 0;
    noticeItems.forEach(item => {
        const title = item.querySelector('.notice-title a').innerText.toLowerCase();
        const meta = item.querySelector('.notice-meta')?.innerText.toLowerCase() || '';
        const refNo = item.querySelector('.notice-date')?.innerText.toLowerCase() || '';
        
        if (title.includes(searchTerm) || meta.includes(searchTerm) || refNo.includes(searchTerm)) {
            item.style.display = 'block';
            foundCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show no results message if needed
    let noResultsMsg = document.querySelector('.no-results-msg');
    if (foundCount === 0 && noticeItems.length > 0) {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'empty-state';
            noResultsMsg.innerHTML = '<i>🔍</i><p>No matching office orders found.</p><p style="font-size:0.8rem">Try different keywords.</p>';
            document.querySelector('.main-content').appendChild(noResultsMsg);
        }
        noResultsMsg.style.display = 'block';
    } else if (noResultsMsg) {
        noResultsMsg.style.display = 'none';
    }
}

// Search on enter key
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchNotices();
    }
});

// Clear search and reset
document.getElementById('searchInput').addEventListener('input', function() {
    if (this.value === '') {
        const noticeItems = document.querySelectorAll('.notice-item');
        noticeItems.forEach(item => {
            item.style.display = 'block';
        });
        const noResultsMsg = document.querySelector('.no-results-msg');
        if (noResultsMsg) {
            noResultsMsg.style.display = 'none';
        }
    }
});
</script>
</body>
</html>