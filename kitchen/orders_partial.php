<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireRole('kitchen');

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

foreach ($orders as $order): ?>
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