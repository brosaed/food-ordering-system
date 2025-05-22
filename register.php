<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/validation.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $inviteCode = sanitizeInput($_POST['invite_code'] ?? '');

    // Validate inputs
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (!validateUsername($username)) {
        $errors['username'] = 'Username must be 4-20 characters (letters, numbers, underscores)';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (!validatePassword($password)) {
        $errors['password'] = 'Password must be at least 8 characters';
    }

    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    if (empty($fullName)) {
        $errors['full_name'] = 'Full name is required';
    }

    if (empty($inviteCode)) {
        $errors['invite_code'] = 'Invite code is required';
    } elseif ($inviteCode !== INVITE_CODE) { // Define INVITE_CODE in config.php
        $errors['invite_code'] = 'Invalid invite code';
    }

    // Check if username exists
    $existingUser = fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
    if ($existingUser) {
        $errors['username'] = 'Username already exists';
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Default role is 'kitchen' - admin can change it later
        execute(
            "INSERT INTO users (username, password, role, full_name) 
            VALUES (?, ?, 'kitchen', ?)",
            [$username, $hashedPassword, $fullName]
        );

        $_SESSION['message'] = 'Registration successful! Please login';
        header('Location: login.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Registration - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Staff Registration</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger"><?= $errors['general'] ?></div>
                        <?php endif; ?>

                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                            <div class="mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                                    id="username" name="username"
                                    value="<?= htmlspecialchars($username ?? '') ?>" required>
                                <?php if (isset($errors['username'])): ?>
                                    <div class="invalid-feedback"><?= $errors['username'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                    id="password" name="password" required>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback"><?= $errors['password'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                                    id="confirm_password" name="confirm_password" required>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                                    id="full_name" name="full_name"
                                    value="<?= htmlspecialchars($fullName ?? '') ?>" required>
                                <?php if (isset($errors['full_name'])): ?>
                                    <div class="invalid-feedback"><?= $errors['full_name'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="invite_code" class="form-label">Invite Code *</label>
                                <input type="text" class="form-control <?= isset($errors['invite_code']) ? 'is-invalid' : '' ?>"
                                    id="invite_code" name="invite_code" required>
                                <?php if (isset($errors['invite_code'])): ?>
                                    <div class="invalid-feedback"><?= $errors['invite_code'] ?></div>
                                <?php endif; ?>
                                <small class="text-muted">Get this from your administrator</small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Register</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        Already have an account? <a href="login.php">Login here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>