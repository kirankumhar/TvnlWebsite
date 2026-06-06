<?php
session_start();
include('./timeout.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/layouts/header.php';
$module = 'category';
$params = [];
$sql = "SELECT 
            a.*,
            dm.eng_name,
            b.category_name,
            c.sub_category_name,
            c.id AS sub_cat_id
        FROM child_sub_category a
        INNER JOIN category_master b 
            ON b.id = a.category_id
        LEFT JOIN sub_category c 
            ON c.id = a.subcategory_id
        LEFT JOIN domains dm 
            ON dm.id = a.domain_id
        WHERE a.is_deleted = '0'";

if ($domainId > 0) {
    $sql .= " AND a.domain_id = ?";
    $params[] = $domainId;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$chsubcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body p-0">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                Manage Child Sub Category
                <?php if (canCreate($pdo, $userId, $module)) : ?>
                    <a href="<?= $base_url ?>/create-child-sub-category.php" class="btn btn-warning btn-sm">
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
            <table id="syFyTable" class="table table-bordered table-striped ">
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <th>Commissionerate</th>
                        <th>Category Name</th>
                        <th>Sub Category Name</th>
                        <th>Child Name</th>
                        <th>Hindi Child Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $i = 1;
                    foreach ($chsubcategories as $row): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['eng_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['sub_category_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['child_sub_category_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['hn_child_sub_category_name']); ?></td>
                            <td>
                                <?php if (canEdit($pdo, $userId, $module)) : ?>
                                    <a href="<?= $base_url ?>/edit-child-sub-category.php?id=<?= htmlspecialchars($row['id']) ?>"
                                    class="btn btn-primary btn-sm"><i class="ti ti-edit"></i></a>&nbsp;&nbsp;
                                <?php endif; ?>
                                <?php if (canDelete($pdo, $userId, $module)) : ?>
                                <button class="btn btn-danger btn-sm delete-child-sub-category-button"
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