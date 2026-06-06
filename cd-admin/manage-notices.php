<?php

/**
 * Manage Notices Page
 * Protected page with session check
 */
require_once __DIR__ . '/src/helpers/session_helper.php';
requireLogin(); // This will redirect if not logged in or session expired

require_once __DIR__ . '/layouts/header.php';
$module = 'notices';

$params = [];
$sql = "SELECT *, b.sub_category_name as category_name , csc.child_sub_category_name 
        FROM notices a 
        JOIN sub_category b ON a.notice_subcategory = b.id 
        LEFT JOIN child_sub_category as csc ON csc.id = a.notice_childsubcategory 
        WHERE a.is_deleted = '0' 
        ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$notices_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">

    <div class="card">
        <div class="card-body p-0">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                Manage Notices
                <?php if (canCreate($pdo, $userId, $module)) : ?>
                <a href="<?= $base_url ?>/create-notice.php" class="btn btn-warning btn-sm">
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
                        <th>Ref. No.</th>
                        <th>Category</th>
                        <th style="white-space: nowrap;">Sub Category</th>
                        <th style="white-space: nowrap;">Notice Title</th>
                        <th>Dated</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $i = 1;
                    foreach ($notices_data as $row): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['notice_ref_no']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['child_sub_category_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['notice_title']); ?></td>
                            <td style="white-space: nowrap;"><?= htmlspecialchars($row['notice_dated']); ?></td>
                            <td style="white-space: nowrap;">
                                <?php if (canEdit($pdo, $userId, $module)) : ?>    
                                <a href="<?= $base_url ?>/edit-notices.php?id=<?php echo htmlspecialchars($row['uniq_id']) ?>"
                                        class="btn btn-primary btn-sm"><i class="ti ti-edit"></i></a>&nbsp;&nbsp;
                                <?php endif; ?>
                                <?php if (canDelete($pdo, $userId, $module)) : ?>
                                <form action="<?= $base_url ?>/src/controllers/notice/NoticeController.php" method="post" style="display: inline-block;">
                                    <input type="hidden" name="ed" value="<?php echo htmlspecialchars($row['uniq_id']); ?>">
                                    <button class="btn btn-danger btn-sm delete-category-button" type="submit">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>