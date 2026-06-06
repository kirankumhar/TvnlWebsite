<?php include "header1.php"; ?>

<style>
    @media (max-width: 767px) {
        .custom-row {
            margin-left: 20px !important;
            margin-right: 0 !important;
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
        <h2 class="banner-title text-white">Cancellation Notices</h2>
    </div>

</section>

<div>

    <h2 class="fw-bold text-primary mb-3 text-center" style="font-size:21px; margin-top: 50px;">
    Cancellation Notices
    </h2>
    <h2 class=" text-center custom-row fw-bold" style=" font-size: 15px; ">To be Updated Soon</h3>
</div>
<?php include("footer_top.php"); ?>
<?php include "footer1.php"; ?>