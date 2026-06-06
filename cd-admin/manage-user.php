<?php
session_start();
include('./timeout.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/layouts/header.php';
$module = 'users';

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
                Manage Users
                <?php if (canCreate($pdo, $userId, $module)) : ?>
                    <a href="<?= $base_url ?>/add-user.php" class="btn btn-warning btn-sm">
                        <strong>+ Create</strong>
                    </a>
                <?php endif; ?>
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
                        <th>Pwd Expires In (Days)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php $i = 1;
                        foreach ($users as $row): ?>

                            <?php
                            // Password expiry calculation
                            $setDate = new DateTime(date('Y-m-d', strtotime($row['password_set_date'])));
                            $today   = new DateTime(date('Y-m-d'));

                            $interval   = $setDate->diff($today);
                            $daysPassed = (int) $interval->days;

                            $expireIn = (int) $row['password_expire_in_days'];
                            $daysLeft = max(0, $expireIn - $daysPassed);
                            $Id_delete = $row['is_deleted'];

                            // Status label
                            $statusBadge = $Id_delete == 1
                                ? '<span class="badge bg-danger">Expired</span>'
                                : '<span class="badge bg-success">Active</span>';
                            ?>

                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= htmlspecialchars($row['eng_name']); ?></td>
                                <td><?= htmlspecialchars($row['username']); ?></td>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                                <td><?= htmlspecialchars($row['mobile']); ?></td>
                                <td><?= htmlspecialchars(ucfirst($usersRoles[$row['role']])); ?></td>
                                <td><?= $daysLeft; ?></td>
                                <td><?= $statusBadge; ?></td>
                                <td>
                                    <?php if (canEdit($pdo, $userId, $module)) : ?>
                                        <a href="<?= $base_url ?>/edit-user.php?id=<?= (int)$row['id']; ?>"
                                            class="btn btn-primary btn-sm">
                                            <i class="ti ti-edit"></i>
                                        </a>

                                        <a href="<?= $base_url ?>/edit-permission.php?id=<?= (int)$row['id']; ?>"
                                            class="btn btn-secondary btn-sm">
                                            <i class="ti ti-shield-lock"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (canDelete($pdo, $userId, $module)) : ?>
                                        <button class="btn btn-danger btn-sm delete-user-btn"
                                            data-id="<?= (int)$row['id']; ?>">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">
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