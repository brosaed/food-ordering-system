<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/notifications.php';

requireRole('delivery');

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

// Get orders ready for delivery or out for delivery
$orders = fetchAll("
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.status IN ('ready', 'out_for_delivery')
    GROUP BY o.id
    ORDER BY 
        CASE 
            WHEN o.status = 'ready' THEN 1
            WHEN o.status = 'out_for_delivery' THEN 2
        END,
        o.created_at ASC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Delivery Dashboard</a>
            <div class="navbar-nav">
                <a class="nav-link" href="../logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h1 class="mb-4">Delivery Orders</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['order_code'] ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td><?= htmlspecialchars($order['customer_address']) ?></td>
                            <td><?= htmlspecialchars($order['customer_phone']) ?></td>
                            <td>
                                <span class="badge 
                                    <?= $order['status'] === 'ready' ? 'bg-success' : '' ?>
                                    <?= $order['status'] === 'out_for_delivery' ? 'bg-warning' : '' ?>">
                                    <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                </span>
                            </td>
                            <td><?= date('H:i', strtotime($order['created_at'])) ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                                    <?php if ($order['status'] === 'ready'): ?>
                                        <input type="hidden" name="status" value="out_for_delivery">
                                        <button type="submit" name="update_status" class="btn btn-sm btn-warning">
                                            Out for Delivery
                                        </button>
                                    <?php elseif ($order['status'] === 'out_for_delivery'): ?>
                                        <input type="hidden" name="status" value="delivered">
                                        <button type="submit" name="update_status" class="btn btn-sm btn-success">
                                            Mark as Delivered
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>