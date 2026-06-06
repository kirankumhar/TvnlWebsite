<?php
session_start();
include('./timeout.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}

if (isset($_SESSION['user_id'])) {
    $title = "Admin - Add Category";
    require_once __DIR__ . '/layouts/header.php';

    $permissions = [
        'domain' => 'Domain',
        'category' => 'Category',
        'news' => 'News',
        'notices' => 'Notices',
        'mediaPressclip' => 'Gallery Press Clip',
        'mediaPhoto' => 'Gallery Photos',
        'mediavideo' => 'Gallery Videos',
        'users' => 'Users',
        'permission' => 'Permissions'
    ];
?>

    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-body p-0">

                <!-- Header -->
                <div class="card-header-modern d-flex align-items-center justify-content-between">
                    Add User
                </div>

                <!-- Alerts -->
                <div class="px-3 pt-3">
                    <?php if (!empty($_SESSION['message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= $_SESSION['message'];
                            unset($_SESSION['message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif (!empty($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Form -->
                <form action="<?= $base_url ?>/src/controllers/UserController.php" method="post">
                    <input type="hidden" name="action" value="actionCreateUser">
                    <?php
                    if (isset($_SESSION['error_message'])) {
                        echo '<div style="color: red;">' . $_SESSION['error_message'] . '</div><br>';
                        unset($_SESSION['error_message']);
                    }
                    $errors = isset($_SESSION['req_error_msg']) ? $_SESSION['req_error_msg'] : '';
                    ?>
                    <div class="p-2">
                        <div class="row g-3">
                            <!-- Username -->
                            <div class="col-md-6">
                                <label class="form-label">Name<span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control only-alphabet" required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label">Email ID <span class="text-danger">*</span></label>

                                <div class="input-group">
                                    <input
                                        type="text"
                                        name="email_prefix"
                                        id="emailPrefix"
                                        class="form-control"
                                        placeholder="username"
                                        required>
                                    <span class="input-group-text btn-primary text-white">@cgstranchizone.gov.in</span>
                                </div>

                                <div class="form-text text-muted">
                                    Only lowercase letters and numbers allowed
                                </div>

                                <!-- hidden final email -->
                                <input type="hidden" name="email" id="finalEmail">

                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback d-block"><?= $errors['email'] ?></div>
                                <?php endif; ?>
                            </div>


                            <!-- Phone -->
                            <div class="col-md-6">
                                <label class="form-label">Phone No <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control only-number" maxlength="10" placeholder="10-digit mobile number" pattern="[0-9]{10}" required>
                                <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Role -->
                            <div class="col-md-6">
                                <label class="form-label">Role Type <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" required>
                                    <option value="admin">Admin</option>
                                    <option value="coadmin">Co-Admin</option>
                                    <option value="author">Author</option>
                                    <option value="writer">Writer</option>
                                </select>
                                <?php if (isset($errors['role'])): ?>
                                    <div class="invalid-feedback"><?= $errors['role'] ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Password Expiry -->
                            <div class="col-md-6">
                                <label class="form-label">Password Expiry</label>
                                <select name="password_expire_in_days" class="form-select">
                                    <option value="90" selected>90 Days (Default)</option>
                                    <option value="60">60 Days</option>
                                    <option value="30">30 Days</option>
                                    <option value="15">15 Days</option>
                                </select>
                            </div>

                            <!-- Password -->

                            <div class="col-md-6">
                                <label class="form-label">
                                    Password <span class="text-danger">*</span>
                                </label>

                                <div class="input-group">
                                    <input type="password" name="password" id="UserPassword"
                                        class="form-control" autocomplete="new-password"
                                        autofill="off"
                                        autocorrect="off"
                                        autocapitalize="off" required>

                                    <button class="input-group-text btn-primary text-white border-0" type="button" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback"><?= $errors['password'] ?></div>
                                <?php endif; ?>
                                <!-- Password Criteria -->
                                <ul class="mt-2 small password-criteria" id="passwordCriteria">
                                    <li id="len" class="text-dark">Max 8 characters</li>
                                    <li id="upper" class="text-dark">Only 1 uppercase letter</li>
                                    <li id="lower" class="text-dark">At least 1 lowercase</li>
                                    <li id="number" class="text-dark">At least 1 number</li>
                                    <li id="special" class="text-dark">Only 1 special character</li>
                                </ul>
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="confirm_password" class="form-control" id="confirmPassword" required>
                                <div id="passwordMismatch" class="text-danger mt-2" style="display: none;">Passwords do not match.</div>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Permissions -->
                            <div class="col-12">
                                <table class="table table-bordered">
                                    <thead class="table-warning">
                                        <tr>
                                            <th style="width:60px; text-align:center;">#</th>
                                            <th>Modules</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php foreach ($permissions as $key => $label): ?>
                                            <!-- Module Row -->
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox"
                                                        class="form-check-input module-check"
                                                        name="module[]"
                                                        value="<?= $key ?>"
                                                        data-target="<?= $key ?>">
                                                </td>
                                                <td><strong><?= $label ?></strong></td>
                                            </tr>

                                            <!-- Sub Permission Row -->
                                            <tr class="d-none permission-row" id="<?= $key ?>">
                                                <td colspan="2">
                                                    <div class="row" style="padding : 0px 15px 0px 15px !important;">
                                                        <div class="col-md-4">
                                                            <label>
                                                                <input type="checkbox"
                                                                    name="permission[<?= $key ?>][]"
                                                                    value="create">
                                                                Can Create
                                                            </label>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>
                                                                <input type="checkbox"
                                                                    name="permission[<?= $key ?>][]"
                                                                    value="edit">
                                                                Can Update
                                                            </label>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>
                                                                <input type="checkbox"
                                                                    name="permission[<?= $key ?>][]"
                                                                    value="delete">
                                                                Can Delete
                                                            </label>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>

                                    </tbody>
                                </table>
                            </div>

                            <!-- Submit -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4">
                                    Create User
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('.module-check').forEach(cb => {
            cb.addEventListener('change', function() {
                const row = document.getElementById(this.dataset.target);
                row.classList.toggle('d-none', !this.checked);

                // Uncheck sub permissions if module unchecked
                if (!this.checked) {
                    row.querySelectorAll('input[type="checkbox"]').forEach(c => c.checked = false);
                }
            });
        });
    </script>

<?php
    $embed_script = "restriction.js";
    require_once __DIR__ . '/layouts/footer.php';
} else {
    echo "Invalid session, <a href='index.php'>click here</a> to login";
}
?>
