<?php
// view-tender.php - View single tender details

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/src/database/Database.php';

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

$tenderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$tenderId) {
    $_SESSION['error'] = "Invalid tender ID.";
    header("Location: manage-tenders.php");
    exit;
}

// Get tender details
$stmt = $pdo->prepare("
    SELECT t.*, 
           d.department_name,
           tm.type_name as tender_type_name
    FROM tender_notice t
    LEFT JOIN departments d ON t.department_id = d.department_id
    LEFT JOIN tender_type_master tm ON t.tender_type_id = tm.id
    WHERE t.id = :id AND t.is_deleted = 0
");
$stmt->execute([':id' => $tenderId]);
$tender = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tender) {
    $_SESSION['error'] = "Tender not found.";
    header("Location: manage-tenders.php");
    exit;
}

// Get attachments
$stmt = $pdo->prepare("SELECT * FROM tender_attachments WHERE tender_id = :tender_id ORDER BY sort_order");
$stmt->execute([':tender_id' => $tenderId]);
$attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .detail-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .detail-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
    }
    .detail-value {
        color: #212529;
        margin-bottom: 15px;
    }
    .attachment-list {
        list-style: none;
        padding: 0;
    }
    .attachment-list li {
        padding: 8px 0;
        border-bottom: 1px solid #dee2e6;
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-file-text-fill"></i> Tender Details
                </h4>
                <div>
                    <a href="edit-tender.php?id=<?= $tender['id'] ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="manage-tenders.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="detail-section">
                        <h6 class="mb-3">Basic Information</h6>
                        <div class="detail-label">NIT No.</div>
                        <div class="detail-value"><?= htmlspecialchars($tender['tender_number']) ?></div>
                        
                        <div class="detail-label">Reference Number</div>
                        <div class="detail-value"><?= htmlspecialchars($tender['reference_number'] ?? 'N/A') ?></div>
                        
                        <div class="detail-label">Title</div>
                        <div class="detail-value"><?= htmlspecialchars($tender['title']) ?></div>
                        
                        <div class="detail-label">Description</div>
                        <div class="detail-value"><?= nl2br(htmlspecialchars($tender['description'] ?? 'N/A')) ?></div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="detail-section">
                        <h6 class="mb-3">Tender Information</h6>
                        <div class="detail-label">Department</div>
                        <div class="detail-value"><?= htmlspecialchars($tender['department_name'] ?? 'N/A') ?></div>
                        
                        <div class="detail-label">Tender Category</div>
                        <div class="detail-value"><?= htmlspecialchars($tender['tender_type_name'] ?? 'N/A') ?></div>
                        
                        <div class="detail-label">Bidding URL</div>
                        <div class="detail-value">
                            <?php if ($tender['bid_url']): ?>
                                <a href="<?= htmlspecialchars($tender['bid_url']) ?>" target="_blank">
                                    <?= htmlspecialchars($tender['bid_url']) ?>
                                </a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </div>
                        
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span class="badge bg-<?= $tender['status'] == 'Published' ? 'success' : ($tender['status'] == 'Draft' ? 'secondary' : 'danger') ?>">
                                <?= $tender['status'] ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="detail-section">
                        <h6 class="mb-3">Publish Date</h6>
                        <div class="detail-value">
                            <?= $tender['publish_date'] ? date('d-m-Y H:i:s', strtotime($tender['publish_date'])) : 'N/A' ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="detail-section">
                        <h6 class="mb-3">Closing Date (Bid Submission)</h6>
                        <div class="detail-value">
                            <?= $tender['closing_date'] ? date('d-m-Y H:i:s', strtotime($tender['closing_date'])) : 'N/A' ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="detail-section">
                        <h6 class="mb-3">Opening Date (Part A)</h6>
                        <div class="detail-value">
                            <?= $tender['opening_date'] ? date('d-m-Y H:i:s', strtotime($tender['opening_date'])) : 'N/A' ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attachments Section -->
            <div class="detail-section">
                <h6 class="mb-3">Attachments</h6>
                <?php if (empty($attachments)): ?>
                    <p class="text-muted">No attachments available.</p>
                <?php else: ?>
                    <ul class="attachment-list">
                        <?php foreach ($attachments as $attachment): ?>
                            <li>
                                <i class="bi bi-file-earmark-text-fill me-2"></i>
                                <strong><?= htmlspecialchars($attachment['attachment_title']) ?></strong>
                                <br>
                                <small class="text-muted">
                                    File: <?= htmlspecialchars($attachment['file_name']) ?> 
                                    (<?= $attachment['file_size'] ?? 'Unknown' ?>)
                                </small>
                                <br>
                                <a href="<?= htmlspecialchars($attachment['file_path']) ?>" class="btn btn-sm btn-primary mt-1" target="_blank">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="<?= htmlspecialchars($attachment['file_path']) ?>" class="btn btn-sm btn-success mt-1" download>
                                    <i class="bi bi-download"></i> Download
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>