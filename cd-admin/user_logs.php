<?php
session_start();
require_once __DIR__ . '/layouts/header.php';

$limit = 50;

// current page
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0
    ? (int) $_GET['page']
    : 1;

$offset = ($page - 1) * $limit;

$countSql = "SELECT COUNT(*) 
             FROM users_logs al 
             JOIN users u ON u.id = al.user_id";

$totalRecords = (int) $pdo->query($countSql)->fetchColumn();
$totalPages   = (int) ceil($totalRecords / $limit);

$sql = "
    SELECT 
        ul.id AS login_id,
        ul.action,
        ul.created_at,
        ul.ip,
        u.username,
        ul.duration,
        COUNT(al.id) AS activity_count
    FROM users_logs ul
    JOIN users u ON u.id = ul.user_id
    LEFT JOIN activity_logs al ON al.login_id = ul.id
    GROUP BY ul.id
    ORDER BY ul.created_at DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$activityLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$maxVisible = 5;

$startPage = max(1, $page - floor($maxVisible / 2));
$endPage   = $startPage + $maxVisible - 1;

if ($endPage > $totalPages) {
    $endPage = $totalPages;
    $startPage = max(1, $endPage - $maxVisible + 1);
}

?>

<div class="container-fluid">

    <div class="card">
        <div class="card-body p-0">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                Activity Logs
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
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <th>User Name</th>
                        <th>Action</th>
                        <th>Duration</th>
                        <th>User IP</th>
                        <th>Date</th>
                        <th>Action Counts</th>
                        <th>Actvities</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = $offset + 1;
                    foreach ($activityLogs as $row): ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['action']) ?></td>
                            <?php
                            $durationText = 'N/A';

                            if (!empty($row['action'])) {
                                if ($row['action'] === 'Session Timeout') {
                                    $durationText = '15 minutes';
                                } elseif ($row['action'] === 'User logged out' && isset($row['duration'])) {
                                    $durationText = (int)$row['duration'] . ' minutes';
                                }
                            }
                            ?>
                            <td><?= htmlspecialchars($durationText) ?></td>

                            <td><?= htmlspecialchars($row['ip']) ?></td>
                            <td><?= date('d/m/Y h:i A', strtotime($row['created_at'])) ?></td>
                            <td><?= htmlspecialchars($row['activity_count']) ?></td>
                            <?php if ($row['action'] == 'User logged in successfully' && $row['activity_count'] > 0) { ?>
                                <td><a href="<?= $base_url ?>/activity_logs.php?id=<?php echo htmlspecialchars($row['login_id']) ?>"
                                        class="btn btn-primary btn-sm"><i class="ti ti-list-details"></i></a>
                                    </button>
                                </td>
                            <?php } else { ?>
                                <td></td>
                            <?php } ?>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($activityLogs)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No activity found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-end">

                        <!-- Previous -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                        </li>

                        <!-- First Page -->
                        <?php if ($startPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1">1</a>
                            </li>
                            <?php if ($startPage > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                            <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $p ?>">
                                    <?= $p ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Last Page -->
                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $totalPages ?>">
                                    <?= $totalPages ?>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Next -->
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                        </li>

                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$embed_script = "newsForm.js";
require_once __DIR__ . '/layouts/footer.php'; ?>