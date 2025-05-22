<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireRole('admin');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    if (isset($_POST['add_user'])) {
        $username = sanitizeInput($_POST['username']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        $fullName = sanitizeInput($_POST['full_name']);
        $role = $_POST['role'];

        // Validate inputs
        $errors = [];

        if (empty($username)) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($username) < 4) {
            $errors['username'] = 'Username must be at least 4 characters';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        if (empty($fullName)) {
            $errors['full_name'] = 'Full name is required';
        }

        if (empty($role)) {
            $errors['role'] = 'Role is required';
        }

        // Check if username exists
        $existingUser = fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
        if ($existingUser) {
            $errors['username'] = 'Username already exists';
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            execute(
                "INSERT INTO users (username, password, role, full_name) 
                VALUES (?, ?, ?, ?)",
                [$username, $hashedPassword, $role, $fullName]
            );

            $_SESSION['message'] = 'User added successfully';
            header('Location: users.php');
            exit;
        }
    }
}

// Get all users
$users = fetchAll("SELECT * FROM users ORDER BY role, username");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body>
    <?php include '../includes/admin_nav.php'; ?>
    <div class="row">
        <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">

                    <!-- Sidebar -->
                    <nav id=" sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                        <div class="position-sticky pt-3">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link active text-white" href="dashboard.php">
                                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white" href="orders.php">
                                        <i class="bi bi-list-check me-2"></i>Orders
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white" href="users.php">
                                        <i class="bi bi-list-check me-2"></i>Users
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white" href="categories.php">
                                        <i class="bi bi-list-check me-2"></i>Categories
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white" href="menu.php">
                                        <i class="bi bi-menu-button me-2"></i>Menu
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white" href="reports.php">
                                        <i class="bi bi-graph-up me-2"></i>Reports
                                    </a>
                                </li>
                                <!-- <li class="nav-item dropdown">
                            <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell"></i>
                                <?php if ($unreadCount > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?= $unreadCount ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="min-width: 300px;">
                                <li class="dropdown-header">Notifications</li>
                                <?php foreach ($notifications as $note): ?>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <?= htmlspecialchars($note['message']) ?><br>
                                            <small class="text-muted"><?= date('H:i', strtotime($note['created_at'])) ?></small>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (empty($notifications)): ?>
                                    <li><span class="dropdown-item text-muted">No new notifications</span></li>
                                <?php endif; ?>
                            </ul>
                        </li> -->

                            </ul>
                        </div>
                    </nav>
                    <!-- End Sidebar -->

            </div>
        </div>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-4">


            <div class="container py-5">
                <h1 class="mb-4">Manage Users</h1>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Add New User</h5>
                            </div>
                            <div class="card-body">
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username *</label>
                                        <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                                            id="username" name="username" required>
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
                                            id="full_name" name="full_name" required>
                                        <?php if (isset($errors['full_name'])): ?>
                                            <div class="invalid-feedback"><?= $errors['full_name'] ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role *</label>
                                        <select class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>"
                                            id="role" name="role" required>
                                            <option value="">Select Role</option>
                                            <option value="admin">Admin</option>
                                            <option value="kitchen">Kitchen Staff</option>
                                            <option value="delivery">Delivery Staff</option>
                                        </select>
                                        <?php if (isset($errors['role'])): ?>
                                            <div class="invalid-feedback"><?= $errors['role'] ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Existing Users</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Full Name</th>
                                                <th>Role</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                                                    <td>
                                                        <span class="badge 
                                                    <?= $user['role'] === 'admin' ? 'bg-danger' : '' ?>
                                                    <?= $user['role'] === 'kitchen' ? 'bg-primary' : '' ?>
                                                    <?= $user['role'] === 'delivery' ? 'bg-success' : '' ?>">
                                                            <?= ucfirst($user['role']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </a>
                                                        <a href="delete_user.php?id=<?= $user['id'] ?>"
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Are you sure you want to delete this user?')">
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
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>