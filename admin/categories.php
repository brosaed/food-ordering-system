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

    if (isset($_POST['add_category'])) {
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);

        execute(
            "INSERT INTO categories (name, description) VALUES (?, ?)",
            [$name, $description]
        );

        $_SESSION['message'] = 'Category added successfully';
        header('Location: categories.php');
        exit;
    }
}

// Get all categories
$categories = fetchAll("SELECT * FROM categories ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <h1 class="mb-4">Manage Categories</h1>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Add New Category</h5>
                            </div>
                            <div class="card-body">
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                                    <div class="mb-3">
                                        <label for="name" class="form-label">Category Name</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                    </div>

                                    <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Existing Categories</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categories as $category): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($category['name']) ?></td>
                                                    <td><?= htmlspecialchars($category['description']) ?></td>
                                                    <td>
                                                        <a href="edit_category.php?id=<?= $category['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                        <a href="delete_category.php?id=<?= $category['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</a>
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