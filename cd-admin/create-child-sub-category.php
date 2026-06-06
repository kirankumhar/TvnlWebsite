<?php
session_start();
include('./timeout.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}
if (isset($_SESSION['user_id'])) {

    require_once __DIR__ . '/layouts/header.php';

    $params = [];
    $categorySql = "SELECT id, category_name, domain_id FROM category_master WHERE is_deleted = '0'";

    if ($domainId > 0) {
        $categorySql .= " AND domain_id = ?";
        $params[] = $domainId;
    }

    $categorySql .= " ORDER BY category_name ASC";
    $categoryStmt = $pdo->prepare($categorySql);
    $categoryStmt->execute($params);
    $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0">
                <div class="col-md-12">
                    <div class="card-header-modern d-flex align-items-center justify-content-between">
                        Create Child Sub Category
                    </div>

                    <div class="p-2">
                        <!-- rest form / content -->
                    </div>

                    <?php if (isset($_SESSION['message'])) { ?>
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <strong>Success!</strong> <?php echo $_SESSION['message']; ?>.
                            <button type="button"
                                class="btn btn-sm btn-primary ml-3"
                                aria-label="Close"
                                onclick="closeAlert(this)">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?php unset($_SESSION['message']); ?>
                        </div>
                    <?php } elseif (isset($_SESSION['error'])) { ?>
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <?php echo $_SESSION['error']; ?>.
                            <button type="button"
                                class="btn btn-sm btn-primary ml-3"
                                aria-label="Close"
                                onclick="closeAlert(this)">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php } ?>

                    <form action="<?= $base_url ?>/src/controllers/ChildSubCategoryController.php" method="post">
                        <input type="hidden" name="domainId" id="domainId" value="<?= (int)$domainId; ?>">
                        <div class="mb-3">
                            <label for="categoryId" class="form-label">Category Name<span class="text-danger">*</span></label>
                            <select name="categoryId" id="categoryId" class="form-control" required>
                                <option value="">Choose Category...</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int)$category['id']; ?>" data-domain-id="<?= (int)$category['domain_id']; ?>">
                                        <?= htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="subCategoryId" class="form-label">Sub Category Name <span class="text-danger">*</span></label>
                            <select name="subCategoryId" id="subCategoryId" class="form-select" required>
                                <option value="">Choose Sub Category...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="chsubCatName" class="form-label">English Child Sub-Category Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="chsubCatName" id="chsubCatName" class="form-control" value=""
                                required>

                        </div>

                        <div class="mb-3">
                            <label for="chhnSubCatName" class="form-label">Hindi Child Sub-Category Name</label>
                            <input type="text" name="chhnSubCatName" id="chhnSubCatName" class="form-control"
                                value="">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description (optional)</label>
                            <textarea name="description" id="description" class="form-control"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Submit</button>

                    </form>
                    <script>
                        document.getElementById('categoryId').addEventListener('change', function () {
                            var selected = this.options[this.selectedIndex];
                            document.getElementById('domainId').value = selected.dataset.domainId || '<?= (int)$domainId; ?>';
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
    <?php
    $embed_script = "newsForm.js";
    require_once __DIR__ . '/layouts/footer.php'; ?>
<?php } else {
    echo "Invalid session, <a href='index.php'>click here</a> to login";
}
?>
