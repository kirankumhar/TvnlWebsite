<?php
// manage-sliders.php - List all sliders

session_start();

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/src/database/Database.php';
require_once __DIR__ . '/src/models/SliderModel.php';

$database = new Database();
$pdo = $database->getConnection();
$sliderModel = new SliderModel($pdo);

// Handle delete request
if (isset($_POST['delete_slider']) && isset($_POST['slider_id'])) {
    $sliderId = $_POST['slider_id'];
    try {
        // Get image path to delete file
        $stmt = $pdo->prepare("SELECT image_path FROM sliders WHERE uniq_id = :id");
        $stmt->execute([':id' => $sliderId]);
        $slider = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($slider && !empty($slider['image_path'])) {
            $filePath = __DIR__ . '/src/' . $slider['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM sliders WHERE uniq_id = :id");
        $stmt->execute([':id' => $sliderId]);
        $_SESSION['message'] = "Slider deleted successfully.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to delete slider.";
    }
    header("Location: manage-sliders.php");
    exit;
}

// Handle display order update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_order') {
    header('Content-Type: application/json');
    $orders = json_decode($_POST['orders'] ?? '{}', true);
    if (empty($orders) || !is_array($orders)) {
        echo json_encode(['success' => false, 'message' => 'No order data provided']);
        exit;
    }

    $normalizedOrders = [];
    foreach ($orders as $id => $displayOrder) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        $displayOrder = filter_var($displayOrder, FILTER_VALIDATE_INT);
        if ($id !== false && $displayOrder !== false) {
            $normalizedOrders[$id] = $displayOrder;
        }
    }

    $result = $sliderModel->updateDisplayOrder($normalizedOrders);
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Display order updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update display order']);
    }
    exit;
}

// Get all sliders
$sliders = $sliderModel->getAllSliders();
?>

<style>
    .slider-image {
        width: 100px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
    }
    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }
    .status-A {
        background: #28a745;
        color: white;
    }
    .status-I {
        background: #dc3545;
        color: white;
    }
    .slider-badge {
        background: #ffc107;
        color: #000;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
    }
    .action-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-images"></i> Manage Sliders
                </h4>
                <a href="create-slider.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Slider
                </a>
            </div>

            <?php if (isset($_SESSION['message'])) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="50">SL</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Display Order</th>
                            <th width="80">Slider</th>
                            <th width="80">Status</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sliders)): ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1"></i><br>
                                    No sliders found.
                                    <div class="mt-2">
                                        <a href="create-slider.php" class="btn btn-sm btn-primary">Create First Slider</a>
                                    </div>
                                  </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                             $i= 0
                            ?>
                            <?php foreach ($sliders as $slider): ?>
                                <?php $i++ ;?>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td>
                                        <?php if ($slider['image_path']): ?>
                                            <img src="<?= htmlspecialchars($slider['image_path']) ?>" 
                                                 class="slider-image" 
                                                 alt="<?= htmlspecialchars($slider['title']) ?>">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($slider['title']) ?></strong>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               class="form-control form-control-sm order-input" 
                                               value="<?= htmlspecialchars($slider['display_order']) ?>" 
                                               style="width: 80px;" 
                                               data-id="<?= $slider['id'] ?>">
                                    </td>
                                    
                                    <td class="text-center">
                                        <?php if ($slider['make_slider'] == 1): ?>
                                            <span class="slider-badge"><i class="bi bi-sliders2"></i> Yes</span>
                                        <?php else: ?>
                                            <span class="text-muted">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="status-badge status-<?= $slider['status'] ?>">
                                            <?= $slider['status'] == 'A' ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit-slider.php?id=<?= $slider['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this slider?')">
                                                <input type="hidden" name="slider_id" value="<?= $slider['id'] ?>">
                                                <button type="submit" name="delete_slider" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="fixed-bottom mb-3 me-3 text-end">
    <button id="updateOrderBtn" class="btn btn-primary btn-lg rounded-circle" style="display: none; width: 50px; height: 50px;" title="Save Order">
        <i class="bi bi-save"></i>
    </button>
</div>

<script>
let ordersChanged = false;
document.querySelectorAll('.order-input').forEach(input => {
    input.addEventListener('change', function() {
        ordersChanged = true;
        document.getElementById('updateOrderBtn').style.display = 'inline-block';
    });
});

document.getElementById('updateOrderBtn').addEventListener('click', function() {
    const orders = {};
    document.querySelectorAll('.order-input').forEach(input => {
        const id = input.dataset.id;
        const value = input.value;
        orders[id] = value;
    });

    fetch('manage-sliders.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=update_order&orders=' + encodeURIComponent(JSON.stringify(orders))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Failed to update display order');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating order');
    });
});

setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(function() {
            alert.remove();
        }, 500);
    });
}, 3000);
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>