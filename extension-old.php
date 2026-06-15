<?php include "header1.php"; ?>
<?php 
    require_once __DIR__ . '/db.php';
    $database = new Database();
    $conn = $database->getConnection();

    // Get distinct years for filter from extension table
    $available_years = [];
    try {
        $query = "SELECT DISTINCT YEAR(pub_date) as year 
                FROM tbl_tender_ext_details 
                WHERE pub_date IS NOT NULL 
                ORDER BY year DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $available_years = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $available_years = [];
    }
?>
<style>
    .page-title { border-left: 5px solid #0d6efd; padding-left: 15px; }
    .table thead th { white-space: nowrap; }
    .badge { font-size: 0.85rem; }
    .btn-sm { min-width: 110px; }
    
    /* Title + Button row alignment */
    .title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    .title-row h3 {
        margin-bottom: 0;
    }
    .archive-btn {
        white-space: nowrap;
        background-color: #6c757d;
        color: white;
        border: none;
        transition: all 0.2s ease;
    }
    .archive-btn:hover {
        background-color: #5a6268;
        color: white;
        transform: translateY(-1px);
    }
    
    .loading-spinner { text-align: center; padding: 50px; }
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
    
    .no-tenders {
        text-align: center;
        padding: 50px;
    }
    .no-tenders i {
        font-size: 3rem;
        color: #999;
        margin-bottom: 15px;
    }
    .tender-row {
        transition: background-color 0.3s;
    }
    .tender-row:hover {
        background-color: #f8f9fa;
    }
    .filter-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 15px;
    }
    @media (min-width: 768px) {
        .filter-buttons {
            margin-top: 32px;
        }
    }
    @media (max-width: 576px) {
        .title-row {
            flex-direction: column;
            align-items: flex-start;
        }
        .archive-btn {
            width: 100%;
            text-align: center;
        }
    }
    
    /* Extension specific badge */
    .badge-extension {
        background-color: #fd7e14;
        font-size: 0.7rem;
        margin-left: 8px;
        vertical-align: middle;
    }
    
    .ext-date-old {
        text-decoration: line-through;
        opacity: 0.6;
        font-size: 0.8rem;
        margin-right: 5px;
    }
    
    .ext-date-new {
        font-weight: bold;
    }
    
    .debug-info {
        font-size: 11px;
        color: #666;
        margin-top: 5px;
        word-break: break-all;
    }
    
    .error-message {
        text-align: center;
        padding: 50px;
        color: #dc3545;
    }
    .error-message i {
        font-size: 3rem;
        margin-bottom: 15px;
    }
</style>

<section class="tvnl-banner">
    <img src="assets/images/banner/board-banner.jpg" alt="Extension Tenders - Tenders Notices" title="Extension Tenders - Tenders Notices"
        class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">Extension Tender Notices</h2>
        </div>
    </div>
</section>

<div class="container my-5">

    <!-- Title & Archive Button Row (Button right side) -->
    <div class="mb-4 page-title title-row">
        <h3 class="fw-bold text-primary mb-0">
            <i class="bi bi-calendar-plus" title="Extension Tender Notices"></i> Extension Tender Notices
        </h3>
        <div>
            <a href="tender-notices.php" class="btn archive-btn">
                <i class="bi bi-archive"></i> Archive Tender Notices
            </a>
        </div>
    </div>

    <div class="filter-card theme-extension mb-4">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="filter-icon-wrapper">
                <i class="bi bi-funnel-fill"></i>
            </div>
            <div>
                <h6 class="filter-card-title mb-0">Search Extensions Archive</h6>
                <small class="text-muted">Filter archived extension notices by financial year or specific date range</small>
            </div>
        </div>
        
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold text-secondary small">Financial Year</label>
                <select class="form-select" id="financial_year">
                    <option value="">All Years</option>
                    <?php foreach($available_years as $year_data): ?>
                        <option value="<?php echo $year_data['year']; ?>">
                            <?php echo $year_data['year']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold text-secondary small">Start Date</label>
                <input type="date" class="form-control" id="start_date">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold text-secondary small">End Date</label>
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

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <div id="extensionsContainer">
                <div class="loading-spinner">
                    <i class="bi bi-arrow-repeat"></i>
                    <p>Loading extension tenders...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Base URLs for different document types
const BASE_URL = 'https://tvnl.in';
const EXTENSION_DOC_PATH = '/master_login/dashboard/upload_doc_ext/';
const TENDER_DOC_PATH = '/master_login/dashboard/upload_document/';

// Function to build URL for extension notices
function buildExtensionUrl(fileName) {
    if (!fileName || fileName === '') {
        return '';
    }
    
    if (fileName.startsWith('http://') || fileName.startsWith('https://')) {
        return fileName;
    }
    
    return BASE_URL + EXTENSION_DOC_PATH + fileName;
}

// Function to build URL for tender documents
function buildTenderDocumentUrl(fileName) {
    if (!fileName || fileName === '') {
        return '';
    }
    
    if (fileName.startsWith('http://') || fileName.startsWith('https://')) {
        return fileName;
    }
    
    return BASE_URL + TENDER_DOC_PATH + fileName;
}

let currentFilters = {
    financial_year: '',
    start_date: '',
    end_date: ''
};

function fetchExtensions(page = 1) {
    let financial_year = $('#financial_year').val();
    let start_date = $('#start_date').val();
    let end_date = $('#end_date').val();
    
    currentFilters = {
        financial_year: financial_year,
        start_date: start_date,
        end_date: end_date
    };
    
    $('#extensionsContainer').html(`
        <div class="loading-spinner">
            <i class="bi bi-arrow-repeat"></i>
            <p>Loading extension tenders...</p>
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
            console.log('Response:', response);
            
            $('#extensionsContainer').empty();
            
            if (response.success && response.extensions && response.extensions.length > 0) {
                let tableHtml = `
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Sl No</th>
                                <th>Description</th>
                                <th class="text-center">Original Last Date<br>of Bid Submission</th>
                                <th class="text-center">Extended Last Date<br>of Bid Submission</th>
                                <th class="text-center">Publishing Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                $.each(response.extensions, function(index, extension) {
                    let serialNumber = response.offset + index + 1;
                    
                    // Format dates
                    let originalEndDate = extension.original_end_date ? new Date(extension.original_end_date) : null;
                    let originalEndDateFormatted = originalEndDate ? formatDate(originalEndDate) : 'Not Available';
                    
                    let extendedEndDate = extension.extended_end_date ? new Date(extension.extended_end_date) : null;
                    let extendedEndDateFormatted = extendedEndDate ? formatDate(extendedEndDate) : 'Not Available';
                    
                    let publishingDate = extension.pub_date ? new Date(extension.pub_date) : null;
                    let publishingDateFormatted = publishingDate ? formatDate(publishingDate) : 'Not Available';
                    
                    // Truncate description
                    let description = extension.descp || 'No description available';
                    if (description.length > 150) {
                        description = description.substring(0, 150) + '...';
                    }
                    
                    let title = extension.title || 'Tender Notice';
                    let tenderNit = extension.nit_no || 'N/A';
                    
                    // Build URLs using the correct functions
                    let extensionDocUrl = buildExtensionUrl(extension.ext_doc_url);
                    let tenderDocumentUrl = buildTenderDocumentUrl(extension.document_url);
                    
                    tableHtml += `
                        <tr class="tender-row">
                            <td>${serialNumber}</td>
                            <td>
                                <h6 class="fw-semibold text-primary mb-1">
                                    ${escapeHtml(title)}
                                    <span class="badge badge-extension">Extended</span>
                                </h6>
                                <p class="small text-muted mb-1">${escapeHtml(description)}</p>
                                <span class="badge bg-info">${escapeHtml(tenderNit)}</span>
                                <div class="debug-info">
                                    <small>Ext File: ${escapeHtml(extension.ext_doc_url || 'No file')}</small><br>
                                    <small>Tender File: ${escapeHtml(extension.document_url || 'No file')}</small>
                                </div>
                            </div>
                            <td class="text-center">
                                <span class="badge bg-secondary ext-date-old">${originalEndDateFormatted}</span>
                            </div>
                            <td class="text-center">
                                <span class="badge bg-danger ext-date-new">${extendedEndDateFormatted}</span>
                            </div>
                            <td class="text-center">
                                <span class="badge bg-success">${publishingDateFormatted}</span>
                            </div>
                            <td class="text-center">
                    `;
                    
                    // Extension Notice Button
                    if (extensionDocUrl && extensionDocUrl !== '') {
                        tableHtml += `<a href="${escapeHtml(extensionDocUrl)}" target="_blank" class="btn btn-outline-warning btn-sm mb-1">
                                        <i class="bi bi-calendar-plus"></i> Extension Notice
                                      </a><br>`;
                    } else {
                        tableHtml += `<button class="btn btn-outline-warning btn-sm mb-1" disabled>
                                        <i class="bi bi-calendar-plus"></i> Extension Notice
                                      </button><br>`;
                    }
                    
                    // Tender Document Button
                    if (tenderDocumentUrl && tenderDocumentUrl !== '') {
                        tableHtml += `<a href="${escapeHtml(tenderDocumentUrl)}" target="_blank" class="btn btn-outline-success btn-sm mb-1">
                                        <i class="bi bi-download"></i> Tender Document
                                      </a>`;
                    } else {
                        tableHtml += `<button class="btn btn-outline-success btn-sm mb-1" disabled>
                                        <i class="bi bi-download"></i> Tender Document
                                      </button>`;
                    }
                    
                    tableHtml += `
                            </div>
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
                let errorMsg = response.message || 'No extension tenders found in the database.';
                $('#extensionsContainer').html(`
                    <div class="no-tenders">
                        <i class="bi bi-inbox"></i>
                        <h4 style="color: #666; margin-bottom: 10px;">No Extension Tenders Found</h4>
                        <p style="color: #999;">${escapeHtml(errorMsg)}</p>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            let errorDetail = '';
            try {
                let response = JSON.parse(xhr.responseText);
                errorDetail = response.message || response.error || '';
            } catch(e) {
                errorDetail = xhr.responseText ? xhr.responseText.substring(0, 200) : 'Unknown error';
            }
            
            $('#extensionsContainer').html(`
                <div class="error-message">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <h4 style="color: #dc3545;">Error Loading Extension Tenders</h4>
                    <p style="color: #666;">${escapeHtml(errorDetail)}</p>
                    <button class="btn btn-primary mt-3" onclick="fetchExtensions(1)">Try Again</button>
                </div>
            `);
        }
    });
}

function formatDate(date) {
    let day = date.getDate().toString().padStart(2, '0');
    let month = (date.getMonth() + 1).toString().padStart(2, '0');
    let year = date.getFullYear();
    let hours = date.getHours().toString().padStart(2, '0');
    let minutes = date.getMinutes().toString().padStart(2, '0');
    return `${day}-${month}-${year} | ${hours}:${minutes}`;
}

function generatePagination(currentPage, totalPages, totalRecords, resultsPerPage, offset) {
    let startRecord = offset + 1;
    let endRecord = Math.min(offset + resultsPerPage, totalRecords);
    
    let html = `
        <div class="row mt-4 align-items-center">
            <div class="col-md-6">
                <div class="records-info">
                    Showing ${startRecord} to ${endRecord} of ${totalRecords} entries
                </div>
            </div>
            <div class="col-md-6">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-end">
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