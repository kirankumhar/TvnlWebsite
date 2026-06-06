<?php

session_start();
include('./timeout.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/layouts/header.php';
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
    .attachment-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }
    .attachment-box-title {
        font-weight: 600;
        margin-bottom: 10px;
        color: #495057;
    }
    .file-info {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }
    .attachment-item {
        position: relative;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 10px;
    }
    .help-text {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }
    .btn-add-more {
        transition: all 0.3s ease;
    }
    .btn-add-more:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">Create New Tender</h4>

            <?php if (isset($_SESSION['message'])) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <form action="src/controllers/tender/TenderController.php" method="post" enctype="multipart/form-data" id="tenderForm">
                <input type="hidden" name="action" value="create_tender">
                
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-info-circle-fill me-2"></i>Tender Information
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tender_category" class="form-label required-field">Tender Category</label>
                                <select class="form-select" id="tender_category" name="tender_type_id" required>
                                    <option value="">Select Tender Category</option>
                                    <option value="1">Short Tender Notice</option>
                                    <option value="2">NIT (Notice Inviting Tender)</option>
                                    <option value="3">Expression of Interest (EOI)</option>
                                    <option value="4">Quotation</option>
                                    <option value="5">Others</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department_id" class="form-label required-field">Department</label>
                                <select class="form-select" id="department_id" name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php
                                    try {
                                        require_once 'src/database/Database.php';
                                        $database = new Database();
                                        $pdo = $database->getConnection();
                                        $deptStmt = $pdo->query("SELECT department_id, department_name FROM departments WHERE status = 'Active' ORDER BY display_order");
                                        while ($dept = $deptStmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<option value="' . $dept['department_id'] . '">' . htmlspecialchars($dept['department_name']) . '</option>';
                                        }
                                    } catch (Exception $e) {
                                        echo '<option value="">Error loading departments</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tender_number" class="form-label required-field">NIT No.</label>
                                <input type="text" class="form-control" id="tender_number" name="tender_number" placeholder="Enter NIT Number" required>
                                <small class="text-muted">Example: TVNL/2026/001</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reference_number" class="form-label">Reference Number</label>
                                <input type="text" class="form-control" id="reference_number" name="reference_number" placeholder="Enter Reference Number (Optional)">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label required-field">Title</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Enter Tender Title" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Enter tender description..."></textarea>
                    </div>

                    <!-- Bid URL Field -->
                    <div class="mb-3">
                        <label for="bid_url" class="form-label">Bidding URL</label>
                        <input type="url" class="form-control" id="bid_url" name="bid_url" placeholder="https://example.com/tender/bidding">
                        <div class="help-text">
                            <i class="bi bi-link-45deg"></i> Enter the URL where vendors can submit bids online (Optional)
                        </div>
                    </div>
                </div>

                <!-- Date & Time Section -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-calendar-event-fill me-2"></i>Important Dates & Times
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="publish_date" class="form-label required-field">Date & Time of Publishing</label>
                                <input type="datetime-local" class="form-control" id="publish_date" name="publish_date" required>
                                <small class="text-muted">Select date and time when tender will be published</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="closing_date" class="form-label required-field">Last Date & Time of Bid Submission</label>
                                <input type="datetime-local" class="form-control" id="closing_date" name="closing_date" required>
                                <small class="text-muted">Deadline for bid submission</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="opening_date" class="form-label required-field">Due Date & Time of Opening of Part A</label>
                                <input type="datetime-local" class="form-control" id="opening_date" name="opening_date" required>
                                <small class="text-muted">Technical bid opening date and time</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="Draft">Draft</option>
                                    <option value="Published">Published</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attachments Section -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-paperclip me-2"></i>Attachments
                    </div>
                    
                    <!-- Main PDF Attachment -->
                    <div class="attachment-box">
                        <div class="attachment-box-title">
                            <i class="bi bi-file-pdf-fill text-danger me-2"></i>Main PDF Attachment <span class="text-danger">*</span>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="file" class="form-control" id="main_pdf" name="main_pdf" accept=".pdf" required>
                                <div class="file-info">Supported format: PDF only. Max size: 10MB</div>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Title (Optional)" name="main_pdf_title">
                            </div>
                        </div>
                    </div>

                    <!-- Word Document / PDF Attachment -->
                    <div class="attachment-box">
                        <div class="attachment-box-title">
                            <i class="bi bi-file-earmark-text-fill text-success me-2"></i>Supporting Document (Word/PDF) <span class="text-muted">(Optional)</span>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="file" class="form-control" id="word_doc" name="word_doc" accept=".pdf,.doc,.docx">
                                <div class="file-info">Supported formats: PDF, DOC, DOCX. Max size: 5MB</div>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Title (Optional)" name="word_doc_title">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Attachments -->
                    <div class="attachment-box">
                        <div class="attachment-box-title">
                            <i class="bi bi-files me-2"></i>Additional Attachments (Max 10 files)
                        </div>
                        <div id="additionalAttachmentsContainer">
                            <div class="attachment-item" id="attachment_1">
                                <div class="row">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" placeholder="Attachment Title" name="additional_title[]">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="file" class="form-control additional-file-input" id="additional_file_1" name="additional_file[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger btn-sm remove-attachment-btn" data-id="1" disabled>
                                            <i class="bi bi-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                                <div class="file-info mt-2">Supported: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX. Max size: 5MB each</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-success btn-sm btn-add-more" id="addMoreAttachment" disabled>
                                <i class="bi bi-plus-circle"></i> Add More Attachment
                            </button>
                            <span class="text-muted ms-2" id="attachmentCount">0 / 10 attachments added</span>
                        </div>
                        <div class="help-text mt-2">
                            <i class="bi bi-info-circle"></i> Note: The "Add More Attachment" button will be enabled only after you select a file for the first additional attachment.
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn px-4">
                        <i class="bi bi-save"></i> Create Tender
                    </button>
                    <a href="manage-tenders.php" class="btn btn-secondary btn px-4">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Set default datetime values
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    
    // Set publish date to current date/time
    const publishDateInput = document.getElementById('publish_date');
    publishDateInput.value = now.toISOString().slice(0, 16);
    
    // Set closing date to 30 days from now
    const closingDate = new Date();
    closingDate.setDate(closingDate.getDate() + 30);
    closingDate.setMinutes(closingDate.getMinutes() - closingDate.getTimezoneOffset());
    document.getElementById('closing_date').value = closingDate.toISOString().slice(0, 16);
    
    // Set opening date to 31 days from now
    const openingDate = new Date();
    openingDate.setDate(openingDate.getDate() + 31);
    openingDate.setMinutes(openingDate.getMinutes() - openingDate.getTimezoneOffset());
    document.getElementById('opening_date').value = openingDate.toISOString().slice(0, 16);
});

// Dynamic attachment counter
let attachmentCounter = 1;
const MAX_ATTACHMENTS = 10;

function areAllAttachmentFilesFilled() {
    const allFileInputs = document.querySelectorAll('.additional-file-input');
    if (!allFileInputs.length) {
        return false;
    }

    return Array.from(allFileInputs).every(input => input.files && input.files.length > 0);
}

function updateAddMoreButtonState() {
    const addButton = document.getElementById('addMoreAttachment');
    if (!addButton) return;

    const allFilled = areAllAttachmentFilesFilled();
    const isNotMax = attachmentCounter < MAX_ATTACHMENTS;
    addButton.disabled = !allFilled || !isNotMax;
}

// Update attachment count display
function updateAttachmentCount() {
    const countSpan = document.getElementById('attachmentCount');
    const totalAttachments = document.querySelectorAll('.attachment-item').length;
    countSpan.textContent = `${totalAttachments} / ${MAX_ATTACHMENTS} attachments added`;
    
    // Enable/disable remove button for first attachment
    const firstRemoveBtn = document.querySelector('#attachment_1 .remove-attachment-btn');
    if (firstRemoveBtn) {
        firstRemoveBtn.disabled = (totalAttachments <= 1);
    }
    
    updateAddMoreButtonState();
}

// Add more attachment button
const addMoreAttachmentButton = document.getElementById('addMoreAttachment');
if (addMoreAttachmentButton) {
    addMoreAttachmentButton.addEventListener('click', function() {
        if (attachmentCounter < MAX_ATTACHMENTS) {
            attachmentCounter++;
            
            const container = document.getElementById('additionalAttachmentsContainer');
            const newAttachment = document.createElement('div');
            newAttachment.className = 'attachment-item';
            newAttachment.id = 'attachment_' + attachmentCounter;
            newAttachment.innerHTML = `
                <div class="row">
                    <div class="col-md-5">
                        <input type="text" class="form-control" placeholder="Attachment Title" name="additional_title[]">
                    </div>
                    <div class="col-md-5">
                        <input type="file" class="form-control additional-file-input" name="additional_file[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-attachment-btn" data-id="${attachmentCounter}">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                </div>
                <div class="file-info mt-2">Supported: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX. Max size: 5MB each</div>
            `;
            container.appendChild(newAttachment);
            
            // Add file change event listener to the new file input
            const newFileInput = newAttachment.querySelector('.additional-file-input');
            if (newFileInput) {
                newFileInput.addEventListener('change', function() {
                    updateAddMoreButtonState();
                    updateAttachmentCount();
                });
            }
            
            // Add remove event listener
            const removeBtn = newAttachment.querySelector('.remove-attachment-btn');
            removeBtn.addEventListener('click', function() {
                removeAttachment(this.getAttribute('data-id'));
            });
            
            updateAttachmentCount();
        } else {
            alert('Maximum ' + MAX_ATTACHMENTS + ' attachments allowed!');
        }
    });
}

// Remove attachment function
function removeAttachment(id) {
    const totalAttachments = document.querySelectorAll('.attachment-item').length;
    if (totalAttachments > 1) {
        const element = document.getElementById('attachment_' + id);
        if (element) {
            element.remove();
            updateAttachmentCount();
            updateAddMoreButtonState();
        }
    } else {
        alert('At least one attachment field is required!');
    }
}

// File validation for main PDF
document.getElementById('main_pdf').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.type !== 'application/pdf') {
            alert('Main attachment must be a PDF file!');
            this.value = '';
        } else if (file.size > 10 * 1024 * 1024) {
            alert('PDF file size must be less than 10MB!');
            this.value = '';
        }
    }
});

// File validation for supporting document
document.getElementById('word_doc').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const allowedTypes = [
            'application/pdf',
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        if (!allowedTypes.includes(file.type)) {
            alert('Supporting document must be PDF, DOC, or DOCX format!');
            this.value = '';
        } else if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB!');
            this.value = '';
        }
    }
});

// File validation for additional attachments
function validateAdditionalFile(input) {
    const file = input.files[0];
    if (file) {
        const allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        if (!allowedTypes.includes(file.type)) {
            alert('Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX');
            input.value = '';
            return false;
        } else if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB!');
            input.value = '';
            return false;
        }
    }
    return true;
}

// Add change event listeners to all additional file inputs
document.querySelectorAll('.additional-file-input').forEach(input => {
    input.addEventListener('change', function() {
        if (validateAdditionalFile(this)) {
            updateAddMoreButtonState();
        }
        updateAttachmentCount();
    });
});

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

// URL validation for bid_url
document.getElementById('bid_url').addEventListener('change', function() {
    const url = this.value;
    if (url && !url.match(/^https?:\/\/.+\..+/)) {
        alert('Please enter a valid URL starting with http:// or https://');
        this.value = '';
    }
});

// Form validation
document.getElementById('tenderForm').addEventListener('submit', function(e) {
    // Check if main PDF is uploaded
    const mainPdf = document.getElementById('main_pdf').files[0];
    if (!mainPdf) {
        alert('Please upload the main PDF attachment.');
        e.preventDefault();
        return false;
    }
    
    // Validate URL if provided
    const bidUrl = document.getElementById('bid_url').value;
    if (bidUrl && !bidUrl.match(/^https?:\/\/.+\..+/)) {
        alert('Please enter a valid Bidding URL (must start with http:// or https://)');
        e.preventDefault();
        return false;
    }
    
    return true;
});

// Initialize remove button for first attachment
document.querySelectorAll('.remove-attachment-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        removeAttachment(this.getAttribute('data-id'));
    });
});

// Set initial attachment count
updateAttachmentCount();
updateAddMoreButtonState();
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>