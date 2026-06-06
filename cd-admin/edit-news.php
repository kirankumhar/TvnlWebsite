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
    $title = "Admin - Post News";

    $postingId = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $stmt = $pdo->prepare("SELECT a.* FROM news a WHERE a.uniq_id = :postingId AND is_deleted='0'");
    $stmt->bindParam(':postingId', $postingId, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    $domainId = $data['domain_id'];
    $sql = "SELECT * FROM category_master WHERE domain_id = $domainId";
    $categories = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $categoryId = $data['category_id'];
    $sql = "SELECT * FROM sub_category WHERE category_id = '$categoryId'";
    $subcategories = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $subcategoryId = $data['sub_category_id'];
    $sql = "SELECT * FROM child_sub_category WHERE subcategory_id = $subcategoryId";
    $childsubcategories = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

    <script src="https://cdn.ckeditor.com/4.9.2/standard/ckeditor.js"></script>

    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0">
                <div class="card-header-modern d-flex align-items-center justify-content-between">
                    Edit News
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

                <form id="form" action="<?= $base_url ?>/src/controllers/news/insertNews.php" method="post" id="news_form"
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
                            <input type="hidden" name="page" id="currentPage" value="news">
                            <!-- News Date -->
                            <div class="mb-3">
                                <label for="domainId" class="form-label">Domains<span
                                        class="text-danger">*</span></label>
                                <select name="domainId" id="subCategoryList" class="form-select" required <?= ($domainId > 0) ? 'disabled' : '' ?>>
                                    <option value="">Choose domain...</option>
                                    <?php foreach ($domains_data as $values): ?>
                                        <option value="<?php echo htmlspecialchars($values['id']); ?>" <?php if (!empty($data['domain_id']) && $data['domain_id'] == $values['id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($values['eng_name']) . ' / ' . htmlspecialchars($values['hin_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($domainId > 0): ?>
                                    <input type="hidden" name="domainId" value="<?= (int)$domainId; ?>">
                                <?php endif; ?>
                                <?php if (isset($err['domainId'])) { ?>
                                    <div class="form-text text-danger"><?php echo $err['domainId']; ?></div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- News Date -->
                            <div class="mb-3">
                                <label for="subCategoryId" class="form-label">Category</label>
                                <select name="subCategoryId" id="postcategoryId" class="form-select">
                                    <option value="">Choose Sub Category...</option>
                                    <?php foreach ($subcategories as $subcategory): ?>
                                        <option value="<?php echo htmlspecialchars($subcategory['id']); ?>" <?= $subcategory['id'] == $data['sub_category_id'] ? 'selected' : '' ?>>
                                            <?php echo htmlspecialchars($subcategory['sub_category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- News Date -->
                            <div class="mb-3">
                                <label for="childSubCategoryId" class="form-label">Sub Category</label>
                                <select name="childSubCategoryId" id="SubCategoryId" class="form-select">
                                    <option value="">Choose Child Sub Category...</option>
                                    <?php foreach ($childsubcategories as $childsubcategory): ?>
                                        <option value="<?php echo htmlspecialchars($childsubcategory['id']); ?>" <?= $childsubcategory['id'] == $data['child_sub_category_id'] ? 'selected' : '' ?>>
                                            <?php echo htmlspecialchars($childsubcategory['child_sub_category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <input type="hidden" name="post" value="<?= $postingId ?>">
                            <!-- News Date -->
                            <div class="mb-3">
                                <label for="news_date" class="form-label">Date of News <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="news_date" name="news_date"
                                    value="<?= $data['news_event_date']; ?>" max="<?php echo date('Y-m-d'); ?>" required>
                                <?php if (isset($err['news_date'])) { ?>
                                    <div class="form-text text-danger"><?php echo $err['news_date']; ?></div>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- News Title -->
                        <div class="col-md-12 mb-3">
                            <label for="news_title" class="form-label">News Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" class="form-control" id="news_title" name="news_title"
                                placeholder="Max 255 characters" maxlength="255" value="<?= $data['news_title']; ?>"
                                required>
                            <?php if (isset($err['news_title'])) { ?>
                                <div class="form-text text-danger"><?php echo $err['news_title']; ?></div>
                            <?php } ?>
                        </div>

                        <!-- Hindi News Title -->
                        <div class="col-md-12 mb-3">
                            <label for="news_title_hin" class="form-label">News Title (Hindi)</label>
                            <input type="text" class="form-control" class="form-control" id="news_title_hin" name="news_title_hin"
                                placeholder="Max 255 characters" maxlength="255" value="<?= $data['news_title_hin']; ?>">
                        </div>

                        <!-- News Description -->
                        <div class="col-md-12 mb-3">
                            <label for="editor" class="form-label">News Description <span
                                    class="text-danger">*</span></label>
                            <textarea name="news_description" id="editor" placeholder="Max 15000 chars (Optional)"
                                data-constraints="@Required" maxlength="15000" required
                                cols="50"><?= $data['news_description']; ?></textarea>
                            <script>
                                CKEDITOR.replace('editor', {

                                });
                            </script>
                        </div>

                        <!-- Title Picture -->
                        <div class="col-md-12 mb-3">
                            <label for="picture1" class="form-label">
                                Title Picture <span class="text-danger">*</span>
                                <small class="text-muted">(File size must be less than 500kb)</small>
                            </label>
                            <input type="file" class="form-control" id="picture1" name="picture1"
                                accept="image/png, image/gif, image/jpg, image/jpeg" <?= $data['news_pic1'] ? '' : 'required'; ?>>
                            <div id="previewImage1" class="mt-2">
                                <img style="height: 150px;" class="preview-image" title="title-img"
                                    src="<?= $base_url ?>/src/<?= $data['news_pic1']; ?>">
                            </div>
                            <?php if (isset($err['picture1'])) { ?>
                                <div class="form-text text-danger"><?php echo $err['picture1']; ?></div>
                            <?php } ?>
                        </div>

                        <!-- Picture Attachments -->
                        <div class="col-md-12 mb-3">
                            <label for="picture2" class="form-label">
                                Picture Attachments
                                <small class="text-primary">(Use Ctrl+click to select multiple pictures, Max
                                    6)</small>
                                <small class="text-muted">(File size must be less than 500kb)</small>
                            </label>
                            <input type="file" class="form-control" id="picture2" name="picture2[]" multiple
                                accept="image/png, image/gif, image/jpg, image/jpeg" onchange="checkFiles(this)">
                            <div id="fileCountMessage" class="form-text text-danger d-none">Maximum 6 files allowed.
                            </div>
                            <div id="previewImage2" class="mt-2"></div>
                        </div>
                    </div>
                    <!-- YouTube Videos Section -->
                    <div class="mb-3">
                        <h5 class="mb-0 text-primary border-bottom border-primary">YouTube Videos (Max. 4 Links allowed)</h5>
                        <div class="mt-3" id="videoAttachments">
                            <!-- Video input groups will go here -->
                            <div class="row" id="video1">
                                <div class="col-md-6">
                                    <label class="videoAttach1 w-100">
                                        <span class="text">Youtube Video Link 1</span>
                                        <input type="url" class="form-control" name="videoAttach1"
                                            placeholder="Enter video URL"
                                            value="<?= htmlspecialchars($data['video_attach_1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-constraints="@Required" pattern="https?://.+"
                                            title="Please enter a valid URL starting with http:// or https://" />
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="videoAttach_title1 w-100">
                                        <span class="text">Youtube Video Link Title 1</span>
                                        <input type="text" class="form-control" name="videoAttach_title1"
                                            placeholder="Enter video title"
                                            value="<?= htmlspecialchars($data['video_attach_title1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-constraints="@Required" />
                                    </label>
                                </div>
                            </div>
                            <div class="row" id="video2"
                                style="<?= ($data['video_attach_2'] || $data['video_attach_title2']) ? '' : 'display:none;' ?>">
                                <div class="col-md-6">
                                    <label class="videoAttach2 w-100">
                                        <span class="text">Youtube Video Link 2</span>
                                        <input type="url" class="form-control" name="videoAttach2"
                                            placeholder="Enter video URL" value="<?= $data['video_attach_2']; ?>"
                                            pattern="https?://.+"
                                            title="Please enter a valid URL starting with http:// or https://"
                                            data-constraints="@Required" accept="video/mp4" />
                                    </label>
                                </div>
                                <div class="col-md-6"><label class="videoAttach_title2 w-100">
                                        <span class="text">Youtube Video Attachments Title 2</span>
                                        <input type="text" class="form-control" name="videoAttach_title2"
                                            placeholder="Enter video title" value="<?= $data['video_attach_title2']; ?>"
                                            data-constraints="@Required" />
                                    </label>
                                </div>
                            </div>
                            <div class="row" id="video3"
                                style="<?= ($data['video_attach_3'] || $data['video_attach_title3']) ? '' : 'display:none;' ?>">
                                <div class="col-md-6">
                                    <label class="videoAttach3 w-100">
                                        <span class="text">Youtube Video Link 3</span>
                                        <input type="url" class="form-control" name="videoAttach3"
                                            placeholder="Enter video URL" pattern="https?://.+"
                                            title="Please enter a valid URL starting with http:// or https://"
                                            value="<?= $data['video_attach_3']; ?>" data-constraints="@Required" />
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="videoAttach_title3 w-100">
                                        <span class="text">Youtube Video Attachments Title 3</span>
                                        <input type="text" class="form-control" name="videoAttach_title3"
                                            placeholder="Enter video title" value="<?= $data['video_attach_title3']; ?>"
                                            data-constraints="@Required" />
                                    </label>
                                </div>
                            </div>
                            <div class="row" id="video4"
                                style="<?= ($data['video_attach_4'] || $data['video_attach_title4']) ? '' : 'display:none;' ?>">
                                <div class="col-md-6"><label class="videoAttach4 w-100">
                                        <span class="text">Youtube Video Attachments 4</span>
                                        <input type="url" class="form-control" name="videoAttach4"
                                            placeholder="Enter video URL" pattern="https?://.+"
                                            title="Please enter a valid URL starting with http:// or https://"
                                            value="<?= $data['video_attach_4']; ?>" data-constraints="@Required" />
                                    </label>
                                </div>

                                <div class="col-md-6">
                                    <label class="videoAttach_title4 w-100">
                                        <span class="text">Youtube Video Attachments Title 4</span>
                                        <input type="text" class="form-control" name="videoAttach_title4"
                                            placeholder="Enter video title" value="<?= $data['video_attach_title4']; ?>"
                                            data-constraints="@Required" />
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" id="deleteVideo" class="btn" style="background-color: #db0101; color:white;">Delete Last Attachement &
                                Title</button>
                            <button type="button" id="addVideo" class="btn" style="background-color: #02a102; color:white;">Add More</button>
                        </div>
                    </div>

                    <!-- PDF Section -->
                    <div class="mb-3">
                        <h5 class="mb-0 text-light">PDF Attachement</h5>
                        <div class="mt-3" id="pdfAttachments">
                            <!-- PDF input groups will go here -->

                            <div class="row" id="pdf1">
                                <div class="col-md-6">
                                    <label class="w-100 pdf_attachement1">
                                        <span class="text">PDF Attachment 1</span>
                                        <input type="file" name="pdf_attachement1" class="form-control" id="pdf_attachment1"
                                            value="" data-constraints="@Required" accept=".pdf" />
                                        <?php if ($data['pdf_attachement1']) { ?>
                                            <iframe src="<?= $base_url ?>/src/<?= $data['pdf_attachement1']; ?>" frameborder="0" style="height: 150px;"></iframe>
                                        <?php  } ?>

                                    </label>
                                    <?php if (isset($err['pdf_attachement1'])) {
                                        echo '<span class="empty-message">' . $err['pdf_attachement1'] . '</span>';
                                    } ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="w-100 pdf_attachement_title1">
                                        <span class="text">PDF Attachment Title 1</span>
                                        <input type="text" class="form-control" name="pdf_attachement_title1"
                                            id="pdf_attachement_title1" value="<?= $data['pdf_attachement_title1']; ?>"
                                            data-constraints="@Required" placeholder="Title PDF Attachement 1" />
                                    </label>
                                    <?php if (isset($err['pdf_attachement1'])) {
                                        echo '<span style="color:red">' . $err['pdf_attachement1'] . '</span>';
                                    } ?>
                                </div>
                            </div>

                            <div class="row" id="pdf2" style="display: none;">
                                <div class="col-md-6">
                                    <label class="w-100 pdf_attachement2">
                                        <span class="text">PDF Attachment 2</span>
                                        <input type="file" name="pdf_attachement2" class="form-control"
                                            id="pdf_attachement2" value="" data-constraints="@Required"
                                            accept=".pdf, .mp3" />
                                    </label>
                                    <?php if (isset($err['pdf_attachement2'])) {
                                        echo '<span style="color:red">' . $err['pdf_attachement2'] . '</span>';
                                    } ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="w-100 pdf_attachement_title2">
                                        <span class="text">PDF Attachment Title 2</span>
                                        <input type="text" class="form-control" name="pdf_attachement_title2"
                                            id="pdf_attachement_title2" value="<?= $data['pdf_attachement_title2']; ?>"
                                            data-constraints="@Required" placeholder="Title PDF Attachement 2" />
                                    </label>
                                    <?php if (isset($err['pdf_attachement2'])) {
                                        echo '<span style="color:red">' . $err['pdf_attachement2'] . '</span>';
                                    } ?>
                                </div>

                            </div>

                            <div class="row" id="pdf3" style="display: none;">
                                <div class="col-md-6">
                                    <label class="w-100 pdf_attachement3">
                                        <span class="text">PDF Attachment 3</span>
                                        <input type="file" name="pdf_attachement3" class="form-control" id="pdf_attachment3"
                                            value="<?= $data['pdf_attachement3']; ?>" data-constraints="@Required"
                                            accept=".pdf, .mp3" />
                                    </label>
                                    <?php if (isset($err['pdf_attachement3'])) {
                                        echo '<span style="color:red">' . $err['pdf_attachement3'] . '</span>';
                                    } ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="w-100 pdf_attachement_title3">
                                        <span class="text">PDF Attachment Title 3</span>
                                        <input type="text" class="form-control" name="pdf_attachement_title3"
                                            id="pdf_attachement_title3" value="<?= $data['pdf_attachement_title3']; ?>"
                                            data-constraints="@Required" placeholder="Title PDF Attachement 3" />
                                    </label>
                                    <?php if (isset($err['pdf_attachement3'])) {
                                        echo '<span style="color:red">' . $err['pdf_attachement3'] . '</span>';
                                    } ?>
                                </div>
                            </div>
                            <div class="row" id="pdf4" style="display: none;">
                                <div class="col-md-6">
                                    <label class="w-100 pdf_attachement4">
                                        <span class="text">PDF Attachment 4</span>
                                        <input type="file" name="pdf_attachement4" class="form-control"
                                            id="pdf_attachement4" value="<?= $data['pdf_attachement4']; ?>"
                                            data-constraints="@Required" accept=".pdf, .mp3" />
                                    </label>
                                    <?php if (isset($err['pdf_attachement4'])) {
                                        echo '<span style="color:red">' . $err['pdf_attachement4'] . '</span>';
                                    } ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="w-100 pdf_attachement_title4">
                                        <span class="text">PDF Attachment Title 4</span>
                                        <input type="text" class="form-control" name="pdf_attachement_title4"
                                            id="pdf_attachement_title4" value="<?= $data['pdf_attachement_title4']; ?>"
                                            data-constraints="@Required" placeholder="Title PDF Attachement 4" />
                                    </label>
                                    <?php if (isset($err['pdf_attachement4'])) {
                                        echo '<span style="color:red">' . $err['pdf_attachement4'] . '</span>';
                                    } ?>
                                </div>

                            </div>

                            <div class="row" id="pdf5" style="display: none;">
                                <div class="col-md-6">
                                    <label class="w-100 pdf_attachement5">
                                        <span class="text">PDF Attachment 5</span>
                                        <input type="file" name="pdf_attachement5" class="form-control"
                                            id="pdf_attachement5" value="<?= $data['pdf_attachement5']; ?>"
                                            data-constraints="@Required" accept=".pdf, .mp3" />
                                    </label>
                                    <?php if (isset($err['pdf_attachement5'])) {
                                        echo '<span style="color:red">' . $err['pdf_attachement5'] . '</span>';
                                    } ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="w-100 pdf_attachement_title5">
                                        <span class="text">PDF Attachment Title 5</span>
                                        <input type="text" class="form-control" name="pdf_attachement_title5"
                                            id="pdf_attachement_title5" value="<?= $data['pdf_attachement_title5']; ?>"
                                            data-constraints="@Required" placeholder="Title PDF Attachement 5" />
                                    </label>
                                    <?php if (isset($err['pdf_attachement5'])) {
                                        echo '<span style="color:red">' . $err['pdf_attachement5'] . '</span>';
                                    } ?>
                                </div>
                            </div>

                            <div class="row" id="pdf6" style="display: none;">
                                <div class="col-md-6">
                                    <label class="w-100 pdf_attachement6">
                                        <span class="text">PDF Attachment 6</span>
                                        <input type="file" name="pdf_attachement6" class="form-control"
                                            id="pdf_attachement6" value="<?= $data['pdf_attachement6']; ?>"
                                            data-constraints="@Required" accept=".pdf, .mp3" />
                                    </label>
                                    <?php if (isset($err['pdf_attachement6'])) {
                                        echo '<span style="color:red">' . $err['pdf_attachement6'] . '</span>';
                                    } ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="w-100 pdf_attachement_title6">
                                        <span class="text">PDF Attachment Title 6</span>
                                        <input type="text" class="form-control" name="pdf_attachement_title6"
                                            id="pdf_attachement_title6" value="<?= $data['pdf_attachement_title6']; ?>"
                                            data-constraints="@Required" placeholder="Title PDF Attachement 6" />
                                    </label>
                                    <?php if (isset($err['pdf_attachement6'])) {
                                        echo '<span style="color:red">' . $err['pdf_attachement6'] . '</span>';
                                    } ?>
                                </div>
                            </div>

                            <div class="row" id="pdf7" style="display: none;">
                                <div class="col-md-6">
                                    <label class="w-100 pdf_attachement7">
                                        <span class="text">PDF Attachment 7</span>
                                        <input type="file" name="pdf_attachement7" class="form-control"
                                            id="pdf_attachement7" value="<?= $data['pdf_attachement7']; ?>"
                                            data-constraints="@Required" accept=".pdf, .mp3" />
                                    </label>
                                    <?php if (isset($err['pdf_attachement7'])) {
                                        echo '<span style="color:red">' . $err['pdf_attachement7'] . '</span>';
                                    } ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="w-100 pdf_attachement_title7">
                                        <span class="text">PDF Attachment Title 7</span>
                                        <input type="text" class="form-control" name="pdf_attachement_title7"
                                            id="pdf_attachement_title7" value="<?= $data['pdf_attachement_title7']; ?>"
                                            data-constraints="@Required" placeholder="Title PDF Attachement 7" />
                                    </label>
                                    <?php if (isset($err['pdf_attachement7'])) {
                                        echo '<span style="color:red">' . $err['pdf_attachement7'] . '</span>';
                                    } ?>
                                </div>

                            </div>

                            <div class="row" id="pdf8" style="display: none;">
                                <div class="col-md-6">
                                    <label class="w-100 pdf_attachement8">
                                        <span class="text">PDF Attachment 8</span>
                                        <input type="file" name="pdf_attachement8" class="form-control"
                                            id="pdf_attachement8" value="<?= $data['pdf_attachement8']; ?>"
                                            data-constraints="@Required" accept=".pdf, .mp3" />
                                    </label>
                                    <?php if (isset($err['pdf_attachement8'])) {
                                        echo '<span style="color:red">' . $err['pdf_attachement8'] . '</span>';
                                    } ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="w-100 pdf_attachement_title8">
                                        <span class="text">PDF Attachment Title 8</span>
                                        <input type="text" class="form-control" name="pdf_attachement_title8"
                                            id="pdf_attachement_title8" value="<?= $data['pdf_attachement_title8']; ?>"
                                            data-constraints="@Required" placeholder="Title PDF Attachement 8" />
                                    </label>
                                    <?php if (isset($err['pdf_attachement8'])) {
                                        echo '<span style="color:red">' . $err['pdf_attachement8'] . '</span>';
                                    } ?>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" id="deletePdf" class="btn" style="background-color: #db0101; color:white;">Delete Last
                                Attachment</button>
                            <button type="button" id="addPdf" class="btn" style="background-color: #02a102; color:white;">Add More</button>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" class="form-control" id="location" name="location"
                                placeholder="Enter location" value="<?= $data['location']; ?>" required>
                        </div>
                        <!-- <div class="col-md-6 mb-3">
                            <label for="new_tag" class="form-label">New Button <span
                                    class="text-danger">*</span></label><br>
                            <input type="radio" id="new_tag_yes" name="new_tag" value="Y"
                                <?php #$data['new_tag'] == 'Y' ? 'checked' : '' 
                                ?> onchange="toggleNumberInput()"> Yes
                            <input type="radio" id="new_tag_no" name="new_tag" value="N"
                                <?php #$data['new_tag'] == 'N' ? 'checked' : '' 
                                ?> onchange="toggleNumberInput()"> No
                        </div>

                        <div class="col-md-6 mb-3" id="number_input_container"
                            style="<?php #$data['new_tag'] == 'N' ?? 'display: none' 
                                    ?>">
                            <label for="number_input" class="form-label">Enter Number of Days<span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="number_input" name="new_tag_days" <?php #$data['new_tag'] == 'N' ? 'disabled' : '' 
                                                                                                            ?> value="<?php #$data['new_news_valid_upto']; 
                                                                                                                        ?>">
                        </div>

                        <script>
                            function toggleNumberInput() {
                                const numberInputContainer = document.getElementById('number_input_container');
                                const numberInput = document.getElementById('number_input');
                                const isYesSelected = document.getElementById('new_tag_yes').checked;

                                if (isYesSelected) {
                                    numberInputContainer.style.display = 'block';
                                    numberInput.disabled = false;
                                    numberInput.required = true;
                                } else {
                                    numberInputContainer.style.display = 'none';
                                    numberInput.disabled = true;
                                    numberInput.required = false;
                                    numberInput.value = '';
                                }
                            }

                            // Initialize on page load
                            document.addEventListener('DOMContentLoaded', function() {
                                toggleNumberInput();
                            });
                        </script> -->
                        <div class="col-md-12 mb-3">
                            <label for="hashTag" class="form-label">
                                Hash Tags
                                <small class="text-muted">(Comma separated)</small>
                            </label>
                            <textarea class="form-control" class="form-control" id="hashTag" name="hashTag"
                                placeholder="#hashtag1, #hashtag2"><?= $data['hashtag']; ?></textarea>
                        </div>
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


    <?php

    $embed_script = "newsForm.js";

    require_once __DIR__ . '/layouts/footer.php'; ?>
<?php } else {
    echo "Invalid session, <a href='index.php'>click here</a> to login";
}
?>