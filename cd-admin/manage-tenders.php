<?php
// manage-tenders.php - Fixed headers already sent issue

session_start();

// Handle POST requests BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/src/database/Database.php';
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Handle delete request
    if (isset($_POST['delete_tender']) && isset($_POST['tender_id'])) {
        $tenderId = $_POST['tender_id'];
        try {
            $stmt = $pdo->prepare("UPDATE tender_notice SET is_deleted = 1, updated_at = NOW() WHERE id = :id");
            $stmt->execute([':id' => $tenderId]);
            $_SESSION['message'] = "Tender deleted successfully.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to delete tender.";
        }
        header("Location: manage-tenders.php");
        exit;
    }
    
    // Handle status update
    if (isset($_POST['update_status']) && isset($_POST['tender_id']) && isset($_POST['status'])) {
        $tenderId = $_POST['tender_id'];
        $status = $_POST['status'];
        try {
            $stmt = $pdo->prepare("UPDATE tender_notice SET status = :status, updated_at = NOW() WHERE id = :id");
            $stmt->execute([':id' => $tenderId, ':status' => $status]);
            $_SESSION['message'] = "Status updated successfully.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to update status.";
        }
        header("Location: manage-tenders.php");
        exit;
    }
}

// Check login AFTER processing POST
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/src/database/Database.php';

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// Build the query
$sql = "SELECT t.*, 
               d.department_name,
               tm.type_name as tender_type_name
        FROM tender_notice t
        LEFT JOIN departments d ON t.department_id = d.department_id
        LEFT JOIN tender_type_master tm ON t.tender_type_id = tm.id
        WHERE t.is_deleted = 0";

$countSql = "SELECT COUNT(*) as total FROM tender_notice t WHERE t.is_deleted = 0";

if (!empty($search)) {
    $sql .= " AND (t.tender_number LIKE :search OR t.title LIKE :search OR t.reference_number LIKE :search)";
    $countSql .= " AND (t.tender_number LIKE :search OR t.title LIKE :search OR t.reference_number LIKE :search)";
}

if (!empty($statusFilter)) {
    $sql .= " AND t.status = :status";
    $countSql .= " AND t.status = :status";
}

$sql .= " ORDER BY t.created_at DESC LIMIT :limit OFFSET :offset";

// Get total records for pagination
$countStmt = $pdo->prepare($countSql);
if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $countStmt->bindParam(':search', $searchTerm);
}
if (!empty($statusFilter)) {
    $countStmt->bindParam(':status', $statusFilter);
}
$countStmt->execute();
$totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $limit);

// Get tenders
$stmt = $pdo->prepare($sql);
if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $stmt->bindParam(':search', $searchTerm);
}
if (!empty($statusFilter)) {
    $stmt->bindParam(':status', $statusFilter);
}
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$tenders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }
    .status-Draft { background: #6c757d; color: white; }
    .status-Published { background: #28a745; color: white; }
    .status-Closed { background: #dc3545; color: white; }
    .status-Cancelled { background: #ffc107; color: #000; }
    .filter-bar {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .action-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    .btn-sm {
        padding: 4px 8px;
        font-size: 12px;
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-file-text-fill"></i> Manage Tenders
                </h4>
                <a href="add-tender.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create New Tender
                </a>
            </div>

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
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by NIT No., Title..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="Draft" <?= $statusFilter == 'Draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="Published" <?= $statusFilter == 'Published' ? 'selected' : '' ?>>Published</option>
                            <option value="Closed" <?= $statusFilter == 'Closed' ? 'selected' : '' ?>>Closed</option>
                            <option value="Cancelled" <?= $statusFilter == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            <a href="manage-tenders.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tenders Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="50">S.No.</th>
                            <th>NIT No.</th>
                            <th>Title</th>
                            <th>Department</th>
                            <!-- <th>Tender Category</th> -->
                            <!-- <th>Publish Date</th>
                            <th>Closing Date</th> -->
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tenders)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1"></i><br>
                                    No tenders found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $i = $offset + 1; foreach ($tenders as $tender): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($tender['tender_number']) ?></strong><br>
                                        <small class="text-muted">Ref: <?= htmlspecialchars($tender['reference_number'] ?? 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars(substr($tender['title'], 0, 60)) ?>
                                        <?php if (strlen($tender['title']) > 60): ?>...<?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($tender['department_name'] ?? 'N/A') ?></td>
                                    <!-- <td><?= htmlspecialchars($tender['tender_type_name'] ?? 'N/A') ?></td> -->
                                    <!-- <td>
                                        <?php if ($tender['publish_date']): ?>
                                            <?= date('d-m-Y H:i', strtotime($tender['publish_date'])) ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($tender['closing_date']): ?>
                                            <?= date('d-m-Y H:i', strtotime($tender['closing_date'])) ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td> -->
                                    <td>
                                        <span class="status-badge status-<?= $tender['status'] ?>">
                                            <?= $tender['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <!-- View Button -->
                                            <a href="view-tender.php?id=<?= $tender['id'] ?>" class="btn btn-info btn-sm" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            
                                            <!-- Edit Button -->
                                            <a href="edit-tender.php?id=<?= $tender['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <!-- Delete Form -->
                                            <form method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this tender?')">
                                                <input type="hidden" name="tender_id" value="<?= $tender['id'] ?>">
                                                <input type="hidden" name="delete_tender" value="1">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>

                                            <!-- Status Dropdown Form -->
                                            <form method="POST" style="display: inline-block;" onsubmit="return confirm('Change tender status?')">
                                                <input type="hidden" name="tender_id" value="<?= $tender['id'] ?>">
                                                <input type="hidden" name="update_status" value="1">
                                                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                                                    <option value="Draft" <?= $tender['status'] == 'Draft' ? 'selected' : '' ?>>Draft</option>
                                                    <option value="Published" <?= $tender['status'] == 'Published' ? 'selected' : '' ?>>Published</option>
                                                    <option value="Closed" <?= $tender['status'] == 'Closed' ? 'selected' : '' ?>>Closed</option>
                                                    <option value="Cancelled" <?= $tender['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                </select>
                                            </form>
                                            
                                            
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>">
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
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>">
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
            <div class="mt-3 text-muted text-center">
                <small>Showing <?= count($tenders) ?> of <?= $totalRecords ?> records</small>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-hide alerts after 3 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        });
    }, 3000);
});
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>