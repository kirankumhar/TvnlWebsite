<?php
require_once __DIR__ . '/src/helpers/session_helper.php';
requireLogin(); // This will redirect if not logged in or session expired

require_once __DIR__ . '/layouts/header.php';

$params = [];
$noticeCategorySql = "SELECT id, sub_category_name, domain_id FROM sub_category WHERE is_deleted = '0'";

if ($domainId > 0) {
    $noticeCategorySql .= " AND domain_id = ?";
    $params[] = $domainId;
}

$noticeCategorySql .= " AND sub_category_name LIKE ? ORDER BY sub_category_name ASC";
$params[] = "%notice%";

$noticeCategoryStmt = $pdo->prepare($noticeCategorySql);
$noticeCategoryStmt->execute($params);
$noticeCategories = $noticeCategoryStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body p-0">
            <div class="col-md-12">
                <div class="card-header-modern d-flex align-items-center justify-content-between">
                    Create Notice
                </div>

                <div class="p-2">
                    <!-- rest form / content -->
                </div>

                <?php if (isset($_SESSION['message'])) { ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <strong>Success!</strong> <?php echo $_SESSION['message']; ?>.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?php unset($_SESSION['message']); ?>
                    </div>
                <?php } elseif (isset($_SESSION['error'])) { ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <?php echo $_SESSION['error']; ?>.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php } ?>

                <form id="form" action="<?= $base_url ?>/src/controllers/PostingController.php" method="post"
                    id="news_form" enctype="multipart/form-data">
                    <?php
                    if (isset($_SESSION['error_message'])) {
                        echo '<div style="color: red;">' . $_SESSION['error_message'] . '</div><br>';
                        unset($_SESSION['error_message']);
                    }
                    $err = isset($_SESSION['req_error_msg']) ? $_SESSION['req_error_msg'] : '';
                    ?>

                    <?php if (isset($_SESSION['success_message'])) { ?>
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <strong>Success!</strong> <?php echo $_SESSION['success_message']; ?>.
                            <button type="button"
                                class="btn btn-sm btn-primary ml-3"
                                aria-label="Close"
                                onclick="closeAlert(this)">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?php unset($_SESSION['success_message']); ?>
                        </div>
                    <?php } elseif (isset($_SESSION['error_message'])) { ?>
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

                    <div class="row">
                        <input type="hidden" name="page" id="currentPage" value="notice">
                        <input type="hidden" name="domainId" id="domainId" value="<?= (int)$domainId; ?>">

                        <div class="col-md-6">
                            <!-- Notice Category -->
                            <div class="mb-3">
                                <input type="hidden" name="cat_id" value="2">
                                <label for="noticeCategory" class="form-label">Category <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" name="noticeCategory" id="postcategoryId" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($noticeCategories as $noticeCategory): ?>
                                        <option value="<?= (int)$noticeCategory['id']; ?>" data-domain-id="<?= (int)$noticeCategory['domain_id']; ?>">
                                            <?= htmlspecialchars($noticeCategory['sub_category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($_SESSION['req_error_msg']['noticeCategory'])) { ?>
                                    <div class="form-text text-danger">
                                        <?php echo $_SESSION['req_error_msg']['noticeCategory']; ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- News Date -->
                            <div class="mb-3">
                                <label for="childSubCategoryId" class="form-label">Sub Category</label>
                                <select name="childSubCategoryId" id="SubCategoryId" class="form-select" >
                                    <option value="">Choose Child Sub Category...</option>
                                </select>
                            </div>
                        </div>

                        <!-- Ref. No. -->
                        <div class="col-md-6 mb-3">
                            <label for="referenceNo" class="form-label">Ref. No. </label>
                            <input type="text" class="form-control" id="referenceNo" name="referenceNo"
                                value="<?= $_SESSION['post']['referenceNo'] ?? ''; ?>">

                            <?php if (isset($_SESSION['req_error_msg']['referenceNo'])) { ?>
                                <div class="form-text text-danger"><?php echo $_SESSION['req_error_msg']['referenceNo']; ?>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="col-md-6">
                            <!-- Dated -->
                            <div class="mb-3">
                                <label for="dated" class="form-label">Dated <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dated" name="dated"
                                    value="<?= $_SESSION['post']['dated'] ?? ''; ?>" max="<?= date('Y-m-d'); ?>"
                                    required>
                                <?php if (isset($_SESSION['req_error_msg']['dated'])) { ?>
                                    <div class="form-text text-danger"><?php echo $_SESSION['req_error_msg']['dated'] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- Posting Dated -->
                        <!-- <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="datePosting" class="form-label">Date of Posting <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="datePosting" name="datePosting"
                                        value="<?= @$_SESSION['post']['datePosting']; ?>"
                                        max="<?php echo date('Y-m-t'); ?>" required>
                                    
                                        <?php if (isset($_SESSION['req_error_msg']['datePosting'])) { ?>
                                            <div class="form-text text-danger"><?php echo $_SESSION['req_error_msg']['datePosting']; ?></div>
                                        <?php } ?>
                                </div>
                            </div> -->

                        <!-- Notice Title -->
                        <div class="col-md-12 mb-3">
                            <label for="noticeTitle" class="form-label">Notice Title <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" class="form-control" id="noticeTitle" name="noticeTitle"
                                placeholder="Title Max 255 characters" maxlength="255"
                                required>  <?= $_SESSION['post']['noticeTitle'] ?? ''; ?> </textarea>

                            <?php if (isset($_SESSION['req_error_msg']['noticeTitle'])) { ?>
                                <div class="form-text text-danger"><?php echo $_SESSION['req_error_msg']['Notice_title']; ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="notice_type" class="form-label">Notice Type <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="notice_type" name="notice_type" required>
                                <option value="F">File</option>
                                <option value="E">External URL</option>
                                <option value="Both">Both URL & File</option>
                                <option value="None">None</option>
                            </select>
                        </div>

                        <div class="col-md-8" id="noticeURLWrapper">
                            <label for="attachNoticeURL" class="form-label">Notice URL &nbsp;&nbsp; ( Is New Tab ? <input type="radio" name="isNewTab" value="yes" id="radioYes" checked required> Yes <input type="radio" name="isNewTab" value="no" id="radioNo"> No )</label>
                            <input type="text" name="attachNoticeURL" id="attachNoticeURL" class="form-control" required>
                        </div>

                        <!-- Notice Notice -->
                        <div class="col-md-5 mb-3" id="noticeFileWrapper">
                            <label for="attachNotice" class="form-label">
                                Notice Attach. <span class="text-danger">*</span>
                                <small class="text-muted">(File size must be less than 1MB, PDF,JPG Only)</small>
                            </label>

                            <input type="file" class="form-control" id="attachNotice" name="attachNotice"
                                accept="application/pdf" required>
                            <div id="preview-container" class="mt-2"></div>
                            <?php if (isset($err['attachNotice'])) { ?>
                                <div class="form-text text-danger"><?php echo $err['attachNotice']; ?></div>
                            <?php } ?>
                        </div>
                        <div class="col-md-3 preview-container" id="noticeFilePreview"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="isNewTag" class="form-label">Is New Tag? <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="isNewTag" name="new_tag" required>
                                <option value="N">No</option>
                                <option value="Y">Yes</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3" id="newTagDaysWrapper" style="display: none;">
                            <label for="newTagDays" class="form-label">New Tag Days <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="newTagDays" name="newTagDays"
                                placeholder="Enter newTagDays" max="100" value="0">
                        </div>
                    </div>


                    <!-- Submit Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content">
                        <button type="reset" class="btn btn-secondary me-md-2">Clear</button>
                        <button type="submit" class="btn btn-primary">Post Notice</button>
                    </div>
                </form>
                <script>
                    document.getElementById('postcategoryId').addEventListener('change', function () {
                        var selected = this.options[this.selectedIndex];
                        document.getElementById('domainId').value = selected.dataset.domainId || '<?= (int)$domainId; ?>';
                    });
                </script>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const notice_type = document.getElementById('notice_type');
        const noticeURLWrapper = document.getElementById('noticeURLWrapper');
        const noticeFileWrapper = document.getElementById('noticeFileWrapper');
        const noticeFilePreview = document.getElementById('noticeFilePreview');

        const FileInput = document.getElementById('attachNotice');
        const URLInput = document.getElementById('attachNoticeURL');

        const isNewTag = document.getElementById('isNewTag');
        const newTagDaysWrapper = document.getElementById('newTagDaysWrapper');
        const newTagDaysInput = document.getElementById('newTagDays');

        function toggleNoticeType() {
            if (notice_type.value === 'E') {
                noticeURLWrapper.style.display = 'block';
                noticeFileWrapper.style.display = 'none';
                noticeFilePreview.style.display = 'none';

                URLInput.removeAttribute('disabled');
                FileInput.value = '';
                FileInput.required = false;
                FileInput.setAttribute('disabled', true);
                URLInput.required = true;

            } else if (notice_type.value === 'F') {
                noticeURLWrapper.style.display = 'none';
                noticeFileWrapper.style.display = 'block';
                noticeFilePreview.style.display = 'block';

                URLInput.value = '';
                FileInput.removeAttribute('disabled');
                URLInput.setAttribute('disabled', true);
                URLInput.required = false;
                FileInput.required = true;
            } else if (notice_type.value === 'Both') {
                noticeURLWrapper.style.display = 'block';
                noticeFileWrapper.style.display = 'block';
                noticeFilePreview.style.display = 'block';

                FileInput.value = '';
                URLInput.value = '';

                URLInput.removeAttribute('disabled');
                FileInput.removeAttribute('disabled');

                URLInput.required = true;
                FileInput.required = true;
            } else {
                noticeURLWrapper.style.display = 'none';
                noticeFileWrapper.style.display = 'none';
                noticeFilePreview.style.display = 'none';

                FileInput.value = '';
                URLInput.value = '';

                URLInput.setAttribute('disabled', true);
                FileInput.setAttribute('disabled', true);

                URLInput.required = false;
                FileInput.required = false;
            }

        }

        function toggleNewTagDays() {
            if (isNewTag.value === 'Y') {
                newTagDaysWrapper.style.display = 'block';
                newTagDaysInput.removeAttribute('readonly');
            } else {
                newTagDaysWrapper.style.display = 'none';
                newTagDaysInput.value = 0;
                newTagDaysInput.setAttribute('readonly', true);
            }
        }

        // Initial check
        toggleNewTagDays();
        toggleNoticeType();

        // On change
        isNewTag.addEventListener('change', toggleNewTagDays);
        notice_type.addEventListener('change', toggleNoticeType);

    });
</script>

<?php
$embed_script = "newsForm.js";
require_once __DIR__ . '/layouts/footer.php'; ?>
