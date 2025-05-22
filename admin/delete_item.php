<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireRole('admin');

if (!isset($_GET['id'])) {
    header('Location: menu.php');
    exit;
}

$itemId = (int)$_GET['id'];

// Check if the item exists
$item = fetchOne("SELECT * FROM menu_items WHERE id = ?", [$itemId]);
if (!$item) {
    header('Location: menu.php');
    exit;
}

// Delete the item
execute("DELETE FROM menu_items WHERE id = ?", [$itemId]);

// Delete the associated image if it exists
if ($item['image_path']) {
    $imagePath = '../' . $item['image_path'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

$_SESSION['message'] = 'Menu item deleted successfully';
header('Location: menu.php');
exit;
