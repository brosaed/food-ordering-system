<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireRole('admin');

// Initialize report variables
$salesReport = [];
$topItems = [];
$statusStats = [];
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

try {
    // Sales Report Data
    $salesReport = fetchAll(
        "SELECT 
            DATE(created_at) AS order_date,
            COUNT(*) AS total_orders,
            SUM(final_amount) AS total_sales
        FROM orders
        WHERE created_at BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY order_date DESC",
        ["$startDate 00:00:00", "$endDate 23:59:59"]
    );

    // Top Selling Items
    $topItems = fetchAll(
        "SELECT 
            mi.name,
            SUM(oi.quantity) AS total_quantity,
            SUM(oi.price * oi.quantity) AS total_revenue
        FROM order_items oi
        JOIN menu_items mi ON oi.item_id = mi.id
        WHERE oi.created_at BETWEEN ? AND ?
        GROUP BY mi.id
        ORDER BY total_quantity DESC
        LIMIT 10",
        ["$startDate 00:00:00", "$endDate 23:59:59"]
    );

    // Order Status Statistics
    $statusStats = fetchAll(
        "SELECT 
            status,
            COUNT(*) AS count,
            SUM(final_amount) AS total_amount
        FROM orders
        WHERE created_at BETWEEN ? AND ?
        GROUP BY status",
        ["$startDate 00:00:00", "$endDate 23:59:59"]
    );
} catch (Exception $e) {
    error_log("Reports Error: " . $e->getMessage());
    $_SESSION['error'] = "Error generating reports: " . $e->getMessage();
}

// Calculate summary numbers safely
$totalSales = array_sum(array_column($salesReport, 'total_sales')) ?? 0;
$totalOrders = array_sum(array_column($salesReport, 'total_orders')) ?? 0;
$avgOrderValue = $totalOrders ? ($totalSales / $totalOrders) : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">

                <div class=" container-fluid py-4">
                    <h1 class="mb-4">Sales Reports</h1>

                    <!-- Date Filter -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Filter Report</h5>
                        </div>
                        <div class="card-body">
                            <form method="get" class="row g-3">
                                <div class="col-md-5">
                                    <input type="date" name="start_date" class="form-control"
                                        value="<?= htmlspecialchars($startDate) ?>">
                                </div>
                                <div class="col-md-5">
                                    <input type="date" name="end_date" class="form-control"
                                        value="<?= htmlspecialchars($endDate) ?>">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-filter"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Sales Summary -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card text-white bg-primary">
                                <div class="card-body">
                                    <h5 class="card-title">Total Sales</h5>
                                    <p class="h2">
                                        $<?= number_format($totalSales, 2) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-success">
                                <div class="card-body">
                                    <h5 class="card-title">Total Orders</h5>
                                    <p class="h2">
                                        <?= number_format($totalOrders) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-info">
                                <div class="card-body">
                                    <h5 class="card-title">Average Order Value</h5>
                                    <p class="h2">
                                        $<?= number_format($avgOrderValue, 2) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Chart -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Daily Sales</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChart" style="height: 300px;"></canvas>
                        </div>
                    </div>

                    <!-- Top Selling Items -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Top Selling Items</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Quantity Sold</th>
                                            <th>Total Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ((array)$topItems as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['name'] ?? 'N/A') ?></td>
                                                <td><?= number_format($item['total_quantity'] ?? 0) ?></td>
                                                <td>$<?= number_format($item['total_revenue'] ?? 0, 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($topItems)): ?>
                                            <tr>
                                                <td colspan="3" class="text-center">No items sold in this period</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    // Sales Chart
                    const salesCtx = document.getElementById('salesChart').getContext('2d');
                    new Chart(salesCtx, {
                        type: 'line',
                        data: {
                            labels: <?= json_encode(array_column($salesReport, 'order_date')) ?>,
                            datasets: [{
                                label: 'Daily Sales',
                                data: <?= json_encode(array_column($salesReport, 'total_sales')) ?>,
                                borderColor: '#4e73df',
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '$' + value.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                </script>
</body>

</html>