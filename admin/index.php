<?php
/**
 * Admin Dashboard
 *
 * Shows statistics and overview of the pharmacy
 */

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin access
if (!isAdmin()) {
    setFlashMessage('error', 'Access denied. Admin privileges required.', 'error');
    redirect(getBaseUrl() . '/index.php');
}

// Get statistics
try {
    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];

    // Total revenue
    $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status != 'cancelled'");
    $totalRevenue = $stmt->fetch()['total'];

    // Total products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
    $totalProducts = $stmt->fetch()['total'];

    // Low stock products (less than 10)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity < 10 AND is_active = 1");
    $lowStockCount = $stmt->fetch()['total'];

    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
    $totalCustomers = $stmt->fetch()['total'];

    // Pending orders
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
    $pendingOrders = $stmt->fetch()['total'];

    // Recent orders
    $stmt = $pdo->query("
        SELECT o.*, u.first_name, u.last_name, u.email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $recentOrders = $stmt->fetchAll();

    // Low stock products
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.stock_quantity < 10 AND p.is_active = 1
        ORDER BY p.stock_quantity ASC
        LIMIT 5
    ");
    $lowStockProducts = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $totalOrders = $totalRevenue = $totalProducts = $lowStockCount = $totalCustomers = $pendingOrders = 0;
    $recentOrders = $lowStockProducts = [];
}

// Admin-specific header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - PharmaCare Admin</title>
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="admin-header">
                <h1 class="admin-title">Dashboard</h1>
                <span style="color: var(--gray-600);">Welcome back, <?php echo sanitize($_SESSION['user_name']); ?></span>
            </div>

            <?php echo displayFlashMessages(); ?>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Total Orders</span>
                        <div class="stat-card-icon blue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($totalOrders); ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Total Revenue</span>
                        <div class="stat-card-icon green">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo formatPrice($totalRevenue); ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Products</span>
                        <div class="stat-card-icon blue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"></path><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path><path d="m3.3 7 8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($totalProducts); ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Low Stock</span>
                        <div class="stat-card-icon <?php echo $lowStockCount > 0 ? 'orange' : 'green'; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($lowStockCount); ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Customers</span>
                        <div class="stat-card-icon blue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($totalCustomers); ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Pending Orders</span>
                        <div class="stat-card-icon <?php echo $pendingOrders > 0 ? 'orange' : 'green'; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($pendingOrders); ?></div>
                </div>
            </div>

            <!-- Recent Orders & Low Stock -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <!-- Recent Orders -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Recent Orders</h2>
                        <a href="<?php echo getBaseUrl(); ?>/admin/orders.php" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentOrders)): ?>
                                    <tr><td colspan="4" class="text-center">No orders yet</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo sanitize($order['first_name'] . ' ' . $order['last_name']); ?></td>
                                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td><span class="badge <?php echo getStatusBadgeClass($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Low Stock Alert</h2>
                        <a href="<?php echo getBaseUrl(); ?>/admin/products.php" class="btn btn-sm btn-outline">Manage</a>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($lowStockProducts)): ?>
                                    <tr><td colspan="3" class="text-center" style="color: var(--success-color);">All products well stocked!</td></tr>
                                <?php else: ?>
                                    <?php foreach ($lowStockProducts as $product): ?>
                                        <tr>
                                            <td><?php echo sanitize(truncateText($product['name'], 30)); ?></td>
                                            <td><?php echo sanitize($product['category_name']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $product['stock_quantity'] == 0 ? 'badge-danger' : 'badge-warning'; ?>">
                                                    <?php echo (int)$product['stock_quantity']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="<?php echo getBaseUrl(); ?>/assets/js/main.js"></script>
</body>
</html>
