<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php'; // Load auth functions


// Set page title
$pageTitle = 'Home';
// Get featured menu items
$featuredItems = fetchAll("SELECT * FROM menu_items WHERE is_available = 1 LIMIT 6");

// Include header
include 'includes/header.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Food Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>


    <main class="container my-5">
        <section class="hero-section mb-5">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 fw-bold mb-4">Delicious food delivered to your doorstep</h1>
                    <p class="lead mb-4">Order from your favorite restaurants with just a few clicks.</p>
                    <a href="menu.php" class="btn btn-primary btn-lg px-4">Order Now</a>
                </div>
                <div class="col-md-6">
                    <img src="assets/images/hero-image.jpg" alt="Food delivery" class="img-fluid rounded">
                </div>
            </div>
        </section>

        <section class="featured-items mb-5">
            <h2 class="text-center mb-4">Our Featured Items</h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($featuredItems as $item): ?>
                    <div class="col">
                        <div class="card h-100">
                            <img src="<?= htmlspecialchars($item['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($item['description']) ?></p>
                                <p class="text-success fw-bold">$<?= number_format($item['price'], 2) ?></p>
                                <a href="menu.php" class="btn btn-outline-primary">View Menu</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>