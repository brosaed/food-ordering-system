<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php'; // Add this line to include auth functions

// Initialize cart count
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    $cartCount = array_reduce($_SESSION['cart'], function ($carry, $item) {
        return $carry + $item['quantity'];
    }, 0);
}

// $cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?><?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>"><?= SITE_NAME ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/menu.php">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/order_tracking.php">Track Order</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <?php if (hasRole('admin')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard.php">
                                    <i class="bi bi-speedometer2"></i> Admin Dashboard
                                </a>
                            </li>
                        <?php elseif (hasRole('kitchen')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>/kitchen/dashboard.php">
                                    <i class="bi bi-egg-fried"></i> Kitchen
                                </a>
                            </li>
                        <?php elseif (hasRole('delivery')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>/delivery/dashboard.php">
                                    <i class="bi bi-truck"></i> Delivery
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                    <!-- <li class="nav-item">
                        <a class="nav-link position-relative" href="<?= BASE_URL ?>/cart.php">
                            <i class="bi bi-cart"></i> Cart
                            <?php if ($cartCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?= $cartCount ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li> -->
                    <li class="nav-item">
                        <div class="d-flex">
                            <a href="<?= BASE_URL ?>/cart.php" class="btn btn-primary position-relative ms-2" id="cartButton">
                                <i class="bi bi-cart"></i> Cart
                                <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?= isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0 ?>
                                </span>
                            </a>
                        </div>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-4">
        <?php displayFlashMessage(); ?>