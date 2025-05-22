<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Ensure the user is an admin
requireRole('admin');

// Handle adding a new promo code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_promo_code'])) {
    try {
        $code = $_POST['code'];
        $discountType = $_POST['discount_type'];
        $discountValue = $_POST['discount_value'];
        $minOrderAmount = $_POST['min_order_amount'];
        $validFrom = $_POST['valid_from'];
        $validUntil = $_POST['valid_until'];
        $useLimit = $_POST['use_limit'];

        // Validate input
        if (empty($code) || empty($discountType) || empty($discountValue) || empty($minOrderAmount) || empty($validFrom) || empty($validUntil) || empty($useLimit)) {
            throw new Exception('All fields are required.');
        }

        $query = "INSERT INTO promo_codes (code, discount_type, discount_value, min_order_amount, valid_from, valid_until, use_limit) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        execute($query, [$code, $discountType, $discountValue, $minOrderAmount, $validFrom, $validUntil, $useLimit]);

        $_SESSION['message'] = 'Promo code added successfully';
        header('Location: promo_codes.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Handle deleting a promo code
if (isset($_GET['delete'])) {
    try {
        $promoCodeId = (int)$_GET['delete'];

        $query = "DELETE FROM promo_codes WHERE id = ?";
        execute($query, [$promoCodeId]);

        $_SESSION['message'] = 'Promo code deleted successfully';
        header('Location: promo_codes.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Fetch all promo codes
$query = "SELECT * FROM promo_codes ORDER BY valid_from DESC";
$promoCodes = fetchAll($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promo Codes - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .form-group {
            margin-bottom: 1.5rem;
        }

        .card-header,
        .table thead th {
            background-color: #f8f9fa;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .form-label {
            font-weight: bold;
        }

        .input-group-text {
            font-size: 1rem;
        }

        .btn-sm {
            padding: .375rem .75rem;
        }
    </style>
</head>

<body>
    <?php include '../includes/admin_nav.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4 text-center">Promo Codes Management</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Promo Code Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Add New Promo Code</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="code" class="form-label">Promo Code</label>
                            <input type="text" class="form-control" id="code" name="code" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="discount_type" class="form-label">Discount Type</label>
                            <select id="discount_type" name="discount_type" class="form-select" required>
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="discount_value" class="form-label">Discount Value</label>
                            <input type="number" class="form-control" id="discount_value" name="discount_value" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="min_order_amount" class="form-label">Min Order Amount</label>
                            <input type="number" class="form-control" id="min_order_amount" name="min_order_amount" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="valid_from" class="form-label">Valid From</label>
                            <input type="datetime-local" class="form-control" id="valid_from" name="valid_from" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="valid_until" class="form-label">Valid Until</label>
                            <input type="datetime-local" class="form-control" id="valid_until" name="valid_until" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="use_limit" class="form-label">Use Limit</label>
                            <input type="number" class="form-control" id="use_limit" name="use_limit" required>
                        </div>
                    </div>
                    <button type="submit" name="add_promo_code" class="btn btn-primary btn-sm">Add Promo Code</button>
                </form>
            </div>
        </div>

        <!-- Promo Code List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Promo Codes List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Promo Code</th>
                                <th>Discount Type</th>
                                <th>Discount Value</th>
                                <th>Min Order</th>
                                <th>Valid From</th>
                                <th>Valid Until</th>
                                <th>Use Limit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($promoCodes as $promoCode): ?>
                                <tr>
                                    <td><?= htmlspecialchars($promoCode['code']) ?></td>
                                    <td><?= ucfirst($promoCode['discount_type']) ?></td>
                                    <td><?= $promoCode['discount_type'] === 'percentage' ? $promoCode['discount_value'] . '%' : '$' . number_format($promoCode['discount_value'], 2) ?></td>
                                    <td>$<?= number_format($promoCode['min_order_amount'], 2) ?></td>
                                    <td><?= date('M d, Y H:i', strtotime($promoCode['valid_from'])) ?></td>
                                    <td><?= date('M d, Y H:i', strtotime($promoCode['valid_until'])) ?></td>
                                    <td><?= $promoCode['use_limit'] ?></td>
                                    <td>
                                        <a href="?delete=<?= $promoCode['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this promo code?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>