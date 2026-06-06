<?php
session_start();
include('./timeout.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/layouts/header.php';
$module = 'permission';

$users = [];
$params = [];
$params1 = [];
$params2 = [];
if ($authRole == 'superadmin') {
    $sql1 = "SELECT 
            u.*, 
            'Administration' AS eng_name, 
            'Administration' AS hin_name
        FROM users u
        WHERE u.domain_id = 0 AND u.is_deleted = '0'";

    $sql2 = "SELECT 
            u.*, 
            dm.eng_name, 
            dm.hin_name
        FROM users u
        JOIN domains dm ON dm.id = u.domain_id
        WHERE u.domain_id != 0";

    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute($params1);
    $systemUsers = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute($params2);
    $domainUsers = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $users = array_merge($systemUsers, $domainUsers);
} else {
    $sql = "SELECT u.*, dm.eng_name , dm.hin_name FROM users u JOIN domains as dm ON dm.id = u.domain_id ";
    if ($domainId > 0) {
        $sql .= " WHERE u.domain_id = ?";
        $params[] = $domainId;
    }
    $sql .= " ORDER BY u.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}



?>
<div class="container-fluid">

    <div class="card">
        <div class="card-body p-0">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                Manage Permission List
            </div>

            <div class="p-2">
                <!-- rest form / content -->
            </div>
            <?php if (isset($_SESSION['message'])) { ?>
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <strong>Success!</strong> <?php echo $_SESSION['message']; ?>.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <?php unset($_SESSION['message']); ?>
                </div>
            <?php } elseif (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <?php echo $_SESSION['error']; ?>.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php } ?>
            <table id="syFyTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <th>Domain</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php $i = 1;
                        foreach ($users as $row): ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= htmlspecialchars($row['eng_name']); ?></td>
                                <td><?= htmlspecialchars($row['username']); ?></td>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                                <td><?= htmlspecialchars($row['mobile']); ?></td>
                                <td><?= htmlspecialchars(ucfirst($usersRoles[$row['role']])); ?></td>
                                <td>
                                    <?php if (canEdit($pdo, $userId, $module)) : ?>
                                        <a href="<?= $base_url ?>/edit-permission.php?id=<?= (int)$row['id']; ?>"
                                            class="btn btn-secondary btn-sm">
                                            <i class="ti ti-shield-lock"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                No records found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>

<?php
$embed_script = "newsForm.js";
require_once __DIR__ . '/layouts/footer.php'; ?>