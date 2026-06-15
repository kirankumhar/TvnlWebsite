<?php 
    require_once __DIR__ . '/cd-admin/src/database/database.php';
    $database = new Database();
    $conn = $database->getConnection();

    // Get distinct years for filter from publish_date
    try {
        $query = "SELECT DISTINCT YEAR(publish_date) as year 
                FROM tender_notice 
                WHERE publish_date IS NOT NULL AND is_deleted = 0 AND status = 'Published'
                ORDER BY year DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $available_years = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $available_years = [];
        error_log("Table error: " . $e->getMessage());
    }

    // Fetch tenders based on year filter
    try {
        $selected_year = isset($_GET['year']) && !empty($_GET['year']) ? $_GET['year'] : null;
        
        if ($selected_year) {
            $query = "SELECT * FROM tender_notice 
                     WHERE YEAR(publish_date) = ? 
                     AND status = 'Published' 
                     AND is_deleted = 0 
                     ORDER BY publish_date DESC, closing_date ASC";
            $stmt = $conn->prepare($query);
            $stmt->execute([$selected_year]);
        } else {
            $query = "SELECT * FROM tender_notice 
                     WHERE status = 'Published' 
                     AND is_deleted = 0 
                     ORDER BY publish_date DESC, closing_date ASC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
        }
        $tenders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $tenders = [];
        error_log("Tender error: " . $e->getMessage());
    }
?>
<?php include "header1.php"; ?>
<link rel="stylesheet" href="assets/css/tenders.css">

<section class="tvnl-banner">
    <img src="assets/images/banner/board-banner.jpg" alt="Tender Notices" class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">Tender Notices</h2>
        </div>
    </div>
</section>

<div class="container my-5">
    <div class="mb-4 page-title title-row">
        <h3 class="fw-bold text-primary mb-0">
            <i class="bi bi-file-earmark-text" title="Tender Notices"></i> Tender Notices
        </h3>
        <div>
            <a href="tender-old.php" class="btn archive-btn">
                <i class="bi bi-archive"></i> Archive Tender Notices
            </a>
        </div>
    </div>

    <!-- Year Filter Card -->
    <div class="filter-card theme-tender mb-4">
        <div class="filter-card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="filter-icon-wrapper">
                    <i class="bi bi-funnel-fill"></i>
                </div>
                <div>
                    <h6 class="filter-card-title mb-0">Filter Tenders</h6>
                    <small class="text-muted">Select a year to display corresponding tender notices</small>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-3 flex-wrap flex-sm-nowrap w-100-mobile">
                <form method="GET" action="" id="filterForm" class="m-0">
                    <div class="custom-select-wrapper">
                        <i class="bi bi-calendar-event select-icon"></i>
                        <select name="year" id="yearFilter" class="custom-filter-select" onchange="this.form.submit()">
                            <option value="" <?= empty($selected_year) ? 'selected' : '' ?>>All Years</option>
                            <?php
                            if (!empty($available_years)) {
                                foreach ($available_years as $year_item) {
                                    $selected = ($selected_year == $year_item['year']) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($year_item['year']) . '" ' . $selected . '>' . htmlspecialchars($year_item['year']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </form>
                
                <div class="record-badge">
                    <span class="badge border bg-light text-dark">
                        <i class="bi bi-database"></i> Total Tenders: <strong><?php echo count($tenders); ?></strong>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Layout -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped" id="tenderTable">
            <thead class="table-primary">
                <tr>
                    <th width="5%">S.No.</th>
                    <th width="20%">Tender Number</th>
                    <th width="35%">Title</th>
                    <th width="15%">Publish Date</th>
                    <th width="15%">Closing Date & Time</th>
                    <th width="10%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (count($tenders) > 0) {
                    $serial_no = 1;
                    foreach ($tenders as $tender) {
                        // Extract data using correct column names
                        $tender_number = $tender['tender_number'] ?? 'N/A';
                        $title = $tender['title'] ?? 'Untitled Tender';
                        $title_hindi = $tender['title_hindi'] ?? '';
                        $publish_date = $tender['publish_date'] ?? null;
                        $closing_date = $tender['closing_date'] ?? null;
                        $closing_time = $tender['closing_time'] ?? null;
                        $opening_date = $tender['opening_date'] ?? null;
                        $opening_time = $tender['opening_time'] ?? null;
                        $bid_url = $tender['bid_url'] ?? '#';
                        $pdf_path = $tender['pdf_path'] ?? '';
                        
                        // Format dates
                        $formatted_publish_date = $publish_date ? date("d-m-Y", strtotime($publish_date)) : 'N/A';
                        $closing_datetime = '';
                        if ($closing_date) {
                            $closing_datetime = date("d-m-Y", strtotime($closing_date));
                            if ($closing_time) {
                                $closing_datetime .= '<br><small class="text-danger">' . date("h:i A", strtotime($closing_time)) . '</small>';
                            }
                        } else {
                            $closing_datetime = 'N/A';
                        }
                        
                        // Tooltip title with Hindi if available
                        $title_display = htmlspecialchars($title);
                        if ($title_hindi) {
                            $title_display .= '<br><small class="text-muted">' . htmlspecialchars($title_hindi) . '</small>';
                        }
                        
                        echo '<tr>
                                <td class="text-center">' . $serial_no++ . '</td>
                                <td>' . htmlspecialchars($tender_number) . '</td>
                                <td>' . $title_display . '</td>
                                <td class="text-center">' . $formatted_publish_date . '</td>
                                <td class="text-center">' . $closing_datetime . '</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="' . htmlspecialchars($bid_url) . '" target="_blank" class="btn btn-info btn-sm" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>';
                                        if ($pdf_path) {
                                            echo '<a href="' . htmlspecialchars($pdf_path) . '" target="_blank" class="btn btn-danger btn-sm" title="Download PDF">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                  </a>';
                                        }
                                        echo '<button type="button" class="btn btn-secondary btn-sm" title="Quick View" data-bs-toggle="modal" data-bs-target="#tenderModal" 
                                                data-title="' . htmlspecialchars($title) . '" 
                                                data-title-hindi="' . htmlspecialchars($title_hindi) . '"
                                                data-description="' . htmlspecialchars($tender['description'] ?? '') . '"
                                                data-tender-number="' . htmlspecialchars($tender_number) . '"
                                                data-publish-date="' . $formatted_publish_date . '"
                                                data-opening-date="' . ($opening_date ? date("d-m-Y", strtotime($opening_date)) : 'N/A') . '"
                                                data-opening-time="' . ($opening_time ? date("h:i A", strtotime($opening_time)) : 'N/A') . '"
                                                data-closing-date="' . ($closing_date ? date("d-m-Y", strtotime($closing_date)) : 'N/A') . '"
                                                data-closing-time="' . ($closing_time ? date("h:i A", strtotime($closing_time)) : 'N/A') . '"
                                                data-bid-url="' . htmlspecialchars($bid_url) . '"
                                                data-pdf-path="' . htmlspecialchars($pdf_path) . '">
                                                <i class="bi bi-info-circle"></i>
                                              </button>
                                    </div>
                                </td>
                              </tr>';
                    }
                } else {
                    echo '<tr>
                            <td colspan="6" class="text-center">
                                <div class="alert alert-info m-3">
                                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                    <strong>No Tenders Found</strong><br>
                                    No tender notices available ' . (isset($selected_year) && $selected_year ? 'for the year ' . htmlspecialchars($selected_year) : 'in the database') . '
                                </div>
                            </td>
                          </tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <!-- Records info -->
    <div class="row mt-3">
        <div class="col-md-6">
            <p class="text-muted">
                <i class="bi bi-info-circle"></i> Showing <?php echo count($tenders); ?> tender(s)
            </p>
        </div>
    </div>
</div>

<!-- Modal for Quick View -->
<div class="modal fade" id="tenderModal" tabindex="-1" aria-labelledby="tenderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tenderModalLabel">
                    <i class="bi bi-file-earmark-text"></i> Tender Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="tender-details">
                    <h4 id="modalTitle" class="text-primary"></h4>
                    <h6 id="modalTitleHindi" class="text-muted"></h6>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong><i class="bi bi-hash"></i> Tender Number:</strong>
                            <span id="modalTenderNumber"></span>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="bi bi-calendar-event"></i> Publish Date:</strong>
                            <span id="modalPublishDate"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong><i class="bi bi-calendar-check text-success"></i> Opening Date & Time:</strong><br>
                            <span id="modalOpeningDate"></span>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="bi bi-calendar-x text-danger"></i> Closing Date & Time:</strong><br>
                            <span id="modalClosingDate"></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong><i class="bi bi-file-text"></i> Description:</strong>
                        <p id="modalDescription" class="text-muted mt-2"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="modalBidUrl" target="_blank" class="btn btn-primary">
                    <i class="bi bi-eye"></i> View Full Details
                </a>
                <a href="#" id="modalPdfPath" target="_blank" class="btn btn-danger">
                    <i class="bi bi-file-earmark-pdf"></i> Download PDF
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript to populate modal with data
document.addEventListener('DOMContentLoaded', function() {
    var tenderModal = document.getElementById('tenderModal');
    tenderModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        
        // Get data from button attributes
        var title = button.getAttribute('data-title');
        var titleHindi = button.getAttribute('data-title-hindi');
        var description = button.getAttribute('data-description');
        var tenderNumber = button.getAttribute('data-tender-number');
        var publishDate = button.getAttribute('data-publish-date');
        var openingDate = button.getAttribute('data-opening-date');
        var openingTime = button.getAttribute('data-opening-time');
        var closingDate = button.getAttribute('data-closing-date');
        var closingTime = button.getAttribute('data-closing-time');
        var bidUrl = button.getAttribute('data-bid-url');
        var pdfPath = button.getAttribute('data-pdf-path');
        
        // Set modal content
        document.getElementById('modalTitle').textContent = title;
        if (titleHindi && titleHindi !== '') {
            document.getElementById('modalTitleHindi').textContent = titleHindi;
            document.getElementById('modalTitleHindi').style.display = 'block';
        } else {
            document.getElementById('modalTitleHindi').style.display = 'none';
        }
        
        document.getElementById('modalDescription').textContent = description || 'No description available';
        document.getElementById('modalTenderNumber').textContent = tenderNumber || 'N/A';
        document.getElementById('modalPublishDate').textContent = publishDate || 'N/A';
        
        var openingDateTime = openingDate;
        if (openingTime && openingTime !== 'N/A') {
            openingDateTime += ' at ' + openingTime;
        }
        document.getElementById('modalOpeningDate').innerHTML = openingDateTime || 'N/A';
        
        var closingDateTime = closingDate;
        if (closingTime && closingTime !== 'N/A') {
            closingDateTime += ' at ' + closingTime;
        }
        document.getElementById('modalClosingDate').innerHTML = closingDateTime || 'N/A';
        
        // Set footer links
        document.getElementById('modalBidUrl').href = bidUrl || '#';
        if (pdfPath && pdfPath !== '') {
            document.getElementById('modalPdfPath').href = pdfPath;
            document.getElementById('modalPdfPath').style.display = 'inline-flex';
        } else {
            document.getElementById('modalPdfPath').style.display = 'none';
        }
    });
});
</script>


<?php include("footer_top.php"); ?>
<?php include "footer1.php"; ?>