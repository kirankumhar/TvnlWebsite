<?php include "header1.php"; ?>
<?php 
    require_once __DIR__ . '/db.php';
    $database = new Database();
    $conn = $database->getConnection();

    // Get distinct years for filter from pub_date
    try {
        $query = "SELECT DISTINCT YEAR(pub_date) as year 
                FROM tbl_tender_ext_details 
                WHERE pub_date IS NOT NULL 
                ORDER BY year DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $available_years = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $available_years = [];
        error_log("Table error: " . $e->getMessage());
    }
?>
<style>
    .page-title {
        border-left: 5px solid #0d6efd;
        padding-left: 15px;
    }

    .table thead th {
        white-space: nowrap;
    }

    .badge {
        font-size: 0.85rem;
    }

    .btn-sm {
        min-width: 110px;
    }
    
    .loading-spinner {
        text-align: center;
        padding: 50px;
    }
    
    .loading-spinner i {
        font-size: 3rem;
        color: #0d6efd;
        margin-bottom: 15px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .no-extensions {
        text-align: center;
        padding: 50px;
    }
    
    .no-extensions i {
        font-size: 3rem;
        color: #999;
        margin-bottom: 15px;
    }
    
    .extension-row {
        transition: background-color 0.3s;
    }
    
    .extension-row:hover {
        background-color: #f8f9fa;
    }
    
    .filter-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 15px;
    }
    
    .date-box {
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 6px;
        margin-bottom: 8px;
    }
    
    .original-date {
        border-left: 3px solid #dc3545;
    }
    
    .extended-date {
        border-left: 3px solid #28a745;
    }
    
    .date-label {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 600;
        color: #6c757d;
        display: block;
        margin-bottom: 4px;
    }
    
    .date-value {
        font-size: 13px;
        font-weight: 600;
    }
    
    .document-buttons {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .document-buttons .btn {
        white-space: nowrap;
    }
    
    @media (max-width: 768px) {
        .table {
            font-size: 12px;
        }
        .btn-sm {
            font-size: 11px;
            padding: 4px 8px;
        }
    }
</style>

<section class="tvnl-banner">
    <img src="assets/images/banner/board-banner.jpg" alt="Tender Extensions" class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">Tender Extension Notices</h2>
        </div>
    </div>
</section>

<div class="container my-5">

    <div class="mb-4 page-title">
        <h3 class="fw-bold text-primary mb-0">
            <i class="bi bi-clock-history"></i> Tender Extension Notices
        </h3>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Financial Year</label>
                    <select class="form-select" id="financial_year">
                        <option value="">All Years</option>
                        <?php if(!empty($available_years)): ?>
                            <?php foreach($available_years as $year_data): ?>
                                <option value="<?php echo $year_data['year']; ?>">
                                    <?php echo $year_data['year']; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Start Date</label>
                    <input type="date" class="form-control" id="start_date">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">End Date</label>
                    <input type="date" class="form-control" id="end_date">
                </div>
                
                <div class="col-md-12">
                    <div class="filter-buttons">
                        <button class="btn btn-primary" id="apply_filters">
                            <i class="bi bi-search"></i> Apply Filters
                        </button>
                        <button class="btn btn-secondary" id="reset_filters">
                            <i class="bi bi-arrow-repeat"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <div id="extensionsContainer">
                <div class="loading-spinner">
                    <i class="bi bi-arrow-repeat"></i>
                    <p>Loading tender extensions...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function fetchExtensions(page = 1) {
    let financial_year = $('#financial_year').val();
    let start_date = $('#start_date').val();
    let end_date = $('#end_date').val();
    
    $('#extensionsContainer').html(`
        <div class="loading-spinner">
            <i class="bi bi-arrow-repeat"></i>
            <p>Loading tender extensions...</p>
        </div>
    `);
    
    $.ajax({
        url: 'ajax_call/fetch_tender_extensions.php',
        type: 'POST',
        data: {
            financial_year: financial_year,
            start_date: start_date,
            end_date: end_date,
            page: page
        },
        dataType: 'json',
        success: function(response) {
            $('#extensionsContainer').empty();
            
            if (response.success && response.extensions && response.extensions.length > 0) {
                let tableHtml = `
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Tender Details</th>
                                <th class="text-center">Original Schedule</th>
                                <th class="text-center">Extended Schedule</th>
                                <th class="text-center">Documents</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                $.each(response.extensions, function(index, extension) {
                    let serialNumber = response.offset + index + 1;
                    
                    // Format dates
                    let pubDate = formatDateTime(extension.pub_date);
                    let pubDefDate = formatDateTime(extension.pub_def_date);
                    let extNewDate = formatDateTime(extension.ext_new_date);
                    let extEndDate = formatDateTime(extension.ext_end_date);
                    
                    // Tender details
                    let title = extension.title || 'Tender Notice';
                    let nitNo = extension.nit_no || 'N/A';
                    let description = extension.descp || '';
                    if (description.length > 80) {
                        description = description.substring(0, 80) + '...';
                    }
                    
                    tableHtml += `
                        <tr class="extension-row">
                            <td><strong>${serialNumber}</strong></td>
                            <td>
                                <div class="fw-semibold text-primary mb-1">${escapeHtml(title)}</div>
                                ${description ? `<p class="small text-muted mb-1">${escapeHtml(description)}</p>` : ''}
                                <span class="badge bg-secondary">NIT: ${escapeHtml(nitNo)}</span>
                            </td>
                            <td class="text-center">
                                <div class="date-box original-date">
                                    <span class="date-label">Publish Date</span>
                                    <span class="date-value">${pubDate}</span>
                                </div>
                                <div class="date-box original-date">
                                    <span class="date-label">Closing Date</span>
                                    <span class="date-value text-danger">${pubDefDate}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="date-box extended-date">
                                    <span class="date-label">New Publish Date</span>
                                    <span class="date-value">${extNewDate}</span>
                                </div>
                                <div class="date-box extended-date">
                                    <span class="date-label">Extended Closing Date</span>
                                    <span class="date-value text-success">${extEndDate}</span>
                                </div>
                            </td>
                            <td>
                                <div class="document-buttons">
                    `;
                    
                    // Extension document
                    if (extension.ext_upload_doc && extension.ext_upload_doc !== 'NULL' && extension.ext_upload_doc !== '') {
                        let docPath = extension.ext_upload_doc;
                        if (!docPath.startsWith('http') && !docPath.startsWith('/')) {
                            docPath = 'uploads/' + docPath;
                        }
                        tableHtml += `<a href="${escapeHtml(docPath)}" target="_blank" class="btn btn-outline-warning btn-sm">
                                        <i class="bi bi-file-earmark-pdf"></i> Extension Notice
                                      </a>`;
                    }
                    
                    // Original notice
                    if (extension.notice_url && extension.notice_url !== 'NULL' && extension.notice_url !== '') {
                        tableHtml += `<a href="${escapeHtml(extension.notice_url)}" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> Tender Notice
                                      </a>`;
                    }
                    
                    // Tender document
                    if (extension.document_url && extension.document_url !== 'NULL' && extension.document_url !== '') {
                        tableHtml += `<a href="${escapeHtml(extension.document_url)}" target="_blank" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-download"></i> Tender Document
                                      </a>`;
                    }
                    
                    if ((!extension.ext_upload_doc || extension.ext_upload_doc === 'NULL' || extension.ext_upload_doc === '') &&
                        (!extension.notice_url || extension.notice_url === 'NULL' || extension.notice_url === '') &&
                        (!extension.document_url || extension.document_url === 'NULL' || extension.document_url === '')) {
                        tableHtml += `<span class="text-muted small text-center">No documents</span>`;
                    }
                    
                    tableHtml += `
                                </div>
                            </td>
                        </tr>
                    `;
                });
                
                tableHtml += `
                        </tbody>
                    </table>
                `;
                
                if (response.total_pages > 1) {
                    tableHtml += generatePagination(response.current_page, response.total_pages, response.total_records, response.results_per_page, response.offset);
                }
                
                $('#extensionsContainer').html(tableHtml);
            } else {
                $('#extensionsContainer').html(`
                    <div class="no-extensions">
                        <i class="bi bi-inbox"></i>
                        <h4>No Tender Extensions Found</h4>
                        <p>There are no tender extension notices available for the selected criteria.</p>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            $('#extensionsContainer').html(`
                <div class="no-extensions">
                    <i class="bi bi-exclamation-triangle-fill" style="color: #ff4444;"></i>
                    <h4>Error Loading Extensions</h4>
                    <p>Please try again later.</p>
                </div>
            `);
        }
    });
}

function formatDateTime(dateTimeStr) {
    if (!dateTimeStr || dateTimeStr === 'NULL' || dateTimeStr === '0000-00-00 00:00:00') {
        return '<span class="text-muted">Not Available</span>';
    }
    try {
        let date = new Date(dateTimeStr);
        if (isNaN(date.getTime())) return '<span class="text-muted">Invalid Date</span>';
        
        let day = date.getDate().toString().padStart(2, '0');
        let month = (date.getMonth() + 1).toString().padStart(2, '0');
        let year = date.getFullYear();
        return `${day}/${month}/${year}`;
    } catch(e) {
        return '<span class="text-muted">Not Available</span>';
    }
}

function generatePagination(currentPage, totalPages, totalRecords, resultsPerPage, offset) {
    let startRecord = offset + 1;
    let endRecord = Math.min(offset + resultsPerPage, totalRecords);
    
    let html = `
        <div class="row mt-4 align-items-center">
            <div class="col-md-6">
                <small class="text-muted">Showing ${startRecord} to ${endRecord} of ${totalRecords} entries</small>
            </div>
            <div class="col-md-6">
                <nav>
                    <ul class="pagination justify-content-end mb-0">
    `;
    
    html += `<li class="page-item ${currentPage == 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="1">&laquo;&laquo;</a>
            </li>`;
    
    html += `<li class="page-item ${currentPage == 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">&laquo;</a>
            </li>`;
    
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);
    
    if (startPage > 1) {
        html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `<li class="page-item ${i == currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`;
    }
    
    if (endPage < totalPages) {
        html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    
    html += `<li class="page-item ${currentPage == totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">&raquo;</a>
            </li>`;
    
    html += `<li class="page-item ${currentPage == totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${totalPages}">&raquo;&raquo;</a>
            </li>`;
    
    html += `
                    </ul>
                </nav>
            </div>
        </div>
    `;
    
    return html;
}

function escapeHtml(text) {
    if (!text) return '';
    let div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

$(document).ready(function() {
    fetchExtensions(1);
    
    $('#financial_year').change(function() {
        fetchExtensions(1);
    });
    
    $('#apply_filters').click(function() {
        fetchExtensions(1);
    });
    
    $('#reset_filters').click(function() {
        $('#financial_year').val('');
        $('#start_date').val('');
        $('#end_date').val('');
        fetchExtensions(1);
    });
    
    $(document).on('click', '.pagination .page-link', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        if (page && !$(this).parent().hasClass('disabled')) {
            fetchExtensions(page);
        }
    });
});
</script>

<?php include("footer_top.php"); ?>
<?php include "footer1.php"; ?>