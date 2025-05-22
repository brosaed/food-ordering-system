<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

function sendOrderStatusNotification($orderId, $newStatus)
{
    $order = fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
    if (!$order) return false;

    $statusMessages = [
        'pending' => 'Your order has been received and is being processed',
        'confirmed' => 'Your order has been confirmed',
        'preparing' => 'Your food is being prepared',
        'ready' => 'Your order is ready for delivery',
        'out_for_delivery' => 'Your order is out for delivery',
        'delivered' => 'Your order has been delivered',
        'cancelled' => 'Your order has been cancelled'
    ];

    // Notify Admin and Delivery Team when order is ready
    if ($newStatus === 'ready') {
        $adminMessage = "Order #{$order['order_code']} is ready for delivery.";

        // Insert notification for Admin
        execute("INSERT INTO notifications (user_role, message, created_at) VALUES (?, ?, NOW())", [
            'admin',
            $adminMessage
        ]);
        // Insert notification for Delivery Team
        execute("INSERT INTO notifications (user_role, message, created_at) VALUES (?, ?, NOW())", [
            'delivery',
            $adminMessage
        ]);

        return true;
    }


    // function sendOrderConfirmationEmail($orderId)
    // {
    //     $order = fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
    //     if (!$order || empty($order['customer_email'])) return false;

    //     $orderItems = fetchAll("
    //     SELECT oi.*, mi.name 
    //     FROM order_items oi
    //     JOIN menu_items mi ON oi.menu_item_id = mi.id
    //     WHERE oi.order_id = ?
    // ", [$orderId]);

    //     $itemsHtml = '';
    //     foreach ($orderItems as $item) {
    //         $itemsHtml .= "
    //         <tr>
    //             <td>{$item['name']}</td>
    //             <td>\${$item['price']}</td>
    //             <td>{$item['quantity']}</td>
    //             <td>\$" . number_format($item['price'] * $item['quantity'], 2) . "</td>
    //         </tr>
    //     ";
    //     }

    //     $subject = "Order Confirmation #{$order['order_code']}";
    //     $message = "
    //     <h2>Thank you for your order!</h2>
    //     <p>Your order #{$order['order_code']} has been received and is being processed.</p>

    //     <h3>Order Summary</h3>
    //     <table border='1' cellpadding='10' cellspacing='0' style='width:100%; border-collapse: collapse;'>
    //         <thead>
    //             <tr>
    //                 <th>Item</th>
    //                 <th>Price</th>
    //                 <th>Qty</th>
    //                 <th>Total</th>
    //             </tr>
    //         </thead>
    //         <tbody>
    //             $itemsHtml
    //         </tbody>
    //         <tfoot>
    //             <tr>
    //                 <th colspan='3'>Subtotal:</th>
    //                 <td>\${$order['total_amount']}</td>
    //             </tr>
    // ";

    //     if ($order['discount_amount'] > 0) {
    //         $message .= "
    //             <tr>
    //                 <th colspan='3'>Discount:</th>
    //                 <td>\${$order['discount_amount']}</td>
    //             </tr>
    //     ";
    //     }

    //     $message .= "
    //             <tr>
    //                 <th colspan='3'>Total:</th>
    //                 <td>\${$order['final_amount']}</td>
    //             </tr>
    //         </tfoot>
    //     </table>

    //     <p>You can track your order status at any time using this link: 
    //     <a href=\"" . BASE_URL . "/order_tracking.php?order_code={$order['order_code']}\">Track Order</a></p>

    //     <p>Thank you for choosing our service!</p>
    // ";

    //     return sendEmailNotification($order['customer_email'], $subject, $message);
    // }
}
