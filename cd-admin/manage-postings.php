<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once __DIR__ . '/layouts/header.php';

$sql1 = "SELECT *  FROM category_master";

$categories = $pdo->query($sql1)->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT p.*, cm.category_name as cat_name, sc.sub_category_name  FROM postings p INNER JOIN category_master cm ON p.category = cm.id LEFT JOIN  sub_category sc on p.sub_category = sc.id where p.is_deleted='0' order by p.created_on asc";

$postings = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body p-0">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                Manage Posting
            </div>

            <div class="p-2">
                <!-- rest form / content -->
            </div>

            <div class="row">
                <div class="col-md-3 my-3">
                    <label for="post_category">Category</label>
                    <select name="post_category" id="post_category" class="form-control">
                        <option value="">Choose Category...</option>
                        <?php foreach ($categories as $category) {
                            echo '<option value="' . $category['id'] . '">' . $category['category_name'] . '</option>';
                        } ?>
                    </select>
                </div>
                <div class="col-md-3 my-3">
                    <label for="cat_type">Type</label>
                    <select name="cat_type" id="cat_type" class="form-control">
                        <option value="">Choose Type...</option>
                    </select>
                </div>
                <div class="col-md-3 my-3">
                    <label for="">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                    <input type="button" value="Search" id="search-post" class="form-control bg-success">
                </div>
            </div>

            <table id="postingTable" class="table table-bordered table-striped ">
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <th>Category</th>
                        <th>Sub-Category</th>
                        <th>Title</th>
                        <th>Posted Date</th>
                        <th>Document</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody id="postingsTableBody">
                    <?php $i = 1;
                    foreach ($postings as $row): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['cat_name']); ?></td>
                            <td><?php echo empty(htmlspecialchars($row['sub_category'])) ? 'NA' : htmlspecialchars($row['sub_category_name']); ?>
                            </td>
                            <td><a
                                    href="<?= $base_url ?>/view-posting.php?id=<?= htmlspecialchars($row['id']) ?>"><?php echo htmlspecialchars($row['title']); ?></a>
                            </td>
                            <td><?php echo htmlspecialchars($row['created_on']); ?></td>
                            <td><button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#viewDocModal"
                                    data-pdf="<?php echo $base_url . '/' . htmlspecialchars($row['attachment']); ?>">View</button>
                            </td>
                            <td>Active</td>
                            <td><a href="<?= $base_url ?>/view-posting.php?id=<?= htmlspecialchars($row['id']) ?>"
                                    class="btn btn-success btn-sm"><i class="ti ti-eye"></i></a>&nbsp;&nbsp;<a
                                    href="<?= $base_url ?>/edit-postings.php?id=<?= htmlspecialchars($row['id']) ?>"
                                    class="btn btn-primary btn-sm"><i class="ti ti-edit"></i></a>&nbsp;&nbsp;
                                <button class="btn btn-danger btn-sm delete-post-button"
                                    data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <div class="modal fade" id="viewDocModal" tabindex="-1" role="dialog"
                        aria-labelledby="viewDocModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="viewDocModalLabel">View Document</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <embed src="" type="" width="450px" height="550px">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </tbody>
            </table>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>