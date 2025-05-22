<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

/// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Secure admin access
if (!isset($_SESSION['admin_logged_in'])) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token check
    if (!validateCsrfToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $orderId = (int)$_POST['order_id'];
    $newStatus = sanitizeInput($_POST['status']);

    // Validate status
    $validStatuses = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
    if (!in_array($newStatus, $validStatuses)) {
        die("Invalid status");
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status = ?, 
                status_updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$newStatus, $orderId]);

        // Return JSON response for AJAX or redirect
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header("Location: order_details.php?id=$orderId&updated=1");
        }
        exit;
    } catch (PDOException $e) {
        error_log("Status update error: " . $e->getMessage());
        die("Error updating status");
    }
}
