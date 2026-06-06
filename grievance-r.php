<?php include "header1.php"; ?>

<section class="tvnl-banner">
    <img src="assets/images/banner/board-banner.jpg" alt="Info Desk - Grievance Redressal" title="Tenders - Info Desk - Grievance Redressal"
        class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">Grievance Redressal</h2>
        </div>
    </div>
</section>

<div class="container py-5">
    <div class="row align-items-center">

        <div class="col-lg-7 col-md-12">
            <h2 class="fw-bold mb-4 mt-3 text-primary">Redressal Form</h2>

            <form>
                <div class="row g-4 ml-3 mr-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" class="form-control" placeholder="Name">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">E-mail</label>
                        <input type="email" class="form-control" placeholder="E-mail">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mobile No.</label>
                        <input type="tel" class="form-control" placeholder="Mobile No.">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Subject</label>
                        <input type="text" class="form-control" placeholder="Subject">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Message</label>
                        <textarea class="form-control" rows="4" placeholder="Description"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea class="form-control" rows="3" placeholder="Address"></textarea>
                    </div>

                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-send px-4">
                            Send
                        </button>
                    </div>

                </div>
            </form>
        </div>

        <div class="col-lg-5 col-md-12 text-center mt-4 mt-lg-0">
            <img src="assets/images/about/contact-logo.jpg" class="img-fluid rounded" style="max-width:200px;"
                alt="Redressal Form" title="Redressal Form">
        </div>

    </div>
</div>

<?php include("footer_top.php"); ?>
<?php include "footer1.php"; ?>