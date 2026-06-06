<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/layouts/header.php';

$limit = 20;

// current page  id=34&&page=2
$limit = 20;

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
    'options' => ['default' => 1, 'min_range' => 1]
]);

$loginId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$loginId) {
    die('Invalid login session');
}

$offset = ($page - 1) * $limit;

$countSql = "
    SELECT COUNT(*)
    FROM activity_logs al
    WHERE al.login_id = :login_id
";

$stmt = $pdo->prepare($countSql);
$stmt->bindValue(':login_id', $loginId, PDO::PARAM_INT);
$stmt->execute();

$totalRecords = (int)$stmt->fetchColumn();
$totalPages   = (int)ceil($totalRecords / $limit);


$sql = "
    SELECT
        al.id AS activity_id,
        al.table_name,
        al.record_id,
        al.action AS activity_action,
        al.column_name,
        al.old_value,
        al.new_value,
        al.performed_by,
        al.performed_at,
        al.ip_address AS activity_ip,

        u.username,
        ul.action AS login_action,
        ul.created_at AS login_time,
        ul.ip AS login_ip

    FROM activity_logs al
    INNER JOIN users_logs ul ON ul.id = al.login_id
    INNER JOIN users u ON u.id = ul.user_id
    WHERE al.login_id = :login_id
    ORDER BY al.performed_at DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':login_id', $loginId, PDO::PARAM_INT);
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

function prettyJson($json)
{
    if (empty($json)) return '';

    $decoded = json_decode($json, true);
    if (!$decoded) return '';

    return json_encode(
        $decoded,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );
}

function renderTable(array $data, ?array $oldData = null)
{
    echo '<table class="table table-bordered table-striped">';
    echo '<tbody>';

    foreach ($data as $key => $value) {

        $label = ucwords(str_replace('_', ' ', $key));

        $oldValue = $oldData[$key] ?? null;

        // Compare flag
        $isChanged = $oldData !== null
            && trim((string)$oldValue) !== trim((string)$value);

        // Highlight style
        $highlightStart = $isChanged
            ? '<div style="background:#ffe6e6;color:#b30000;padding:6px;border-radius:4px;">'
            : '';

        $highlightEnd = $isChanged ? '</div>' : '';

        echo '<tr>';
        echo '<th width="30%">' . htmlspecialchars($label) . '</th>';
        echo '<td>';
        echo $highlightStart;

        // NULL / Empty
        if ($value === null || $value === '') {
            echo 'N/A';

            // Image
        } elseif (is_string($value) && preg_match('/\.(jpg|jpeg|png|webp)$/i', $value)) {
            echo '<a href="src/' . htmlspecialchars($value) . '" target="_blank">View Image</a>';

            // Video (YouTube iframe)
        } elseif (is_string($value) && str_contains($value, 'youtube.com/embed')) {
            echo '<iframe width="300" height="170" src="' . htmlspecialchars($value) . '" frameborder="0" allowfullscreen></iframe>';

            // PDF
        } elseif (is_string($value) && str_ends_with($value, '.pdf')) {
            echo '<a href="src/' . htmlspecialchars($value) . '" target="_blank">View PDF</a>';

            // HTML content (description)
        } elseif (is_string($value) && str_contains($key, 'description')) {
            echo '<div style="max-height:200px; overflow:auto;">' . $value . '</div>';

            // Array / Object
        } elseif (is_array($value)) {
            echo '<pre>' . json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';

            // Default
        } else {
            echo htmlspecialchars((string) $value);
        }

        echo $highlightEnd;
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
}


?>
<style>
    .accordion-content {
        display: none;
        background: #f9fafb;
    }

    pre {
        background: #111827;
        color: #e5e7eb;
        padding: 10px;
        border-radius: 6px;
        font-size: 12px;
        overflow-x: auto;
    }

    .badge-active {
        color: #065f46;
        font-weight: 600;
    }

    .badge-inactive {
        color: #991b1b;
        font-weight: 600;
    }
</style>
<div class="container-fluid">

    <div class="card">
        <div class="card-body p-0">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                <span>Activity Logs</span>
                <a href="javascript:history.back()" class="btn btn-danger btn-sm">
                    ← Back
                </a>
            </div>

            <div class="p-2">
                <!-- rest form / content -->
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <?php
                    $i = $offset + 1;

                    foreach ($activityLogs as $row):
                        $isDelete = ($row['activity_action'] === 'DELETE');
                    ?>
                        <!-- Main Row -->
                        <tr class="activity-row">
                            <td><?= $i++; ?>
                                <hr>
                                <strong>Module:</strong> <?= htmlspecialchars(strtoupper($row['table_name'])) ?><br>
                                <strong>Action: </strong> <?php if ($row['activity_action'] === 'INSERT'): ?>
                                    <span class="badge me-3 d-none d-md-inline-block role-badge">INSERT</span>
                                <?php elseif ($row['activity_action'] === 'UPDATE'): ?>
                                    <span class="badge me-3 d-none d-md-inline-block" style="background: linear-gradient(90deg,#00ccff 60%,#0492e4 100%);">UPDATE</span>
                                <?php elseif ($row['activity_action'] === 'DELETE'): ?>
                                    <span class="badge me-3 d-none d-md-inline-block" style="background: linear-gradient(90deg,#d30606 60%,#920404 100%);">DELETE</span>
                                <?php endif; ?><br>
                                <strong>Performed At: </strong> <?= htmlspecialchars(date('d/m/Y h:i A', strtotime($row['performed_at']))) ?><br>
                                <?php if ($row['activity_action'] === 'DELETE'): ?>
                                    <strong>Status:</strong>
                                    <?= ($row['new_value'] == 0)
                                        ? '<span class="badge-active">Active</span>'
                                        : '<span class="badge-inactive">Inactive</span>' ?>
                                    <h6>Old Value</h6>
                                    <pre><?= $row['old_value'] ?></pre>
                                    <h6>New Value</h6>
                                    <pre><?= $row['new_value'] ?></pre>
                                <?php endif; ?>
                                
                                <?php if ($row['activity_action'] === 'UPDATE' && $row['column_name'] == 'is_hide'): ?>
                                    <strong>Hide :</strong>
                                    <?= ($row['old_value'] == 'N')
                                        ? '<span class="badge-active">Yes</span>'
                                        : '<span class="badge-inactive">No</span>' ?>
                                    <h6>Old Value</h6>
                                    <pre><?= $row['old_value'] ?></pre>
                                    <h6>New Value</h6>
                                    <pre><?= $row['new_value'] ?></pre>
                                <?php endif; ?>

                                <?php
                                $oldData = !empty($row['old_value']) ? json_decode($row['old_value'], true) : [];
                                $newData = !empty($row['new_value']) ? json_decode($row['new_value'], true) : [];
                                ?>

                                <?php if ($row['activity_action'] !== 'DELETE' && $row['column_name'] == NULL): ?>
                                    <div class="row">

                                        <!-- OLD VALUE COLUMN -->
                                        <div class="col-md-6">
                                            <div class="card-header-modern bg-light d-flex align-items-center justify-content-between p-2 mt-2">
                                                <span class="fw-semibold">
                                                    <i class="ti ti-history me-1"></i> Old Value
                                                </span>
                                                <span class="badge bg-secondary-subtle text-secondary">
                                                    Previous
                                                </span>
                                            </div>
                                            <?php if (!empty($oldData)): ?>
                                                <?php renderTable($oldData); ?>
                                            <?php else: ?>
                                                <p class="text-muted">N/A</p>
                                            <?php endif; ?>
                                        </div>

                                        <!-- NEW VALUE COLUMN -->
                                        <div class="col-md-6">
                                            <div class="card-header-modern text-white d-flex align-items-center justify-content-between mt-2 p-2">
                                                <span class="fw-semibold">
                                                    <i class="ti ti-edit me-1"></i> New Value
                                                </span>
                                                <span class="badge bg-warning text-dark">
                                                    Updated
                                                </span>
                                            </div>

                                            <?php if (!empty($newData)): ?>
                                                <?php renderTable($newData, $oldData ?: null); ?>
                                            <?php else: ?>
                                                <p class="text-muted">N/A</p>
                                            <?php endif; ?>
                                        </div>

                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($activityLogs)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No activity found</td>
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