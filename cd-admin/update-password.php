<?php

/**
 * Update Password Page
 * Protected page with session check
 */
require_once __DIR__ . '/src/helpers/session_helper.php';
requireLogin();

require_once __DIR__ . '/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body p-0">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                Update Password
            </div>

            <div class="p-2">
                <!-- rest form / content -->
            </div>

            <?php if (isset($_SESSION['message'])) { ?>
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <strong>Success!</strong> <?php echo $_SESSION['message']; ?>.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?php unset($_SESSION['message']); ?>
                </div>
            <?php } elseif (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <?php echo $_SESSION['error']; ?>.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php } ?>

            <form action="<?= $base_url ?>/src/controllers/UpdatePasswordController.php" method="post" id="updatePass">
                <div class="mb-4">
                    <label for="oldpassword" class="form-label">Old Password: <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" name="useroldpassword" id="oldpassword"
                        placeholder="Enter your old password" required />
                </div>
                <div class="mb-4">
                    <label for="UserPassword" class="form-label">Password: <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="password" id="UserPassword"
                            placeholder="Enter your password" autocomplete="new-password"
                            autofill="off"
                            autocorrect="off"
                            autocapitalize="off" required />

                        <button class="input-group-text btn-primary text-white border-0" type="button" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <!-- Password Criteria -->
                    <ul class="mt-2 small password-criteria" id="passwordCriteria">
                        <li id="len" class="text-dark">Max 8 characters</li>
                        <li id="upper" class="text-dark">Only 1 uppercase letter</li>
                        <li id="lower" class="text-dark">At least 1 lowercase</li>
                        <li id="number" class="text-dark">At least 1 number</li>
                        <li id="special" class="text-dark">Only 1 special character</li>
                    </ul>
                </div>
                <div class="mb-4">
                    <label for="confirmPassword" class="form-label" >Confirm Password: <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" name="confirmPassword" id="confirmPassword"
                        placeholder="Enter your confirm password" required/>
                    <div id="passwordMismatch" class="text-danger mt-2" style="display: none;">Passwords do not match.</div>
                </div>

                <button type="submit" class="btn btn-primary" disabled>Update</button>
            </form>
        </div>
    </div>
</div>
<?php
$embed_script = "restriction.js";
require_once __DIR__ . '/layouts/footer.php'; ?>