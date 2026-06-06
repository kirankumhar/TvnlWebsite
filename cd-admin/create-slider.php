<?php
require_once __DIR__ . '/src/helpers/session_helper.php';
requireLogin();

// Check login
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}

// Define base URL (adjust this to your actual base path)
$base_url = ''; // or '/tvnl-website/cd-admin' depending on your setup

// Get form data from session if exists (for validation errors)
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
$title_value = isset($form_data['title']) ? htmlspecialchars($form_data['title']) : '';
$subtitle_value = isset($form_data['subtitle']) ? htmlspecialchars($form_data['subtitle']) : '';
$make_slider_checked = isset($form_data['make_slider']) && $form_data['make_slider'] == 1;
$status_value = isset($form_data['status']) ? htmlspecialchars($form_data['status']) : 'A';

$page_title = "Admin - Create Slider";

require_once __DIR__ . '/layouts/header.php';
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
        border-bottom: 2px solid #0d6efd;
        color: #0d6efd;
    }
    .required-field::after {
        content: " *";
        color: red;
        font-weight: bold;
    }
    .image-preview {
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
        display: none;
    }
    .image-preview img {
        max-height: 200px;
        max-width: 300px;
        border-radius: 5px;
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
                    <i class="bi bi-images"></i> Create Slider
                </h4>
                <a href="manage-sliders.php" class="btn btn-secondary">
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

            <form action="<?= $base_url ?>/src/controllers/slider/createSliderController.php" method="post" enctype="multipart/form-data" id="sliderForm">
                
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-info-circle-fill me-2"></i>Slider Information
                    </div>
                    
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label required-field">Title</label>
                        <input type="text" name="title" id="title" class="form-control" 
                               value="<?= $title_value ?>" required maxlength="255" 
                               placeholder="Enter slider title">
                        <div class="help-text">Maximum 255 characters</div>
                    </div>

                    <!-- Subtitle / Description -->
                    <div class="mb-3">
                        <label for="subtitle" class="form-label">Sub Title / Description</label>
                        <textarea name="subtitle" id="subtitle" class="form-control" 
                                  placeholder="Enter subtitle or description (Optional)"
                                  maxlength="500" rows="3"><?= $subtitle_value ?></textarea>
                        <div class="help-text">Maximum 500 characters</div>
                    </div>

                    <!-- Slider Image -->
                    <div class="mb-3">
                        <label for="image" class="form-label required-field">Slider Image</label>
                        <input type="file" name="image" id="image" class="form-control" 
                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" 
                               onchange="previewImage(this)" required>
                        <div class="help-text">
                            <i class="bi bi-info-circle"></i> 
                            Allowed formats: JPG, PNG, GIF, WEBP. Max size: 2MB. 
                            Recommended size: 1350x465px
                        </div>
                        <div id="imagePreview" class="image-preview"></div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-gear-fill me-2"></i>Settings
                    </div>

                    <!-- Slider Option -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="make_slider" name="make_slider" value="1" <?= $make_slider_checked ? 'checked' : '' ?>>
                            <label class="form-check-label" for="make_slider">
                                <i class="bi bi-sliders2"></i> Make this as a slider
                            </label>
                        </div>
                        <div class="help-text">Enable to display this image in homepage slider</div>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="A" <?= $status_value == 'A' ? 'selected' : '' ?>>Active</option>
                            <option value="I" <?= $status_value == 'I' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <div class="help-text">Inactive sliders won't be displayed on the website</div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn px-4">
                        <i class="bi bi-save"></i> Create Slider
                    </button>
                    <a href="manage-sliders.php" class="btn btn-secondary btn px-4">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const previewDiv = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Invalid file type. Please upload JPG, PNG, GIF, or WEBP images only.');
            input.value = '';
            previewDiv.style.display = 'none';
            previewDiv.innerHTML = '';
            return;
        }
        
        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size exceeds 2MB limit. Please compress your image.');
            input.value = '';
            previewDiv.style.display = 'none';
            previewDiv.innerHTML = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewDiv.innerHTML = '<img src="' + e.target.result + '" alt="Preview" class="img-thumbnail">';
            previewDiv.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        previewDiv.style.display = 'none';
        previewDiv.innerHTML = '';
    }
}

// Form validation before submit
document.getElementById('sliderForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const image = document.getElementById('image').files[0];
    
    if (title === '') {
        alert('Please enter a title for the slider.');
        e.preventDefault();
        return false;
    }
    
    if (!image) {
        alert('Please select an image for the slider.');
        e.preventDefault();
        return false;
    }
    
    return true;
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

<?php
require_once __DIR__ . '/layouts/footer.php';
unset($_SESSION['form_data']);
?>