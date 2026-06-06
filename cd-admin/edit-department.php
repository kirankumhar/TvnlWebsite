<?php

session_start();
include('./timeout.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/layouts/header.php';

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: manage-departments.php');
    exit;
}

ob_start();
include __DIR__ . '/src/controllers/tender/DepartmentController.php';
$department = $controller->getDepartmentById($id);

if (!$department) {
    $_SESSION['error'] = 'Department not found.';
    header('Location: manage-departments.php');
    exit;
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">Edit Department</h4>
            
            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <form action="src/controllers/tender/DepartmentController.php" method="post">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="dept_id" value="<?= $department['department_id'] ?>">
                
                <div class="mb-3">
                    <label for="eng_dep" class="form-label">Department Name (English) <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="eng_dep" 
                           id="eng_dep" 
                           class="form-control" 
                           value="<?= htmlspecialchars($department['department_name']) ?>" 
                           required>
                </div>
                
                <div class="mb-3">
                    <label for="hin_dep" class="form-label">Department Name (Hindi) <span class="text-muted">(विभाग का नाम हिंदी में)</span></label>
                    <input type="text" 
                           name="hin_dep" 
                           id="hin_dep" 
                           class="form-control" 
                           placeholder="उदाहरण: सूचना प्रौद्योगिकी विभाग"
                           value="<?= htmlspecialchars($department['department_name_hindi'] ?? '') ?>">
                    <small class="text-muted">Optional. Enter department name in Hindi.</small>
                </div>
                
                <div class="mb-3">
                    <label for="dept_code" class="form-label">Department Code</label>
                    <input type="text"
                           name="dept_code"
                           id="dept_code"
                           class="form-control"
                           maxlength="10"
                           style="text-transform:uppercase;"
                           value="<?= htmlspecialchars($department['department_code'] ?? '') ?>"
                           onkeyup="this.value = this.value.toUpperCase();">
                    <small class="text-muted">Optional. Max 10 characters, uppercase only.</small>
                    <div id="codeCheckResult" class="mt-1"></div>
                </div>

                <div class="mb-3">
                    <label for="display_order" class="form-label">Display Order</label>
                    <input type="number"
                           name="display_order"
                           id="display_order"
                           class="form-control"
                           value="<?= $department['display_order'] ?? 0 ?>">
                    <small class="text-muted">Lower numbers appear first.</small>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="active" <?= $department['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $department['status'] == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>  
                </div>
                
                <button type="submit" class="btn btn-primary">Update Department</button>
                <a href="manage-departments.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('dept_code').addEventListener('blur', function() {
    const code = this.value;
    const currentId = <?= $department['department_id'] ?>;
    if (code.length > 0) {
        fetch('src/controllers/tender/DepartmentController.php?action=check_code&code=' + encodeURIComponent(code) + '&exclude_id=' + currentId)
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('codeCheckResult');
                if (data.exists) {
                    resultDiv.innerHTML = '<span class="text-danger">⚠️ Department code already exists!</span>';
                } else {
                    resultDiv.innerHTML = '<span class="text-success">✓ Code available</span>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    } else {
        document.getElementById('codeCheckResult').innerHTML = '';
    }
});
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>