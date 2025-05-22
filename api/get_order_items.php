<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: text/html');

if (!isset($_GET['order_id'])) {
    die('Order ID is required');
}

$orderId = (int)$_GET['order_id'];

// Get order items
$orderItems = fetchAll("
    SELECT oi.*, mi.name, mi.image_path, mi.description 
    FROM order_items oi
    JOIN menu_items mi ON oi.menu_item_id = mi.id
    WHERE oi.order_id = ?
", [$orderId]);

if (empty($orderItems)) {
    echo '<div class="alert alert-info">No items found for this order</div>';
    exit;
}
?>

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
                    <div class="d-flex align-items-center">
                        <?php if ($item['image_path']): ?>
                            <img src="../<?= htmlspecialchars($item['image_path']) ?>"
                                alt="<?= htmlspecialchars($item['name']) ?>"
                                style="width: 50px; height: 50px; object-fit: cover;" class="me-3">
                        <?php endif; ?>
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                            <p class="small text-muted mb-0"><?= htmlspecialchars($item['description']) ?></p>
                        </div>
                    </div>
                </td>
                <td>$<?= number_format($item['price'], 2) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>