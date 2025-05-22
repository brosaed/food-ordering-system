<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Initialize cart from session
$cart = $_SESSION['cart'] ?? [];

// Handle remove item from cart
if (isset($_GET['remove'])) {
    $itemId = (int)$_GET['remove'];
    if (isset($cart[$itemId])) {
        unset($cart[$itemId]);
        $_SESSION['cart'] = $cart;

        // Update sessionStorage via JavaScript
        echo "<script>sessionStorage.setItem('cart', JSON.stringify(" . json_encode($cart) . "));</script>";
    }
    redirectWithMessage('cart.php', 'success', 'Item removed from cart');
}

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    foreach ($_POST['quantities'] as $itemId => $quantity) {
        $itemId = (int)$itemId;
        $quantity = (int)$quantity;

        if ($quantity <= 0) {
            unset($cart[$itemId]);
        } else {
            $cart[$itemId]['quantity'] = $quantity;
        }
    }

    $_SESSION['cart'] = $cart;

    // Update sessionStorage via JavaScript
    echo "<script>sessionStorage.setItem('cart', JSON.stringify(" . json_encode($cart) . "));</script>";

    redirectWithMessage('cart.php', 'success', 'Cart updated successfully');
}


// Initialize cart if not exists
// if (!isset($_SESSION['cart'])) {
//     $_SESSION['cart'] = [];
// }

// Handle remove item from cart
// if (isset($_GET['remove'])) {
//     $itemId = (int)$_GET['remove'];
//     if (isset($_SESSION['cart'][$itemId])) {
//         unset($_SESSION['cart'][$itemId]);
//     }
//     redirectWithMessage('cart.php', 'success', 'Item removed from cart');
// }

// Handle quantity update
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
//     if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
//         die('Invalid CSRF token');
//     }

//     foreach ($_POST['quantities'] as $itemId => $quantity) {
//         $itemId = (int)$itemId;
//         $quantity = (int)$quantity;

//         if ($quantity <= 0) {
//             unset($_SESSION['cart'][$itemId]);
//         } else {
//             $_SESSION['cart'][$itemId]['quantity'] = $quantity;
//         }
//     }
//     redirectWithMessage('cart.php', 'success', 'Cart updated successfully');
// }


// Calculate subtotal
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}


// Calculate 
// $subtotal = 0;
// foreach ($_SESSION['cart'] as $item) {
//     $subtotal += $item['price'] * $item['quantity'];
// }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="container py-5">
        <h1 class="mb-4">Your Cart</h1>

        <?php displayFlashMessage(); ?>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="alert alert-info">
                Your cart is empty. <a href="menu.php">Browse our menu</a> to add items.
            </div>
        <?php else: ?>
            <form method="post" action="cart.php">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['cart'] as $itemId => $item): ?>
                                <?php
                                $itemTotal = $item['price'] * $item['quantity'];
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td>$<?= number_format($item['price'], 2) ?></td>
                                    <td>
                                        <input type="number" name="quantities[<?= $itemId ?>]"
                                            value="<?= $item['quantity'] ?>" min="1" class="form-control" style="width: 80px;">
                                    </td>
                                    <td>$<?= number_format($itemTotal, 2) ?></td>
                                    <td>
                                        <a href="cart.php?remove=<?= $itemId ?>" class="btn btn-sm btn-danger">Remove</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Subtotal:</td>
                                <td colspan="2" class="fw-bold">$<?= number_format($subtotal, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="menu.php" class="btn btn-outline-primary">Continue Shopping</a>
                    <div>
                        <button type="submit" name="update_cart" class="btn btn-secondary me-2">Update Cart</button>
                        <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>