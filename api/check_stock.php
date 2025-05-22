<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

$itemId = (int)$_GET['id'];
$quantity = (int)$_GET['qty'];

$stock = $pdo->query("SELECT stock FROM menu_items WHERE id = $itemId")->fetchColumn();

echo json_encode([
    'available' => $stock >= $quantity,
    'message' => $stock >= $quantity ? '' : "Only $stock items left in stock"
]);
