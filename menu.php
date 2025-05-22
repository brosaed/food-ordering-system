<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Get all available categories
$categories = fetchAll("SELECT * FROM categories ORDER BY name");

// Get all available menu items
$menuItems = fetchAll("SELECT * FROM menu_items WHERE is_available = 1 ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="container my-5">
        <h1 class="text-center mb-5">Our Menu</h1>

        <!-- Categories Navigation -->
        <ul class="nav nav-tabs mb-4" id="menuTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button">All Items</button>
            </li>
            <?php foreach ($categories as $category): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cat-<?= $category['id'] ?>-tab" data-bs-toggle="tab"
                        data-bs-target="#cat-<?= $category['id'] ?>" type="button">
                        <?= htmlspecialchars($category['name']) ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Menu Items -->
        <div class="tab-content" id="menuTabsContent">
            <div class="tab-pane fade show active" id="all" role="tabpanel">
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach ($menuItems as $item): ?>
                        <div class="col">
                            <div class="card h-100">
                                <img src="<?= htmlspecialchars($item['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($item['description']) ?></p>
                                    <p class="text-success fw-bold">$<?= number_format($item['price'], 2) ?></p>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="input-group" style="width: 120px;">
                                            <button class="btn btn-outline-secondary minus-btn" type="button">-</button>
                                            <input type="number" class="form-control text-center quantity-input" value="1" min="1">
                                            <button class="btn btn-outline-secondary plus-btn" type="button">+</button>
                                        </div>
                                        <button class="btn btn-primary add-to-cart"
                                            data-item-id="<?= $item['id'] ?>"
                                            data-item-name="<?= htmlspecialchars($item['name']) ?>"
                                            data-item-price="<?= $item['price'] ?>">
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php foreach ($categories as $category): ?>
                <div class="tab-pane fade" id="cat-<?= $category['id'] ?>" role="tabpanel">
                    <div class="row row-cols-1 row-cols-md-3 g-4">
                        <?php
                        $categoryItems = array_filter($menuItems, function ($item) use ($category) {
                            return $item['category_id'] == $category['id'];
                        });

                        foreach ($categoryItems as $item): ?>
                            <div class="col">
                                <div class="card h-100">
                                    <img src="<?= htmlspecialchars($item['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                        <p class="card-text"><?= htmlspecialchars($item['description']) ?></p>
                                        <p class="text-success fw-bold">$<?= number_format($item['price'], 2) ?></p>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="input-group" style="width: 120px;">
                                                <button class="btn btn-outline-secondary minus-btn" type="button">-</button>
                                                <input type="number" class="form-control text-center quantity-input" value="1" min="1">
                                                <button class="btn btn-outline-secondary plus-btn" type="button">+</button>
                                            </div>
                                            <button class="btn btn-primary add-to-cart"
                                                data-item-id="<?= $item['id'] ?>"
                                                data-item-name="<?= htmlspecialchars($item['name']) ?>"
                                                data-item-price="<?= $item['price'] ?>">
                                                Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/menu.js"></script>
</body>

</html>