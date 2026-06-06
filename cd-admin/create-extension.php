<?php
// create-extension.php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/src/database/Database.php';

$database = new Database();
$pdo = $database->getConnection();

// Get all active tenders
$tenders = $pdo->query("SELECT id, tender_number, title, closing_date FROM tender_notice WHERE is_deleted = 0 AND status = 'Published' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .form-section {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 25px;
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
    }
    .info-box {
        background: #e3f2fd;
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
                    <i class="bi bi-clock-history text-warning"></i> Create Extension Notice
                </h4>
                <a href="manage-extensions.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>

            <?php if (isset($_SESSION['message'])) { ?>
                <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php } ?>
            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php } ?>

            <form action="src/controllers/tender/ExtensionController.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                
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
                                        <option value="<?= $tender['id'] ?>" data-closing="<?= $tender['closing_date'] ?>">
                                            <?= htmlspecialchars($tender['tender_number']) ?> - <?= htmlspecialchars(substr($tender['title'], 0, 50)) ?>...
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="extension_no" class="form-label required-field">Extension No.</label>
                                <input type="text" class="form-control" id="extension_no" name="extension_no" placeholder="e.g., EXT/001/2026" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label required-field">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Reason for extension..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="old_closing_date" class="form-label required-field">Old Closing Date</label>
                                <input type="datetime-local" class="form-control" id="old_closing_date" name="old_closing_date" readonly required>
                                <div class="info-box" id="oldDateInfo">Select a tender to see current closing date</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="new_closing_date" class="form-label required-field">New Closing Date</label>
                                <input type="datetime-local" class="form-control" id="new_closing_date" name="new_closing_date" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="extension_date" class="form-label required-field">Extension Date</label>
                                <input type="date" class="form-control" id="extension_date" name="extension_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pdf_file" class="form-label">PDF Attachment</label>
                                <input type="file" class="form-control" id="pdf_file" name="pdf_file" accept=".pdf">
                                <small class="text-muted">Optional. Max size: 5MB</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="bi bi-save"></i> Create Extension
                    </button>
                    <a href="manage-extensions.php" class="btn btn-secondary px-4">Cancel</a>
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
        document.getElementById('oldDateInfo').innerHTML = `<i class="bi bi-calendar"></i> Current closing date: ${new Date(closingDate).toLocaleString()}`;
    } else {
        document.getElementById('old_closing_date').value = '';
        document.getElementById('oldDateInfo').innerHTML = 'Select a tender to see current closing date';
    }
});

// Set default dates
document.getElementById('extension_date').valueAsDate = new Date();

// Set default new closing date (30 days from now)
const defaultNewDate = new Date();
defaultNewDate.setDate(defaultNewDate.getDate() + 30);
document.getElementById('new_closing_date').value = defaultNewDate.toISOString().slice(0, 16);
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>