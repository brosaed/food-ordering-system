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

    if (isset($_POST['add_item'])) {
        // Add new menu item
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $price = (float)$_POST['price'];
        $categoryId = (int)$_POST['category_id'];
        $isAvailable = isset($_POST['is_available']) ? 1 : 0;

        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/menu/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $imagePath = 'assets/images/menu/' . $filename;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
        }

        execute(
            "INSERT INTO menu_items (category_id, name, description, price, image_path, is_available) 
            VALUES (?, ?, ?, ?, ?, ?)",
            [$categoryId, $name, $description, $price, $imagePath, $isAvailable]
        );

        $_SESSION['message'] = 'Menu item added successfully';
        header('Location: menu.php');
        exit;
    }
}

// Get all menu items
$menuItems = fetchAll("
    SELECT mi.*, c.name AS category_name 
    FROM menu_items mi
    LEFT JOIN categories c ON mi.category_id = c.id
    ORDER BY mi.name
");

// Get all categories for dropdown
$categories = fetchAll("SELECT * FROM categories ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include '../includes/admin_nav.php'; ?>

    <!-- In your header.php or directly in menu.php -->
    <!-- <a href="cart.php" class="btn btn-primary position-relative">
        <i class="bi bi-cart"></i> Cart
        <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?= !empty($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0 ?>
        </span>
    </a> -->

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

        <main role="main" class="col-md-9 ms-sm-auto col-lg-10 px-4">

            <div class="container py-5">
                <h1 class="mb-4">Manage Menu Items</h1>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-5">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Add New Menu Item</h5>
                            </div>
                            <div class="card-body">
                                <form method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                                    <div class="mb-3">
                                        <label for="name" class="form-label">Item Name</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price</label>
                                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="image" class="form-label">Image</label>
                                        <input type="file" class="form-control" id="image" name="image">
                                    </div>

                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="is_available" name="is_available" checked>
                                        <label class="form-check-label" for="is_available">Available</label>
                                    </div>

                                    <button type="submit" name="add_item" class="btn btn-primary">Add Item</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header">
                                <h5>Menu Items</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Category</th>
                                                <th>Price</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($menuItems as $item): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                                    <td><?= htmlspecialchars($item['category_name']) ?></td>
                                                    <td>$<?= number_format($item['price'], 2) ?></td>
                                                    <td>
                                                        <span class="badge <?= $item['is_available'] ? 'bg-success' : 'bg-secondary' ?>">
                                                            <?= $item['is_available'] ? 'Available' : 'Unavailable' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="edit_item.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                        <a href="delete_item.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</a>
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