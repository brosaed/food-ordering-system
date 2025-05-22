<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Debugging: Log received data
error_log("Received cart data: " . print_r(file_get_contents('php://input'), true));

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['cart'])) {
        throw new Exception('Cart data not received');
    }

    // Convert string keys to integers
    $sanitizedCart = [];
    foreach ($input['cart'] as $key => $item) {
        $sanitizedCart[(int)$key] = [
            'name' => htmlspecialchars($item['name']),
            'price' => (float)$item['price'],
            'quantity' => (int)$item['quantity']
        ];
    }

    $_SESSION['cart'] = $sanitizedCart;

    // Debugging: Log session content
    error_log("Session cart after update: " . print_r($_SESSION['cart'], true));

    echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
