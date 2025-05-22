<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireRole('admin');

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$orderId = (int)$_GET['id'];

// Get order details
$order = fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
if (!$order) {
    header('Location: orders.php');
    exit;
}

// Get order items
$orderItems = fetchAll("
    SELECT oi.*, mi.name, mi.image_path 
    FROM order_items oi
    JOIN menu_items mi ON oi.menu_item_id = mi.id
    WHERE oi.order_id = ?
", [$orderId]);

// Status options
$statusOptions = [
    'pending' => 'Pending',
    'confirmed' => 'Confirmed',
    'preparing' => 'Preparing',
    'ready' => 'Ready',
    'out_for_delivery' => 'Out for Delivery',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body>
    <?php include '../includes/admin_nav.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Order #<?= $order['order_code'] ?></h1>
            <a href="orders.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
        </div>
        <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
            <div class="alert alert-success">Order status updated successfully.</div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Order Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <p class="mb-1"><strong>Status:</strong></p>
                            <span class="badge 
                                <?= $order['status'] === 'pending' ? 'bg-warning' : '' ?>
                                <?= $order['status'] === 'confirmed' ? 'bg-info' : '' ?>
                                <?= $order['status'] === 'preparing' ? 'bg-primary' : '' ?>
                                <?= $order['status'] === 'ready' ? 'bg-success' : '' ?>
                                <?= $order['status'] === 'out_for_delivery' ? 'bg-secondary' : '' ?>
                                <?= $order['status'] === 'delivered' ? 'bg-dark' : '' ?>
                                <?= $order['status'] === 'cancelled' ? 'bg-danger' : '' ?>">
                                <?= $statusOptions[$order['status']] ?? ucfirst($order['status']) ?>
                            </span>
                        </div>

                        <div class="mb-3">
                            <p class="mb-1"><strong>Customer:</strong></p>
                            <p><?= htmlspecialchars($order['customer_name']) ?></p>
                        </div>

                        <div class="mb-3">
                            <p class="mb-1"><strong>Phone:</strong></p>
                            <p><?= htmlspecialchars($order['customer_phone']) ?></p>
                        </div>

                        <?php if (!empty($order['customer_email'])): ?>
                            <div class="mb-3">
                                <p class="mb-1"><strong>Email:</strong></p>
                                <p><?= htmlspecialchars($order['customer_email']) ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <p class="mb-1"><strong>Delivery Address:</strong></p>
                            <p><?= nl2br(htmlspecialchars($order['customer_address'])) ?></p>
                        </div>

                        <?php if (!empty($order['special_instructions'])): ?>
                            <div class="mb-3">
                                <p class="mb-1"><strong>Special Instructions:</strong></p>
                                <p><?= nl2br(htmlspecialchars($order['special_instructions'])) ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <p class="mb-1"><strong>Order Date:</strong></p>
                            <p><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Update Status</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="update_order_status.php">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                            <div class="mb-3">
                                <label for="status" class="form-label">New Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <?php foreach ($statusOptions as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= $order['status'] === $value ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" name="update_status" class="btn btn-primary">
                                Update Status
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Order Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orderItems as $item): ?>
                                        <tr>
                                            <td>
                                                <?php if ($item['image_path']): ?>
                                                    <img src="../<?= htmlspecialchars($item['image_path']) ?>"
                                                        alt="<?= htmlspecialchars($item['name']) ?>"
                                                        style="width: 50px; height: 50px; object-fit: cover;" class="me-2">
                                                <?php endif; ?>
                                                <?= htmlspecialchars($item['name']) ?>
                                            </td>
                                            <td>$<?= number_format($item['price'], 2) ?></td>
                                            <td><?= $item['quantity'] ?></td>
                                            <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3">Subtotal:</th>
                                        <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                    </tr>
                                    <?php if ($order['discount_amount'] > 0): ?>
                                        <tr>
                                            <th colspan="3">Discount:</th>
                                            <td class="text-danger">-$<?= number_format($order['discount_amount'], 2) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <th colspan="3">Total:</th>
                                        <td class="fw-bold">$<?= number_format($order['final_amount'], 2) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>