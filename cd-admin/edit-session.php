<?php
session_start();
include('./timeout.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/layouts/header.php';
$sessionId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $pdo->prepare("SELECT * FROM sy_fy WHERE id = :sessionId ");

$stmt->bindParam(':sessionId', $sessionId, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body p-0">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                Edit Session & Financial Year
                <a href="javascript:history.back()" class="btn btn-danger btn-sm">
                    ← Back
                </a>
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

            <form action="<?= $base_url ?>/src/controllers/SyFyController.php" method="post" id="SyFyForm">

                <div class="mb-3">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-control" id="type" name="type">
                        <option value="<?= $data['type'] ?>">
                            <?php echo ($data['type'] == 'sy') ? 'Calender Year' : 'Financial Year' ?>
                        </option>
                        <!-- <option value="fy" <?php echo ($data['type'] == 'fy') ? 'selected' : 'disabled' ?>></option> -->
                    </select>
                </div>

                <?php if ($data['type'] == 'sy') { ?>
                    <div class="mb-3" id="syField">
                        <label for="sessionYear" class="form-label">Choose Session Year</label>
                        <input type="text" class="form-control" value="<?= $data['calender_year'] ?>" disabled>
                        <small class="text-danger mb-3">Current Session Year</small>

                        <select class="form-control" id="sessionYear" name="sessionYear">
                            <option value="">Choose Session Year...</option>
                        </select>
                    </div>
                <?php }
                if ($data['type'] == 'fy') { ?>
                    <div class="mb-3" id="fyField">
                        <label for="financialYear" class="form-label">Choose Financial Year</label>
                        <input type="text" class="form-control" value="<?= $data['financial_year'] ?>" disabled>
                        <small class="text-danger mb-3">Current Financial Year</small>

                        <select class="form-control" id="financialYear" name="financialYear">
                            <option value="">Choose Financial Year...</option>
                        </select>
                    </div>
                <?php } ?>

                <input type="hidden" value="updateSyFy" name="action">
                <input type="hidden" value="<?= $sessionId ?>" name="catid">
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>