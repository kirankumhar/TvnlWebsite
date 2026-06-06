<?php include "header1.php"; ?>
<?php 
    require_once __DIR__ . '/db.php';
    $database = new Database();
    $conn = $database->getConnection();

    // Get distinct years for filter
    $query = "SELECT DISTINCT YEAR(publishing_date) as year 
            FROM tbl_tender_details 
            WHERE publishing_date IS NOT NULL 
            ORDER BY year DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $available_years = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If no years found from publishing_date, try end_date_time
    if (empty($available_years)) {
        $query = "SELECT DISTINCT YEAR(end_date_time) as year 
                FROM tbl_tender_details 
                WHERE end_date_time IS NOT NULL 
                ORDER BY year DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $available_years = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
?>
<style>
    .page-title { border-left: 5px solid #0d6efd; padding-left: 15px; }
    .table thead th { white-space: nowrap; }
    .badge { font-size: 0.85rem; }
    .btn-sm { min-width: 110px; }
    .loading-spinner { text-align: center; padding: 50px; }
    .loading-spinner i {
        font-size: 3rem;
        color: #0d6efd;
        margin-bottom: 15px;
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
        gap: 10px;
        margin-top: 15px;
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
</style>

<section class="tvnl-banner">
    <img src="assets/images/banner/board-banner.jpg" alt="Tenders - Tenders Notices" title="Tenders - Tenders Notices"
        class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">Tenders Notices</h2>
        </div>
    </div>
</section>

<div class="container my-5">

    <div class="mb-4 page-title">
        <h3 class="fw-bold text-primary mb-0">
            <i class="bi bi-file-earmark-text" title="Tender Notices"></i> Tender Notices
        </h3>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end" style="padding: 10px;">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Financial Year</label>
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
            <div id="tendersContainer">
                <div class="loading-spinner">
                    <i class="bi bi-arrow-repeat fa-spin"></i>
                    <p>Loading tenders...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let currentFilters = {
    financial_year: '',
    start_date: '',
    end_date: ''
};

function fetchTenders(page = 1) {
    let financial_year = $('#financial_year').val();
    let start_date = $('#start_date').val();
    let end_date = $('#end_date').val();
    
    // Update current filters
    currentFilters = {
        financial_year: financial_year,
        start_date: start_date,
        end_date: end_date
    };
    
    $('#tendersContainer').html(`
        <div class="loading-spinner">
            <i class="bi bi-arrow-repeat fa-spin"></i>
            <p>Loading tenders...</p>
        </div>
    `);
    
    $.ajax({
        url: 'ajax_call/fetch_tenders.php',
        type: 'POST',
        data: {
            financial_year: financial_year,
            start_date: start_date,
            end_date: end_date,
            page: page
        },
        dataType: 'json',
        success: function(response) {
            $('#tendersContainer').empty();
            
            if (response.success && response.tenders.length > 0) {
                // Build table
                let tableHtml = `
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Sl No</th>
                                <th>Description</th>
                                <th class="text-center">Last Date & Time<br>of Bid Submission</th>
                                <th class="text-center">Due Date & Time<br>of Opening (Part-A)</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                $.each(response.tenders, function(index, tender) {
                    let serialNumber = response.offset + index + 1;
                    
                    // Format dates
                    let endDate = tender.end_date_time ? new Date(tender.end_date_time) : null;
                    let endDateFormatted = endDate ? formatDate(endDate) : 'Not Available';
                    
                    let publishingDate = tender.publishing_date ? new Date(tender.publishing_date) : null;
                    let publishingDateFormatted = publishingDate ? formatDate(publishingDate) : 'Not Available';
                    
                    // Truncate description
                    let description = tender.descp || 'No description available';
                    if (description.length > 150) {
                        description = description.substring(0, 150) + '...';
                    }
                    
                    let title = tender.title || 'Tender Notice';
                    let tenderNit = tender.nit_no || 'N/A';
                    
                    tableHtml += `
                        <tr class="tender-row">
                            <td>${serialNumber}</td>
                            <td>
                                <h6 class="fw-semibold text-primary mb-1">${escapeHtml(title)}</h6>
                                <p class="small text-muted mb-1">${escapeHtml(description)}</p>
                                <span class="badge bg-info">${escapeHtml(tenderNit)}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">${endDateFormatted}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success">${publishingDateFormatted}</span>
                            </td>
                            <td class="text-center">
                    `;
                    
                    if (tender.notice_url) {
                        tableHtml += `<a href="${escapeHtml(tender.notice_url)}" target="_blank" class="btn btn-outline-primary btn-sm mb-1">
                                        <i class="bi bi-eye"></i> Tender Notice
                                      </a>`;
                    } else {
                        tableHtml += `<button class="btn btn-outline-primary btn-sm mb-1" disabled>
                                        <i class="bi bi-eye"></i> Tender Notice
                                      </button>`;
                    }
                    
                    if (tender.document_url) {
                        tableHtml += `<a href="${escapeHtml(tender.document_url)}" target="_blank" class="btn btn-outline-success btn-sm mb-1">
                                        <i class="bi bi-download"></i> Tender Document
                                      </a>`;
                    } else {
                        tableHtml += `<button class="btn btn-outline-success btn-sm mb-1" disabled>
                                        <i class="bi bi-download"></i> Tender Document
                                      </button>`;
                    }
                    
                    tableHtml += `
                            </td>
                        </tr>
                    `;
                });
                
                tableHtml += `
                        </tbody>
                    </table>
                `;
                
                // Add pagination if needed
                if (response.total_pages > 1) {
                    tableHtml += generatePagination(response.current_page, response.total_pages, response.total_records, response.results_per_page, response.offset);
                }
                
                $('#tendersContainer').html(tableHtml);
            } else {
                $('#tendersContainer').html(`
                    <div class="no-tenders">
                        <i class="bi bi-inbox"></i>
                        <h4 style="color: #666; margin-bottom: 10px;">No Tenders Found</h4>
                        <p style="color: #999;">There are no tenders available for the selected criteria.</p>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            $('#tendersContainer').html(`
                <div class="no-tenders">
                    <i class="bi bi-exclamation-triangle-fill" style="color: #ff4444;"></i>
                    <h4 style="color: #ff4444;">Error Loading Tenders</h4>
                    <p style="color: #666;">Please try again later.</p>
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
    
    // First page
    html += `<li class="page-item ${currentPage == 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="1" aria-label="First">
                    <span aria-hidden="true">&laquo;&laquo;</span>
                </a>
            </li>`;
    
    // Previous page
    html += `<li class="page-item ${currentPage == 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>`;
    
    // Page numbers
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);
    
    if (startPage > 1) {
        html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    
    for (let i = startPage; i <= endPage; i++) {
        let activeClass = (i == currentPage) ? 'active' : '';
        html += `<li class="page-item ${activeClass}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`;
    }
    
    if (endPage < totalPages) {
        html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    
    // Next page
    html += `<li class="page-item ${currentPage == totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>`;
    
    // Last page
    html += `<li class="page-item ${currentPage == totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${totalPages}" aria-label="Last">
                    <span aria-hidden="true">&raquo;&raquo;</span>
                </a>
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

// Function to check if date filters have changed
function haveDateFiltersChanged() {
    return $('#start_date').val() !== currentFilters.start_date || 
           $('#end_date').val() !== currentFilters.end_date;
}

$(document).ready(function() {
    // Initial load
    fetchTenders(1);
    
    // Auto-load when financial year changes (no click needed)
    $('#financial_year').change(function() {
        // Reset date filters when year changes (optional - remove if not needed)
        // $('#start_date').val('');
        // $('#end_date').val('');
        fetchTenders(1);
    });
    
    // Apply filters only for date range (requires click)
    $('#apply_filters').click(function() {
        fetchTenders(1);
    });
    
    // Reset all filters
    $('#reset_filters').click(function() {
        $('#financial_year').val('');
        $('#start_date').val('');
        $('#end_date').val('');
        fetchTenders(1);
    });
    
    // Optional: Clear date filters when year changes (uncomment if needed)
    // $('#financial_year').change(function() {
    //     $('#start_date').val('');
    //     $('#end_date').val('');
    // });
    
    // Pagination click handler (event delegation)
    $(document).on('click', '.pagination .page-link', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        if (page && !$(this).parent().hasClass('disabled')) {
            fetchTenders(page);
        }
    });
});
</script>

<?php include("footer_top.php"); ?>
<?php include "footer1.php"; ?>