<?php 
    require_once __DIR__ . '/cd-admin/src/database/database.php';
    $database = new Database();
    $conn = $database->getConnection();

    // Get distinct years for filter from corrigendum_date
    try {
        $query = "SELECT DISTINCT YEAR(c.corrigendum_date) as year 
                FROM tender_corrigendum c
                JOIN tender_notice t ON c.tender_id = t.id
                WHERE c.corrigendum_date IS NOT NULL 
                AND t.is_deleted = 0 AND t.status = 'Published'
                ORDER BY year DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $available_years = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $available_years = [];
        error_log("Table error: " . $e->getMessage());
    }

    // Fetch corrigendums based on year filter
    try {
        $selected_year = isset($_GET['year']) && !empty($_GET['year']) ? $_GET['year'] : null;
        
        if ($selected_year) {
            $query = "SELECT c.*, t.tender_number, t.title as tender_title 
                     FROM tender_corrigendum c
                     LEFT JOIN tender_notice t ON c.tender_id = t.id
                     WHERE YEAR(c.corrigendum_date) = ? 
                     ORDER BY c.corrigendum_date DESC, c.id DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute([$selected_year]);
        } else {
            $query = "SELECT c.*, t.tender_number, t.title as tender_title 
                     FROM tender_corrigendum c
                     LEFT JOIN tender_notice t ON c.tender_id = t.id
                     ORDER BY c.corrigendum_date DESC, c.id DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
        }
        $corrigendums = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $corrigendums = [];
        error_log("Corrigendum error: " . $e->getMessage());
    }
?>
<?php include "header1.php"; ?>
<link rel="stylesheet" href="assets/css/tenders.css">

<section class="tvnl-banner">
    <img src="assets/images/banner/board-banner.jpg" alt="Tenders - Corrigendum" title="Tenders - Corrigendum"
        class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">Corrigendum</h2>
        </div>
    </div>
</section>

<div class="container my-5">
    <div class="mb-4 page-title title-row">
        <h3 class="fw-bold text-primary mb-0">
            <i class="bi bi-file-earmark-text" title="Tender Notices"></i> Corrigendum Notices
        </h3>
        <div>
            <a href="corrigendum-old.php" class="btn archive-btn">
                <i class="bi bi-archive"></i> Archive Corrigendum Notices
            </a>
        </div>
    </div>

    <!-- Year Filter Card -->
    <div class="filter-card theme-corrigendum mb-4">
        <div class="filter-card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="filter-icon-wrapper">
                    <i class="bi bi-funnel-fill"></i>
                </div>
                <div>
                    <h6 class="filter-card-title mb-0">Filter Corrigendum Notices</h6>
                    <small class="text-muted">Select a year to display corresponding corrigendum notices</small>
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
                        <i class="bi bi-database"></i> Total Records: <strong><?php echo count($corrigendums); ?></strong>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Layout for Corrigendum -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped" id="corrigendumTable">
            <thead class="table-warning">
                <tr>
                    <th width="5%">S.No.</th>
                    <th width="15%">Corrigendum No.</th>
                    <th width="30%">Title</th>
                    <th width="20%">Corrigendum Date</th>
                    <th width="20%">Related Tender ID</th>
                    <th width="10%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (count($corrigendums) > 0) {
                    $serial_no = 1;
                    foreach ($corrigendums as $corrigendum) {
                        // Extract data
                        $corrigendum_no = $corrigendum['corrigendum_no'] ?? 'N/A';
                        $title = $corrigendum['title'] ?? 'Untitled';
                        $description = $corrigendum['description'] ?? '';
                        $corrigendum_date = $corrigendum['corrigendum_date'] ?? null;
                        $tender_id = $corrigendum['tender_id'] ?? 'N/A';
                        $tender_number = $corrigendum['tender_number'] ?? 'Not Available';
                        $tender_title = $corrigendum['tender_title'] ?? '';
                        $pdf_path = $corrigendum['pdf_path'] ?? '';
                        
                        // Format date
                        $formatted_date = $corrigendum_date ? date("d-m-Y", strtotime($corrigendum_date)) : 'N/A';
                        
                        // Get day and month for badge
                        $day = $corrigendum_date ? date("d", strtotime($corrigendum_date)) : '';
                        $month = $corrigendum_date ? date("M", strtotime($corrigendum_date)) : '';
                        
                        echo '<tr>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="fw-bold">' . $serial_no++ . '</span>
                                    </div>
                                 </td>
                                <td>
                                    <span class="badge bg-warning text-dark">' . htmlspecialchars($corrigendum_no) . '</span>
                                 </td>
                                <td>
                                    <strong>' . htmlspecialchars($title) . '</strong>
                                    ' . (!empty($description) ? '<br><small class="text-muted">' . htmlspecialchars(substr($description, 0, 100)) . (strlen($description) > 100 ? '...' : '') . '</small>' : '') . '
                                 </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="fw-bold">' . $formatted_date . '</div>
                                        <small class="text-muted">' . $day . ' ' . $month . '</small>
                                    </div>
                                 </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="badge bg-info text-dark mb-1">ID: ' . $tender_id . '</span>
                                        ' . ($tender_number != 'Not Available' ? '<small class="text-muted">No: ' . htmlspecialchars($tender_number) . '</small>' : '') . '
                                    </div>
                                 </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-warning btn-sm" title="View Details" data-bs-toggle="modal" data-bs-target="#corrigendumModal" 
                                            data-title="' . htmlspecialchars($title) . '"
                                            data-description="' . htmlspecialchars($description) . '"
                                            data-corrigendum-no="' . htmlspecialchars($corrigendum_no) . '"
                                            data-date="' . $formatted_date . '"
                                            data-tender-id="' . $tender_id . '"
                                            data-tender-number="' . htmlspecialchars($tender_number) . '"
                                            data-tender-title="' . htmlspecialchars($tender_title) . '"
                                            data-pdf-path="' . htmlspecialchars($pdf_path) . '">
                                            <i class="bi bi-info-circle"></i>
                                        </button>';
                                        if ($pdf_path) {
                                            echo '<a href="' . htmlspecialchars($pdf_path) . '" target="_blank" class="btn btn-danger btn-sm" title="Download PDF">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                  </a>';
                                        }
                                        echo '<a href="?view=tender&id=' . $tender_id . '" class="btn btn-primary btn-sm" title="View Related Tender">
                                                <i class="bi bi-link-45deg"></i>
                                              </a>
                                    </div>
                                 </td>
                               </tr>';
                    }
                } else {
                    echo '<tr>
                            <td colspan="6" class="text-center">
                                <div class="alert alert-warning m-3">
                                    <i class="bi bi-exclamation-triangle fs-4 d-block mb-2"></i>
                                    <strong>No Corrigendum Found</strong><br>
                                    No corrigendum notices available ' . (isset($selected_year) && $selected_year ? 'for the year ' . htmlspecialchars($selected_year) : 'in the database') . '
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
                <i class="bi bi-info-circle"></i> Showing <?php echo count($corrigendums); ?> corrigendum record(s)
            </p>
        </div>
    </div>
</div>

<!-- Modal for Corrigendum Details -->
<div class="modal fade" id="corrigendumModal" tabindex="-1" aria-labelledby="corrigendumModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="corrigendumModalLabel">
                    <i class="bi bi-exclamation-triangle"></i> Corrigendum Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="corrigendum-details">
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-megaphone"></i> <strong>Important Notice:</strong> Please read the corrigendum carefully before submitting your bid.
                    </div>
                    
                    <h4 id="modalTitle" class="text-warning"></h4>
                    <hr>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong><i class="bi bi-hash"></i> Corrigendum No:</strong>
                            <span id="modalCorrigendumNo" class="badge bg-warning text-dark ms-2"></span>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="bi bi-calendar-event"></i> Issue Date:</strong>
                            <span id="modalDate"></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong><i class="bi bi-file-text"></i> Description:</strong>
                        <p id="modalDescription" class="text-muted mt-2"></p>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-link"></i> Related Tender Information</strong>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Tender ID:</strong><br>
                                    <span id="modalTenderId"></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Tender Number:</strong><br>
                                    <span id="modalTenderNumber"></span>
                                </div>
                            </div>
                            <div class="mt-2">
                                <strong>Tender Title:</strong><br>
                                <span id="modalTenderTitle"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="modalPdfPath" target="_blank" class="btn btn-danger">
                    <i class="bi bi-file-earmark-pdf"></i> Download Corrigendum PDF
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript to populate modal with data
document.addEventListener('DOMContentLoaded', function() {
    var corrigendumModal = document.getElementById('corrigendumModal');
    corrigendumModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        
        // Get data from button attributes
        var title = button.getAttribute('data-title');
        var description = button.getAttribute('data-description');
        var corrigendumNo = button.getAttribute('data-corrigendum-no');
        var date = button.getAttribute('data-date');
        var tenderId = button.getAttribute('data-tender-id');
        var tenderNumber = button.getAttribute('data-tender-number');
        var tenderTitle = button.getAttribute('data-tender-title');
        var pdfPath = button.getAttribute('data-pdf-path');
        
        // Set modal content
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalDescription').textContent = description || 'No description provided for this corrigendum.';
        document.getElementById('modalCorrigendumNo').textContent = corrigendumNo || 'N/A';
        document.getElementById('modalDate').textContent = date || 'N/A';
        document.getElementById('modalTenderId').innerHTML = tenderId != 'N/A' ? '<span class="badge bg-info">' + tenderId + '</span>' : 'N/A';
        document.getElementById('modalTenderNumber').textContent = tenderNumber || 'Not Available';
        document.getElementById('modalTenderTitle').textContent = tenderTitle || 'Not Available';
        
        // Set PDF link
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