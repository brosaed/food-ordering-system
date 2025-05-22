<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/notifications.php';

requireRole('kitchen');

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $orderId = (int)$_POST['order_id'];
    $status = $_POST['status'];

    execute("UPDATE orders SET status = ? WHERE id = ?", [$status, $orderId]);

    // Send notification
    sendOrderStatusNotification($orderId, $status);

    $_SESSION['message'] = 'Order status updated successfully';
    header('Location: dashboard.php');
    exit;
}

// Get orders that need kitchen attention
$orders = fetchAll("
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.status IN ('confirmed', 'preparing', 'ready')
    GROUP BY o.id
    ORDER BY 
        CASE 
            WHEN o.status = 'confirmed' THEN 1
            WHEN o.status = 'preparing' THEN 2
            WHEN o.status = 'ready' THEN 3
        END,
        o.created_at ASC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Kitchen Dashboard</a>
            <div class="navbar-nav">
                <a class="nav-link" href="../logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h1 class="mb-4">Kitchen Orders</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Items</th>
                        <th>Special Instructions</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['order_code'] ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#orderItemsModal"
                                    data-order-id="<?= $order['id'] ?>">
                                    View Items (<?= $order['item_count'] ?>)
                                </button>
                            </td>
                            <td><?= $order['special_instructions'] ? htmlspecialchars($order['special_instructions']) : 'None' ?></td>
                            <td>
                                <span class="badge 
                                    <?= $order['status'] === 'confirmed' ? 'bg-info' : '' ?>
                                    <?= $order['status'] === 'preparing' ? 'bg-primary' : '' ?>
                                    <?= $order['status'] === 'ready' ? 'bg-success' : '' ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td><?= date('H:i', strtotime($order['created_at'])) ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                                    <?php if ($order['status'] === 'confirmed'): ?>
                                        <input type="hidden" name="status" value="preparing">
                                        <button type="submit" name="update_status" class="btn btn-sm btn-primary">
                                            Start Preparing
                                        </button>
                                    <?php elseif ($order['status'] === 'preparing'): ?>
                                        <input type="hidden" name="status" value="ready">
                                        <button type="submit" name="update_status" class="btn btn-sm btn-success">
                                            Mark as Ready
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Order Items Modal -->
    <div class="modal fade" id="orderItemsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderItemsContent">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load order items when modal is shown
        document.getElementById('orderItemsModal').addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');

            fetch(`../api/get_order_items.php?order_id=${orderId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('orderItemsContent').innerHTML = data;
                });
        });
    </script>
</body>

</html>