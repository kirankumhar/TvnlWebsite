<?php

/**
 * Manage Profile Page
 * Protected page with session check
 */
require_once __DIR__ . '/src/helpers/session_helper.php';
requireLogin();

require_once __DIR__ . '/layouts/header.php';
// Get current user data
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT id, email, username, mobile FROM users WHERE id = :userId");
$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$email = $user['email'];
$prefix = explode('@', $email)[0];

if (!$user) {
    $_SESSION['error'] = 'User not found.';
    header("Location: dashboard_view.php");
    exit;
}

?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body p-0">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                Manage Profile
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

            <form action="<?= $base_url ?>/src/controllers/ProfileController.php" method="post" id="profileForm">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']); ?>">

                <div class="mb-4">
                    <label for="username" class="form-label">Name:</label>
                    <input type="text" class="form-control only-alphabet" name="username" id="username"
                        value="<?= htmlspecialchars($user['username'] ?? ''); ?>"
                        placeholder="Enter your username" required />
                </div>

                <div class="mb-4">
                    <label for="mobile" class="form-label">Phone No.:</label>
                    <input type="text" class="form-control only-number" name="mobile" id="mobile" maxlength="10" placeholder="10-digit mobile number" pattern="[0-9]{10}"
                        value="<?= htmlspecialchars($user['mobile'] ?? ''); ?>"
                        placeholder="Enter your phone" required />
                </div>

                <div class="mb-4">
                    <label class="form-label">Email ID <span class="text-danger">*</span></label>

                    <div class="input-group">
                        <input
                            type="text"
                            name="email_prefix"
                            id="emailPrefix"
                            class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($prefix ?? '') ?>"
                            required>
                        <span class="input-group-text btn-primary text-white">@cgstranchizone.gov.in</span>
                    </div>

                    <div class="form-text text-muted">
                        Only lowercase letters and numbers allowed
                    </div>

                    <!-- hidden final email -->
                    <input type="hidden" name="email" id="finalEmail" value="<?= $user['email']; ?>">

                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback d-block"><?= $errors['email'] ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>
</div>

<?php 
$embed_script = "restriction.js";
require_once __DIR__ . '/layouts/footer.php'; ?>