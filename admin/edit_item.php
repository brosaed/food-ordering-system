<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireRole('admin');

if (!isset($_GET['id'])) {
    header('Location: menu.php');
    exit;
}

$itemId = (int)$_GET['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    if (isset($_POST['update_item'])) {
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $price = (float)$_POST['price'];
        $categoryId = (int)$_POST['category_id'];
        $isAvailable = isset($_POST['is_available']) ? 1 : 0;

        // Handle image upload if new image was provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/menu/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $imagePath = 'assets/images/menu/' . $filename;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);

            // Update with new image
            execute(
                "UPDATE menu_items SET 
                category_id = ?, name = ?, description = ?, price = ?, image_path = ?, is_available = ?
                WHERE id = ?",
                [$categoryId, $name, $description, $price, $imagePath, $isAvailable, $itemId]
            );
        } else {
            // Update without changing image
            execute(
                "UPDATE menu_items SET 
                category_id = ?, name = ?, description = ?, price = ?, is_available = ?
                WHERE id = ?",
                [$categoryId, $name, $description, $price, $isAvailable, $itemId]
            );
        }

        $_SESSION['message'] = 'Menu item updated successfully';
        header('Location: menu.php');
        exit;
    }
}

// Get the menu item to edit
$menuItem = fetchOne("SELECT * FROM menu_items WHERE id = ?", [$itemId]);
if (!$menuItem) {
    header('Location: menu.php');
    exit;
}

// Get all categories for dropdown
$categories = fetchAll("SELECT * FROM categories ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu Item - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include '../includes/admin_nav.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">Edit Menu Item</h1>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Item Name</label>
                <input type="text" class="form-control" id="name" name="name"
                    value="<?= htmlspecialchars($menuItem['name']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($menuItem['description']) ?></textarea>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price"
                    value="<?= htmlspecialchars($menuItem['price']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $category['id'] == $menuItem['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input type="file" class="form-control" id="image" name="image">
                <?php if ($menuItem['image_path']): ?>
                    <div class="mt-2">
                        <img src="../<?= htmlspecialchars($menuItem['image_path']) ?>" alt="Current image" style="max-height: 100px;">
                        <p class="small text-muted mt-1">Current image</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_available" name="is_available"
                    <?= $menuItem['is_available'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_available">Available</label>
            </div>

            <button type="submit" name="update_item" class="btn btn-primary">Update Item</button>
            <a href="menu.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>