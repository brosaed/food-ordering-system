<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireRole('admin');

if (!isset($_GET['id'])) {
    header('Location: categories.php');
    exit;
}

$categoryId = (int)$_GET['id'];

// Check if the category exists
$category = fetchOne("SELECT * FROM categories WHERE id = ?", [$categoryId]);
if (!$category) {
    header('Location: categories.php');
    exit;
}

// Check if category has menu items
$menuItemsCount = fetchOne("SELECT COUNT(*) as count FROM menu_items WHERE category_id = ?", [$categoryId])['count'];

if ($menuItemsCount > 0) {
    $_SESSION['error'] = 'Cannot delete category that has menu items assigned to it';
    header('Location: categories.php');
    exit;
}

// Delete the category
execute("DELETE FROM categories WHERE id = ?", [$categoryId]);

$_SESSION['message'] = 'Category deleted successfully';
header('Location: categories.php');
exit;
