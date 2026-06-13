<?php

require_once __DIR__ . '/src/helpers/session_helper.php';
requireLogin();

require_once __DIR__ . '/layouts/header.php';
$module = 'news'; // The module is 'news'
$sql = "SELECT cm.* FROM news cm WHERE cm.is_deleted='0' ";

$sql .= " ORDER BY cm.created_at desc";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$news_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">

    <div class="card">
        <div class="card-body p-0">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                Manage News
                <?php if (canCreate($pdo, $userId, $module)) : ?>
                <a href="<?= $base_url ?>/post-news.php" class="btn btn-warning btn-sm">
                    <strong>+ Create</strong>
                </a>
                <?php endif; ?>
            </div>

            <div class="p-2">
                <!-- rest form / content -->
            </div>
            <div class="mt-1">
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
                            
                            <th style="white-space: nowrap;">Image</th>
                            <th>Title</th>
                            <th>News Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $i = 1;
                        foreach ($news_data as $row): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                
                                <td>
                                    <?php if (!empty($row['news_pic1'])): ?>
                                        <img src="<?= $base_url ?>/src/<?= htmlspecialchars($row['news_pic1']) ?>" style="max-width: 100px; max-height: 60px; object-fit: cover; border-radius: 5px;" alt="News Image">
                                    <?php else: ?>
                                        <span class="text-muted">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['news_title']); ?></td>
                                <td style="white-space: nowrap;"><?php echo htmlspecialchars(date("d-M-Y", strtotime($row['news_event_date']))); ?></td>
                                <td style="white-space: nowrap;">
                                    <?php if (canEdit($pdo, $userId, $module)) : ?>
                                    <a href="<?= $base_url ?>/edit-news.php?id=<?php echo htmlspecialchars($row['uniq_id']) ?>"
                                        class="btn btn-primary btn-sm" title="Edit News"><i class="ti ti-edit"></i></a>
                                    <?php endif; ?>

                                    <?php if (canDelete($pdo, $userId, $module)) : ?>
                                    <button class="btn btn-danger btn-sm" title="Delete News"
                                        onclick="deleteNews(<?php echo htmlspecialchars($row['uniq_id']); ?>, 'dn', '')">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if (canEdit($pdo, $userId, $module)) : ?>
                                    <button class="btn btn-<?= $row['is_hide'] == 'Y' ? 'warning' : 'danger' ?> btn-sm"
                                        title="<?= $row['is_hide'] == 'Y' ? 'Unhide' : 'Hide' ?> News"
                                        onclick="deleteNews(<?php echo htmlspecialchars($row['uniq_id']); ?>, 'hn', '<?= $row['is_hide'] == 'Y' ? 'Unhide' : 'Hide' ?>' )">
                                        <i class="ti ti-<?= $row['is_hide'] == 'Y' ? 'link' : 'unlink' ?>"></i>
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

    <script>
        function deleteNews(photoId, type1, hide = '') {
            let actionType = type1 === 'dn' ? 'deleteNews' : 'hideNews';
            if (!confirm("Are you sure you want to " + (type1 === 'dn' ? 'Delete' : hide) + " this News?")) {
                return;
            }
            $("#loader").show();
            $.ajax({
                url: '<?= $base_url ?>/src/controllers/news/insertNews.php',
                type: 'POST',
                data: {
                    ed: photoId,
                    action: actionType
                },
                success: function(response) {
                    window.location.reload();
                },
                error: function(xhr, status, error) {
                    window.location.reload();
                    $("#loader").hide();
                    console.error('Error deleting photo:', error);
                }
            });
        }
    </script>

    <?php require_once __DIR__ . '/layouts/footer.php'; ?>
