<?php 
    require_once __DIR__ . '/cd-admin/src/database/database.php';
    $database = new Database();
    $conn = $database->getConnection();

    // Get distinct years for filter from extension_date
    try {
        $query = "SELECT DISTINCT YEAR(e.extension_date) as year 
                FROM tender_extension e
                JOIN tender_notice t ON e.tender_id = t.id
                WHERE e.extension_date IS NOT NULL 
                AND t.is_deleted = 0 AND t.status = 'Published'
                ORDER BY year DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $available_years = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $available_years = [];
        error_log("Table error: " . $e->getMessage());
    }

    // Fetch extensions based on year filter
    try {
        $selected_year = isset($_GET['year']) && !empty($_GET['year']) ? $_GET['year'] : null;
        
        if ($selected_year) {
            $query = "SELECT e.*, t.tender_number, t.title as tender_title, t.closing_date as original_closing_date 
                     FROM tender_extension e
                     LEFT JOIN tender_notice t ON e.tender_id = t.id
                     WHERE YEAR(e.extension_date) = ? 
                     ORDER BY e.extension_date DESC, e.id DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute([$selected_year]);
        } else {
            $query = "SELECT e.*, t.tender_number, t.title as tender_title, t.closing_date as original_closing_date 
                     FROM tender_extension e
                     LEFT JOIN tender_notice t ON e.tender_id = t.id
                     ORDER BY e.extension_date DESC, e.id DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
        }
        $extensions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $extensions = [];
        error_log("Extension error: " . $e->getMessage());
    }
?>
<?php include "header1.php"; ?>
<link rel="stylesheet" href="assets/css/tenders.css">

<section class="tvnl-banner">
    <img src="assets/images/banner/board-banner.jpg" alt="Tenders - Extension Notices" title="Tenders - Extension Notices"
        class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">Extension Notices</h2>
        </div>
    </div>
</section>

<div class="container my-5">
    <div class="mb-4 page-title title-row">
        <h3 class="fw-bold text-primary mb-0">
            <i class="bi bi-file-earmark-text" title="Tender Notices"></i>Extension Notices
        </h3>
        <div>
            <a href="extension-old.php" class="btn archive-btn">
                <i class="bi bi-archive"></i> Archive Extension Notices
            </a>
        </div>
    </div>

    <!-- Year Filter Card -->
    <div class="filter-card theme-extension mb-4">
        <div class="filter-card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="filter-icon-wrapper">
                    <i class="bi bi-funnel-fill"></i>
                </div>
                <div>
                    <h6 class="filter-card-title mb-0">Filter Extension Notices</h6>
                    <small class="text-muted">Select a year to display corresponding extension notices</small>
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
                        <i class="bi bi-database"></i> Total Records: <strong><?php echo count($extensions); ?></strong>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Layout for Extension Notices -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped" id="extensionTable">
            <thead class="table-success">
                <tr>
                    <th width="5%">S.No.</th>
                    <th width="10%">Extension No.</th>
                    <th width="25%">Title</th>
                    <th width="12%">Extension Date</th>
                    <th width="18%">Old Closing Date</th>
                    <th width="18%">New Closing Date</th>
                    <th width="12%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (count($extensions) > 0) {
                    $serial_no = 1;
                    foreach ($extensions as $extension) {
                        // Extract data
                        $extension_no = $extension['extension_no'] ?? 'N/A';
                        $title = $extension['title'] ?? 'Untitled';
                        $description = $extension['description'] ?? '';
                        $extension_date = $extension['extension_date'] ?? null;
                        $old_closing_date = $extension['old_closing_date'] ?? null;
                        $new_closing_date = $extension['new_closing_date'] ?? null;
                        $tender_id = $extension['tender_id'] ?? 'N/A';
                        $tender_number = $extension['tender_number'] ?? 'Not Available';
                        $tender_title = $extension['tender_title'] ?? '';
                        $pdf_path = $extension['pdf_path'] ?? '';
                        
                        // Format dates
                        $formatted_extension_date = $extension_date ? date("d-m-Y", strtotime($extension_date)) : 'N/A';
                        $formatted_old_date = $old_closing_date ? date("d-m-Y h:i A", strtotime($old_closing_date)) : 'N/A';
                        $formatted_new_date = $new_closing_date ? date("d-m-Y h:i A", strtotime($new_closing_date)) : 'N/A';
                        
                        // Calculate extension days
                        $extension_days = '';
                        if ($old_closing_date && $new_closing_date) {
                            $old = new DateTime($old_closing_date);
                            $new = new DateTime($new_closing_date);
                            $diff = $old->diff($new);
                            $extension_days = $diff->days;
                            if ($extension_days > 0) {
                                $extension_days = '<span class="badge bg-success">+' . $extension_days . ' days</span>';
                            }
                        }
                        
                        echo '<tr>
                                <td class="text-center fw-bold">' . $serial_no++ . '</td>
                                <td>
                                    <span class="badge bg-success text-white">' . htmlspecialchars($extension_no) . '</span>
                                 </td>
                                <td>
                                    <strong>' . htmlspecialchars($title) . '</strong>
                                    ' . (!empty($description) ? '<br><small class="text-muted">' . htmlspecialchars(substr($description, 0, 80)) . (strlen($description) > 80 ? '...' : '') . '</small>' : '') . '
                                 </td>
                                <td class="text-center">' . $formatted_extension_date . '</td>
                                <td class="text-center">
                                    <span class="text-danger"><i class="bi bi-calendar-x"></i> ' . $formatted_old_date . '</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-success fw-bold"><i class="bi bi-calendar-check"></i> ' . $formatted_new_date . '</span>
                                    ' . $extension_days . '
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-success btn-sm" title="View Details" data-bs-toggle="modal" data-bs-target="#extensionModal" 
                                            data-title="' . htmlspecialchars($title) . '"
                                            data-description="' . htmlspecialchars($description) . '"
                                            data-extension-no="' . htmlspecialchars($extension_no) . '"
                                            data-extension-date="' . $formatted_extension_date . '"
                                            data-old-date="' . $formatted_old_date . '"
                                            data-new-date="' . $formatted_new_date . '"
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
                            <td colspan="7" class="text-center">
                                <div class="alert alert-success m-3">
                                    <i class="bi bi-clock-history fs-4 d-block mb-2"></i>
                                    <strong>No Extension Notices Found</strong><br>
                                    No tender extension notices available ' . (isset($selected_year) && $selected_year ? 'for the year ' . htmlspecialchars($selected_year) : 'in the database') . '
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
                <i class="bi bi-info-circle"></i> Showing <?php echo count($extensions); ?> extension record(s)
            </p>
        </div>
    </div>
</div>

<!-- Modal for Extension Details -->
<div class="modal fade" id="extensionModal" tabindex="-1" aria-labelledby="extensionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="extensionModalLabel">
                    <i class="bi bi-clock-history"></i> Tender Extension Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="extension-details">
                    <div class="alert alert-success mb-3">
                        <i class="bi bi-megaphone"></i> <strong>Important Notice:</strong> The bid submission deadline has been extended. Please note the new closing date.
                    </div>
                    
                    <h4 id="modalTitle" class="text-success"></h4>
                    <hr>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong><i class="bi bi-hash"></i> Extension No:</strong>
                            <span id="modalExtensionNo" class="badge bg-success text-white ms-2"></span>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="bi bi-calendar-event"></i> Extension Date:</strong>
                            <span id="modalExtensionDate"></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong><i class="bi bi-file-text"></i> Description:</strong>
                        <p id="modalDescription" class="text-muted mt-2"></p>
                    </div>
                    
                    <div class="card mb-3 border-success">
                        <div class="card-header bg-success text-white">
                            <strong><i class="bi bi-calendar-range"></i> Date Extension Details</strong>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 text-center">
                                    <div class="border-end">
                                        <strong class="text-danger"><i class="bi bi-calendar-x"></i> Previous Closing Date:</strong><br>
                                        <span id="modalOldDate" class="h5 text-danger"></span>
                                    </div>
                                </div>
                                <div class="col-md-6 text-center">
                                    <strong class="text-success"><i class="bi bi-calendar-check"></i> New Closing Date:</strong><br>
                                    <span id="modalNewDate" class="h5 text-success"></span>
                                </div>
                            </div>
                        </div>
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
                    <i class="bi bi-file-earmark-pdf"></i> Download Extension Notice PDF
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript to populate modal with data
document.addEventListener('DOMContentLoaded', function() {
    var extensionModal = document.getElementById('extensionModal');
    extensionModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        
        // Get data from button attributes
        var title = button.getAttribute('data-title');
        var description = button.getAttribute('data-description');
        var extensionNo = button.getAttribute('data-extension-no');
        var extensionDate = button.getAttribute('data-extension-date');
        var oldDate = button.getAttribute('data-old-date');
        var newDate = button.getAttribute('data-new-date');
        var tenderId = button.getAttribute('data-tender-id');
        var tenderNumber = button.getAttribute('data-tender-number');
        var tenderTitle = button.getAttribute('data-tender-title');
        var pdfPath = button.getAttribute('data-pdf-path');
        
        // Set modal content
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalDescription').textContent = description || 'No description provided for this extension notice.';
        document.getElementById('modalExtensionNo').textContent = extensionNo || 'N/A';
        document.getElementById('modalExtensionDate').textContent = extensionDate || 'N/A';
        document.getElementById('modalOldDate').innerHTML = oldDate || 'N/A';
        document.getElementById('modalNewDate').innerHTML = newDate || 'N/A';
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
        background-color: rgba(40, 167, 69, 0.05);
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
    
    #extensionTable {
        margin-bottom: 0;
    }
    
    .modal-content {
        border-radius: 12px;
    }
    
    .badge {
        padding: 5px 10px;
        font-weight: 500;
    }
    
    /* Date highlight styles */
    .text-danger, .text-success {
        font-weight: 500;
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
    
    /* Animation for extension days badge */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .badge.bg-success {
        animation: pulse 2s infinite;
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
</style>

<?php include("footer_top.php"); ?>
<?php include "footer1.php"; ?>