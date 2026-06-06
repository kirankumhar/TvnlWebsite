<?php
session_start();
include('./timeout.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', '1');


if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Session Timeout,Login Again";
    header("Location: index.php"); // Redirect to form
    exit();
}

require_once __DIR__ . '/layouts/header.php';

$tenderId = $_GET['id'] ?? 0;

$tenderQuery = $pdo->prepare('SELECT * FROM tenders WHERE uniq_id = ? AND is_deleted="0"');
$tenderQuery->execute([$tenderId]);
$tender = $tenderQuery->fetch(PDO::FETCH_ASSOC);

$tender_id = $tender['id'];


if (!$tender) {
    die('Tender not found.');
}

function getFinancialYears()
{
    $currentYear = date('Y');
    $currentMonth = date('m');

    // Determine the latest financial year
    if ($currentMonth > 3) {
        $startYear = $currentYear;
    } else {
        $startYear = $currentYear - 1;
    }

    $endYear = $startYear + 1;

    // Generate last 5 financial years including the latest one
    $financialYears = [];
    for ($i = 4; $i >= 0; $i--) {
        $financialYears[] = ($startYear - $i) . '-' . ($endYear - $i);
    }

    return $financialYears;
}

$financialYear = getFinancialYears();

$latestFinancialYear = end($financialYear);


$sql_subcat = "SELECT * FROM sub_category WHERE is_deleted='0' AND category_id=1";
$subcategories = $pdo->query($sql_subcat)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body p-0">
            <div class="col-md-12">
                <div class="card-header-modern d-flex align-items-center justify-content-between">
                    Edit Tender
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

                <form id="form" action="<?= $base_url ?>/src/controllers/tender/EditTenderController.php" method="post"
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

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="financialYear" class="form-label">Financial Year <span
                                        class="text-danger">*</span></label>
                                <select class="form-control" name="financialYear" id="financialYear" required>
                                    <option value="">Select Financial Year</option>

                                    <?php
                                    foreach ($financialYear as $year) {
                                        // $selected = ($year === $latestFinancialYear) ? "selected" : "";
                                        $selected = ($tender['financial_year'] == $year) ? "selected" : "";

                                        echo "<option value='$year' $selected>$year</option>";
                                    }
                                    ?>

                                </select>
                                <?php if (isset($_SESSION['req_error_msg']['financialYear'])) { ?>
                                    <div class="form-text text-danger">
                                        <?php echo $_SESSION['req_error_msg']['financialYear']; ?></div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Tender Category -->
                            <div class="mb-3">
                                <label for="tenderCategory" class="form-label">Tender Category <span
                                        class="text-danger">*</span></label>
                                <select class="form-control" name="tenderCategory" id="tenderCategory" required>
                                    <option value="">Select Category</option>
                                    <?php
                                    foreach ($subcategories as $subCat) {
                                        $selected = ($subCat['id'] == $tender['tender_category']) ? "selected" : "";

                                        echo "<option value='" . $subCat['id'] . "' " . $selected . ">" . $subCat['sub_category_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                                <?php if (isset($_SESSION['req_error_msg']['tenderCategory'])) { ?>
                                    <div class="form-text text-danger">
                                        <?php echo $_SESSION['req_error_msg']['tenderCategory']; ?></div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Tender Type -->
                            <div class="mb-3">
                                <label for="tenderType" class="form-label">Type <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" name="tenderType" id="tenderType" required>
                                    <option value="">Select Type</option>
                                    <option value="NIT" <?= $tender['tender_type'] == 'NIT' ? 'selected' : '' ?>>NIT No.
                                    </option>
                                    <option value="REF" <?= $tender['tender_type'] == 'REF' ? 'selected' : '' ?>>Reference
                                        No</option>
                                </select>
                                <?php if (isset($_SESSION['req_error_msg']['tenderType'])) { ?>
                                    <div class="form-text text-danger">
                                        <?php echo $_SESSION['req_error_msg']['tenderType']; ?></div>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- NIT/Ref. No. -->
                        <div class="col-md-6 mb-3">
                            <label for="nit_ref_no" class="form-label">NIT/ Ref. No. <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nit_ref_no" name="nit_ref_no"
                                value="<?= $tender['tender_nit_ref_no'] ?? ''; ?>" required>

                            <?php if (isset($_SESSION['req_error_msg']['nit_ref_no'])) { ?>
                                <div class="form-text text-danger"><?php echo $_SESSION['req_error_msg']['nit_ref_no']; ?>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="col-md-6">
                            <!-- Dated -->
                            <div class="mb-3">
                                <label for="dated" class="form-label">Dated <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dated" name="dated"
                                    value="<?= date('Y-m-d', strtotime($tender['tender_dated'])); ?>" max="<?= date('Y-m-d'); ?>" required>
                                <?php if (isset($_SESSION['req_error_msg']['dated'])) { ?>
                                    <div class="form-text text-danger"><?php echo $_SESSION['req_error_msg']['dated'] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Posting Dated -->
                            <div class="mb-3">
                                <label for="datePosting" class="form-label">Date of Posting <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="datePosting" name="datePosting"
                                    value="<?= date('Y-m-d', strtotime($tender['tender_posting'])); ?>" max="<?php echo date('Y-m-t'); ?>"
                                    required>

                                <?php if (isset($_SESSION['req_error_msg']['datePosting'])) { ?>
                                    <div class="form-text text-danger">
                                        <?php echo $_SESSION['req_error_msg']['datePosting']; ?></div>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- Tender Title -->
                        <div class="col-md-12 mb-3">
                            <label for="tender_title" class="form-label">Tender Title <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" class="form-control" id="tender_title" name="tender_title"
                                placeholder="Title Max 255 characters" maxlength="255"
                                required>  <?= $tender['tender_title'] ?? ''; ?> </textarea>

                            <?php if (isset($_SESSION['req_error_msg']['tender_title'])) { ?>
                                <div class="form-text text-danger"><?php echo $_SESSION['req_error_msg']['tender_title']; ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Tender Notice -->
                        <div class="col-md-6 mb-3">
                            <label for="attachNotice" class="form-label">
                                Tender Notice <span class="text-danger">*</span>
                                <small class="text-muted">(File size must be less than 1MB, PDF Only)</small>
                            </label>

                            <input type="file" class="form-control" id="attachNotice" name="attachNotice"
                                accept="application/pdf">
                            <div id="preview-container" class="mt-2"></div>
                            <?php if (isset($err['attachNotice'])) { ?>
                                <div class="form-text text-danger"><?php echo $err['attachNotice']; ?></div>
                            <?php } ?>
                        </div>
                        <div class="col-md-6 preview-container">
                            <embed src="https://gpsimdega.ac.in/cdgps/src/<?= $tender['tender_notice_path'] ?>" class="preview" width="100%" height="150px" style="border:1px solid #ccc;">
                        </div>
                    </div>
                    <div class="row">
                        <!-- Tender Documents -->
                        <div class="col-md-6 mb-3">
                            <label for="attachDoc" class="form-label">
                                Tender Document
                                <small class="text-muted">(File size must be less than 1MB, PDF Only)</small>
                            </label>

                            <input type="file" class="form-control" id="attachDoc" name="attachDoc"
                                accept="application/pdf">
                            <div id="preview-container" class="mt-2"></div>
                            <?php if (isset($err['attachDoc'])) { ?>
                                <div class="form-text text-danger"><?php echo $err['attachDoc']; ?></div>
                            <?php } ?>
                        </div>
                        <div class="col-md-6 preview-container">
                            <?php if (!empty($tender['tender_doc_path'])) { ?>
                                <embed src="https://gpsimdega.ac.in/cdgps/src/<?= $tender['tender_doc_path'] ?>" class="preview" width="100%" height="150px" style="border:1px solid #ccc;">
                            <?php } ?>
                        </div>
                    </div>

            </div>

            <!-- PDF Section -->
            <div class="card mb-3">
                <div class="card-header bg-info">
                    <h5 class="mb-0 text-light">Other Attachment</h5>
                </div>
                <div class="card-body" id="pdfAttachments">
                    <!-- Attachment 1 -->
                    <div class="row mb-2" id="pdf1">
                        <div class="col-md-6">
                            <label class="w-100 pdf_attachment1">
                                <span class="text">Other Attachment 1 <small class="text-muted">(Max 1MB, PDF, Excel,
                                        PPT)</small></span>
                                <input type="file" name="pdf_attachment1" class="form-control" id="pdf_attachment1"
                                    accept=".pdf, .xls, .xlsx, .ppt, .pptx" />
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="w-100 pdf_attachment_title1">
                                <span class="text">Other Attachment Title 1</span>
                                <input type="text" name="pdf_attachment_title1" class="form-control" value="<?= $tender['tender_other_attach_1_title'] ?? '' ?>"
                                    id="pdf_attachment_title1" />
                            </label>
                        </div>
                        <div class="col-md-6 preview-container">
                            <?php if (!empty($tender['tender_other_attach_1_path'])) { ?>
                                <a class="btn btn-primary my-2" href="https://gpsimdega.ac.in/cdgps/src/<?= $tender['tender_other_attach_1_path'] ?? '' ?>">See File</a>
                            <?php } ?>
                        </div> <!-- Preview Column -->
                    </div>

                    <!-- Attachment 2 (Initially Hidden) -->
                    <div class="row mb-2" id="pdf2" style="<?= empty($tender['tender_other_attach_2_title']) && empty($tender['tender_other_attach_2_path']) ? 'display: none' : ''; ?>">
                        <div class="col-md-6">
                            <label class="w-100 pdf_attachment2">
                                <span class="text">Other Attachment 2 <small class="text-muted">(Max 1MB, PDF, Excel,
                                        PPT)</small></span>
                                <input type="file" name="pdf_attachment2" class="form-control" id="pdf_attachment2"
                                    accept=".pdf, .xls, .xlsx, .ppt, .pptx" />
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="w-100 pdf_attachment_title2">
                                <span class="text">Other Attachment Title 2</span>
                                <input type="text" name="pdf_attachment_title2" class="form-control"
                                    id="pdf_attachment_title2" value="<?= $tender['tender_other_attach_2_title'] ?? '' ?>" />
                            </label>
                        </div>
                        <div class="col-md-6 preview-container">
                            <?php if (!empty($tender['tender_other_attach_2_path'])) { ?>
                                <a class="btn btn-primary my-2" href="https://gpsimdega.ac.in/cdgps/src/<?= $tender['tender_other_attach_2_path'] ?? '' ?>">See File</a>
                            <?php } ?>
                        </div> <!-- Preview Column -->
                    </div>

                    <!-- Attachment 3 (Initially Hidden) -->
                    <div class="row" id="pdf3" style="<?= empty($tender['tender_other_attach_3_title']) && empty($tender['tender_other_attach_3_path']) ? 'display: none' : ''; ?>">
                        <div class="col-md-6">
                            <label class="w-100 pdf_attachment3">
                                <span class="text">Other Attachment 3 <small class="text-muted">(Max 1MB, PDF, Excel,
                                        PPT)</small></span>
                                <input type="file" name="pdf_attachment3" class="form-control" id="pdf_attachment3"
                                    accept=".pdf, .xls, .xlsx, .ppt, .pptx" />
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="w-100 pdf_attachment_title3">
                                <span class="text">Other Attachment Title 3</span>
                                <input type="text" name="pdf_attachment_title3" class="form-control"
                                    id="pdf_attachment_title3" value="<?= $tender['tender_other_attach_3_title'] ?? '' ?>" />
                            </label>
                        </div>
                        <div class="col-md-6 preview-container">
                            <?php if (!empty($tender['tender_other_attach_3_path'])) { ?>
                                <a class="btn btn-primary my-2" href="https://gpsimdega.ac.in/cdgps/src/<?= $tender['tender_other_attach_3_path'] ?? '' ?>">See File</a>
                            <?php } ?>

                        </div> <!-- Preview Column -->
                    </div>
                </div>

                <div class="card-footer">
                    <button type="button" id="deletePdf" style="color:red">Delete Last Attachment</button>
                    <button type="button" id="addPdf">Add More</button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="new_tag" class="form-label">New Tag <span class="text-danger">*</span></label>
                    <select name="new_tag" id="new_tag" class="form-select">
                        <option value="N" <?= $tender['new_tag'] == 'Y' ? 'selected' : '' ?>>No</option>
                        <option value="Y" <?= $tender['new_tag'] == 'N' ? 'selected' : '' ?>>Yes</option>
                    </select>
                    <!-- <input type="text" class="form-control" class="form-control" id="new_tag"
                                        name="new_tag" placeholder="Enter new_tag"
                                        value="<?= @$_SESSION['post']['new_tag']; ?>" required> -->
                </div>

                <div class="col-md-6 mb-3">
                    <label for="newTagDays" class="form-label">New Tag Days <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" class="form-control" id="newTagDays" name="newTagDays"
                        placeholder="Enter newTagDays" value="0" max="100"
                        value="<?= $tender['tender_newtag_days']; ?>" <?= $tender['new_tag'] == 'Y' ? '' : 'readonly'; ?>>
                </div>
                <input type="hidden" name="tender_id" value="<?= $tenderId ?>">
                <!-- Submit Buttons -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-secondary me-md-2">Clear</button>
                    <button type="submit" class="btn btn-primary">Post Tender</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>


<?php
$embed_script = "userForm.js";

require_once __DIR__ . '/layouts/footer.php'; ?>