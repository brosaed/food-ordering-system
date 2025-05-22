<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$order = [];
if (isset($_SESSION['last_order'])) {
    $order = $_SESSION['last_order'];
    unset($_SESSION['last_order']);
}

include 'includes/header.php';
?>

<div class="container my-5">
    <?php if (!empty($order)): ?>
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0">Order Confirmed!</h3>
            </div>
            <div class="card-body">
                <p class="lead">Thank you for your order, <?= htmlspecialchars($order['name']) ?>!</p>
                <div class="row">
                    <div class="col-md-6">
                        <h4>Order Details</h4>
                        <p>Order ID: <?= htmlspecialchars($order['order_code']) ?></p>
                        <p>Total: $<?= number_format($order['total'], 2) ?></p>
                        <p>Estimated Delivery: <?= date('M j, Y H:i', strtotime('+45 minutes')) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h4>Delivery Address</h4>
                        <p><?= nl2br(htmlspecialchars($order['address'])) ?></p>
                    </div>
                </div>
                <a href="order_tracking.php?order_code=<?= urlencode($order['order_code']) ?>" class="btn btn-primary mt-3">
                    Track Your Order
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <h4>Order Not Found</h4>
            <p>Your order details could not be retrieved. Please contact support.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>