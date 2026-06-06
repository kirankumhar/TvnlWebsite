<?php
// manage-cancellations.php - Complete management page for cancellation notices

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/src/database/Database.php';

$database = new Database();
$pdo = $database->getConnection();

// Handle delete request
if (isset($_POST['delete_cancellation']) && isset($_POST['cancellation_id'])) {
    $cancellationId = $_POST['cancellation_id'];
    try {
        // Get cancellation details
        $stmt = $pdo->prepare("SELECT pdf_path, tender_id FROM tender_cancellation WHERE id = :id");
        $stmt->execute([':id' => $cancellationId]);
        $cancellation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cancellation && !empty($cancellation['pdf_path'])) {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $cancellation['pdf_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        // Restore tender status to Published
        if ($cancellation && $cancellation['tender_id']) {
            $updateTender = $pdo->prepare("UPDATE tender_notice SET status = 'Published' WHERE id = :tender_id");
            $updateTender->execute([':tender_id' => $cancellation['tender_id']]);
        }
        
        $stmt = $pdo->prepare("DELETE FROM tender_cancellation WHERE id = :id");
        $stmt->execute([':id' => $cancellationId]);
        $_SESSION['message'] = "Cancellation deleted and tender status restored to 'Published'.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to delete cancellation: " . $e->getMessage();
    }
    header("Location: manage-cancellations.php");
    exit;
}

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$sql = "SELECT c.*, t.tender_number, t.title as tender_title, t.status as tender_status
        FROM tender_cancellation c
        JOIN tender_notice t ON c.tender_id = t.id";
$countSql = "SELECT COUNT(*) as total FROM tender_cancellation c JOIN tender_notice t ON c.tender_id = t.id";

if (!empty($search)) {
    $sql .= " WHERE c.cancellation_no LIKE :search OR c.title LIKE :search OR t.tender_number LIKE :search";
    $countSql .= " WHERE c.cancellation_no LIKE :search OR c.title LIKE :search OR t.tender_number LIKE :search";
}

$sql .= " ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset";

// Get total count
$countStmt = $pdo->prepare($countSql);
if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $countStmt->bindParam(':search', $searchTerm);
}
$countStmt->execute();
$totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $limit);

// Get cancellations
$stmt = $pdo->prepare($sql);
if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $stmt->bindParam(':search', $searchTerm);
}
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$cancellations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN YEAR(cancellation_date) = YEAR(CURDATE()) THEN 1 END) as this_year,
        COUNT(CASE WHEN MONTH(cancellation_date) = MONTH(CURDATE()) THEN 1 END) as this_month
    FROM tender_cancellation
")->fetch(PDO::FETCH_ASSOC);
?>

<style>
    /* Page Header */
    .page-header-modern {
        background: linear-gradient(135deg, #dc3545, #c82333);
        padding: 20px 25px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    .page-header-modern h4 {
        color: white;
        margin: 0;
        font-weight: 600;
    }
    .btn-create {
        background: white;
        color: #dc3545;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        background: white;
        color: #c82333;
    }
    
    /* Stats Cards */
    .stats-row {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        flex: 1;
        min-width: 180px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 4px solid;
        transition: transform 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    .stat-card.total { border-left-color: #dc3545; }
    .stat-card.year { border-left-color: #fd7e14; }
    .stat-card.month { border-left-color: #20c997; }
    .stat-card .stat-number {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    .stat-card .stat-label {
        color: #6c757d;
        font-size: 14px;
    }
    
    /* Filter Bar */
    .filter-bar {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    
    /* Data Table */
    .data-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .data-table table {
        width: 100%;
        margin-bottom: 0;
    }
    .data-table th {
        background: #f8f9fa;
        padding: 15px;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }
    .data-table td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #e9ecef;
    }
    .data-table tr:hover {
        background: #f8f9fa;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .btn-view {
        background: #17a2b8;
        color: white;
        border: none;
    }
    .btn-view:hover {
        background: #138496;
        color: white;
    }
    .btn-edit {
        background: #ffc107;
        color: #000;
        border: none;
    }
    .btn-edit:hover {
        background: #e0a800;
        color: #000;
    }
    .btn-delete {
        background: #dc3545;
        color: white;
        border: none;
    }
    .btn-delete:hover {
        background: #c82333;
        color: white;
    }
    
    /* Badges */
    .badge-cancelled {
        background: #dc3545;
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
    }
    .empty-state i {
        font-size: 64px;
        color: #dee2e6;
        margin-bottom: 20px;
    }
    .empty-state h5 {
        color: #495057;
        margin-bottom: 10px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .stats-row {
            flex-direction: column;
        }
        .data-table {
            overflow-x: auto;
        }
        .action-buttons {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header-modern">
        <h4>
            <i class="bi bi-x-octagon-fill"></i> Manage Cancellation Notices
        </h4>
        <a href="create-cancellation.php" class="btn btn-create">
            <i class="bi bi-plus-circle"></i> New Cancellation
        </a>
    </div>

    <!-- Statistics Cards -->
    <!-- <div class="stats-row">
        <div class="stat-card total">
            <div class="stat-number"><?= $stats['total'] ?></div>
            <div class="stat-label">Total Cancellations</div>
        </div>
        <div class="stat-card year">
            <div class="stat-number"><?= $stats['this_year'] ?></div>
            <div class="stat-label">This Year</div>
        </div>
        <div class="stat-card month">
            <div class="stat-number"><?= $stats['this_month'] ?></div>
            <div class="stat-label">This Month</div>
        </div>
    </div> -->

    <!-- Alerts -->
    <?php if (isset($_SESSION['message'])) { ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <?php if (isset($_SESSION['error'])) { ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Search by Cancellation No., Title, or Tender No..." 
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <a href="manage-cancellations.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="data-table">
        <?php if (empty($cancellations)): ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>No Cancellation Notices Found</h5>
                <p>Get started by creating your first cancellation notice.</p>
                <a href="create-cancellation.php" class="btn btn-danger">
                    <i class="bi bi-plus-circle"></i> Create First Cancellation
                </a>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Tender Details</th>
                        <th>Cancellation No.</th>
                        <th>Title</th>
                        <th>Reason</th>
                        <th>Cancellation Date</th>
                        <th width="80">PDF</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = $offset + 1; foreach ($cancellations as $can): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($can['tender_number']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars(substr($can['tender_title'], 0, 40)) ?>...</small>
                            <div><span class="badge-cancelled">Cancelled</span></div>
                        </td>
                        <td>
                            <span class="fw-bold text-danger"><?= htmlspecialchars($can['cancellation_no']) ?></span>
                        </td>
                        <td>
                            <?= htmlspecialchars($can['title']) ?>
                        </td>
                        <td>
                            <?php if (!empty($can['reason'])): ?>
                                <span title="<?= htmlspecialchars($can['reason']) ?>">
                                    <?= htmlspecialchars(substr($can['reason'], 0, 60)) ?>
                                    <?php if (strlen($can['reason']) > 60): ?>...<?php endif; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <i class="bi bi-calendar3 me-1 text-muted"></i>
                            <?= date('d-m-Y', strtotime($can['cancellation_date'])) ?>
                        </td>
                        <td>
                            <?php if ($can['pdf_path']): ?>
                                <a href="<?= htmlspecialchars($can['pdf_path']) ?>" class="btn btn-icon btn-view" target="_blank" title="View PDF">
                                    <i class="bi bi-file-pdf-fill"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit-cancellation.php?id=<?= $can['id'] ?>" class="btn btn-icon btn-edit" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" style="display: inline-block;" 
                                      onsubmit="return confirm('Are you sure you want to delete this cancellation? This will restore the tender status to Published.')">
                                    <input type="hidden" name="cancellation_id" value="<?= $can['id'] ?>">
                                    <button type="submit" name="delete_cancellation" class="btn btn-icon btn-delete" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                                <i class="bi bi-chevron-left"></i> Previous
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link"><i class="bi bi-chevron-left"></i> Previous</span>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">Next <i class="bi bi-chevron-right"></i></span>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            
            <!-- Record Info -->
            <div class="text-center text-muted py-3 border-top">
                <small>Showing <?= count($cancellations) ?> of <?= $totalRecords ?> records</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Auto-hide alerts after 4 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(function() {
            alert.remove();
        }, 500);
    });
}, 4000);
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>