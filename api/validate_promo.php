<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/validation.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['code']) || !isset($_GET['amount'])) {
        throw new Exception('Missing parameters');
    }

    $result = validatePromoCode($_GET['code'], (float)$_GET['amount']);

    if ($result['valid']) {
        echo json_encode([
            'valid' => true,
            'message' => $result['message'],
            'discount_amount' => $result['discount_amount']
        ]);
    } else {
        echo json_encode([
            'valid' => false,
            'message' => $result['message']
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'valid' => false,
        'message' => 'Error processing promo code'
    ]);
}
