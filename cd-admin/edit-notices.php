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
    $postingId = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $stmt = $pdo->prepare("SELECT a.* FROM notices a WHERE a.uniq_id = :postingId AND is_deleted='0'");
    $stmt->bindParam(':postingId', $postingId, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    $domain_id = (int) $data['domain_id'];
    $search = 'notice';

    $sql = "
            SELECT id, sub_category_name
            FROM sub_category
            WHERE is_deleted = '0'
            AND domain_id = :domain_id
            AND sub_category_name LIKE :search
            ORDER BY sub_category_name ASC
        ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':domain_id', $domain_id, PDO::PARAM_INT);
    $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
    $stmt->execute();

    $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $subcategoryId = $data['notice_subcategory'];
    $sql = "SELECT * FROM child_sub_category WHERE subcategory_id = $subcategoryId";
    $childsubcategories = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0">
                <div class="col-md-12">
                    <div class="card-header-modern d-flex align-items-center justify-content-between">
                        Edit Notice
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

                        <!-- *********************************** -->
                        <div class="row">
                            <input type="hidden" name="id" value="<?= $postingId ?>">
                            <input type="hidden" name="page" id="currentPage" value="notice">
                            <div class="mb-3 col-md-6">
                                <label for="domainId" class="form-label">Domains <span
                                        class="text-danger">*</span></label>
                                <select name="domainId" id="subCategoryList" class="form-select" <?= ($domainId > 0) ? 'disabled' : '' ?>>
                                    <option value="">Choose Domain..</option>
                                    <?php foreach ($domains_data as $values): ?>
                                        <option value="<?php echo htmlspecialchars($values['id']); ?>" <?php if (!empty($data['domain_id']) && $data['domain_id'] == $values['id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($values['eng_name']) . ' / ' . htmlspecialchars($values['hin_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($domainId > 0): ?>
                                    <input type="hidden" name="domainId" value="<?= (int)$domainId; ?>">
                                <?php endif; ?>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="subCategory" class="form-label">Category <span
                                        class="text-danger">*</span></label>
                                <select name="subCategory" id="postcategoryId" class="form-select">
                                    <option value="">Choose Category..</option>
                                    <?php foreach ($subcategories as $subcategory): ?>
                                        <option value="<?php echo htmlspecialchars($subcategory['id']); ?>" <?php echo $data['notice_subcategory'] == $subcategory['id'] ? "selected" : "" ?>>
                                            <?php echo htmlspecialchars($subcategory['sub_category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <!-- News Date -->
                                <div class="mb-3">
                                    <label for="childSubCategoryId" class="form-label">Sub Category</label>
                                    <select name="childSubCategoryId" id="SubCategoryId" class="form-select">
                                        <option value="">Choose Child Sub Category...</option>
                                        <?php foreach ($childsubcategories as $subcategory): ?>
                                            <option value="<?php echo htmlspecialchars($subcategory['id']); ?>" <?= $subcategory['id'] == $data['notice_childsubcategory'] ? 'selected' : '' ?>>
                                                <?php echo htmlspecialchars($subcategory['child_sub_category_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="doc_date" class="form-label">Dated <span class="text-danger">*</span></label>
                                <input type="date" name="doc_date" id="doc_date" class="form-control"
                                    value="<?= htmlspecialchars($data['notice_dated']); ?>" max="<?= date("Y-m-d") ?>"
                                    required>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="ref_nos" class="form-label">Reference No.</label>
                                <input type="text" name="ref_nos" id="ref_nos" class="form-control"
                                    value="<?= htmlspecialchars($data['notice_ref_no']); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control"
                                value="<?= htmlspecialchars($data['notice_title']); ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="notice_type" class="form-label">Notice Type <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="notice_type" name="notice_type" required>
                                    <option value="F" <?= !empty($data['notice_path']) && empty($data['notice_url']) ? 'selected' : '' ?>>File</option>
                                    <option value="E" <?= empty($data['notice_path']) && !empty($data['notice_url']) ? 'selected' : '' ?>>External URL</option>
                                    <option value="Both" <?= !empty($data['notice_path']) && !empty($data['notice_url']) ? 'selected' : '' ?>>Both URL & File</option>
                                    <option value="None" <?= empty($data['notice_path']) && empty($data['notice_url']) ? 'selected' : '' ?>>None</option>
                                </select>
                            </div>

                            <div class="col-md-8" id="noticeURLWrapper">
                                <label for="attachNoticeURL" class="form-label">Notice URL &nbsp;&nbsp; (Is New Tab ? <input type="radio" name="isNewTab" value="yes" id="radioYes" <?= $data['url_tab_open'] == '_blank' ? 'checked' : '' ?>> Yes <input type="radio" name="isNewTab" value="no" id="radioNo" <?= $data['url_tab_open'] == '_self' ? 'checked' : '' ?>> No )</label>
                                <input type="text" name="attachNoticeURL" id="attachNoticeURL" class="form-control" value="<?= $data['notice_url'] ?>"
                                    required>
                            </div>

                            <div class="mb-3 col-md-5" id="noticeFileWrapper">
                                <label for="attachment" class="form-label">Attachment <span class="text-danger">*</span>
                                    <small class="text-muted">(File size must be less than 1MB, PDF,JPG
                                        Only)</small></label>
                                <input type="file" name="attachment" id="attachment" class="form-control" value=""
                                    <?= empty($data['notice_path']) ? "" : "disabled"; ?>>
                                <small id="size_error" style="display:none" class="text-danger">Please check File attachment
                                    size or file type.</small>
                                <?= empty($data['notice_path']) ? "" : "<span class='text-danger'>First you need to delete attachment (if required) to enable attachment input.</span>"; ?>
                            </div>
                            <?php if (!empty($data['notice_path'])) { ?>
                                <div class="col-md-3" id="preview-attachment">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#docmodal">
                                        View Documents
                                    </button>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="hidden" name="action" value="deleteAttach">
                                    <input type="hidden" name="post" value="<?= $postingId ?>">
                                    <button type="button" class="btn btn-danger btn-sm" id="delete-attach-button"
                                        data-id="<?php echo htmlspecialchars($data['id']); ?>"
                                        onclick="confirmDeleteAttachment()">
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
                                                <embed src="<?= $base_url . '/src/' . $data['notice_path']; ?>" height="500"
                                                    width="1000px">

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } else {
                                echo '<div class="col-md-3" id="preview-attachment"></div>';
                            } ?>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="new_tag" class="form-label">New Tag <span class="text-danger">*</span></label>
                                <select name="new_tag" id="new_tag" class="form-control" onchange="toggleDaysInput()">
                                    <option value="Y" <?php echo $data['notice_new_tag'] == 'Y' ? "selected" : "" ?>>Yes
                                    </option>
                                    <option value="N" <?php echo $data['notice_new_tag'] == 'N' ? "selected" : "" ?>>No
                                    </option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6" id="days_input_container">
                                <label for="new_tag_day" class="form-label">Show New Tag in Days <span
                                        class="text-danger">*</span></label>
                                <input type="number" name="new_tag_day" id="new_tag_day" class="form-control"
                                    value="<?= htmlspecialchars($data['notice_new_tag_days']); ?>">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="status" class="form-label">Status<span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control">
                                    <option value="A" <?php echo $data['status'] == "A" ? "selected" : "" ?>>Active</option>
                                    <option value="I" <?php echo $data['status'] == "I" ? "selected" : "" ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <script>
                            // Initialize on page load
                            document.addEventListener('DOMContentLoaded', function() {

                                const notice_type = document.getElementById('notice_type');
                                const noticeURLWrapper = document.getElementById('noticeURLWrapper');
                                const noticeFileWrapper = document.getElementById('noticeFileWrapper');
                                const noticeFilePreview = document.getElementById('preview-attachment');

                                const FileInput = document.getElementById('attachment');
                                const URLInput = document.getElementById('attachNoticeURL');

                                const isNewTag = document.getElementById('new_tag');

                                function toggleDaysInput() {
                                    const newTagValue = document.getElementById('new_tag').value;
                                    const daysContainer = document.getElementById('days_input_container');
                                    const daysInput = document.getElementById('new_tag_day');

                                    if (newTagValue === 'Y') {
                                        daysContainer.style.display = 'block';
                                        daysInput.required = true;
                                    } else {
                                        daysContainer.style.display = 'none';
                                        daysInput.required = false;
                                        daysInput.value = '';
                                    }
                                }

                                function toggleNoticeType() {
                                    if (notice_type.value === 'E') {
                                        noticeURLWrapper.style.display = 'block';
                                        noticeFileWrapper.style.display = 'none';
                                        noticeFilePreview.style.display = 'none';

                                        attachNoticeURL.required = true;
                                        FileInput.required = false;
                                    } else if (notice_type.value === 'F') {
                                        noticeURLWrapper.style.display = 'none';
                                        noticeFileWrapper.style.display = 'block';
                                        noticeFilePreview.style.display = 'block';

                                        attachNoticeURL.required = false;
                                        FileInput.required = true;
                                    } else if (notice_type.value === 'Both') {
                                        noticeURLWrapper.style.display = 'block';
                                        noticeFileWrapper.style.display = 'block';
                                        noticeFilePreview.style.display = 'block';

                                        attachNoticeURL.required = true;
                                        FileInput.required = true;
                                    } else {
                                        noticeURLWrapper.style.display = 'none';
                                        noticeFileWrapper.style.display = 'none';
                                        noticeFilePreview.style.display = 'none';

                                        attachNoticeURL.required = false;
                                        FileInput.required = false;
                                    }

                                }

                                toggleDaysInput();

                                toggleNoticeType();

                                // On change
                                isNewTag.addEventListener('change', toggleDaysInput);
                                notice_type.addEventListener('change', toggleNoticeType);
                            });
                        </script>
                        <button type="submit" class="btn btn-primary">Submit</button>

                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
        function confirmDeleteAttachment() {
            if (confirm('Are you sure you want to delete this attachment?')) {
                deleteAttachment();
            }
        }

        function deleteAttachment() {
            const postId = document.querySelector('[name="post"]').value;

            fetch('<?= $base_url ?>/src/controllers/PostingController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=deleteAttach&post=' + encodeURIComponent(postId)
                })
                .then(response => response.json())
                .then(data => {
                    // Handle the response
                    if (data.success) {
                        // Maybe refresh the page or update the UI
                        location.reload();
                    } else {
                        alert('Error deleting attachment');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    </script>

    <?php
    $embed_script = "newsForm.js";
    require_once __DIR__ . '/layouts/footer.php'; ?>
<?php } else {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}
?>