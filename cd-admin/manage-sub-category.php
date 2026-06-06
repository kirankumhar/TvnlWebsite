<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/layouts/header.php';
$module = 'category';

$params = [];
$sql = "SELECT a.*, b.category_name , dm.eng_name FROM sub_category a INNER JOIN category_master b ON b.id = a.category_id LEFT JOIN domains as dm ON dm.id = a.domain_id WHERE a.is_deleted='0'";

if ($domainId > 0) {
    $sql .= " AND a.domain_id = ?";
    $params[] = $domainId;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="container-fluid">
    <div class="card">
        <div class="card-body p-0">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                Manage Sub Category
                <?php if (canCreate($pdo, $userId, $module)) : ?>
                    <a href="<?= $base_url ?>/create-sub-category.php" class="btn btn-warning btn-sm">
                        <strong>+ Create</strong>
                    </a>
                <?php endif; ?>
            </div>

            <div class="p-2">
                <!-- rest form / content -->
            </div>

            <table id="syFyTable" class="table table-bordered table-striped ">
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <th>Domain</th>
                        <th>Category Name</th>
                        <th>Name (English)</th>
                        <th>Name (Hindi)</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $i = 1;
                    foreach ($subcategories as $row): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['eng_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['sub_category_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['hindi_sub_category_name']); ?></td>

                            <td>
                                <?php if (canEdit($pdo, $userId, $module)) : ?>
                                    <a href="<?= $base_url ?>/edit-sub-category.php?id=<?= htmlspecialchars($row['id']) ?>"
                                        class="btn btn-primary btn-sm"><i class="ti ti-edit"></i></a>&nbsp;&nbsp;
                                <?php endif; ?>
                                <?php if (canDelete($pdo, $userId, $module)) : ?>
                                    <button class="btn btn-danger btn-sm delete-sub-category-button"
                                        data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>

<?php
$embed_script = "newsForm.js";
require_once __DIR__ . '/layouts/footer.php'; ?>