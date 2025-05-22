<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/validation.php';
require_once 'includes/functions.php';

// Start session and check authentication
// session_start();
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

$order = null;
$orderItems = [];
$error = '';

// Check if order code is provided
if (isset($_GET['order_code'])) {
    $orderCode = sanitizeInput($_GET['order_code']);

    try {
        // Get order details without user authentication
        $order = fetchOne("
            SELECT * 
            FROM orders 
            WHERE order_code = ?
        ", [$orderCode]);

        if ($order) {
            // Get order items
            $orderItems = fetchAll("
                SELECT oi.*, mi.name, mi.description, mi.image_path 
                FROM order_items oi
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                WHERE oi.order_id = ?
            ", [$order['id']]);
        } else {
            $error = "Order not found. Please check your order code.";
        }
    } catch (PDOException $e) {
        $error = "Error retrieving order details: " . $e->getMessage();
        error_log($error);
    }
} else {
    $error = "Please provide an order code";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking - <?= htmlspecialchars(SITE_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .timeline {
            position: relative;
            padding-left: 50px;
            list-style: none;
        }

        .timeline:before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #dee2e6;
        }

        .timeline-step {
            position: relative;
            margin-bottom: 2rem;
        }

        .timeline-step-icon {
            position: absolute;
            left: -38px;
            top: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            background: #fff;
            border: 2px solid #dee2e6;
        }

        .timeline-step.active .timeline-step-icon {
            border-color: #0d6efd;
            background-color: #0d6efd;
            color: white;
        }

        .order-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?php if ($error): ?>
                    <div class="alert alert-info text-center">
                        <h4 class="alert-heading"><?= htmlspecialchars($error) ?></h4>
                        <form action="order_tracking.php" method="get" class="mt-3">
                            <div class="input-group" style="width: 300px;">
                                <input type="text" name="order_code" class="form-control"
                                    placeholder="Enter your order code" required>
                                <button class="btn btn-primary" type="submit">
                                    Track Order
                                </button>
                            </div>
                        </form>
                        <p class="mt-3 mb-0">Need help? <a href="contact.php">Contact support</a></p>
                    </div>

                    <!-- <a href="orders.php" class="btn btn-secondary">Back to Orders</a> -->
                <?php elseif ($order): ?>
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0">Order #<?= htmlspecialchars($order['order_code']) ?></h3>
                        </div>
                        <div class="card-body">
                            <!-- Order Status Timeline -->
                            <div class="mb-5">
                                <h4 class="mb-4">Order Progress</h4>
                                <ul class="timeline">
                                    <?php
                                    $statuses = [
                                        'pending' => ['icon' => 'clock', 'text' => 'Order Received'],
                                        'confirmed' => ['icon' => 'check2', 'text' => 'Order Confirmed'],
                                        'preparing' => ['icon' => 'egg-fried', 'text' => 'Preparing Your Order'],
                                        'out_for_delivery' => ['icon' => 'truck', 'text' => 'Out for Delivery'],
                                        'delivered' => ['icon' => 'check-circle', 'text' => 'Delivered']
                                    ];

                                    foreach ($statuses as $status => $data):
                                        $isActive = array_search($order['status'], array_keys($statuses)) >= array_search($status, array_keys($statuses));
                                    ?>
                                        <li class="timeline-step <?= $isActive ? 'active' : '' ?>">
                                            <div class="timeline-step-icon">
                                                <i class="bi bi-<?= $data['icon'] ?>"></i>
                                            </div>
                                            <div class="timeline-content ps-4 pb-4">
                                                <h5 class="mb-1"><?= $data['text'] ?></h5>
                                                <?php if ($status === $order['status']): ?>
                                                    <p class="text-muted mb-0">
                                                        <?=
                                                        // Use created_at if status_updated_at doesn't exist
                                                        isset($order['status_updated_at']) && !empty($order['status_updated_at'])
                                                            ? date('M j, Y g:i A', strtotime($order['status_updated_at']))
                                                            : date('M j, Y g:i A', strtotime($order['created_at']))
                                                        ?>

                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <!-- Order Details -->
                            <div class="row">
                                <div class="col-md-6">
                                    <h4 class="mb-3">Order Summary</h4>
                                    <dl class="row">
                                        <dt class="col-sm-5">Order Date:</dt>
                                        <dd class="col-sm-7"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></dd>

                                        <dt class="col-sm-5">Delivery Address:</dt>
                                        <dd class="col-sm-7"><?= nl2br(htmlspecialchars($order['customer_address'])) ?></dd>

                                        <dt class="col-sm-5">Contact Phone:</dt>
                                        <dd class="col-sm-7"><?= htmlspecialchars($order['customer_phone']) ?></dd>

                                        <?php if ($order['customer_email']): ?>
                                            <dt class="col-sm-5">Contact Email:</dt>
                                            <dd class="col-sm-7"><?= htmlspecialchars($order['customer_email']) ?></dd>
                                        <?php endif; ?>
                                    </dl>
                                </div>

                                <div class="col-md-6">
                                    <h4 class="mb-3">Payment Summary</h4>
                                    <dl class="row">
                                        <dt class="col-sm-5">Subtotal:</dt>
                                        <dd class="col-sm-7">$<?= number_format($order['total_amount'], 2) ?></dd>

                                        <?php if ($order['discount_amount'] > 0): ?>
                                            <dt class="col-sm-5">Discount:</dt>
                                            <dd class="col-sm-7 text-danger">-$<?= number_format($order['discount_amount'], 2) ?></dd>
                                        <?php endif; ?>

                                        <dt class="col-sm-5">Total Paid:</dt>
                                        <dd class="col-sm-7 fw-bold">$<?= number_format($order['final_amount'], 2) ?></dd>
                                    </dl>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <h4 class="mt-5 mb-3">Order Items</h4>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 100px"></th>
                                            <th>Item</th>
                                            <th class="text-end">Price</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orderItems as $item): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($item['image_path']): ?>
                                                        <img src="<?= htmlspecialchars($item['image_path']) ?>"
                                                            alt="<?= htmlspecialchars($item['name']) ?>"
                                                            class="order-item-image">
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <h5 class="mb-1"><?= htmlspecialchars($item['name']) ?></h5>
                                                    <?php if ($item['description']): ?>
                                                        <p class="text-muted mb-0 small"><?= htmlspecialchars($item['description']) ?></p>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">$<?= number_format($item['price'], 2) ?></td>
                                                <td class="text-center"><?= $item['quantity'] ?></td>
                                                <td class="text-end">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>