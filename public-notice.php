<?php
// public-notice.php - Complete working page with CORRECTED PDF paths

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

// Alternative: Define base URL constant
define('BASE_URL', '/tvnl-website');
define('ADMIN_URL', BASE_URL . '/cd-admin/src');

try {
    
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
              AND csc.child_sub_category_name = 'Public Notices'
              ORDER BY n.notice_dated DESC, n.id DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $publicNotices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fix paths for each notice
    foreach ($publicNotices as &$notice) {
        if (!empty($notice['notice_path'])) {
            // Convert path: /uploads//Notice/2026/06/634981.pdf 
            // To: /tvnl-website/cd-admin/src/uploads/Notice/2026/06/634981.pdf
            $notice['pdf_url'] = getCorrectPdfPath($notice['notice_path']);
            $notice['download_url'] = getCorrectPdfPath($notice['notice_path']);
        } else {
            $notice['pdf_url'] = '';
            $notice['download_url'] = '';
        }
    }
    
    // If no notices found with child_sub_category_name, fetch all active notices
    if (empty($publicNotices)) {
        $fallbackQuery = "SELECT 
                            n.*,
                            csc.child_sub_category_name as child_category_name
                          FROM notices n
                          LEFT JOIN child_sub_category csc ON n.notice_childsubcategory = csc.id
                          WHERE n.is_deleted = '0'
                          AND n.status = 'A'
                          ORDER BY n.notice_dated DESC";
        
        $stmt = $pdo->prepare($fallbackQuery);
        $stmt->execute();
        $publicNotices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fix paths for fallback notices
        foreach ($publicNotices as &$notice) {
            if (!empty($notice['notice_path'])) {
                $notice['pdf_url'] = getCorrectPdfPath($notice['notice_path']);
                $notice['download_url'] = getCorrectPdfPath($notice['notice_path']);
            } else {
                $notice['pdf_url'] = '';
                $notice['download_url'] = '';
            }
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
    
    // Get ticker notices
    $tickerQuery = "SELECT 
                      n.id, 
                      n.notice_title,
                      n.notice_dated
                    FROM notices n
                    WHERE n.is_deleted = '0'
                    AND n.status = 'A'
                    AND n.notice_new_tag = 'Y'
                    ORDER BY n.notice_dated DESC
                    LIMIT 10";
    
    $tickerStmt = $pdo->prepare($tickerQuery);
    $tickerStmt->execute();
    $tickerNotices = $tickerStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    echo "Database Error: " . $e->getMessage();
    $publicNotices = [];
    $recentNotices = [];
    $tickerNotices = [];
}
?>
<?php include "header1.php"; ?>
<style>
    
/* Notice Cards - Minimal Design */
.notice-card {
    background: white;
    padding: 24px;
    margin-bottom: 20px;
    border: 1px solid var(--gray-200);
    transition: all 0.2s ease;
}

.notice-card:hover {
    border-color: var(--gray-300);
    background: var(--gray-50);
}

/* Notice Meta Information */
.notice-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 12px;
    font-size: 13px;
    color: var(--gray-600);
}

.notice-meta span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.notice-badge {
    background: var(--gray-100);
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 12px;
}

/* Notice Title */
.notice-title {
    margin: 0 0 12px 0;
    font-size: 18px;
    font-weight: 500;
    line-height: 1.4;
}

.notice-title a {
    color: var(--gray-800);
    text-decoration: none;
}

.notice-title a:hover {
    color: var(--primary);
}

/* Notice Description */
.notice-description {
    font-size: 14px;
    color: var(--gray-600);
    margin-bottom: 16px;
}

/* Footer with Actions */
.notice-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid var(--gray-200);
    margin-top: 8px;
}

.btn-group-actions {
    display: flex;
    gap: 12px;
}

.btn-view-pdf,
.btn-download-pdf {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    font-size: 13px;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.2s;
}

.btn-view-pdf {
    color: var(--primary);
    background: transparent;
    border: 1px solid var(--gray-300);
}

.btn-view-pdf:hover {
    background: var(--gray-100);
    border-color: var(--primary);
}

.btn-download-pdf {
    color: white;
    background: var(--primary);
    border: 1px solid var(--primary);
}

.btn-download-pdf:hover {
    background: var(--primary-dark);
}

.pdf-icon {
    color: var(--red-500);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--gray-50);
    border: 1px solid var(--gray-200);
}

.empty-state i {
    font-size: 48px;
    color: var(--gray-300);
}

/* Sidebar Cards */
.sidebar-card {
    background: white;
    border: 1px solid var(--gray-200);
    padding: 20px;
    margin-bottom: 24px;
}

.sidebar-title {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 16px 0;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--primary);
    display: inline-block;
}

/* Notice List in Sidebar */
.notice-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.notice-list li {
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--gray-200);
}

.notice-list li:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.notice-list a {
    display: flex;
    gap: 12px;
    text-decoration: none;
    color: var(--gray-700);
    font-size: 14px;
}

.notice-list a:hover {
    color: var(--primary);
}

.notice-list .date {
    display: block;
    font-size: 12px;
    color: var(--gray-600);
    margin-top: 4px;
}

/* Form Controls */
.form-control {
    border: 1px solid var(--gray-300);
    border-radius: 4px;
    padding: 8px 12px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
}

.btn-primary {
    background: var(--primary);
    border: none;
    padding: 8px 16px;
    color: white;
    cursor: pointer;
}

.btn-primary:hover {
    background: var(--primary-dark);
}

/* Badge */
.badge {
    background: var(--gray-100);
    color: var(--gray-700);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
}

/* Debug path - hide in production */
.debug-path {
    display: none;
}

/* Responsive */
@media (max-width: 768px) {
    .banner-title {
        font-size: 32px;
    }
    
    .notice-card {
        padding: 16px;
    }
    
    .notice-footer {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }
    
    .btn-group-actions {
        width: 100%;
    }
    
    .btn-view-pdf,
    .btn-download-pdf {
        flex: 1;
        justify-content: center;
    }
    
    .ticker-container {
        font-size: 12px;
    }
    
    .notice-meta {
        gap: 12px;
        font-size: 12px;
    }
}

@media (max-width: 576px) {
    .notice-title {
        font-size: 16px;
    }
    
    .banner-title {
        font-size: 24px;
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

<div class="container my-5">
    <div class="row g-4">
        <!-- Main Content - Left Side -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0 fw-bold">
                    <i class="bi bi-file-text-fill text-primary me-2"></i>All Public Notices
                </h3>
                <span class="badge bg-primary rounded-pill px-3 py-2">
                    <?= count($publicNotices) ?> Notices
                </span>
            </div>
            
            <?php if (empty($publicNotices)): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h5 class="mt-3">No Public Notices Available</h5>
                    <p class="mb-0">Please check back later for updates.</p>
                </div>
            <?php else: ?>
                <?php foreach ($publicNotices as $notice): ?>
                    <div class="notice-card">
                        <div class="notice-meta">
                            <span>
                                <i class="bi bi-calendar3"></i> 
                                <?= date('d F Y', strtotime($notice['notice_dated'])) ?>
                            </span>
                            <?php if (!empty($notice['child_category_name'])): ?>
                                <span class="notice-badge">
                                    <i class="bi bi-tag"></i> 
                                    <?= htmlspecialchars($notice['child_category_name']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($notice['notice_ref_no'])): ?>
                                <span>
                                    <i class="bi bi-hash"></i> 
                                    Ref: <?= htmlspecialchars($notice['notice_ref_no']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($notice['notice_new_tag'] == 'Y'): ?>
                                <span class="notice-badge" style="background:#ffc107; color:#000;">
                                    <i class="bi bi-star-fill"></i> NEW
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <h4 class="notice-title">
                            <a href="notice-detail.php?id=<?= $notice['id'] ?>">
                                <?= htmlspecialchars($notice['notice_title']) ?>
                            </a>
                        </h4>
                        
                        <?php if (!empty($notice['session_year'])): ?>
                            <div class="notice-description">
                                <i class="bi bi-calendar-week"></i> 
                                Session Year: <?= htmlspecialchars($notice['session_year']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="notice-footer">
                            <div class="btn-group-actions">
                                <!-- VIEW PDF BUTTON - Opens PDF in browser with CORRECT path -->
                                <?php if (!empty($notice['pdf_url'])): ?>
                                    <a href="<?= $notice['pdf_url'] ?>" 
                                       class="btn-view-pdf" 
                                       target="_blank"
                                       data-tooltip="View PDF in browser">
                                        <i class="bi bi-eye"></i> View PDF
                                    </a>
                                <?php endif; ?>
                                
                                <!-- DOWNLOAD PDF BUTTON - Downloads PDF directly with CORRECT path -->
                                <?php if (!empty($notice['download_url'])): ?>
                                    <a href="<?= $notice['download_url'] ?>" 
                                       class="btn-download-pdf" 
                                       download
                                       target="_blank"
                                       data-tooltip="Download PDF to computer">
                                        <i class="bi bi-download"></i> Download PDF
                                    </a>
                                <?php endif; ?>
                               
                            </div>
                        </div>
                        
                        <!-- Debug info - Remove in production -->
                        <?php if (!empty($notice['pdf_url'])): ?>
                            <div class="debug-path">
                                <small>PDF Path: <?= htmlspecialchars($notice['pdf_url']) ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar - Right Side -->
        <div class="col-lg-4">
            <!-- Search Box -->
            <div class="sidebar-card">
                <h5 class="sidebar-title">
                    <i class="bi bi-search"></i> Search Notices
                </h5>
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by title...">
                    <button class="btn btn-primary" type="button" onclick="searchNotices()">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            
            <!-- Recent Notices -->
            <div class="sidebar-card">
                <h5 class="sidebar-title">
                    <i class="bi bi-clock-history"></i> Recent Notices
                </h5>
                <?php if (empty($recentNotices)): ?>
                    <p class="text-muted text-center py-3">No recent notices</p>
                <?php else: ?>
                    <ul class="notice-list">
                        <?php foreach ($recentNotices as $recent): ?>
                            <li>
                                <a href="notice-detail.php?id=<?= $recent['id'] ?>">
                                    <i class="bi bi-file-text-fill text-primary flex-shrink-0 mt-1"></i>
                                    <div>
                                        <?= htmlspecialchars(substr($recent['notice_title'], 0, 50)) ?>
                                        <?php if (strlen($recent['notice_title']) > 50): ?>...<?php endif; ?>
                                        <span class="date">
                                            <i class="bi bi-calendar3"></i> 
                                            <?= date('d M Y', strtotime($recent['notice_dated'])) ?>
                                        </span>
                                        <?php if (!empty($recent['pdf_url'])): ?>
                                            <br>
                                            <small class="text-danger">
                                                <i class="bi bi-file-pdf-fill"></i> PDF Available
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            
            <!-- Info Card -->
            <div class="sidebar-card bg-light">
                <h5 class="sidebar-title">
                    <i class="bi bi-info-circle"></i> Notice Information
                </h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> All notices are official</li>
                    <li class="mb-2"><i class="bi bi-file-pdf-fill text-danger me-2"></i> PDFs can be viewed/downloaded</li>
                    <li><i class="bi bi-clock-history me-2"></i> Latest notices appear first</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include "footer_top.php"; ?>
<?php include "footer1.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Search functionality
    function searchNotices() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const noticeCards = document.querySelectorAll('.notice-card');
        
        noticeCards.forEach(card => {
            const title = card.querySelector('.notice-title a').innerText.toLowerCase();
            if (title.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Search on enter key
    document.getElementById('searchInput').addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            searchNotices();
        }
    });
    
    // Clear search on empty
    document.getElementById('searchInput').addEventListener('input', function() {
        if (this.value === '') {
            const noticeCards = document.querySelectorAll('.notice-card');
            noticeCards.forEach(card => {
                card.style.display = 'block';
            });
        }
    });
    
    // Track PDF views and downloads
    document.querySelectorAll('.btn-view-pdf').forEach(button => {
        button.addEventListener('click', function(e) {
            const noticeTitle = this.closest('.notice-card').querySelector('.notice-title a').innerText;
            console.log('PDF Viewed: ' + noticeTitle);
        });
    });
    
    document.querySelectorAll('.btn-download-pdf').forEach(button => {
        button.addEventListener('click', function(e) {
            const noticeTitle = this.closest('.notice-card').querySelector('.notice-title a').innerText;
            console.log('PDF Downloaded: ' + noticeTitle);
        });
    });
</script>
</body>
</html>