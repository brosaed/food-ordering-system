<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';
require_once 'validation.php';

/**
 * Login user
 */
function login($username, $password)
{
    $user = fetchOne("SELECT * FROM users WHERE username = ?", [$username]);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        $_SESSION['last_activity'] = time();

        // If the user is an admin, set the session variable for admin login
        if ($user['role'] === 'admin') {
            $_SESSION['admin_logged_in'] = true;
        }

        return true;
    }
    return false;
}

/**
 * Logout user
 */
function logout()
{
    session_unset();
    session_destroy();
}

/**
 * Require login
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        redirectWithMessage('login.php', 'danger', 'Please login to access this page');
    }
}


/**
 * Check session timeout (30 minutes)
 */
if (isLoggedIn() && time() - $_SESSION['last_activity'] > 1800) {
    logout();
    redirectWithMessage('login.php', 'danger', 'Your session has expired due to inactivity');
}

// Update last activity time
$_SESSION['last_activity'] = time();
