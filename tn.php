<?php include "header1.php"; ?>

<style>
    body {
        background-color: #f5f7fa;
    }

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
</style>
<section class="tvnl-banner position-relative">

    <!-- Desktop Image (80vh) -->
    <img src="assets/images/banner/board-banner.jpg" class="img-fluid w-100 d-none d-lg-block "
        style="height:90vh; object-fit:cover;">

    <!-- Tablet + Mobile Image (40vh) -->
    <img src="assets/images/banner/board-banner.jpg" class="img-fluid w-100 d-block d-lg-none"
        style="height:40vh; object-fit:cover;">

    <!-- Overlay (DON'T CHANGE POSITION) -->
    <div class="banner-overlay position-absolute top-0 start-0  d-flex justify-content-center align-items-center">
        <h2 class="banner-title text-white">Tenders Notices</h2>
    </div>

</section>



<div class="container my-5">

    <!-- Page Title -->
    <div class="mb-4 page-title">
        <h3 class="fw-bold text-primary mb-0">
            <i class="bi bi-file-earmark-text"></i> Tender Notices
        </h3>
    </div>

    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label fw-semibold mt-4">Financial Year</label>
                    <select class="form-select">
                        <option selected>2025</option>
                        <option>2024</option>
                        <option>2023</option>
                        <option>2021</option>
                        <option>2020</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Start Date</label>
                    <input type="date" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">End Date</label>
                    <input type="date" class="form-control">
                </div>

            </div>
        </div>
    </div>

    <!-- Tender Table -->
    <div class="card shadow-sm">
        <div class="card-body table-responsive">

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
                    <tr>
                        <td>1</td>

                        <td>
                            <h6 class="fw-semibold text-primary mb-1">
                                AMC for maintenance of computer & its peripherals
                            </h6>
                            <p class="small text-muted mb-1">
                                , networking & its devices, Biometric machines & CCTV cameras installed in the offices
                                of TTPS, Lalpania for two years.
                            </p>
                            <span class="badge bg-info">
                                32/IT/W/TVNL/RAN/2025-26
                            </span>
                        </td>

                        <td class="text-center">
                            <span class="badge bg-danger">
                                24-12-2025 | 14:00 Hrs
                            </span>
                        </td>

                        <td class="text-center">
                            <span class="badge bg-success">
                                24-12-2025 | 16:00 Hrs
                            </span>
                        </td>

                        <td class="text-center">
                            <a href="#" class="btn btn-outline-primary btn-sm mb-1">
                                <i class="bi bi-eye"></i> Tender Notice
                            </a>
                            <a href="#" class="btn btn-outline-success btn-sm mb-1">
                                <i class="bi bi-download"></i> Tender Document
                            </a>
                        </td>
                    </tr>

                </tbody>
            </table>

        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body table-responsive">

            <table class="table table-hover align-middle">


                <tbody>
                    <tr>
                        <td>1</td>

                        <td>
                            <h6 class="fw-semibold text-primary mb-1">
                                Supply, Installation, Retrofitting and Commissioning
                            </h6>
                            <p class="small text-muted mb-1">
                                of Bus Bar Numerical Relay Protection System for 14 Running Bays and Central Unit having
                                provision for 06 future bays at T.T.P.S. Lalpania, Bokaro, Jharkhand
                            </p>
                            <span class="badge bg-info">
                                31/EM-II/P/TVNL/RAN/2025–26
                            </span>
                        </td>

                        <td class="text-center">
                            <span class="badge bg-danger">
                                16-12-2025 | 16:00 Hrs
                            </span>
                        </td>

                        <td class="text-center">
                            <span class="badge bg-success">
                                03-03-2022 | 16:00 Hrs
                            </span>
                        </td>

                        <td class="text-center">
                            <a href="#" class="btn btn-outline-primary btn-sm mb-1">
                                <i class="bi bi-eye"></i> Tender Notice
                            </a>
                            <a href="#" class="btn btn-outline-success btn-sm mb-1">
                                <i class="bi bi-download"></i> Tender Document
                            </a>
                        </td>
                    </tr>

                </tbody>
            </table>

        </div>
    </div>

</div>

<?php include("footer_top.php"); ?>
<?php include "footer1.php"; ?>