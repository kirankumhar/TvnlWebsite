<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "Invalid session, <a href='index.php'>click here</a> to login";
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

require_once __DIR__ . '/layouts/header.php';

$sql_subcat = "SELECT * FROM sub_category WHERE is_deleted='0' AND category_id=1";
$subcategories = $pdo->query($sql_subcat)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="card-body p-0">
                <div class="col-md-12">
                    <div class="card-header-modern d-flex align-items-center justify-content-between">
                        Create Tender
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

                    <form id="form" action="<?= $base_url ?>/src/controllers/tender/TenderController.php" method="post" id="news_form" enctype="multipart/form-data">
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
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <?php unset($_SESSION['success_message']); ?>
                            </div>
                        <?php } elseif (isset($_SESSION['error_message'])) { ?>
                            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                <?php echo $_SESSION['error']; ?>.
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
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
                                            $selected = (isset($_SESSION['post']['financialYear']) && $_SESSION['post']['financialYear'] == $year) ? "selected" : "";

                                            echo "<option value='$year' $selected>$year</option>";
                                        }
                                        ?>

                                    </select>
                                    <?php if (isset($_SESSION['req_error_msg']['financialYear'])) { ?>
                                        <div class="form-text text-danger"><?php echo $_SESSION['req_error_msg']['financialYear']; ?></div>
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
                                            // $selected = ($subCat === $latestFinancialsubCat) ? "selected" : "";

                                            echo "<option value='" . $subCat['id'] . "'>" . $subCat['sub_category_name'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <?php if (isset($_SESSION['req_error_msg']['tenderCategory'])) { ?>
                                        <div class="form-text text-danger"><?php echo $_SESSION['req_error_msg']['tenderCategory']; ?></div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Tender Type -->
                                <div class="mb-3">
                                    <label for="tenderType" class="form-label">Type <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" name="tenderType" id="tenderType" required>
                                        <option value="">Select Type</option>
                                        <option value="NIT">NIT No.</option>
                                        <option value="REF">Reference No</option>
                                    </select>
                                    <?php if (isset($_SESSION['req_error_msg']['tenderType'])) { ?>
                                        <div class="form-text text-danger"><?php echo $_SESSION['req_error_msg']['tenderType']; ?></div>
                                    <?php } ?>
                                </div>
                            </div>

                            <!-- NIT/Ref. No. -->
                            <div class="col-md-6 mb-3">
                                <label for="nit_ref_no" class="form-label">NIT/ Ref. No. <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nit_ref_no" name="nit_ref_no" value="<?= $_SESSION['post']['nit_ref_no'] ?? ''; ?>" required>

                                <?php if (isset($_SESSION['req_error_msg']['nit_ref_no'])) { ?>
                                    <div class="form-text text-danger"><?php echo $_SESSION['req_error_msg']['nit_ref_no']; ?></div>
                                <?php } ?>
                            </div>

                            <div class="col-md-6">
                                <!-- Dated -->
                                <div class="mb-3">
                                    <label for="dated" class="form-label">Dated <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="dated" name="dated"
                                        value="<?= $_SESSION['post']['dated'] ?? ''; ?>"
                                        max="<?= date('Y-m-d'); ?>" required>
                                    <?php if (isset($_SESSION['req_error_msg']['dated'])) { ?>
                                        <div class="form-text text-danger"><?php echo $_SESSION['req_error_msg']['dated'] ?></div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Posting Dated -->
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
                            </div>

                            <!-- Tender Title -->
                            <div class="col-md-12 mb-3">
                                <label for="tender_title" class="form-label">Tender Title <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" class="form-control" id="tender_title"
                                    name="tender_title" placeholder="Title Max 255 characters" maxlength="255"
                                    required>  <?= $_SESSION['post']['tender_title'] ?? ''; ?> </textarea>

                                <?php if (isset($_SESSION['req_error_msg']['tender_title'])) { ?>
                                    <div class="form-text text-danger"><?php echo $_SESSION['req_error_msg']['tender_title']; ?></div>
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
                                    accept="application/pdf" required>
                                <div id="preview-container" class="mt-2"></div>
                                <?php if (isset($err['attachNotice'])) { ?>
                                    <div class="form-text text-danger"><?php echo $err['attachNotice']; ?></div>
                                <?php } ?>
                            </div>
                            <div class="col-md-6 preview-container"></div>
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
                            <div class="col-md-6 preview-container"></div>
                        </div>

                </div>

                <!-- PDF Section -->
                <div class="card mb-3">
                    <div class="card-header bg-info">
                        <h5 class="mb-0 text-light">Other Attachment</h5>
                    </div>
                    <div class="card-body" id="pdfAttachments">
                        <!-- Attachment 1 -->
                        <div class="row" id="pdf1">
                            <div class="col-md-6">
                                <label class="w-100 pdf_attachment1">
                                    <span class="text">Other Attachment 1 <small class="text-muted">(Max 1MB, PDF, Excel, PPT)</small></span>
                                    <input type="file" name="pdf_attachment1" class="form-control"
                                        id="pdf_attachment1" accept=".pdf, .xls, .xlsx, .ppt, .pptx" />
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="w-100 pdf_attachment_title1">
                                    <span class="text">Other Attachment Title 1</span>
                                    <input type="text" name="pdf_attachment_title1" class="form-control"
                                        id="pdf_attachment_title1" />
                                </label>
                            </div>
                            <div class="col-md-6 preview-container"></div> <!-- Preview Column -->
                        </div>

                        <!-- Attachment 2 (Initially Hidden) -->
                        <div class="row" id="pdf2" style="display: none;">
                            <div class="col-md-6">
                                <label class="w-100 pdf_attachment2">
                                    <span class="text">Other Attachment 2 <small class="text-muted">(Max 1MB, PDF, Excel, PPT)</small></span>
                                    <input type="file" name="pdf_attachment2" class="form-control"
                                        id="pdf_attachment2" accept=".pdf, .xls, .xlsx, .ppt, .pptx" />
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="w-100 pdf_attachment_title2">
                                    <span class="text">Other Attachment Title 2</span>
                                    <input type="text" name="pdf_attachment_title2" class="form-control"
                                        id="pdf_attachment_title2" />
                                </label>
                            </div>
                            <div class="col-md-6 preview-container"></div> <!-- Preview Column -->
                        </div>

                        <!-- Attachment 3 (Initially Hidden) -->
                        <div class="row" id="pdf3" style="display: none;">
                            <div class="col-md-6">
                                <label class="w-100 pdf_attachment3">
                                    <span class="text">Other Attachment 3 <small class="text-muted">(Max 1MB, PDF, Excel, PPT)</small></span>
                                    <input type="file" name="pdf_attachment3" class="form-control"
                                        id="pdf_attachment3" accept=".pdf, .xls, .xlsx, .ppt, .pptx" />
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="w-100 pdf_attachment_title3">
                                    <span class="text">Other Attachment Title 3</span>
                                    <input type="text" name="pdf_attachment_title3" class="form-control"
                                        id="pdf_attachment_title3" />
                                </label>
                            </div>
                            <div class="col-md-6 preview-container"></div> <!-- Preview Column -->
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="button" id="deletePdf" style="color:red">Delete Last Attachment</button>
                        <button type="button" id="addPdf">Add More</button>
                    </div>
                </div>



                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="new_tag" class="form-label">New Tag <span
                                class="text-danger">*</span></label>
                        <select name="new_tag" id="new_tag" class="form-select">
                            <option value="N">No</option>
                            <option value="Y">Yes</option>
                        </select>
                        <!-- <input type="text" class="form-control" class="form-control" id="new_tag"
                                        name="new_tag" placeholder="Enter new_tag"
                                        value="<?= @$_SESSION['post']['new_tag']; ?>" required> -->
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="newTagDays" class="form-label">New Tag Days <span
                                class="text-danger">*</span></label>
                        <input type="number" class="form-control" class="form-control" id="newTagDays"
                            name="newTagDays" placeholder="Enter newTagDays" value="0" max="100"
                            value="<?= @$_SESSION['post']['newTagDays']; ?>" readonly>
                    </div>
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