<?php
// edit-extension.php - Edit extension notice

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/src/database/Database.php';

$database = new Database();
$pdo = $database->getConnection();

$extensionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$extensionId) {
    $_SESSION['error'] = "Invalid extension ID.";
    header("Location: manage-extensions.php");
    exit;
}

// Get extension details
$stmt = $pdo->prepare("
    SELECT e.*, t.tender_number, t.title as tender_title, t.closing_date as current_closing_date
    FROM tender_extension e
    JOIN tender_notice t ON e.tender_id = t.id
    WHERE e.id = :id
");
$stmt->execute([':id' => $extensionId]);
$extension = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$extension) {
    $_SESSION['error'] = "Extension not found.";
    header("Location: manage-extensions.php");
    exit;
}

// Get all active tenders for dropdown
$tenders = $pdo->query("SELECT id, tender_number, title, closing_date FROM tender_notice WHERE is_deleted = 0 ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
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
        border-bottom: 2px solid #fd7e14;
        color: #fd7e14;
    }
    .required-field::after {
        content: " *";
        color: red;
        font-weight: bold;
    }
    .current-pdf {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        margin-top: 10px;
    }
    .help-text {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }
    .date-change-info {
        background: #e7f3ff;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #fd7e14;
    }
    .old-date {
        text-decoration: line-through;
        color: #dc3545;
        font-weight: 500;
    }
    .new-date {
        color: #28a745;
        font-weight: 600;
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-pencil-square text-warning"></i> Edit Extension Notice
                </h4>
                <a href="manage-extensions.php" class="btn btn-secondary">
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

            <!-- Date Change Information -->
            <div class="date-change-info">
                <i class="bi bi-clock-history text-warning me-2"></i>
                <strong>Previous Extension:</strong> Date changed from 
                <span class="old-date"><?= date('d-m-Y H:i', strtotime($extension['old_closing_date'])) ?></span> 
                to 
                <span class="new-date"><?= date('d-m-Y H:i', strtotime($extension['new_closing_date'])) ?></span>
                <br>
                <small class="text-muted">Extended by <?= round((strtotime($extension['new_closing_date']) - strtotime($extension['old_closing_date'])) / 86400) ?> days</small>
            </div>

            <form action="src/controllers/tender/ExtensionController.php" method="post" enctype="multipart/form-data" id="extensionForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="extension_id" value="<?= $extension['id'] ?>">
                
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-info-circle-fill me-2"></i>Extension Information
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tender_id" class="form-label required-field">Select Tender</label>
                                <select class="form-select" id="tender_id" name="tender_id" required>
                                    <option value="">-- Select Tender --</option>
                                    <?php foreach ($tenders as $tender): ?>
                                        <option value="<?= $tender['id'] ?>" 
                                                <?= $extension['tender_id'] == $tender['id'] ? 'selected' : '' ?>
                                                data-closing="<?= $tender['closing_date'] ?>">
                                            <?= htmlspecialchars($tender['tender_number']) ?> - <?= htmlspecialchars(substr($tender['title'], 0, 50)) ?>...
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="extension_no" class="form-label required-field">Extension No.</label>
                                <input type="text" class="form-control" id="extension_no" name="extension_no" 
                                       value="<?= htmlspecialchars($extension['extension_no']) ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label required-field">Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($extension['title']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($extension['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="old_closing_date" class="form-label required-field">Old Closing Date</label>
                                <input type="datetime-local" class="form-control" id="old_closing_date" name="old_closing_date" 
                                       value="<?= date('Y-m-d\TH:i', strtotime($extension['old_closing_date'])) ?>" required>
                                <div class="help-text">Original closing date before extension</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="new_closing_date" class="form-label required-field">New Closing Date</label>
                                <input type="datetime-local" class="form-control" id="new_closing_date" name="new_closing_date" 
                                       value="<?= date('Y-m-d\TH:i', strtotime($extension['new_closing_date'])) ?>" required>
                                <div class="help-text">Extended closing date</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="extension_date" class="form-label required-field">Extension Date</label>
                                <input type="date" class="form-control" id="extension_date" name="extension_date" 
                                       value="<?= $extension['extension_date'] ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pdf_file" class="form-label">Replace PDF (Optional)</label>
                                <input type="file" class="form-control" id="pdf_file" name="pdf_file" accept=".pdf">
                                <div class="help-text">Leave empty to keep existing PDF. Max size: 5MB</div>
                            </div>
                        </div>
                    </div>

                    <!-- Current PDF -->
                    <?php if ($extension['pdf_path']): ?>
                    <div class="current-pdf">
                        <i class="bi bi-file-pdf-fill text-danger"></i> <strong>Current PDF:</strong>
                        <a href="<?= $base_url . '/cd-admin/src/' . ltrim($extension['pdf_path'], '/'); ?>" target="_blank" class="ms-2">
                            <?= basename($extension['pdf_path']) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-warning btn-lg px-4">
                        <i class="bi bi-save"></i> Update Extension
                    </button>
                    <a href="manage-extensions.php" class="btn btn-secondary btn-lg px-4">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-populate old closing date when tender is selected
document.getElementById('tender_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const closingDate = selectedOption.dataset.closing;
    
    if (closingDate) {
        const formattedDate = closingDate.replace(' ', 'T').slice(0, 16);
        document.getElementById('old_closing_date').value = formattedDate;
    }
});

// Date validation
document.getElementById('new_closing_date').addEventListener('change', function() {
    const oldDate = new Date(document.getElementById('old_closing_date').value);
    const newDate = new Date(this.value);
    
    if (newDate <= oldDate) {
        alert('New closing date must be after the old closing date!');
        this.value = '';
    }
});

// File validation for PDF
document.getElementById('pdf_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.type !== 'application/pdf') {
            alert('PDF file required!');
            this.value = '';
        } else if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB!');
            this.value = '';
        }
    }
});

// Form validation
document.getElementById('extensionForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const extensionNo = document.getElementById('extension_no').value.trim();
    const newClosingDate = document.getElementById('new_closing_date').value;
    const oldClosingDate = document.getElementById('old_closing_date').value;
    
    if (title === '') {
        alert('Please enter a title.');
        e.preventDefault();
        return false;
    }
    
    if (extensionNo === '') {
        alert('Please enter extension number.');
        e.preventDefault();
        return false;
    }
    
    if (newClosingDate && oldClosingDate && new Date(newClosingDate) <= new Date(oldClosingDate)) {
        alert('New closing date must be after the old closing date!');
        e.preventDefault();
        return false;
    }
    
    return true;
});
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>