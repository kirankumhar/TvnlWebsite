<!DOCTYPE html>
<?php
// Start the session at the very beginning of the file
session_start();

include('./system-config.php');

// Cleanup invalid / stale login cookies before redirecting
if (isset($_SESSION['user_id']) && !isset($_SESSION['login_time'])) {
  session_unset();
  session_destroy();
}

// Check for redirects early
if (isset($_SESSION['user_id']) && isset($_SESSION['login_time'])) {
  header('Location: dashboard_view.php');
  exit; // Always exit after a redirect
}

?>
<html lang="en">

<head>
  <title>Admin Panel -- Login</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <link rel="shortcut icon" type="image/png" href="./assets/images/logos/ced_right_mast.jpg" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./assets/css/login.css">
</head>

<body>
  <div class="login-page">
    <!-- Floating Particles -->
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    
    <div class="login-container">
      <!-- <h2 class="page-title">Admin Panel</h2> -->
      <div class="login-card">
        <div class="row g-0">
          <div class="col-md-7">
            <div class="form-left">
              <div class="glass-form">
                <div class="administration-header d-flex align-items-center justify-content-between">

                  <!-- Left Side : Heading -->
                  <h4 class="administration-title mb-0">
                    <?= htmlspecialchars($loginPage_app_name); ?>
                  </h4>

                  <!-- Right Side : Logo -->
                  <div class="administration-logo">
                    <img
                      src="<?= $logo; ?>"
                      alt="CGST Administration Logo"
                      class="img-fluid"
                      loading="lazy"
                      height="50">
                  </div>

                </div>

                <div class="page-header">
                  <p>Please sign in to access your dashboard</p>
                </div>

                <form action="src/controllers/LoginController.php" method="post">
                  <?php
                  if (isset($_SESSION['login_error'])) {
                    echo "<div class='alert alert-danger' role='alert'>"
                      . $_SESSION['login_error'] .
                      "</div>";
                    unset($_SESSION['login_error']);
                  }
                  ?>

                  <div class="form-group">
                    <label class="form-label">Username (E-mail) <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <div class="input-group-text"><i class="bi bi-person"></i></div>
                      <input type="email" name="email" class="form-control" id="email" placeholder="Enter your email">
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="form-label d-flex justify-content-between">
                      <span>Password <span class="text-danger">*</span></span>
                      <!-- <a href="#" class="text-primary">Forgot Password?</a> -->
                    </label>
                    <div class="input-group">
                      <div class="input-group-text"><i class="bi bi-lock"></i></div>
                      <input type="password" name="password" class="form-control" id="password" placeholder="Enter your password">
                      <button class="input-group-text btn-primary text-white border-0" type="button" id="togglePassword">
                        <i class="bi bi-eye"></i>
                      </button>
                    </div>
                  </div>

                  <label class="form-label">Captcha <span class="text-danger">*</span></label>

                  <div class="input-group">
                    <!-- Left icon -->
                    <span class="input-group-text">
                      <i class="bi bi-shield-lock"></i>
                    </span>

                    <!-- CAPTCHA image -->
                    <span class="input-group-text p-1 bg-white">
                      <img src="captcha.php"
                        alt="CAPTCHA Image"
                        id="captchaImage"
                        style="height:35px;">
                    </span>

                    <!-- CAPTCHA input -->
                    <input type="text"
                      name="captcha"
                      class="form-control"
                      placeholder="Enter CAPTCHA"
                      autocomplete="off"
                      required>

                    <!-- Refresh button -->
                    <button class="input-group-text btn-primary text-white border-0"
                      type="button"
                      onclick="refreshCaptcha()">
                      <i class="bi bi-arrow-clockwise"></i>
                    </button>
                  </div>


                  <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                      Sign In <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                  </div>
                </form>
              </div>
              <div class="form-left-footer d-flex align-items-center justify-content-center gap-2">
                <span class="footer-label">Technology Partner</span>
                <img src="./assets/images/logos/insta-logo.jpg" alt="" srcset="" style="width: 35px;">
                <a href="https://www.computered.in/"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="footer-brand">
                  <span style="color:#ff00ff; font-family: old-bookmark;font-size: 16px;"><b>COMPUTER Ed.</b></span>
                </a>
              </div>
            </div>
          </div>
          <div class="col-md-5 d-none d-md-block">
            <div class="form-right">
              <a href="https://www.computered.in/" target="_blank" rel="noopener noreferrer"><img src="assets/images/logos/ced_right_mast.jpg" alt="Logo" class="brand-logo logo-container"></a>
              <div class="info-section">
                <h4>Admin Control Panel</h4>
                <p>Manage your resources efficiently with our powerful admin tools</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
      const passwordInput = document.getElementById('password');
      const icon = this.querySelector('i');

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
      } else {
        passwordInput.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
      }
    });
  </script>

  <script>
    function refreshCaptcha() {
      const captchaImg = document.getElementById('captchaImage');
      captchaImg.src = 'captcha.php?' + new Date().getTime();
    }
  </script>
</body>

</html>