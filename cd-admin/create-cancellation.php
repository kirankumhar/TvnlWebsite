<?php
// create-cancellation.php - Create cancellation notice (Reason optional)

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/src/database/Database.php';

$database = new Database();
$pdo = $database->getConnection();

// Get all active tenders (excluding already cancelled ones)
$tenders = $pdo->query("
    SELECT id, tender_number, title, closing_date 
    FROM tender_notice 
    WHERE is_deleted = 0 AND status != 'Cancelled' 
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);
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
        border-bottom: 2px solid #dc3545;
        color: #dc3545;
    }
    .required-field::after {
        content: " *";
        color: red;
        font-weight: bold;
    }
    .help-text {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }
    .info-box {
        background: #fff3cd;
        border: 1px solid #ffecb5;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 20px;
    }
    .info-box i {
        color: #ffc107;
        margin-right: 8px;
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-x-octagon-fill text-danger"></i> Create Cancellation Notice
                </h4>
                <a href="manage-cancellations.php" class="btn btn-secondary">
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

            <!-- Info Box -->
            <div class="info-box">
                <i class="bi bi-info-circle-fill"></i>
                <strong>Note:</strong> Creating a cancellation notice will automatically update the tender status to "Cancelled".
            </div>

            <form action="src/controllers/tender/CancellationController.php" method="post" enctype="multipart/form-data" id="cancellationForm">
                <input type="hidden" name="action" value="create">
                
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-info-circle-fill me-2"></i>Cancellation Information
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="tender_id" class="form-label required-field">Select Tender</label>
                                <select class="form-select" id="tender_id" name="tender_id" required>
                                    <option value="">-- Select Tender --</option>
                                    <?php foreach ($tenders as $tender): ?>
                                        <option value="<?= $tender['id'] ?>">
                                            <?= htmlspecialchars($tender['tender_number']) ?> - 
                                            <?= htmlspecialchars(substr($tender['title'], 0, 50)) ?>...
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="help-text">Select the tender you want to cancel</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cancellation_no" class="form-label required-field">Cancellation No.</label>
                                <input type="text" class="form-control" id="cancellation_no" name="cancellation_no" 
                                       placeholder="e.g., CAN/001/2026" required>
                                <div class="help-text">Unique cancellation reference number</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label required-field">Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               placeholder="Enter cancellation notice title" required>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Cancellation <span class="text-muted">(Optional)</span></label>
                        <textarea class="form-control" id="reason" name="reason" rows="4" 
                                  placeholder="Provide reason for cancellation (Optional)"></textarea>
                        <div class="help-text">You can leave this empty if no specific reason is needed</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cancellation_date" class="form-label required-field">Cancellation Date</label>
                                <input type="date" class="form-control" id="cancellation_date" name="cancellation_date" required>
                                <div class="help-text">Date when cancellation is issued</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pdf_file" class="form-label">PDF Attachment (Optional)</label>
                                <input type="file" class="form-control" id="pdf_file" name="pdf_file" accept=".pdf">
                                <div class="help-text">Upload cancellation notice PDF. Max size: 5MB</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tender Details Preview -->
                <div class="form-section" id="tenderPreview" style="display: none;">
                    <div class="form-section-title">
                        <i class="bi bi-file-text-fill me-2"></i>Tender Details
                    </div>
                    <div id="tenderInfo"></div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-danger btn px-4">
                        <i class="bi bi-save"></i> Create Cancellation Notice
                    </button>
                    <a href="manage-cancellations.php" class="btn btn-secondary btn px-4">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Set default cancellation date to today
document.getElementById('cancellation_date').valueAsDate = new Date();

// Show tender preview when selected
document.getElementById('tender_id').addEventListener('change', function() {
    const tenderId = this.value;
    const previewDiv = document.getElementById('tenderPreview');
    const tenderInfo = document.getElementById('tenderInfo');
    
    if (tenderId) {
        // Fetch tender details via AJAX
        fetch(`src/controllers/tender/CancellationController.php?action=get_tender&id=${tenderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    tenderInfo.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Tender Number:</strong> ${data.tender.tender_number}<br>
                                <strong>Title:</strong> ${data.tender.title}<br>
                                <strong>Department:</strong> ${data.tender.department_name || 'N/A'}
                            </div>
                            <div class="col-md-6">
                                <strong>Publish Date:</strong> ${new Date(data.tender.publish_date).toLocaleDateString()}<br>
                                <strong>Closing Date:</strong> ${new Date(data.tender.closing_date).toLocaleString()}<br>
                                <strong>Current Status:</strong> <span class="badge bg-warning">${data.tender.status}</span>
                            </div>
                        </div>
                    `;
                    previewDiv.style.display = 'block';
                }
            })
            .catch(error => console.error('Error:', error));
    } else {
        previewDiv.style.display = 'none';
    }
});

// File validation
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
document.getElementById('cancellationForm').addEventListener('submit', function(e) {
    const tenderId = document.getElementById('tender_id').value;
    const cancellationNo = document.getElementById('cancellation_no').value.trim();
    const title = document.getElementById('title').value.trim();
    
    if (!tenderId) {
        alert('Please select a tender.');
        e.preventDefault();
        return false;
    }
    
    if (cancellationNo === '') {
        alert('Please enter cancellation number.');
        e.preventDefault();
        return false;
    }
    
    if (title === '') {
        alert('Please enter a title.');
        e.preventDefault();
        return false;
    }
    
    // Reason is now optional - no validation needed
    
    return confirm('Are you sure you want to cancel this tender? This action will change the tender status to "Cancelled".');
});
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>