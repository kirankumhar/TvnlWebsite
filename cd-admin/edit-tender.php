<?php
// edit-tender.php - Edit tender form

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

// Get departments and tender types for dropdowns
$departments = $pdo->query("SELECT department_id, department_name FROM departments WHERE status = 'Active' ORDER BY display_order")->fetchAll(PDO::FETCH_ASSOC);
$tenderTypes = $pdo->query("SELECT id, type_name FROM tender_type_master WHERE status = 'Active' ORDER BY sort_order")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .form-section {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .form-section-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #0d6efd;
        color: #0d6efd;
    }
    .required-field::after {
        content: " *";
        color: red;
        font-weight: bold;
    }
    .error-text {
        color: red;
        font-size: 12px;
        margin-top: 5px;
    }
    .current-pdf {
        background: #e9ecef;
        padding: 10px;
        border-radius: 5px;
        margin-top: 10px;
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-pencil-square"></i> Edit Tender
                </h4>
                <a href="manage-tenders.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
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

            <form action="src/controllers/tender/EditTenderController.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="tender_id" value="<?= $tender['id'] ?>">
                
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-info-circle-fill me-2"></i>Tender Information
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tender_type_id" class="form-label required-field">Tender Category</label>
                                <select class="form-select" id="tender_type_id" name="tender_type_id" required>
                                    <option value="">Select Tender Category</option>
                                    <?php foreach ($tenderTypes as $type): ?>
                                        <option value="<?= $type['id'] ?>" <?= $tender['tender_type_id'] == $type['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($type['type_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($_SESSION['req_error_msg']['tender_type_id'])): ?>
                                    <div class="error-text"><?= $_SESSION['req_error_msg']['tender_type_id'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department_id" class="form-label required-field">Department</label>
                                <select class="form-select" id="department_id" name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['department_id'] ?>" <?= $tender['department_id'] == $dept['department_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dept['department_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($_SESSION['req_error_msg']['department_id'])): ?>
                                    <div class="error-text"><?= $_SESSION['req_error_msg']['department_id'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tender_number" class="form-label required-field">NIT No.</label>
                                <input type="text" class="form-control" id="tender_number" name="tender_number" 
                                       value="<?= htmlspecialchars($tender['tender_number']) ?>" required>
                                <?php if (isset($_SESSION['req_error_msg']['tender_number'])): ?>
                                    <div class="error-text"><?= $_SESSION['req_error_msg']['tender_number'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reference_number" class="form-label">Reference Number</label>
                                <input type="text" class="form-control" id="reference_number" name="reference_number" 
                                       value="<?= htmlspecialchars($tender['reference_number'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label required-field">Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($tender['title']) ?>" required>
                        <?php if (isset($_SESSION['req_error_msg']['title'])): ?>
                            <div class="error-text"><?= $_SESSION['req_error_msg']['title'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($tender['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Bid URL Field -->
                    <div class="mb-3">
                        <label for="bid_url" class="form-label">Bidding URL</label>
                        <input type="url" class="form-control" id="bid_url" name="bid_url" 
                               value="<?= htmlspecialchars($tender['bid_url'] ?? '') ?>">
                        <small class="text-muted">Enter the URL where vendors can submit bids online (Optional)</small>
                    </div>
                </div>

                <!-- Date & Time Section -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-calendar-event-fill me-2"></i>Important Dates & Times
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="publish_date" class="form-label required-field">Date & Time of Publishing</label>
                                <input type="datetime-local" class="form-control" id="publish_date" name="publish_date" 
                                       value="<?= $tender['publish_date'] ? date('Y-m-d\TH:i', strtotime($tender['publish_date'])) : '' ?>" required>
                                <?php if (isset($_SESSION['req_error_msg']['publish_date'])): ?>
                                    <div class="error-text"><?= $_SESSION['req_error_msg']['publish_date'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="closing_date" class="form-label required-field">Last Date & Time of Bid Submission</label>
                                <input type="datetime-local" class="form-control" id="closing_date" name="closing_date" 
                                       value="<?= $tender['closing_date'] ? date('Y-m-d\TH:i', strtotime($tender['closing_date'])) : '' ?>" required>
                                <?php if (isset($_SESSION['req_error_msg']['closing_date'])): ?>
                                    <div class="error-text"><?= $_SESSION['req_error_msg']['closing_date'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="opening_date" class="form-label required-field">Due Date & Time of Opening of Part A</label>
                                <input type="datetime-local" class="form-control" id="opening_date" name="opening_date" 
                                       value="<?= $tender['opening_date'] ? date('Y-m-d\TH:i', strtotime($tender['opening_date'])) : '' ?>" required>
                                <?php if (isset($_SESSION['req_error_msg']['opening_date'])): ?>
                                    <div class="error-text"><?= $_SESSION['req_error_msg']['opening_date'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="Draft" <?= $tender['status'] == 'Draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="Published" <?= $tender['status'] == 'Published' ? 'selected' : '' ?>>Published</option>
                                    <option value="Closed" <?= $tender['status'] == 'Closed' ? 'selected' : '' ?>>Closed</option>
                                    <option value="Cancelled" <?= $tender['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PDF Attachment Section -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-file-pdf-fill me-2"></i>PDF Attachment
                    </div>
                    
                    <div class="mb-3">
                        <label for="pdf_file" class="form-label">Upload New PDF (Optional)</label>
                        <input type="file" class="form-control" id="pdf_file" name="pdf_file" accept=".pdf">
                        <small class="text-muted">Supported format: PDF only. Max size: 10MB. Leave empty to keep existing PDF.</small>
                    </div>
                    
                    <?php if (!empty($tender['pdf_path'])): ?>
                    <div class="current-pdf">
                        <i class="bi bi-file-pdf-fill text-danger"></i> <strong>Current PDF:</strong>
                        <a href="<?= htmlspecialchars($tender['pdf_path']) ?>" target="_blank">
                            <?= basename($tender['pdf_path']) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Form Actions -->
                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-save"></i> Update Tender
                    </button>
                    <a href="manage-tenders.php" class="btn btn-secondary btn-lg px-4">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Date validation
document.getElementById('closing_date').addEventListener('change', function() {
    const publishDate = new Date(document.getElementById('publish_date').value);
    const closingDate = new Date(this.value);
    
    if (closingDate <= publishDate) {
        alert('Bid submission deadline must be after publish date!');
        this.value = '';
    }
});

document.getElementById('opening_date').addEventListener('change', function() {
    const closingDate = new Date(document.getElementById('closing_date').value);
    const openingDate = new Date(this.value);
    
    if (openingDate <= closingDate) {
        alert('Opening date must be after bid submission deadline!');
        this.value = '';
    }
});

// URL validation
document.getElementById('bid_url').addEventListener('change', function() {
    const url = this.value;
    if (url && !url.match(/^https?:\/\/.+\..+/)) {
        alert('Please enter a valid URL starting with http:// or https://');
        this.value = '';
    }
});

// File validation
document.getElementById('pdf_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.type !== 'application/pdf') {
            alert('File must be a PDF!');
            this.value = '';
        } else if (file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB!');
            this.value = '';
        }
    }
});
</script>

<?php
// Clear session error messages after display
unset($_SESSION['req_error_msg']);
require_once __DIR__ . '/layouts/footer.php';
?>