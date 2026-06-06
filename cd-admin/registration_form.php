<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RIMS: Admin Registration</title>
    <link rel="shortcut icon" type="image/png" href="assets/images/logos/insta-logo.jpg" />
    <link rel="stylesheet" href="assets/css/styles.min.css" />
</head>

<body>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <div
            class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="row justify-content-center w-100">
                    <div class="col-md-8 col-lg-6 col-xxl-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <a href="./index.html" class="text-nowrap logo-img text-center d-block py-3 w-100">
                                    <img src="assets/images/logos/rims_logo.jpg" width="120" alt="RIMS">
                                </a>

                                <form action="src/controllers/RegistrationController.php" method="post"
                                    id="registrationForm">

                                    <?php session_start();

                                    if (isset($_SESSION['user_id'])) {
                                        header("Location: " . $base_url . "dashboard_view.php");
                                    }

                                    if (isset($_SESSION['error'])) {
                                        echo "<div class='alert alert-danger' role='alert'>"
                                            . $_SESSION['error'] .
                                            "</div>";
                                        unset($_SESSION['error']);
                                    }

                                    if (isset($_SESSION['success'])) {
                                        echo "<div class='alert alert-success' role='alert'>"
                                            . $_SESSION['success'] .
                                            "</div>";
                                        unset($_SESSION['success']);

                                    }

                                    ?>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email:</label>
                                        <input type="email" class="form-control" name="email" id="email" required
                                            placeholder="Enter your email" />
                                    </div>
                                    <div class="mb-4">
                                        <label for="password" class="form-label">Password:</label>
                                        <input type="password" class="form-control" name="password" id="password"
                                            required placeholder="Enter your password" />
                                    </div>
                                    <div class="mb-4">
                                        <label for="confirmPassword" class="form-label">Confirm Password:</label>
                                        <input type="confirmPassword" class="form-control" name="confirmPassword"
                                            id="confirmPassword" required placeholder="Enter your confirmPassword" />
                                    </div>
                                    <button type="submit"
                                        class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">Register</button>

                                    <div class="d-flex align-items-center justify-content-center">
                                        <p class="fs-4 mb-0 fw-bold">Already have an Account?</p>
                                        <a class="text-primary fw-bold ms-2" href="/cdrms">Sign
                                            In</a>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center">
                                        Technology Partner <img src="assets/images/logos/insta-logo.jpg" width='50px'
                                            alt="ComputerEd"> <b>ComputerEd</b>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- <script src="assets/js/form.js"></script> -->
    <script src="assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>