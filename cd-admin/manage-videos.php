<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once __DIR__ . '/layouts/header.php';
$module = 'mediavideo';

$params = [];
$sql = "SELECT cm.*, p.video_link, dm.eng_name FROM albums cm LEFT JOIN videos p on p.id = cm.cover_video_id JOIN domains as dm ON dm.id = cm.domain_id WHERE cm.type='Videos' AND cm.is_deleted='0'";

if ($domainId > 0) {
    $sql .= " AND cm.domain_id = ?";
    $params[] = $domainId;
}
$sql .= " ORDER BY cm.created_at desc";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid">

    <div class="card">
        <div class="card-body p-0">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                Manage Videos
                <?php if (canCreate($pdo, $userId, $module)) : ?>
                <a href="<?= $base_url ?>/post-album.php" class="btn btn-warning btn-sm">
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
                        <th>Domain</th>
                        <th>Album Title</th>
                        <th>Event Date</th>
                        <th>Edit Album</th>
                        <th>Actions</th>
                        <th>Created</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $i = 1;
                    foreach ($categories as $row): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['eng_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['name_en']); ?></td>
                            <td style="white-space: nowrap;"><?php echo htmlspecialchars(date("d-M-Y", strtotime($row['event_date']))); ?></td>
                            </td>
                            <td>
                                <a href="<?= $base_url ?>/edit-albums-details.php?album_id=<?php echo htmlspecialchars($row['uniq_id']) ?>"
                                    title="Edit Video Album Details" class="btn btn-primary"><i
                                        class="ti ti-edit"></i></a>&nbsp;&nbsp;

                            </td>
                            <td style="white-space: nowrap;">
                                <?php if (canEdit($pdo, $userId, $module)) : ?>
                                <a href="<?= $base_url ?>/edit-video-album.php?album_id=<?php echo htmlspecialchars($row['uniq_id']) ?>"
                                    title="Add & Manage Videos" class="btn btn-primary"><i
                                        class="ti ti-photo-edit"></i></a>&nbsp;&nbsp;
                                <?php endif; ?>
                                <?php if (canDelete($pdo, $userId, $module)) : ?>
                                <button class="btn btn-danger delete-photo-albums-button" title="Delete Video Album"
                                    data-id="<?php echo htmlspecialchars($row['uniq_id']); ?>">
                                    <i class="ti ti-trash"></i>
                                </button>
                                <?php endif; ?>
                                <!-- <button class="btn btn-warning delete-category-button"
                                    data-id="<?php echo htmlspecialchars($row['uniq_id']); ?>" title="Hide Videos Album">
                                    <i class="ti ti-eye"></i>
                                </button> -->
                            </td>
                            <td><?= date('d-M-Y h:m:s:i', strtotime($row['created_at'])) ?></td>
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