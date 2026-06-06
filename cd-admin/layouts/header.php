<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('./system-config.php'); // system color theme or basic details
include('./service.php'); // database connection and dashboard logged data
include('./src/utils/domains.php'); // global domains list according role based
require('./permission_helper.php');

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token']; ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=  $title; ?> </title>

    <!-- Favicon icon-->
    <link rel="shortcut icon" type="image/png" href="<?= $faviconIcon ?>" />
    <!-- <link rel="stylesheet" href="<?= $base_url ?>/assets/css/styles.min.css"> -->

    <!-- Bootstrap 5.3.0 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Tabler Icons -->
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/icons/tabler-icons/tabler-icons.css" />

    <!-- Loader Css -->
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/loader.css">

    <!-- DataTable Css -->
    <link rel="stylesheet" href="<?= $base_url ?>/assets/libs/DataTables/datatables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <!-- Custom Modern Styles -->
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/modern-admin.css">

</head>
<style>
    :root {
        --primary-color: <?= $projectTheme['primary-color'] ?>;
        --primary-hover: <?= $projectTheme['primary-hover'] ?>;
        /* Sidebar */
        --sidebar-bg: <?= $projectTheme['sidebar-bg'] ?>;
        --sidebar-secondary: <?= $projectTheme['sidebar-secondary'] ?>;
        /* Govt blue */
        --sidebar-hover: <?= $projectTheme['sidebar-hover'] ?>;
        --sidebar-active: <?= $projectTheme['sidebar-active'] ?>;
        --sidebar-active-secondary: <?= $projectTheme['sidebar-active-secondary'] ?>;
    }
</style>

<body>
    <div class="overlay" id="loader" style="display:none">
        <div class="loader"></div>
        <div class="message">Please Wait...</div>
    </div>

    <div class="toast toast-onload align-items-center text-bg-primary border-0" role="alert" aria-live="assertive"
        aria-atomic="true">
        <div class="toast-body hstack align-items-start gap-6">
            <i class="ti ti-alert-circle fs-6"></i>
            <div>
                <h5 class="text-white fs-3 mb-1">Welcome to <?= $app_name; ?></h5>
                <h6 class="text-white fs-2 mb-0">Easy to costomize the Template!!!</h6>
            </div>
            <button type="button" class="btn-close btn-close-white fs-2 m-0 ms-auto shadow-none" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
    <!-- Preloader -->
    <!-- <div class="preloader">
        <img src="https://bootstrapdemos.adminmart.com/modernize/dist/assets/images/logos/favicon.png" alt="loader"
            class="lds-ripple img-fluid" />
    </div> -->

    <div id="main-wrapper">

        <!-- Sidebar Start -->
        <?php include("sidebar.php") ?>
        <!-- Sidebar End -->

        <div class="page-wrapper">
            <!-- Header Start -->
            <header class="topbar">
                <nav class="navbar navbar-expand-lg">
                    <div class="d-flex align-items-center">
                        <button class="d-md-none btn btn-link nav-icon-hover-bg p-2 me-3"
                            id="headerCollapse"
                            type="button">
                            <i class="ti ti-menu-2"></i>
                        </button>

                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= $logo ?>"
                                width="60"
                                height="40"
                                alt="Logo"
                                class="rounded" />

                            <div class="lh-sm">
                                <!-- Main Title -->
                                <h3 class="mb-0 fw-semibold">
                                    <span class="d-none d-md-inline"><?= $full_app_name; ?></span>
                                    <span class="d-md-none"><?= $app_name; ?></span>
                                </h3>

                                <!-- Subtitle -->
                                <small class="d-none d-md-inline text-body-secondary fw-normal">
                                    <?=  $title; ?> 
                                </small>
                            </div>
                        </div>
                    </div>


                    <div class="d-flex align-items-center ms-auto">

                        <!-- Session Timer (Optional) -->
                        <div class="me-3 d-none d-md-block">
                            <small class="text-muted" id="sessionTimer"></small>
                        </div>

                        <!-- User Role Badge -->
                        <?php if (isset($role)): ?>
                            <span class="badge me-3 d-none d-md-inline-block role-badge"><?= htmlspecialchars(strtoupper($role)); ?></span>
                        <?php endif; ?>

                        <!-- Session Timer Data (hidden) -->
                        <?php
                        if (!function_exists('getRemainingSessionTime')) {
                            require_once __DIR__ . '/../src/helpers/session_helper.php';
                        }
                        $remainingTime = getRemainingSessionTime();
                        $loginTime = isset($_SESSION['login_time']) ? strtotime($_SESSION['login_time']) : 0;
                        $sessionDuration = isset($_SESSION['exp_session']) ? $_SESSION['exp_session'] : 900;
                        ?>
                        <script>
                            window.sessionRemainingTime = <?= max(0, $remainingTime); ?>;
                            window.sessionLoginTime = <?= $loginTime; ?>;
                            window.sessionDuration = <?= $sessionDuration; ?>;
                            window.serverTime = <?= time(); ?>;
                        </script>

                        <!-- Theme Toggle -->
                        <button class="btn btn-link nav-icon-hover-bg p-2 me-2" type="button" id="themeToggle" title="Toggle Theme">
                            <i class="ti ti-moon" id="themeIcon"></i>
                        </button>

                        <!-- User Profile Dropdown -->
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center p-0" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?= $base_url ?>/assets/images/profile/user-1.jpg" class="rounded-circle" width="40" height="40" alt="User" />
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <div class="px-4 py-3 border-bottom">
                                        <h6 class="mb-0 fw-semibold"><?= $username; ?></h6>
                                        <small class="text-muted"><?= $email ?></small>
                                    </div>
                                </li>
                                <li>
                                    <div class="d-flex align-items-center px-4 py-3 border-bottom">
                                        <img src="<?= $base_url ?>/assets/images/profile/user-1.jpg" class="rounded-circle me-3" width="60" height="60" alt="User" />
                                        <div>
                                            <h6 class="mb-0"><?= $username;; ?></h6>
                                            <small class="text-muted"><?= isset($role) ? strtoupper($role) : 'Admin'; ?></small>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <a class="dropdown-item p-2" href="<?= $base_url ?>/src/controllers/LogoutController.php">
                                        <i class="ti ti-logout me-2"></i> Log Out
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </header>
            <!-- Header End -->

            <div class="body-wrapper">
                <!-- <div class="container-fluid"> -->