<?php
// Ensure no whitespace before this line
ob_start();

// Error handling for AJAX-safe logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireRole('admin');

// $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
// With (case-insensitive check):
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') === 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        while (ob_get_level()) ob_end_clean();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }

        $orderId = (int)$_POST['order_id'];
        $status = $_POST['status'];

        execute(
            "UPDATE orders 
            SET status = ?, 
                status_updated_at = NOW()  
            WHERE id = ?",
            [$status, $orderId]
        );

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'newStatus' => $status,
                'statusText' => ucfirst(str_replace('_', ' ', $status)),
                'statusClass' => getStatusBadgeClass($status),
                'updatedAt' => date('M d, Y H:i')
            ]);
            exit;
        }

        $_SESSION['message'] = 'Order status updated successfully';
        header('Location: orders.php');
        exit;
    } catch (Exception $e) {
        while (ob_get_level()) ob_end_clean();
        if ($isAjax) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
            exit;
        }
        $_SESSION['error'] = $e->getMessage();
        header('Location: orders.php');
        exit;
    }
}

// Now render page for GET requests
$statusFilter = $_GET['status'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';

$query = "SELECT o.*, COUNT(oi.id) as item_count 
          FROM orders o
          LEFT JOIN order_items oi ON o.id = oi.order_id";

$params = [];
$conditions = [];

if ($statusFilter !== 'all') {
    $conditions[] = "o.status = ?";
    $params[] = $statusFilter;
}

if (!empty($searchQuery)) {
    $conditions[] = "(o.order_code LIKE ? OR o.customer_name LIKE ? OR o.customer_phone LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

$query .= " GROUP BY o.id ORDER BY o.created_at DESC";
$orders = fetchAll($query, $params);

$statusOptions = [
    'all' => 'All Orders',
    'pending' => 'Pending',
    'confirmed' => 'Confirmed',
    'preparing' => 'Preparing',
    'ready' => 'Ready',
    'out_for_delivery' => 'Out for Delivery',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled'
];

ob_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body>
    <?php include '../includes/admin_nav.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id=" sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="orders.php">
                                <i class="bi bi-list-check me-2"></i>Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="users.php">
                                <i class="bi bi-list-check me-2"></i>Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="categories.php">
                                <i class="bi bi-list-check me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="menu.php">
                                <i class="bi bi-menu-button me-2"></i>Menu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="reports.php">
                                <i class="bi bi-graph-up me-2"></i>Reports
                            </a>
                        </li>
                        <!-- <li class="nav-item dropdown">
                            <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell"></i>
                                <?php if ($unreadCount > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?= $unreadCount ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="min-width: 300px;">
                                <li class="dropdown-header">Notifications</li>
                                <?php foreach ($notifications as $note): ?>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <?= htmlspecialchars($note['message']) ?><br>
                                            <small class="text-muted"><?= date('H:i', strtotime($note['created_at'])) ?></small>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (empty($notifications)): ?>
                                    <li><span class="dropdown-item text-muted">No new notifications</span></li>
                                <?php endif; ?>
                            </ul>
                        </li> -->

                    </ul>
                </div>
            </nav>
            <!-- End Sidebar -->

        </div>
    </div>
    <main class="col-md-9 ms-sm-auto col-lg-10 px-4">

        <div class="container py-5">
            <h1 class="mb-4">Manage Orders</h1>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">Order List</h5>
                        </div>
                        <div class="col-md-6">
                            <form method="get" class="row g-2">
                                <div class="col-md-5">
                                    <select name="status" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ($statusOptions as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= $statusFilter === $value ? 'selected' : '' ?>>
                                                <?= $label ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" name="search" class="form-control" placeholder="Search..."
                                        value="<?= htmlspecialchars($searchQuery) ?>">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?= $order['order_code'] ?></td>
                                        <td>
                                            <?= htmlspecialchars($order['customer_name']) ?>
                                            <div class="small text-muted"><?= $order['customer_phone'] ?></div>
                                        </td>
                                        <td><?= $order['item_count'] ?></td>
                                        <td>$<?= number_format($order['final_amount'], 2) ?></td>
                                        <td>
                                            <span class="badge badge status-badge
                                            <?= $order['status'] === 'pending' ? 'bg-warning' : '' ?>
                                            <?= $order['status'] === 'confirmed' ? 'bg-info' : '' ?>
                                            <?= $order['status'] === 'preparing' ? 'bg-primary' : '' ?>
                                            <?= $order['status'] === 'ready' ? 'bg-success' : '' ?>
                                            <?= $order['status'] === 'out_for_delivery' ? 'bg-secondary' : '' ?>
                                            <?= $order['status'] === 'delivered' ? 'bg-dark' : '' ?>
                                            <?= $order['status'] === 'cancelled' ? 'bg-danger' : '' ?>">
                                                <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                            </span>
                                        </td>
                                        <td class="status-time"><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <!-- <button type="button" class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="modal" data-bs-target="#statusModal"
                                            data-order-id="<?= $order['id'] ?>"
                                            data-current-status="<?= $order['status'] ?>">
                                            <i class="bi bi-pencil"></i> Update
                                        </button> -->
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Update Modal -->
        <!-- <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <input type="hidden" name="order_id" id="modalOrderId">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Order Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">New Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="preparing">Preparing</option>
                                <option value="ready">Ready</option>
                                <option value="out_for_delivery">Out for Delivery</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div> -->

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- <script>
        document.getElementById('statusModal').addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');
            const currentStatus = button.getAttribute('data-current-status');
            const modal = this;
            modal.querySelector('#modalOrderId').value = orderId;
            modal.querySelector('#status').value = currentStatus;
            modal._row = button.closest('tr');
        });

        document.querySelector('#statusModal form').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const row = form.closest('.modal')._row;

            fetch('', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            throw new Error(`Invalid JSON: ${text.substring(0, 100)}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) throw new Error(data.error);

                    const badge = row.querySelector('.status-badge');
                    badge.className = `badge ${data.statusClass} status-badge`;
                    badge.textContent = data.statusText;

                    const timeCell = row.querySelector('.status-time');
                    if (timeCell) timeCell.textContent = data.updatedAt;

                    bootstrap.Modal.getInstance(form.closest('.modal')).hide();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(`Update failed: ${error.message}`);
                });
        });
    </script> -->
</body>

</html>