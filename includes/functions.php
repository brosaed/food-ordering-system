<?php
require_once 'config.php';

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['user']);
}

/**
 * Check if user has specific role
 */
function hasRole($role)
{
    return isLoggedIn() && $_SESSION['user']['role'] === $role;
}

/**
 * Generate CSRF token
 */
function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect with flash message
 */
function redirectWithMessage($url, $type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
    header("Location: $url");
    exit;
}



/**
 * Display flash message
 */
function displayFlashMessage()
{
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        echo "<div class='alert alert-$type'>$message</div>";
        unset($_SESSION['flash']);
    }
}

/**
 * Check if user has specific role
 */
// function hasRole($role)
// {
//     return isset($_SESSION['user']) && $_SESSION['user']['role'] === $role;
// }

/**
 * Get current user ID
 */
function getUserId()
{
    return $_SESSION['user']['id'] ?? null;
}


function getStatusBadgeClass($status)
{
    $statusClasses = [
        'pending' => 'bg-warning',
        'confirmed' => 'bg-info',
        'preparing' => 'bg-primary',
        'ready' => 'bg-success',
        'out_for_delivery' => 'bg-secondary',
        'delivered' => 'bg-dark',
        'cancelled' => 'bg-danger'
    ];
    return $statusClasses[$status] ?? 'bg-secondary';
}


// Prevent accidental output
if (headers_sent()) {
    error_log('Headers already sent in ' . __FILE__ . ' line ' . __LINE__);
}


function requireRole($role)
{
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            exit;
        }

        // Fallback: HTML redirect
        header('Location: login.php');
        exit;
    }
}

function getCurrentUserRole()
{
    return $_SESSION['user']['role'] ?? 'guest';
}

function getUnreadNotificationsCount(): int
{
    static $count = null;

    if ($count === null) {
        try {
            $stmt = execute(
                "SELECT COUNT(*) AS count 
                FROM notifications 
                WHERE is_read = 0 AND admin_id = ?",
                [$_SESSION['user']['id']]
            );
            $count = (int)$stmt->fetch()['count'];
        } catch (Exception $e) {
            error_log("Notification count error: " . $e->getMessage());
            $count = 0;
        }
    }

    return $count;
}
