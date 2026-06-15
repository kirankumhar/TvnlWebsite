<?php include "header1.php"; ?>
<?php 
    require_once __DIR__ . '/db.php';
    $database = new Database();
    $conn = $database->getConnection();

    // Get distinct years for filter from pub_date_time
    try {
        $query = "SELECT DISTINCT YEAR(pub_date_time) as year 
                FROM tbl_corrigendum_notice 
                WHERE pub_date_time IS NOT NULL 
                ORDER BY year DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $available_years = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $available_years = [];
        error_log("Table error: " . $e->getMessage());
    }
?>
<link rel="stylesheet" href="assets/css/tenders.css">

<section class="tvnl-banner">
    <img src="assets/images/banner/board-banner.jpg" alt="Corrigendum Notices" class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">Corrigendum Notices</h2>
        </div>
    </div>
</section>

<div class="container my-5">

    <div class="mb-4 page-title">
        <h3 class="fw-bold text-primary mb-0">
            <i class="bi bi-exclamation-triangle"></i> Corrigendum Notices
        </h3>
    </div>

    <div class="filter-card theme-corrigendum mb-4">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="filter-icon-wrapper">
                <i class="bi bi-funnel-fill"></i>
            </div>
            <div>
                <h6 class="filter-card-title mb-0">Search Corrigendum Archive</h6>
                <small class="text-muted">Filter archived corrigendum notices by financial year or specific date range</small>
            </div>
        </div>
        
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold text-secondary small">Financial Year</label>
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
            <div id="corrigendumContainer">
                <div class="loading-spinner">
                    <i class="bi bi-arrow-repeat"></i>
                    <p>Loading corrigendum notices...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function fetchCorrigendum(page = 1) {
    let financial_year = $('#financial_year').val();
    let start_date = $('#start_date').val();
    let end_date = $('#end_date').val();
    
    $('#corrigendumContainer').html(`
        <div class="loading-spinner">
            <i class="bi bi-arrow-repeat"></i>
            <p>Loading corrigendum notices...</p>
        </div>
    `);
    
    $.ajax({
        url: 'ajax_call/fetch_corrigendum.php',
        type: 'POST',
        data: {
            financial_year: financial_year,
            start_date: start_date,
            end_date: end_date,
            page: page
        },
        dataType: 'json',
        success: function(response) {
            $('#corrigendumContainer').empty();
            
            if (response.success && response.corrigendum && response.corrigendum.length > 0) {
                let tableHtml = `
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Tender Details</th>
                                <th class="text-center">Corrigendum Date</th>
                                <th class="text-center">Corrigendum Notice</th>
                                <th class="text-center">Original Documents</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                $.each(response.corrigendum, function(index, item) {
                    let serialNumber = response.offset + index + 1;
                    
                    // Format date
                    let pubDate = formatDateTime(item.pub_date_time);
                    
                    // Tender details
                    let title = item.title || 'Tender Notice';
                    let nitNo = item.nit_no || 'N/A';
                    let description = item.descp || '';
                    if (description.length > 80) {
                        description = description.substring(0, 80) + '...';
                    }
                    
                    // Corrigendum document path
                    let corrigUrl = '';
                    if (item.corrig_url && item.corrig_url !== 'NULL' && item.corrig_url !== '') {
                        // Check if it's already a full URL or path
                        if (item.corrig_url.startsWith('http') || item.corrig_url.startsWith('/')) {
                            corrigUrl = item.corrig_url;
                        } else {
                            corrigUrl = 'uploads/corrigendum/' + item.corrig_url;
                        }
                    }
                    
                    tableHtml += `
                        <tr class="corrigendum-row">
                            <td><strong>${serialNumber}</strong></td>
                            <td>
                                <div class="fw-semibold text-primary mb-1">${escapeHtml(title)}</div>
                                ${description ? `<p class="small text-muted mb-1">${escapeHtml(description)}</p>` : ''}
                                <span class="badge bg-secondary">NIT: ${escapeHtml(nitNo)}</span>
                            </td>
                            <td class="text-center">
                                <div class="notice-card">
                                    <i class="bi bi-calendar3"></i>
                                    <strong>${pubDate}</strong>
                                </div>
                             </td>
                            <td class="text-center">
                                ${corrigUrl ? `
                                    <a href="${escapeHtml(corrigUrl)}" target="_blank" class="btn btn-warning btn-sm">
                                        <i class="bi bi-file-earmark-pdf"></i> Download Corrigendum
                                    </a>
                                ` : `
                                    <span class="text-muted">Not Available</span>
                                `}
                             </td>
                            <td class="text-center">
                                <div class="document-buttons">
                    `;
                    
                    // Original tender notice
                    if (item.notice_url && item.notice_url !== 'NULL' && item.notice_url !== '') {
                        tableHtml += `<a href="${escapeHtml(item.notice_url)}" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> Tender Notice
                                      </a>`;
                    }
                    
                    // Tender document
                    if (item.document_url && item.document_url !== 'NULL' && item.document_url !== '') {
                        tableHtml += `<a href="${escapeHtml(item.document_url)}" target="_blank" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-download"></i> Tender Document
                                      </a>`;
                    }
                    
                    if ((!item.notice_url || item.notice_url === 'NULL' || item.notice_url === '') &&
                        (!item.document_url || item.document_url === 'NULL' || item.document_url === '')) {
                        tableHtml += `<span class="text-muted small">No documents</span>`;
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
                
                $('#corrigendumContainer').html(tableHtml);
            } else {
                $('#corrigendumContainer').html(`
                    <div class="no-data">
                        <i class="bi bi-inbox"></i>
                        <h4>No Corrigendum Notices Found</h4>
                        <p>There are no corrigendum notices available for the selected criteria.</p>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            $('#corrigendumContainer').html(`
                <div class="no-data">
                    <i class="bi bi-exclamation-triangle-fill" style="color: #ff4444;"></i>
                    <h4 style="color: #ff4444;">Error Loading Corrigendum</h4>
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
        
        let hours = date.getHours().toString().padStart(2, '0');
        let minutes = date.getMinutes().toString().padStart(2, '0');
        
        return `${day}/${month}/${year} <small class="text-muted">${hours}:${minutes}</small>`;
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
    
    // First page
    html += `<li class="page-item ${currentPage == 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="1">&laquo;&laquo;</a>
            </li>`;
    
    // Previous page
    html += `<li class="page-item ${currentPage == 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">&laquo;</a>
            </li>`;
    
    // Page numbers
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
    
    // Next page
    html += `<li class="page-item ${currentPage == totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">&raquo;</a>
            </li>`;
    
    // Last page
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
    // Initial load
    fetchCorrigendum(1);
    
    // Auto-load when financial year changes
    $('#financial_year').change(function() {
        fetchCorrigendum(1);
    });
    
    // Apply filters for date range
    $('#apply_filters').click(function() {
        fetchCorrigendum(1);
    });
    
    // Reset all filters
    $('#reset_filters').click(function() {
        $('#financial_year').val('');
        $('#start_date').val('');
        $('#end_date').val('');
        fetchCorrigendum(1);
    });
    
    // Pagination click handler
    $(document).on('click', '.pagination .page-link', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        if (page && !$(this).parent().hasClass('disabled')) {
            fetchCorrigendum(page);
        }
    });
});
</script>

<?php include("footer_top.php"); ?>
<?php include "footer1.php"; ?>