<?php 
    require_once __DIR__ . '/cd-admin/src/database/database.php';
    $database = new Database();
    $conn = $database->getConnection();

    // Get distinct years for filter from cancellation_date
    try {
        $query = "SELECT DISTINCT YEAR(cancellation_date) as year 
                FROM tender_cancellation 
                WHERE cancellation_date IS NOT NULL 
                ORDER BY year DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $available_years = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $available_years = [];
        error_log("Table error: " . $e->getMessage());
    }
?>
<?php include "header1.php"; ?>

<section class="tvnl-banner">
    <img src="assets/images/banner/board-banner.jpg" alt="Tenders - Cancellation Notices" title="Tenders - Cancellation Notices"
        class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">Cancellation Notices</h2>
        </div>
    </div>
</section>

<div class="container my-5">
    <div class="mb-4 page-title">
        <h3 class="fw-bold text-primary mb-0">
            <i class="bi bi-x-octagon"></i> Tender Cancellation Notices
        </h3>
    </div>

    <!-- Year Filter -->
    <div class="row justify-content-between mb-4">
        <div class="col-lg-3 col-md-4 col-sm-6">
            <form method="GET" action="" id="filterForm">
                <div class="input-group">
                    <label for="yearFilter" class="input-group-text bg-primary text-white">
                        <i class="bi bi-calendar-event"></i> Filter by Year
                    </label>
                    <select name="year" id="yearFilter" class="form-select" onchange="this.form.submit()">
                        <option value="">All Years</option>
                        <?php
                        if (!empty($available_years)) {
                            foreach ($available_years as $year_item) {
                                $selected = (isset($_GET['year']) && $_GET['year'] == $year_item['year']) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($year_item['year']) . '" ' . $selected . '>' . htmlspecialchars($year_item['year']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </form>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 text-end">
            <span class="text-muted" id="recordCount"></span>
        </div>
    </div>

    <!-- Table Layout for Cancellation Notices -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped" id="cancellationTable">
            <thead class="table-danger">
                <tr>
                    <th width="5%">S.No.</th>
                    <th width="12%">Cancellation No.</th>
                    <th width="30%">Title</th>
                    <th width="15%">Cancellation Date</th>
                    <th width="28%">Related Tender</th>
                    <th width="10%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    // Build query based on year filter
                    $selected_year = isset($_GET['year']) && !empty($_GET['year']) ? $_GET['year'] : null;
                    
                    if ($selected_year) {
                        $query = "SELECT c.*, t.tender_number, t.title as tender_title, t.reference_number,
                                         t.publish_date, t.closing_date
                                 FROM tender_cancellation c
                                 LEFT JOIN tender_notice t ON c.tender_id = t.id
                                 WHERE YEAR(c.cancellation_date) = ? 
                                 ORDER BY c.cancellation_date DESC, c.id DESC";
                        $stmt = $conn->prepare($query);
                        $stmt->execute([$selected_year]);
                    } else {
                        $query = "SELECT c.*, t.tender_number, t.title as tender_title, t.reference_number,
                                         t.publish_date, t.closing_date
                                 FROM tender_cancellation c
                                 LEFT JOIN tender_notice t ON c.tender_id = t.id
                                 ORDER BY c.cancellation_date DESC, c.id DESC";
                        $stmt = $conn->prepare($query);
                        $stmt->execute();
                    }
                    $cancellations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    $cancellations = [];
                    error_log("Cancellation error: " . $e->getMessage());
                }

                if (count($cancellations) > 0) {
                    $serial_no = 1;
                    foreach ($cancellations as $cancellation) {
                        // Extract data
                        $cancellation_no = $cancellation['cancellation_no'] ?? 'N/A';
                        $title = $cancellation['title'] ?? 'Untitled';
                        $reason = $cancellation['reason'] ?? '';
                        $cancellation_date = $cancellation['cancellation_date'] ?? null;
                        $tender_id = $cancellation['tender_id'] ?? 'N/A';
                        $tender_number = $cancellation['tender_number'] ?? 'Not Available';
                        $tender_title = $cancellation['tender_title'] ?? '';
                        $reference_number = $cancellation['reference_number'] ?? '';
                        $pdf_path = $cancellation['pdf_path'] ?? '';
                        
                        // Format date
                        $formatted_date = $cancellation_date ? date("d-m-Y", strtotime($cancellation_date)) : 'N/A';
                        
                        // Get day and month for badge
                        $day = $cancellation_date ? date("d", strtotime($cancellation_date)) : '';
                        $month = $cancellation_date ? date("M", strtotime($cancellation_date)) : '';
                        
                        // Determine cancellation badge style
                        $is_recent = $cancellation_date && (strtotime($cancellation_date) > strtotime('-30 days'));
                        
                        echo '<tr>
                                <td class="text-center fw-bold">' . $serial_no++ . '</td>
                                <td>
                                    <span class="badge bg-danger">' . htmlspecialchars($cancellation_no) . '</span>
                                    ' . ($is_recent ? '<br><small class="text-danger"><i class="bi bi-clock"></i> Recent</small>' : '') . '
                                 </td>
                                <td>
                                    <strong class="text-danger">' . htmlspecialchars($title) . '</strong>
                                    ' . (!empty($reason) ? '<br><small class="text-muted"><i class="bi bi-info-circle"></i> ' . htmlspecialchars(substr($reason, 0, 60)) . (strlen($reason) > 60 ? '...' : '') . '</small>' : '') . '
                                 </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="fw-bold">' . $formatted_date . '</div>
                                        <small class="text-muted">' . $day . ' ' . $month . '</small>
                                    </div>
                                 </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="badge bg-secondary mb-1">ID: ' . $tender_id . '</span>
                                        ' . ($tender_number != 'Not Available' ? '<small><i class="bi bi-hash"></i> ' . htmlspecialchars($tender_number) . '</small>' : '') . '
                                        ' . ($reference_number ? '<br><small class="text-muted"><i class="bi bi-bookmark"></i> ' . htmlspecialchars($reference_number) . '</small>' : '') . '
                                    </div>
                                 </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-danger btn-sm" title="View Details" data-bs-toggle="modal" data-bs-target="#cancellationModal" 
                                            data-title="' . htmlspecialchars($title) . '"
                                            data-reason="' . htmlspecialchars($reason) . '"
                                            data-cancellation-no="' . htmlspecialchars($cancellation_no) . '"
                                            data-cancellation-date="' . $formatted_date . '"
                                            data-tender-id="' . $tender_id . '"
                                            data-tender-number="' . htmlspecialchars($tender_number) . '"
                                            data-tender-title="' . htmlspecialchars($tender_title) . '"
                                            data-reference-number="' . htmlspecialchars($reference_number) . '"
                                            data-pdf-path="' . htmlspecialchars($pdf_path) . '">
                                            <i class="bi bi-info-circle"></i>
                                        </button>';
                                        if ($pdf_path) {
                                            echo '<a href="' . htmlspecialchars($pdf_path) . '" target="_blank" class="btn btn-warning btn-sm" title="Download PDF">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                  </a>';
                                        }
                                        if ($tender_id != 'N/A') {
                                            echo '<a href="?view=tender&id=' . $tender_id . '" class="btn btn-secondary btn-sm" title="View Related Tender">
                                                    <i class="bi bi-link-45deg"></i>
                                                  </a>';
                                        }
                                        echo '
                                    </div>
                                 </td>
                               </tr>';
                    }
                } else {
                    echo '<tr>
                            <td colspan="6" class="text-center">
                                <div class="alert alert-danger m-3">
                                    <i class="bi bi-x-octagon fs-4 d-block mb-2"></i>
                                    <strong>No Cancellation Notices Found</strong><br>
                                    No tender cancellation notices available ' . (isset($selected_year) && $selected_year ? 'for the year ' . htmlspecialchars($selected_year) : 'in the database') . '
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
                <i class="bi bi-info-circle"></i> Showing <?php echo count($cancellations); ?> cancellation record(s)
            </p>
        </div>
    </div>
</div>

<!-- Modal for Cancellation Details -->
<div class="modal fade" id="cancellationModal" tabindex="-1" aria-labelledby="cancellationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="cancellationModalLabel">
                    <i class="bi bi-x-octagon"></i> Tender Cancellation Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="cancellation-details">
                    <div class="alert alert-danger mb-3">
                        <i class="bi bi-exclamation-triangle-fill"></i> 
                        <strong>Notice of Cancellation:</strong> The following tender has been cancelled. Please read the reason carefully.
                    </div>
                    
                    <h4 id="modalTitle" class="text-danger"></h4>
                    <hr>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong><i class="bi bi-hash"></i> Cancellation No:</strong>
                            <span id="modalCancellationNo" class="badge bg-danger text-white ms-2"></span>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="bi bi-calendar-event"></i> Cancellation Date:</strong>
                            <span id="modalCancellationDate"></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong><i class="bi bi-info-circle-fill text-danger"></i> Reason for Cancellation:</strong>
                        <div class="alert alert-warning mt-2">
                            <p id="modalReason" class="mb-0"></p>
                        </div>
                    </div>
                    
                    <div class="card mb-3 border-danger">
                        <div class="card-header bg-danger text-white">
                            <strong><i class="bi bi-file-earmark-text"></i> Related Tender Information</strong>
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
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <strong>Reference Number:</strong><br>
                                    <span id="modalReferenceNumber"></span>
                                </div>
                            </div>
                            <div class="mt-2">
                                <strong>Tender Title:</strong><br>
                                <span id="modalTenderTitle"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Note:</strong> This tender has been cancelled and is no longer valid for bidding. 
                        Please check other active tenders for opportunities.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="modalPdfPath" target="_blank" class="btn btn-danger">
                    <i class="bi bi-file-earmark-pdf"></i> Download Cancellation Notice PDF
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript to populate modal with data
document.addEventListener('DOMContentLoaded', function() {
    var cancellationModal = document.getElementById('cancellationModal');
    cancellationModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        
        // Get data from button attributes
        var title = button.getAttribute('data-title');
        var reason = button.getAttribute('data-reason');
        var cancellationNo = button.getAttribute('data-cancellation-no');
        var cancellationDate = button.getAttribute('data-cancellation-date');
        var tenderId = button.getAttribute('data-tender-id');
        var tenderNumber = button.getAttribute('data-tender-number');
        var tenderTitle = button.getAttribute('data-tender-title');
        var referenceNumber = button.getAttribute('data-reference-number');
        var pdfPath = button.getAttribute('data-pdf-path');
        
        // Set modal content
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalReason').innerHTML = reason || '<em>No specific reason provided for this cancellation.</em>';
        document.getElementById('modalCancellationNo').textContent = cancellationNo || 'N/A';
        document.getElementById('modalCancellationDate').textContent = cancellationDate || 'N/A';
        document.getElementById('modalTenderId').innerHTML = (tenderId && tenderId != 'N/A') ? '<span class="badge bg-secondary">' + tenderId + '</span>' : 'N/A';
        document.getElementById('modalTenderNumber').textContent = tenderNumber || 'Not Available';
        document.getElementById('modalTenderTitle').textContent = tenderTitle || 'Not Available';
        document.getElementById('modalReferenceNumber').textContent = (referenceNumber && referenceNumber != '') ? referenceNumber : 'Not Available';
        
        // Set PDF link
        if (pdfPath && pdfPath !== '') {
            document.getElementById('modalPdfPath').href = pdfPath;
            document.getElementById('modalPdfPath').style.display = 'inline-flex';
        } else {
            document.getElementById('modalPdfPath').style.display = 'none';
        }
    });
});

// Update record count
document.addEventListener('DOMContentLoaded', function() {
    var tableRows = document.querySelectorAll('#cancellationTable tbody tr');
    var visibleRows = Array.from(tableRows).filter(row => row.style.display !== 'none');
    var recordCountSpan = document.getElementById('recordCount');
    if (recordCountSpan) {
        var count = visibleRows.length;
        recordCountSpan.innerHTML = '<i class="bi bi-database"></i> Total: ' + count + ' record(s)';
    }
});

// Search functionality (optional)
function searchCancellations() {
    var input = document.getElementById('searchInput');
    var filter = input.value.toUpperCase();
    var table = document.getElementById('cancellationTable');
    var tr = table.getElementsByTagName('tr');
    
    for (var i = 1; i < tr.length; i++) {
        var tdTitle = tr[i].getElementsByTagName('td')[2];
        var tdTender = tr[i].getElementsByTagName('td')[4];
        if (tdTitle || tdTender) {
            var titleValue = tdTitle.textContent || tdTitle.innerText;
            var tenderValue = tdTender.textContent || tdTender.innerText;
            if (titleValue.toUpperCase().indexOf(filter) > -1 || tenderValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    }
}
</script>

<style>
    /* Custom styles */
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    
    .table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(220, 53, 69, 0.05);
        transition: background-color 0.3s ease;
    }
    
    .btn-group .btn {
        margin: 0 2px;
        border-radius: 4px !important;
    }
    
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    #cancellationTable {
        margin-bottom: 0;
    }
    
    .modal-content {
        border-radius: 12px;
    }
    
    .badge {
        padding: 5px 10px;
        font-weight: 500;
    }
    
    /* Recent badge animation */
    .text-danger small i {
        animation: blink 1s infinite;
    }
    
    @keyframes blink {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.85rem;
        }
        
        .btn-group .btn {
            padding: 0.2rem 0.4rem;
        }
        
        .badge {
            font-size: 0.7rem;
        }
    }
    
    /* Print styles */
    @media print {
        .tvnl-banner,
        .btn-group,
        .modal,
        .input-group {
            display: none !important;
        }
        
        .table-responsive {
            overflow: visible !important;
        }
        
        .table {
            width: 100% !important;
        }
        
        .badge {
            border: 1px solid #000;
            background: none !important;
            color: #000 !important;
        }
    }
    
    /* Strikethrough effect for cancelled tenders */
    .text-danger {
        text-decoration: none;
    }
</style>

<?php include("footer_top.php"); ?>
<?php include "footer1.php"; ?>