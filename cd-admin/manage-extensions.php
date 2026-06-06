<?php
// manage-corrigendums.php

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
if (isset($_POST['delete_extension']) && isset($_POST['extension_id'])) {
    $extensionId = $_POST['extension_id'];
    try {
        // Get extension details before deleting
        $stmt = $pdo->prepare("SELECT tender_id, old_closing_date FROM tender_extension WHERE id = :id");
        $stmt->execute([':id' => $extensionId]);
        $extension = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($extension) {
            // Restore original closing date to tender
            $updateTender = $pdo->prepare("UPDATE tender_notice SET closing_date = :old_date WHERE id = :tender_id");
            $updateTender->execute([
                ':old_date' => $extension['old_closing_date'],
                ':tender_id' => $extension['tender_id']
            ]);
            
            // Delete the extension record
            $deleteStmt = $pdo->prepare("DELETE FROM tender_extension WHERE id = :id");
            $deleteStmt->execute([':id' => $extensionId]);
            
            $_SESSION['message'] = "Extension deleted and original closing date restored.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to delete extension: " . $e->getMessage();
    }
    header("Location: manage-extensions.php");
    exit;
}

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tenderFilter = isset($_GET['tender_id']) ? (int)$_GET['tender_id'] : 0;

// Build the query
$sql = "SELECT e.*, 
               t.tender_number, 
               t.title as tender_title,
               t.closing_date as current_closing_date
        FROM tender_extension e
        JOIN tender_notice t ON e.tender_id = t.id
        WHERE 1=1";

$countSql = "SELECT COUNT(*) as total FROM tender_extension e JOIN tender_notice t ON e.tender_id = t.id WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (e.extension_no LIKE :search OR e.title LIKE :search OR t.tender_number LIKE :search)";
    $countSql .= " AND (e.extension_no LIKE :search OR e.title LIKE :search OR t.tender_number LIKE :search)";
}

if ($tenderFilter > 0) {
    $sql .= " AND e.tender_id = :tender_id";
    $countSql .= " AND e.tender_id = :tender_id";
}

$sql .= " ORDER BY e.created_at DESC LIMIT :limit OFFSET :offset";

// Get total count
$countStmt = $pdo->prepare($countSql);
if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $countStmt->bindParam(':search', $searchTerm);
}
if ($tenderFilter > 0) {
    $countStmt->bindParam(':tender_id', $tenderFilter, PDO::PARAM_INT);
}
$countStmt->execute();
$totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $limit);

// Get extensions
$stmt = $pdo->prepare($sql);
if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $stmt->bindParam(':search', $searchTerm);
}
if ($tenderFilter > 0) {
    $stmt->bindParam(':tender_id', $tenderFilter, PDO::PARAM_INT);
}
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$extensions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all tenders for filter dropdown
$tenders = $pdo->query("SELECT id, tender_number, title FROM tender_notice WHERE is_deleted = 0 ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }
    .status-expired { background: #dc3545; color: white; }
    .status-active { background: #28a745; color: white; }
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
    .extension-card {
        transition: transform 0.2s;
    }
    .extension-card:hover {
        transform: translateY(-2px);
    }
    .date-change {
        background: #fff3cd;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 12px;
    }
    .old-date {
        text-decoration: line-through;
        color: #dc3545;
    }
    .new-date {
        color: #28a745;
        font-weight: bold;
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-clock-history text-warning"></i> Manage Extension Notices
                </h4>
                <a href="create-extension.php" class="btn btn-warning">
                    <i class="bi bi-plus-circle"></i> New Extension
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
                        <input type="text" name="search" class="form-control" placeholder="Search by Extension No., Title..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Tender</label>
                        <select name="tender_id" class="form-select">
                            <option value="0">All Tenders</option>
                            <?php foreach ($tenders as $tender): ?>
                                <option value="<?= $tender['id'] ?>" <?= $tenderFilter == $tender['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tender['tender_number']) ?> - <?= htmlspecialchars(substr($tender['title'], 0, 40)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            <a href="manage-extensions.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Extensions Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="50">S.No.</th>
                            <th>Tender No.</th>
                            <th>Extension No.</th>
                            <!-- <th>Title</th> -->
                            <th>Date Change</th>
                            <th>Extension Date</th>
                            <th>PDF</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($extensions)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1"></i><br>
                                    No extension notices found.
                                    <div class="mt-2">
                                        <a href="create-extension.php" class="btn btn-sm btn-warning">Create First Extension</a>
                                    </div>
                                 </td>
                            </tr>
                        <?php else: ?>
                            <?php $i = $offset + 1; foreach ($extensions as $ext): ?>
                                <tr class="extension-card">
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($ext['tender_number']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars(substr($ext['tender_title'], 0, 40)) ?>...</small>
                                    </td>
                                    <td>
                                        <span class="fw-bold"><?= htmlspecialchars($ext['extension_no']) ?></span>
                                    </td>
                                    <!-- <td>
                                        <?= htmlspecialchars($ext['title']) ?>
                                        <?php if (!empty($ext['description'])): ?>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars(substr($ext['description'], 0, 50)) ?>...</small>
                                        <?php endif; ?>
                                    </td> -->
                                    <td>
                                        <div class="date-change">
                                            <i class="bi bi-calendar-x text-danger"></i> 
                                            <span class="old-date"><?= date('d-m-Y H:i', strtotime($ext['old_closing_date'])) ?></span>
                                            <i class="bi bi-arrow-right mx-1"></i>
                                            <i class="bi bi-calendar-check text-success"></i> 
                                            <span class="new-date"><?= date('d-m-Y H:i', strtotime($ext['new_closing_date'])) ?></span>
                                        </div>
                                        <small class="text-muted">Extended by <?= round((strtotime($ext['new_closing_date']) - strtotime($ext['old_closing_date'])) / 86400) ?> days</small>
                                    </td>
                                    <td><?= date('d-m-Y', strtotime($ext['extension_date'])) ?></td>
                                    <td>
                                        <?php if ($ext['pdf_path']): ?>
                                            <a href="<?= htmlspecialchars($ext['pdf_path']) ?>" class="btn btn-sm btn-info" target="_blank" title="View PDF">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No PDF</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit-extension.php?id=<?= $ext['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger delete-extension" 
                                                    data-id="<?= $ext['id'] ?>" 
                                                    data-no="<?= htmlspecialchars($ext['extension_no']) ?>"
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <a href="view-extension.php?id=<?= $ext['id'] ?>" class="btn btn-sm btn-secondary" title="View Details">
                                                <i class="bi bi-info-circle"></i>
                                            </a>
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
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&tender_id=<?= $tenderFilter ?>">
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
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&tender_id=<?= $tenderFilter ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&tender_id=<?= $tenderFilter ?>">
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
                <small>Showing <?= count($extensions) ?> of <?= $totalRecords ?> records</small>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill"></i> Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete extension: <strong id="deleteExtensionNo"></strong>?</p>
                <p class="text-danger"><small>This will restore the original closing date to the tender.</small></p>
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="extension_id" id="deleteExtensionId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_extension" class="btn btn-danger">Delete Extension</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Delete extension confirmation
document.querySelectorAll('.delete-extension').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.dataset.id;
        const no = this.dataset.no;
        document.getElementById('deleteExtensionId').value = id;
        document.getElementById('deleteExtensionNo').textContent = no;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });
});

// Auto-hide alerts after 3 seconds
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
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>