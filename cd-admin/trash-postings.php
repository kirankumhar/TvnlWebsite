<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/layouts/header.php';

$database = new Database();
$pdo = $database->getConnection();

$sql = "SELECT * FROM postings where is_deleted='1'";

$postings = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body p-0">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                Trash Posting
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

            <table id="postingTable" class="table table-bordered table-striped ">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <!-- <th>Type</th>
                        <th>Category</th>
                        <th>Sub-Category</th>
                        <th>Document No.</th>
                        <th>Dated</th> -->
                        <th>Title</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $i = 1;
                    foreach ($postings as $row) : ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <!-- <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['type']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><?php echo htmlspecialchars($row['sub_category']); ?></td>
                            <td><?php echo htmlspecialchars($row['document_no']); ?></td>
                            <td><?php echo htmlspecialchars($row['dated']); ?></td> -->
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <!-- <td><?php echo htmlspecialchars($row['ip_address']); ?></td> -->
                            <td><a href="<?= $base_url ?>/view-trash-postings.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-primary btn-sm" title="View and Restore Post"><i class="ti ti-eye"></i></a>&nbsp;&nbsp;
                                <!-- <button class="btn btn-danger btn-sm delete-post-button" data-id="<?php echo htmlspecialchars($row['id']); ?>" title="Delete Post Permanently">
                                    <i class="ti ti-trash"></i>
                                </button> -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>