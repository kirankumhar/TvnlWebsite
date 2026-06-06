<?php
// manage-departments.php
// Path: C:\xampp\htdocs\tvnl-website\cd-admin\manage-departments.php

session_start();
include('./timeout.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/layouts/header.php';

ob_start();
include __DIR__ . '/src/controllers/tender/DepartmentController.php';
$data = $controller->showDepartments();
$departments = $data['departments'];
$current_page = $data['current_page'];
$total_pages = $data['total_pages'];
$total_records = $data['total_records'];
$search = $data['search'];
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Manage Departments</h4>
                <a href="create-department.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Department
                </a>
            </div>

            <?php if (isset($_SESSION['message'])) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <form method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Search by department name (English/Hindi) or code..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if ($search): ?>
                        <a href="manage-departments.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Department Name (English)</th>
                            <th>Department Name (Hindi)</th>
                            <th>Code</th>
                            <th>Display Order</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($departments)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No departments found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($departments as $dept): ?>
                                <tr>
                                    <td><?= $dept['department_id'] ?></td>
                                    <td><?= htmlspecialchars($dept['department_name']) ?></td>
                                    <td><?= htmlspecialchars($dept['department_name_hindi'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($dept['department_code'] ?? '—') ?></td>
                                    <td>
                                        <input type="number" 
                                               class="form-control form-control-sm order-input" 
                                               value="<?= $dept['display_order'] ?? 0 ?>" 
                                               style="width: 80px;"
                                               data-id="<?= $dept['department_id'] ?>">
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $dept['status'] == 'Active' ? 'success' : 'secondary' ?>">
                                            <?= $dept['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d-m-Y', strtotime($dept['created_at'])) ?></td>
                                    <td>
                                        <a href="edit-department.php?id=<?= $dept['department_id'] ?>" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($dept['status'] == 'Active'): ?>
                                            <button class="btn btn-sm btn-danger delete-dept" 
                                                    data-id="<?= $dept['department_id'] ?>"
                                                    data-name="<?= htmlspecialchars($dept['department_name']) ?>"
                                                    title="Deactivate">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-success activate-dept" 
                                                    data-id="<?= $dept['department_id'] ?>"
                                                    data-name="<?= htmlspecialchars($dept['department_name']) ?>"
                                                    title="Activate">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $current_page - 1 ?>&search=<?= urlencode($search) ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $current_page + 1 ?>&search=<?= urlencode($search) ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            
            <div class="mt-3">
                <strong>Total Records:</strong> <?= $total_records ?>
            </div>
        </div>
    </div>
</div>

<div class="fixed-bottom mb-3 me-3 text-end">
    <button id="updateOrderBtn" class="btn btn-primary btn-lg rounded-circle" style="display: none; width: 50px; height: 50px; border-radius: 50%;" title="Save Order">
        <i class="bi bi-save"></i>
    </button>
</div>

<script>
let ordersChanged = false;
document.querySelectorAll('.order-input').forEach(input => {
    input.addEventListener('change', function() {
        ordersChanged = true;
        document.getElementById('updateOrderBtn').style.display = 'block';
    });
});

document.getElementById('updateOrderBtn').addEventListener('click', function() {
    const orders = {};
    document.querySelectorAll('.order-input').forEach(input => {
        const id = input.dataset.id;
        const value = input.value;
        orders[id] = value;
    });
    
    fetch('src/controllers/tender/DepartmentController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=update_order&orders=' + JSON.stringify(orders)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to update order');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating order');
    });
});

document.querySelectorAll('.delete-dept').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.dataset.id;
        const name = this.dataset.name;
        
        if (confirm(`Are you sure you want to deactivate "${name}"?`)) {
            fetch('src/controllers/tender/DepartmentController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=soft_delete&dept_id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deactivating department');
            });
        }
    });
});

document.querySelectorAll('.activate-dept').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.dataset.id;
        const name = this.dataset.name;
        
        if (confirm(`Activate "${name}"?`)) {
            fetch('src/controllers/tender/DepartmentController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=activate&dept_id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error activating department');
            });
        }
    });
});
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>