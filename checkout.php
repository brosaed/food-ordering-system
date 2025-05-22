<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/validation.php';
require_once 'includes/notifications.php';

// Initialize variables
$errors = [];
$customerData = [];
$subtotal = 0;
$discountAmount = 0;
$finalAmount = 0;
$promoCodeId = null;

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    redirectWithMessage('menu.php', 'warning', 'Your cart is empty');
}

// Calculate initial subtotal
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$finalAmount = $subtotal;

$errors = [];
$customerData = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    // Validate inputs
    $customerData = [
        'name' => sanitizeInput($_POST['customer_name'] ?? ''),
        'phone' => sanitizeInput($_POST['customer_phone'] ?? ''),
        'address' => sanitizeInput($_POST['customer_address'] ?? ''),
        'email' => sanitizeInput($_POST['customer_email'] ?? ''),
        'instructions' => sanitizeInput($_POST['special_instructions'] ?? ''),
        'promo_code' => sanitizeInput($_POST['promo_code'] ?? '')
    ];

    // Validation logic
    if (empty($customerData['name'])) {
        $errors['name'] = 'Name is required';
    } elseif (!validateName($customerData['name'])) {
        $errors['name'] = 'Please enter a valid name';
    }

    if (empty($customerData['phone'])) {
        $errors['phone'] = 'Phone number is required';
    } elseif (!validatePhone($customerData['phone'])) {
        $errors['phone'] = 'Please enter a valid phone number';
    }

    if (empty($customerData['address'])) {
        $errors['address'] = 'Delivery address is required';
    }

    if (!empty($customerData['email']) && !validateEmail($customerData['email'])) {
        $errors['email'] = 'Please enter a valid email address';
    }

    // Calculate totals
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }

    $discountAmount = 0;
    $promoCodeId = null;

    // Validate promo code if provided
    $orderId = null;
    if (!empty($customerData['promo_code'])) {
        $promoResult = applyPromoCode($customerData['promo_code'], $subtotal, $orderId);
        // $promoResult = validatePromoCode($customerData['promo_code'], $subtotal);

        if ($promoResult['valid']) {
            // $promo = $promoResult['promo'];
            // $promoCodeId = $promo['id'];
            $discountAmount = $promoResult['discount_amount'];
            $finalAmount = $promoResult['final_total'];  // Updated total after discount

            // if ($promo['discount_type'] === 'percentage') {
            //     $discountAmount = $subtotal * ($promo['discount_value'] / 100);
            // } else {
            //     $discountAmount = min($promo['discount_value'], $subtotal);
            // }
        } else {
            $errors['promo_code'] = $promoResult['message'];
        }
    }

    $finalAmount = $subtotal - $discountAmount;

    // If no errors, process order
    if (empty($errors)) {
        // Generate unique order code
        $orderCode = 'ORD-' . strtoupper(uniqid());

        try {
            $pdo->beginTransaction();

            // Insert order with initial status
            $stmt = $pdo->prepare("INSERT INTO orders 
                                  (order_code, customer_name, customer_phone, customer_email, 
                                   customer_address, special_instructions, status, status_updated_at, total_amount, 
                                   discount_amount, final_amount, promo_code_id, created_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW(), ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $orderCode,
                $customerData['name'],
                $customerData['phone'],
                !empty($customerData['email']) ? $customerData['email'] : null,
                $customerData['address'],
                !empty($customerData['instructions']) ? $customerData['instructions'] : null,
                $subtotal,
                $discountAmount,
                $finalAmount,
                $promoCodeId ?: null
            ]);

            $orderId = $pdo->lastInsertId();

            // Insert order items
            foreach ($_SESSION['cart'] as $itemId => $item) {
                $stmt = $pdo->prepare("INSERT INTO order_items 
                                      (order_id, menu_item_id, quantity, price) 
                                      VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $orderId,
                    $itemId,
                    $item['quantity'],
                    $item['price']
                ]);
            }

            // Update promo code usage if applicable
            if ($promoCodeId) {
                $stmt = $pdo->prepare("UPDATE promo_codes 
                                      SET use_count = use_count + 1 
                                      WHERE id = ?");
                $stmt->execute([$promoCodeId]);
            }

            $pdo->commit();

            // Send order confirmation
            // Send confirmation
            // if (!empty($customerData['email'])) {
            //     sendOrderConfirmationEmail($orderId);
            // }

            // Store order in session and redirect
            $_SESSION['last_order'] = [
                'id' => $orderId,
                'order_code' => $orderCode,
                'total' => $finalAmount,
                'name' => $customerData['name'],
                'address' => $customerData['address']
            ];

            unset($_SESSION['cart']);
            redirectWithMessage('order_success.php', 'success', 'Order placed successfully!');

            // Clear cart
            // $_SESSION['cart'] = [];

            // Redirect to order tracking
            // redirectWithMessage('order_tracking.php?order_code=' . $orderCode, 'success', 'Order placed successfully!');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors['database'] = 'An error occurred while processing your order: ' . $e->getMessage();
            error_log("Order processing error: " . $e->getMessage());
        }
    }
}

// Calculate subtotal if not submitting
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="container py-5">
        <h1 class="mb-4">Checkout</h1>

        <?php if (!empty($errors['database'])): ?>
            <div class="alert alert-danger"><?= $errors['database'] ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-7">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Delivery Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" id="checkoutForm">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                    id="customer_name" name="customer_name"
                                    value="<?= $customerData['name'] ?? '' ?>" required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="customer_phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                                    id="customer_phone" name="customer_phone"
                                    value="<?= $customerData['phone'] ?? '' ?>" required>
                                <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="customer_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                    id="customer_email" name="customer_email"
                                    value="<?= $customerData['email'] ?? '' ?>">
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="customer_address" class="form-label">Delivery Address *</label>
                                <textarea class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>"
                                    id="customer_address" name="customer_address" rows="3" required><?= $customerData['address'] ?? '' ?></textarea>
                                <?php if (isset($errors['address'])): ?>
                                    <div class="invalid-feedback"><?= $errors['address'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="special_instructions" class="form-label">Special Instructions</label>
                                <textarea class="form-control" id="special_instructions" name="special_instructions" rows="2"><?= $customerData['instructions'] ?? '' ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="promo_code" class="form-label">Promo Code</label>
                                <div class="input-group">
                                    <input type="text" class="form-control <?= isset($errors['promo_code']) ? 'is-invalid' : '' ?>"
                                        id="promo_code" name="promo_code"
                                        value="<?= $customerData['promo_code'] ?? '' ?>">
                                    <button type="button" class="btn btn-outline-secondary" id="applyPromoBtn">Apply</button>
                                </div>
                                <?php if (isset($errors['promo_code'])): ?>
                                    <div class="invalid-feedback d-block"><?= $errors['promo_code'] ?></div>
                                <?php endif; ?>
                                <div id="promoMessage" class="mt-2"></div>
                            </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h5>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($_SESSION['cart'] as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['name']) ?></td>
                                            <td><?= $item['quantity'] ?></td>
                                            <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Subtotal:</th>
                                        <td>$<?= number_format($subtotal, 2) ?></td>
                                    </tr>
                                    <tr id="discountRow" style="<?= empty($discountAmount) ? 'display:none;' : '' ?>">
                                        <th colspan="2">Discount:</th>
                                        <td class="text-danger">-$<?= number_format($discountAmount, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">Total:</th>
                                        <td class="fw-bold">$<?= number_format($finalAmount, 2) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary btn-lg" form="checkoutForm">
                                Place Order
                            </button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('applyPromoBtn').addEventListener('click', function() {
            const promoCode = document.getElementById('promo_code').value;
            const subtotal = <?= $subtotal ?>;

            fetch('api/validate_promo.php?code=' + encodeURIComponent(promoCode) + '&amount=' + subtotal)
                .then(response => response.json())
                .then(data => {
                    const messageEl = document.getElementById('promoMessage');
                    const discountRow = document.getElementById('discountRow');

                    if (data.valid) {
                        messageEl.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        discountRow.style.display = '';
                        discountRow.querySelector('td').textContent = `-$${data.discount_amount.toFixed(2)}`;

                        // Update total
                        const total = subtotal - data.discount_amount;
                        document.querySelector('tfoot tr:last-child td').textContent = `$${total.toFixed(2)}`;
                    } else {
                        messageEl.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                        discountRow.style.display = 'none';

                        // Reset total
                        document.querySelector('tfoot tr:last-child td').textContent = `$${subtotal.toFixed(2)}`;
                    }
                });
        });
    </script>
</body>

</html>