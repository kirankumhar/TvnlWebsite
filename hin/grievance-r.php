<?php include "header1.php"; ?>

<section class="tvnl-banner">
    <img src="../assets/images/banner/board-banner.jpg" alt="सूचना डेस्क - शिकायत निवारण" title="सूचना डेस्क - शिकायत निवारण"
        class="banner-img img-fluid">
    <div class="banner-overlay">
        <div class="container text-center">
            <h2 class="banner-title">शिकायत निवारण</h2>
        </div>
    </div>
</section>

<div class="container py-5">
    <div class="row align-items-center">
        <div class="col-lg-7 col-md-12">
            <h2 class="fw-bold mb-4 mt-3 text-primary">निवारण फॉर्म</h2>

            <form>
                <div class="row g-4 ml-3 mr-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            नाम</label>
                        <input type="text" class="form-control" placeholder="
नाम">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ई-मेल</label>
                        <input type="email" class="form-control" placeholder="ई-मेल">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">मोबाइल नंबर</label>
                        <input type="tel" class="form-control" placeholder="मोबाइल नंबर">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">विषय</label>
                        <input type="text" class="form-control" placeholder="विषय">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">संदेश</label>
                        <textarea class="form-control" rows="4" placeholder="
विवरण"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            पता</label>
                        <textarea class="form-control" rows="3" placeholder="
पता"></textarea>
                    </div>

                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-send px-4">
                            भेजें
                        </button>
                    </div>

                </div>
            </form>
        </div>
        <div class="col-lg-5 col-md-12 text-center mt-4 mt-lg-0">
            <img src="../assets/images/about/contact-logo.jpg" class="img-fluid rounded" style="max-width:200px;"
                alt="
निवारण प्रपत्र" title="
निवारण प्रपत्र">
        </div>

    </div>
</div>

<?php include("footer_top.php"); ?>
<?php include "footer1.php"; ?>