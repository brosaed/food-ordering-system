<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireRole('admin');

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$userId = (int)$_GET['id'];

// Prevent deleting the current user
if ($userId === $_SESSION['user']['id']) {
    $_SESSION['error'] = 'You cannot delete your own account';
    header('Location: users.php');
    exit;
}

// Get the user to delete
$user = fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
if (!$user) {
    header('Location: users.php');
    exit;
}

// Delete the user
execute("DELETE FROM users WHERE id = ?", [$userId]);

$_SESSION['message'] = 'User deleted successfully';
header('Location: users.php');
exit;
