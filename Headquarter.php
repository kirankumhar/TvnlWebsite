<?php include "header1.php"; ?>
<style>
    @media (max-width: 767px) {
        .custom-row {
            margin-left: 10px !important;
            margin-right: 30px !important;
            margin-bottom: 30px;
        }
    }
</style>

<section class="tvnl-banner position-relative">

    <!-- Desktop Image (80vh) -->
    <img src="assets/images/banner/businesses-b.jpg" class="img-fluid w-100 d-none d-lg-block"
        style="height:80vh; object-fit:cover;">

    <!-- Tablet + Mobile Image (40vh) -->
    <img src="assets/images/banner/businesses-b.jpg" class="img-fluid w-100 d-block d-lg-none"
        style="height:40vh; object-fit:cover;">

    <!-- Overlay (DON'T CHANGE POSITION) -->
    <div class="banner-overlay position-absolute top-0 start-0  d-flex justify-content-center align-items-center">
        <h2 class="banner-title text-white">Headquarter</h2>
    </div>

</section>

<div class="container my-5 custom-row">
    <div class="row g-4">

        <!-- Address Section -->
        <div class="col-md-4 custom-row">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-primary">Head Office</h5>

                    <p class="mb-2 d-flex align-items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-geo-alt-fill mr-2 mt-1" viewBox="0 0 16 16">
                            <path
                                d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6" />
                        </svg>
                        <span>
                            <strong>Tenughat Vidyut Nigam Limited</strong><br>
                            JUPMI Building Premises in ABD Area<br>
                            Ranchi Smart City, P.O & P.S - Dhurwa<br>
                            Dist. : Ranchi, Jharkhand, 834004
                        </span>
                    </p>

                    <p class="mb-0 d-flex align-items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-alarm mr-2 mt-2" viewBox="0 0 16 16">
                            <path
                                d="M8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9z" />
                            <path
                                d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1zm1.038 3.018a6 6 0 0 1 .924 0 6 6 0 1 1-.924 0M0 3.5c0 .753.333 1.429.86 1.887A8.04 8.04 0 0 1 4.387 1.86 2.5 2.5 0 0 0 0 3.5M13.5 1c-.753 0-1.429.333-1.887.86a8.04 8.04 0 0 1 3.527 3.527A2.5 2.5 0 0 0 13.5 1" />
                        </svg>
                        <span>
                            <strong>Office Hours:</strong><br>
                            Mon - Fri, 10:00 AM - 6:00 PM
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Google Map Section -->
        <div class="col-md-8">
            <div class="card shadow-sm custom-row">
                <div class="card-body p-0 ">
                    <iframe
                        src="https://www.google.com/maps?q=Tenughat+Vidyut+Nigam+Limited,+Dhurwa,+Ranchi,+Jharkhand+834004&output=embed"
                        width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>

    </div>
</div>
<?php include("footer_top.php"); ?>
<?php include "footer1.php"; ?>