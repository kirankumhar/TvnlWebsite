<?php
session_start();

require_once __DIR__ . '/layouts/header.php';
if (isset($_SESSION['user_id'])) {
    $title = "Admin - Add Category";

    $sql_type = "SELECT * FROM sy_fy ORDER BY id desc";

    $types = $pdo->query($sql_type)->fetchAll(PDO::FETCH_ASSOC);

    $sql_cat = "SELECT * FROM category_master WHERE is_deleted='0'";

    $categories = $pdo->query($sql_cat)->fetchAll(PDO::FETCH_ASSOC);

    $sql_subcat = "SELECT * FROM sub_category WHERE is_deleted='0'";

    $subcategories = $pdo->query($sql_subcat)->fetchAll(PDO::FETCH_ASSOC);

?>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0">
                <div class="col-md-12">
                    <div class="card-header-modern d-flex align-items-center justify-content-between">
                        Post Documents
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

                    <form id="form" action="controllers/insertTenderController.php" method="post" id="news_form"
                        enctype="multipart/form-data">
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
                                            $selected = ($year === $latestFinancialYear) ? "selected" : "";

                                            echo "<option value='$year' $selected>$year</option>";
                                        }
                                        ?>

                                    </select>
                                    <?php if (isset($err['financialYear'])) { ?>
                                        <div class="form-text text-danger"><?php echo $err['financialYear']; ?></div>
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
                                        <option value="Tender">Tender</option>
                                        <option value="REF">Short Tender Notice</option>
                                    </select>
                                    <?php if (isset($err['tenderCategory'])) { ?>
                                        <div class="form-text text-danger"><?php echo $err['tenderCategory']; ?></div>
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
                                    <?php if (isset($err['tenderType'])) { ?>
                                        <div class="form-text text-danger"><?php echo $err['tenderType']; ?></div>
                                    <?php } ?>
                                </div>
                            </div>

                            <!-- NIT/Ref. No. -->
                            <div class="col-md-6 mb-3">
                                <label for="nit_ref_no" class="form-label">NIT/ Ref. No. <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" class="form-control" id="nit_ref_no"
                                    name="nit_ref_no" placeholder="Max 255 characters" maxlength="255"
                                    value="<?= @$_SESSION['post']['nit_ref_no']; ?>" required>
                                <?php if (isset($err['nit_ref_no'])) { ?>
                                    <div class="form-text text-danger"><?php echo $err['nit_ref_no']; ?></div>
                                <?php } ?>
                            </div>

                            <div class="col-md-6">
                                <!-- Dated -->
                                <div class="mb-3">
                                    <label for="dated" class="form-label">Dated <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="dated" name="dated"
                                        value="<?= @$_SESSION['post']['dated']; ?>"
                                        max="<?php echo date('Y-m-d'); ?>" required>
                                    <?php if (isset($err['dated'])) { ?>
                                        <div class="form-text text-danger"><?php echo $err['dated']; ?></div>
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
                                        max="<?php echo date('Y-m-d'); ?>" required>
                                    <?php if (isset($err['datePosting'])) { ?>
                                        <div class="form-text text-danger"><?php echo $err['datePosting']; ?></div>
                                    <?php } ?>
                                </div>
                            </div>

                            <!-- Tender Title -->
                            <div class="col-md-12 mb-3">
                                <label for="tender_title" class="form-label">Tender Title <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" class="form-control" id="tender_title"
                                    name="tender_title" placeholder="Title Max 255 characters" maxlength="255"
                                    required> <?= @$_SESSION['post']['tender_title']; ?> </textarea>
                                <?php if (isset($err['tender_title'])) { ?>
                                    <div class="form-text text-danger"><?php echo $err['tender_title']; ?></div>
                                <?php } ?>
                            </div>

                            <!-- Tender Notice -->
                            <div class="col-md-6 mb-3">
                                <label for="attachNotice" class="form-label">
                                    Tender Notice <span class="text-danger">*</span>
                                    <small class="text-muted">(File size must be less than 1MB, PDF Only)</small>
                                </label>

                                <input type="file" class="form-control" id="attachNotice" name="attachNotice"
                                    accept="application/pdf" required>
                                <div id="previewImage1" class="mt-2"></div>
                                <?php if (isset($err['attachNotice'])) { ?>
                                    <div class="form-text text-danger"><?php echo $err['attachNotice']; ?></div>
                                <?php } ?>
                            </div>

                            <!-- Tender Documents -->
                            <div class="col-md-6 mb-3">
                                <label for="attachDoc" class="form-label">
                                    Tender Document
                                    <small class="text-muted">(File size must be less than 1MB, PDF Only)</small>
                                </label>

                                <input type="file" class="form-control" id="attachDoc" name="attachDoc"
                                    accept="application/pdf">
                                <div id="previewImage1" class="mt-2"></div>
                                <?php if (isset($err['attachDoc'])) { ?>
                                    <div class="form-text text-danger"><?php echo $err['attachDoc']; ?></div>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- PDF Section -->
                        <div class="card mb-3">
                            <div class="card-header bg-info">
                                <h5 class="mb-0 text-light">Other Attachment</h5>
                            </div>
                            <div class="card-body" id="pdfAttachments">

                                <div class="row" id="pdf1">
                                    <div class="col-md-6">
                                        <label class="w-100 pdf_attachement1">
                                            <span class="text">Other Attachment 1 <small class="text-muted">(File size must be less than 1MB, Excel, PPT, PDF)</small></span>
                                            <input type="file" name="pdf_attachement1" class="form-control"
                                                id="pdf_attachment1" value="" data-constraints="@Required"
                                                accept=".pdf, .xls, .xlsx, .ppt, .pptx" />
                                        </label>
                                        <?php if (isset($err['pdf_attachement1'])) {
                                            echo '<span class="empty-message">' . $err['pdf_attachement1'] . '</span>';
                                        } ?>
                                    </div>
                                </div>

                                <div class="row" id="pdf2" style="display: none;">
                                    <div class="col-md-6">
                                        <label class="w-100 pdf_attachement2">
                                            <span class="text">Other Attachment 2 <small class="text-muted">(File size must be less than 1MB, Excel, PPT, PDF)</small></span>
                                            <input type="file" name="pdf_attachement2" class="form-control"
                                                id="pdf_attachement2" value="" data-constraints="@Required"
                                                accept=".pdf, .mp3" />
                                        </label>
                                        <?php if (isset($err['pdf_attachement2'])) {
                                            echo '<span style="color:red">' . $err['pdf_attachement2'] . '</span>';
                                        } ?>
                                    </div>

                                </div>

                                <div class="row" id="pdf3" style="display: none;">
                                    <div class="col-md-6">
                                        <label class="w-100 pdf_attachement3">
                                            <span class="text">Other Attachment 3 <small class="text-muted">(File size must be less than 1MB, Excel, PPT, PDF)</small></span>
                                            <input type="file" name="pdf_attachement3" class="form-control"
                                                id="pdf_attachment3"
                                                value="<?= @$_SESSION['post']['pdf_attachement3']; ?>"
                                                data-constraints="@Required" accept=".pdf, .mp3" />
                                        </label>
                                        <?php if (isset($err['pdf_attachement3'])) {
                                            echo '<span style="color:red">' . $err['pdf_attachement3'] . '</span>';
                                        } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" id="deletePdf" style="color:red">Delete Last
                                    Attachment</button>
                                <button type="button" id="addPdf">Add More</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="newTagDays" class="form-label">New Tag Days <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" class="form-control" id="newTagDays"
                                    name="newTagDays" placeholder="Enter newTagDays"
                                    value="<?= @$_SESSION['post']['newTagDays']; ?>" required>
                            </div>
                            <!-- <div class="col-md-6 mb-3">
                                    <label for="hashTag" class="form-label">
                                        Hash Tags
                                        <small class="text-muted">(Comma separated)</small>
                                    </label>
                                    <input type="text" class="form-control" class="form-control" id="hashTag" name="hashTag"
                                        placeholder="#hashtag1, #hashtag2" value="<?= @$_SESSION['post']['hashTag']; ?>">
                                </div> -->
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-secondary me-md-2">Clear</button>
                            <button type="submit" class="btn btn-primary">Post News</button>
                        </div>
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