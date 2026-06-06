<?php
session_start();
include('./timeout.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="card">
            <div class="card-body p-0">
                <div class="card-header-modern d-flex align-items-center justify-content-between">
                   Create Session & Financial Year
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
                        <option value="sy">Calender Year</option>
                        <option value="fy">Financial Year</option>
                    </select>
                </div>

                <div class="mb-3" id="syField">
                    <label for="sessionYear" class="form-label">Choose Session Year</label>
                    <select class="form-control" id="sessionYear" name="sessionYear">
                        <option value="">Choose Session Year...</option>
                    </select>
                </div>

                <div class="mb-3" id="fyField" style="display:none">
                    <label for="financialYear" class="form-label">Choose Financial Year</label>

                    <!-- <input type="text" class="form-control" id="financialYear" name="financialYear" disabled> -->

                    <select class="form-control" id="financialYear" name="financialYear" disabled>
                        <option value="">Choose Financial Year...</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>