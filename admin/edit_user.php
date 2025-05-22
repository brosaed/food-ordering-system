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

// Get the user to edit
$user = fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
if (!$user) {
    header('Location: users.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    if (isset($_POST['update_user'])) {
        $username = sanitizeInput($_POST['username']);
        $fullName = sanitizeInput($_POST['full_name']);
        $role = $_POST['role'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        // Validate inputs
        $errors = [];

        if (empty($username)) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($username) < 4) {
            $errors['username'] = 'Username must be at least 4 characters';
        }

        if (empty($fullName)) {
            $errors['full_name'] = 'Full name is required';
        }

        if (empty($role)) {
            $errors['role'] = 'Role is required';
        }

        // Check if username exists (excluding current user)
        $existingUser = fetchOne("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $userId]);
        if ($existingUser) {
            $errors['username'] = 'Username already exists';
        }

        // Only validate password if provided
        if (!empty($password)) {
            if (strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters';
            }

            if ($password !== $confirmPassword) {
                $errors['confirm_password'] = 'Passwords do not match';
            }
        }

        if (empty($errors)) {
            // Prepare update data
            $updateData = [
                'username' => $username,
                'full_name' => $fullName,
                'role' => $role,
                'id' => $userId
            ];

            // Update password only if provided
            $passwordUpdate = '';
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateData['password'] = $hashedPassword;
                $passwordUpdate = ', password = :password';
            }

            // Execute update
            $query = "UPDATE users SET 
                      username = :username, 
                      full_name = :full_name, 
                      role = :role
                      $passwordUpdate
                      WHERE id = :id";

            execute($query, $updateData);

            $_SESSION['message'] = 'User updated successfully';
            header('Location: users.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body>
    <?php include '../includes/admin_nav.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">Edit User</h1>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

            <div class="mb-3">
                <label for="username" class="form-label">Username *</label>
                <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                    id="username" name="username"
                    value="<?= htmlspecialchars($user['username']) ?>" required>
                <?php if (isset($errors['username'])): ?>
                    <div class="invalid-feedback"><?= $errors['username'] ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name *</label>
                <input type="text" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                    id="full_name" name="full_name"
                    value="<?= htmlspecialchars($user['full_name']) ?>" required>
                <?php if (isset($errors['full_name'])): ?>
                    <div class="invalid-feedback"><?= $errors['full_name'] ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Role *</label>
                <select class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>"
                    id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="kitchen" <?= $user['role'] === 'kitchen' ? 'selected' : '' ?>>Kitchen Staff</option>
                    <option value="delivery" <?= $user['role'] === 'delivery' ? 'selected' : '' ?>>Delivery Staff</option>
                </select>
                <?php if (isset($errors['role'])): ?>
                    <div class="invalid-feedback"><?= $errors['role'] ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                    id="password" name="password">
                <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback"><?= $errors['password'] ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                    id="confirm_password" name="confirm_password">
                <?php if (isset($errors['confirm_password'])): ?>
                    <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
            <a href="users.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>