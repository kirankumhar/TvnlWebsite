<?php
// manage-corrigendums.php - Professional Redesign

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
if (isset($_POST['delete_corrigendum']) && isset($_POST['corrigendum_id'])) {
    $corrigendumId = $_POST['corrigendum_id'];
    try {
        $stmt = $pdo->prepare("SELECT pdf_path FROM tender_corrigendum WHERE id = :id");
        $stmt->execute([':id' => $corrigendumId]);
        $corrigendum = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($corrigendum && !empty($corrigendum['pdf_path'])) {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $corrigendum['pdf_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM tender_corrigendum WHERE id = :id");
        $stmt->execute([':id' => $corrigendumId]);
        $_SESSION['message'] = "Corrigendum deleted successfully.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to delete corrigendum.";
    }
    header("Location: manage-corrigendums.php");
    exit;
}

// Get all corrigendums
$stmt = $pdo->query("
    SELECT c.*, t.tender_number, t.title as tender_title 
    FROM tender_corrigendum c
    JOIN tender_notice t ON c.tender_id = t.id
    ORDER BY c.created_at DESC
");
$corrigendums = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<style>
    .page-header-modern {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    .page-header-modern h4 i {
        margin-right: 10px;
    }
    .btn-create {
        background: white;
        color: #764ba2;
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
        color: #667eea;
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
        min-width: 200px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 4px solid;
        transition: transform 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    .stat-card.total { border-left-color: #764ba2; }
    .stat-card.published { border-left-color: #28a745; }
    .stat-card.draft { border-left-color: #ffc107; }
    .stat-card .stat-number {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    .stat-card .stat-label {
        color: #6c757d;
        font-size: 14px;
    }
    
    /* Table Styles */
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
    
    /* Tender Badge */
    .tender-badge {
        background: #e8f0fe;
        color: #1967d2;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        display: inline-block;
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
    .btn-icon i {
        font-size: 14px;
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
    }
    .btn-view {
        background: #17a2b8;
        color: white;
        border: none;
    }
    .btn-view:hover {
        background: #138496;
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
    .empty-state p {
        color: #6c757d;
        margin-bottom: 20px;
    }
    
    /* Alert Styles */
    .alert-custom {
        border-radius: 10px;
        border: none;
        padding: 15px 20px;
        margin-bottom: 20px;
    }
    
    /* Title Truncate */
    .title-truncate {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .stats-row {
            flex-direction: column;
        }
        .stat-card {
            min-width: auto;
        }
        .data-table {
            overflow-x: auto;
        }
        .title-truncate {
            max-width: 150px;
        }
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header-modern">
        <h4>
            <i class="bi bi-exclamation-triangle-fill"></i> Manage Corrigendum Notices
        </h4>
        <a href="create-corrigendum.php" class="btn btn-create">
            <i class="bi bi-plus-circle"></i> New Corrigendum
        </a>
    </div>

    <!-- Stats Cards -->
    <?php 
        $total = count($corrigendums);
        $currentYear = date('Y');
        $thisYear = count(array_filter($corrigendums, function($c) use ($currentYear) {
            return date('Y', strtotime($c['corrigendum_date'])) == $currentYear;
        }));
    ?>
    <!-- <div class="stats-row">
        <div class="stat-card total">
            <div class="stat-number"><?= $total ?></div>
            <div class="stat-label">Total Corrigendums</div>
        </div>
        <div class="stat-card published">
            <div class="stat-number"><?= $thisYear ?></div>
            <div class="stat-label">This Year</div>
        </div>
        <div class="stat-card draft">
            <div class="stat-number"><?= date('F Y') ?></div>
            <div class="stat-label">Current Month</div>
        </div>
    </div> -->

    <!-- Alerts -->
    <?php if (isset($_SESSION['message'])) { ?>
        <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <?php if (isset($_SESSION['error'])) { ?>
        <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <!-- Data Table -->
    <div class="data-table">
        <?php if (empty($corrigendums)): ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>No Corrigendum Notices Found</h5>
                <p>Get started by creating your first corrigendum notice.</p>
                <a href="create-corrigendum.php" class="btn btn-danger">
                    <i class="bi bi-plus-circle"></i> Create First Corrigendum
                </a>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Tender Details</th>
                        <th>Corrigendum Info</th>
                        <th>Date</th>
                        <th width="100">PDF</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($corrigendums as $corr): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td>
                                <div class="tender-badge"><?= htmlspecialchars($corr['tender_number']) ?></div>
                                <div class="small text-muted mt-1"><?= htmlspecialchars(substr($corr['tender_title'], 0, 50)) ?>...</div>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($corr['title']) ?></strong>
                                <?php if (!empty($corr['description'])): ?>
                                    <div class="small text-muted mt-1"><?= htmlspecialchars(substr($corr['description'], 0, 60)) ?>...</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <i class="bi bi-calendar3 me-1 text-muted"></i>
                                <?= date('d M Y', strtotime($corr['corrigendum_date'])) ?>
                            </td>
                            <td>
                                <?php if ($corr['pdf_path']): ?>
                                    <a href="<?= htmlspecialchars($corr['pdf_path']) ?>" class="btn btn-icon btn-view" target="_blank" title="View PDF">
                                        <i class="bi bi-file-pdf-fill"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit-corrigendum.php?id=<?= $corr['id'] ?>" class="btn btn-icon btn-edit" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this corrigendum?')">
                                        <input type="hidden" name="corrigendum_id" value="<?= $corr['id'] ?>">
                                        <button type="submit" name="delete_corrigendum" class="btn btn-icon btn-delete" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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