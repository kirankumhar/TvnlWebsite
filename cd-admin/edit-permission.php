<?php
session_start();
include('./timeout.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/layouts/header.php';
if (isset($_SESSION['user_id'])) {

    $userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $sql = $pdo->prepare("SELECT username, email FROM users WHERE id= :userId #is_deleted = '0'");

    $sql->bindParam(':userId', $userId, PDO::PARAM_INT);

    $sql->execute();
    $result = $sql->fetch(PDO::FETCH_ASSOC);

    $data = $result ?? [];
    $errors = $errors ?? [];

    // Fetch permissions for user
    $sql = "SELECT * FROM permissions WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert to associative array for easy access
    $userPermissions = [];
    foreach ($rows as $row) {
        $userPermissions[$row['module']] = $row;
    }

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
                <div class="card-header-modern d-flex flex-wrap align-items-center justify-content-between gap-2">

                    <!-- Title -->
                    <div>
                        <span class="fw-semibold">Edit Permission :</span>
                        <strong><?= htmlspecialchars($data['username']) ?></strong>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center gap-2">
                        <!-- Back -->
                        <a href="javascript:history.back()"
                            class="btn btn-danger btn-sm">
                            ← Back
                        </a>

                    </div>
                </div>

                <div class="p-2">
                    <!-- rest form / content -->
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
                <form action="<?= $base_url ?>/src/controllers/PermissionController.php" method="post">
                    <input type="hidden" name="action" value="permissionUpdate">
                    <input type="hidden" name="userId" value="<?= $userId ?>">
                    <div class="p-2">
                        <div class="row g-3">
                            <div class="col-12">
                                <table class="table table-bordered">
                                    <thead class="table-warning">
                                        <tr>
                                            <th style="width:60px; text-align:center;">#</th>
                                            <th>Modules</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($permissions as $key => $label):
                                            $perm = $userPermissions[$key] ?? null;
                                            $moduleChecked = $perm ? 'checked' : '';
                                        ?>
                                            <!-- Module Row -->
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox"
                                                        class="form-check-input module-check"
                                                        name="module[]"
                                                        value="<?= $key ?>"
                                                        data-target="<?= $key ?>"
                                                        <?= $moduleChecked ?>>
                                                </td>
                                                <td><strong><?= $label ?></strong></td>
                                            </tr>

                                            <!-- Sub Permission Row -->
                                            <tr class="permission-row <?= $perm ? '' : 'd-none' ?>" id="<?= $key ?>">
                                                <td colspan="2">
                                                    <input type="hidden"
                                                        name="permission_id[<?= $key ?>]"
                                                        value="<?= $perm['id'] ?? '' ?>">

                                                    <div class="row px-3">
                                                        <div class="col-md-4">
                                                            <label>
                                                                <input type="checkbox"
                                                                    name="permission[<?= $key ?>][]"
                                                                    value="create"
                                                                    <?= (!empty($perm['can_create'])) ? 'checked' : '' ?>>
                                                                Can Create
                                                            </label>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label>
                                                                <input type="checkbox"
                                                                    name="permission[<?= $key ?>][]"
                                                                    value="edit"
                                                                    <?= (!empty($perm['can_edit'])) ? 'checked' : '' ?>>
                                                                Can Update
                                                            </label>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label>
                                                                <input type="checkbox"
                                                                    name="permission[<?= $key ?>][]"
                                                                    value="delete"
                                                                    <?= (!empty($perm['can_delete'])) ? 'checked' : '' ?>>
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
                                    Update Permission
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

                if (this.checked) {
                    row.classList.remove('d-none');
                } else {
                    row.classList.add('d-none');
                    row.querySelectorAll('input[type="checkbox"]').forEach(c => c.checked = false);
                }
            });
        });
    </script>

<?php
    require_once __DIR__ . '/layouts/footer.php';
} else {
    echo "Invalid session, <a href='index.php'>click here</a> to login";
}
?>