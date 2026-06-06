<?php
session_start();
include('./timeout.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/layouts/header.php';

if (isset($_SESSION['user_id'])) {

    $sql_type = "SELECT * FROM sy_fy ORDER BY id desc";
    $types = $pdo->query($sql_type)->fetchAll(PDO::FETCH_ASSOC);

    $sql_cat = "SELECT * FROM category_master WHERE is_deleted='0'";
    $categories = $pdo->query($sql_cat)->fetchAll(PDO::FETCH_ASSOC);
    $sql_subcat = "SELECT * FROM sub_category WHERE is_deleted='0'";
    $subcategories = $pdo->query($sql_subcat)->fetchAll(PDO::FETCH_ASSOC);

    $postingId = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $stmt = $pdo->prepare("SELECT a.*, b.type as syfy_type FROM postings a INNER JOIN sy_fy b ON a.type = b.id WHERE a.id = :postingId AND is_deleted='0'");

    $stmt->bindParam(':postingId', $postingId, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
?>

    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0">
                <div class="col-md-12">
                    <div class="card-header-modern d-flex align-items-center justify-content-between">
                        Edit Post Documents
                        <a href="javascript:history.back()" class="btn btn-danger btn-sm">
                            ← Back
                        </a>
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

                    <form action="<?= $base_url ?>/src/controllers/UpdatePostController.php" method="post"
                        enctype='multipart/form-data'>

                        <div class="row">
                            <input type="hidden" name="id" value="<?= $postingId ?>">
                            <div class="mb-3 col-md-6">
                                <label for="type" class="form-label">Type </label>

                                <select name="type" id="type" class="form-control">
                                    <?php if ($data['syfy_type'] == 'sy') {
                                        echo '<option value="sy">Session
                                            Year</option>';
                                    }
                                    if ($data['syfy_type'] == 'fy') {
                                        echo '<option value="sy">Financial Year</option>';
                                    } ?>

                                </select>
                            </div>
                            <div class="mb-3 col-md-6" id="syField" <?php echo ($data['syfy_type'] == 'sy') ? '' : 'style="display: none;"' ?>>
                                <label for="sessionYear" class="form-label">Session Year </label>
                                <select name="sessionYear" id="" class="form-control" <?php echo ($data['syfy_type'] == 'sy') ? '' : 'disabled' ?>>
                                    <?php foreach ($types as $type):
                                        if ($type['calender_year'] != null) {
                                            if ($data['type'] === $type['id']) {
                                                echo '<option value="' . $type['id'] . '"> ' . htmlspecialchars($type['calender_year']) . '
                                            </option>';
                                            }
                                        }
                                    endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6" id="fyField" <?php echo ($data['syfy_type'] == 'fy') ? '' : 'style="display: none;"' ?>>
                                <label for="financialYear" class="form-label">Financial Year </label>
                                <select name="financialYear" id="" class="form-control" <?php echo ($data['syfy_type'] == 'fy') ? '' : 'disabled' ?>>
                                    <?php foreach ($types as $type):
                                        if ($type['financial_year'] != null) {
                                            if ($data['type'] === $type['id']) {
                                                echo '<option value="' . htmlspecialchars($type['id']) . '"> ' . htmlspecialchars($type['financial_year']) . '</option>';
                                            }
                                        }
                                    endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <!-- *********************************** -->
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="category" class="form-label">Category </label>
                                <select name="category" id="category" class="form-control">
                                    <option value="">Choose Category..</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo $data['category'] === $category['id'] ? "selected" : "" ?>>
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="subCategory" class="form-label">Sub Category Name </label>
                                <select name="subCategory" id="subCategory" class="form-control">
                                    <option value="">Choose Sub Category..</option>
                                    <?php foreach ($subcategories as $subcategory): ?>
                                        <option value="<?php echo htmlspecialchars($subcategory['id']); ?>" <?php echo $data['sub_category'] === $subcategory['id'] ? "selected" : "" ?>>
                                            <?php echo htmlspecialchars($subcategory['sub_category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="doc_nos" class="form-label">Document No. <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="doc_nos" id="doc_nos" class="form-control"
                                    value="<?= htmlspecialchars($data['document_no']); ?>" required>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="doc_date" class="form-label">Document Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="doc_date" id="doc_date" class="form-control"
                                    value="<?= htmlspecialchars($data['dated']); ?>" max="<?= date("Y-m-d") ?>" required>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="ref_nos" class="form-label">Reference No.</label>
                                <input type="text" name="ref_nos" id="ref_nos" class="form-control"
                                    value="<?= htmlspecialchars($data['reference_no']); ?>">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="ref_date" class="form-label">Reference Date</label>
                                <input type="date" name="ref_date" id="ref_date" class="form-control"
                                    value="<?= htmlspecialchars($data['reference_date']); ?>" max="<?= date("Y-m-d") ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control"
                                value="<?= htmlspecialchars($data['title']); ?>" required>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="attachment" class="form-label">Attachment <span
                                        class="text-danger">*</span></label>
                                <input type="file" name="attachment" id="attachment" class="form-control"
                                    value="<?= htmlspecialchars($data['attachment']); ?>" <?= empty($data['attachment']) ? "" : "disabled"; ?>>
                                <small id="size_error" style="display:none" class="text-danger">Please check File attachment
                                    size or file type.</small>
                                <?= empty($data['attachment']) ? "" : "<span class='text-danger'>First you need to delete attachment (if required) to enable attachment input.</span>"; ?>
                            </div>
                            <?php if (!empty($data['attachment'])) { ?>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#docmodal">
                                        View Documents
                                    </button>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <button type="button" class="btn btn-danger btn-sm" id="delete-attach-button"
                                        data-id="<?php echo htmlspecialchars($data['id']); ?>">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                                <div class="modal fade" id="docmodal" tabindex="-1" aria-labelledby="docmodalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="docmodalLabel">View Documents</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <embed src="<?= $base_url . '' . $data['attachment']; ?>" height="500" width="1000px">

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } else {
                                echo '<div class="col-md-6" id="preview-attachment"></div>';
                            } ?>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="new_tag" class="form-label">New Tag <span class="text-danger">*</span></label>
                                <select name="new_tag" id="new_tag" class="form-control">
                                    <option value="Y" <?php echo $data['new_flag'] == 'Y' ? "selected" : "" ?>>Yes</option>
                                    <option value="N" <?php echo $data['new_flag'] == 'N' ? "selected" : "" ?>>No</option>
                                </select>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="new_tag_day" class="form-label">Show New Tag in Days <span
                                        class="text-danger">*</span></label>
                                <input type="number" name="new_tag_day" id="new_tag_day" class="form-control"
                                    value="<?= htmlspecialchars($data['new_no_of_days']); ?>">
                            </div>

                            <!-- <div class="mb-3 col-md-6">
                                <label for="status" class="form-label">Status<span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control">
                                    <option value="active" <?php echo $data['status'] == "active" ? "selected" : "" ?>>Active
                                    </option>
                                    <option value="inactive" <?php echo $data['status'] == "inactive" ? "selected" : "" ?>>
                                        Inactive</option>
                                </select>
                            </div> -->
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/layouts/footer.php'; ?>
<?php } else {
    echo "Invalid session, <a href='index.php'>click here</a> to login";
}
?>