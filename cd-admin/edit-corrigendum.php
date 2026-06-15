<?php
// edit-corrigendum.php - Edit corrigendum notice

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/src/database/Database.php';

$database = new Database();
$pdo = $database->getConnection();

$corrigendumId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$corrigendumId) {
    $_SESSION['error'] = "Invalid corrigendum ID.";
    header("Location: manage-corrigendums.php");
    exit;
}

// Get corrigendum details
$stmt = $pdo->prepare("
    SELECT c.*, t.tender_number, t.title as tender_title 
    FROM tender_corrigendum c
    JOIN tender_notice t ON c.tender_id = t.id
    WHERE c.id = :id
");
$stmt->execute([':id' => $corrigendumId]);
$corrigendum = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$corrigendum) {
    $_SESSION['error'] = "Corrigendum not found.";
    header("Location: manage-corrigendums.php");
    exit;
}

// Get all active tenders for dropdown
$tenders = $pdo->query("SELECT id, tender_number, title FROM tender_notice WHERE is_deleted = 0 AND status = 'Published' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
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
        border-bottom: 2px solid #dc3545;
        color: #dc3545;
    }
    .required-field::after {
        content: " *";
        color: red;
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
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-pencil-square text-danger"></i> Edit Corrigendum Notice
                </h4>
                <a href="manage-corrigendums.php" class="btn btn-secondary">
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

            <form action="src/controllers/tender/CorrigendumController.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="corrigendum_id" value="<?= $corrigendum['id'] ?>">
                
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-info-circle-fill me-2"></i>Corrigendum Information
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tender_id" class="form-label required-field">Select Tender</label>
                                <select class="form-select" id="tender_id" name="tender_id" required>
                                    <option value="">-- Select Tender --</option>
                                    <?php foreach ($tenders as $tender): ?>
                                        <option value="<?= $tender['id'] ?>" <?= $corrigendum['tender_id'] == $tender['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tender['tender_number']) ?> - <?= htmlspecialchars(substr($tender['title'], 0, 50)) ?>...
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="corrigendum_no" class="form-label required-field">Corrigendum No.</label>
                                <input type="text" class="form-control" id="corrigendum_no" name="corrigendum_no" 
                                       value="<?= htmlspecialchars($corrigendum['corrigendum_no']) ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label required-field">Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($corrigendum['title']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description / Changes Made</label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  placeholder="Describe the changes/corrections..."><?= htmlspecialchars($corrigendum['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="corrigendum_date" class="form-label required-field">Corrigendum Date</label>
                                <input type="date" class="form-control" id="corrigendum_date" name="corrigendum_date" 
                                       value="<?= $corrigendum['corrigendum_date'] ?>" required>
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
                    <?php if ($corrigendum['pdf_path']): ?>
                    <div class="current-pdf">
                        <i class="bi bi-file-pdf-fill text-danger"></i> <strong>Current PDF:</strong>
                        <a href="<?= $base_url . '/cd-admin/src/' . ltrim($corrigendum['pdf_path'], '/'); ?>" target="_blank" class="ms-2">
                            <?= basename($corrigendum['pdf_path']) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-danger btn-lg px-4">
                        <i class="bi bi-save"></i> Update Corrigendum
                    </button>
                    <a href="manage-corrigendums.php" class="btn btn-secondary btn-lg px-4">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('corrigendum_date').valueAsDate = new Date();

// File validation
document.getElementById('pdf_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type !== 'application/pdf') {
        alert('PDF file required!');
        this.value = '';
    } else if (file && file.size > 5 * 1024 * 1024) {
        alert('File size must be less than 5MB!');
        this.value = '';
    }
});
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>