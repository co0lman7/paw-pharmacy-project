<?php
/**
 * Admin Orders Management
 *
 * View and update order status
 */

$pageTitle = 'Manage Orders';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin access
if (!isAdmin()) {
    setFlashMessage('error', 'Access denied.', 'error');
    redirect(getBaseUrl() . '/index.php');
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request.', 'error');
    } else {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = sanitize($_POST['status'] ?? '');

        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if ($orderId && in_array($newStatus, $validStatuses)) {
            try {
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $orderId]);
                setFlashMessage('success', 'Order status updated.', 'success');
            } catch (PDOException $e) {
                setFlashMessage('error', 'Error updating status.', 'error');
            }
        }
    }
    redirect(getBaseUrl() . '/admin/orders.php');
}

// Fetch orders
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

try {
    $sql = "
        SELECT o.*, u.first_name, u.last_name, u.email,
               COUNT(oi.id) as item_count
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
    ";

    if ($statusFilter) {
        $sql .= " WHERE o.status = ?";
    }

    $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

    $stmt = $pdo->prepare($sql);
    if ($statusFilter) {
        $stmt->execute([$statusFilter]);
    } else {
        $stmt->execute();
    }
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
}

// View single order
$viewOrder = null;
$orderItems = [];
if (isset($_GET['view'])) {
    $orderId = (int)$_GET['view'];
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, u.first_name, u.last_name, u.email, u.phone
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $viewOrder = $stmt->fetch();

        if ($viewOrder) {
            $stmt = $pdo->prepare("
                SELECT oi.*, p.name, p.slug, p.image
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$orderId]);
            $orderItems = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log("Error fetching order: " . $e->getMessage());
    }
}
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
            <?php echo displayFlashMessages(); ?>

            <?php if ($viewOrder): ?>
                <!-- Single Order View -->
                <div class="admin-header">
                    <h1 class="admin-title">Order #<?php echo str_pad($viewOrder['id'], 6, '0', STR_PAD_LEFT); ?></h1>
                    <a href="<?php echo getBaseUrl(); ?>/admin/orders.php" class="btn btn-outline">Back to Orders</a>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
                    <!-- Order Items -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title">Order Items</h2>
                        </div>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orderItems as $item): ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                    <img src="<?php echo getProductImage($item['image']); ?>" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                                    <?php echo sanitize($item['name']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo formatPrice($item['unit_price']); ?></td>
                                            <td><?php echo (int)$item['quantity']; ?></td>
                                            <td><?php echo formatPrice($item['unit_price'] * $item['quantity']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" style="text-align: right; font-weight: 600;">Total:</td>
                                        <td style="font-weight: 600; color: var(--primary-color);"><?php echo formatPrice($viewOrder['total_amount']); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div>
                        <div class="admin-card mb-3">
                            <div class="admin-card-header">
                                <h2 class="admin-card-title">Status</h2>
                            </div>
                            <div class="admin-card-body">
                                <form action="" method="POST">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="order_id" value="<?php echo (int)$viewOrder['id']; ?>">

                                    <div class="form-group mb-2">
                                        <select name="status" class="form-control">
                                            <option value="pending" <?php echo $viewOrder['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $viewOrder['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo $viewOrder['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $viewOrder['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $viewOrder['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">Update Status</button>
                                </form>
                            </div>
                        </div>

                        <div class="admin-card mb-3">
                            <div class="admin-card-header">
                                <h2 class="admin-card-title">Customer</h2>
                            </div>
                            <div class="admin-card-body">
                                <p style="margin: 0 0 0.5rem;"><strong><?php echo sanitize($viewOrder['first_name'] . ' ' . $viewOrder['last_name']); ?></strong></p>
                                <p style="margin: 0 0 0.5rem; color: var(--gray-600);"><?php echo sanitize($viewOrder['email']); ?></p>
                                <p style="margin: 0; color: var(--gray-600);"><?php echo sanitize($viewOrder['phone'] ?? 'No phone'); ?></p>
                            </div>
                        </div>

                        <div class="admin-card mb-3">
                            <div class="admin-card-header">
                                <h2 class="admin-card-title">Shipping</h2>
                            </div>
                            <div class="admin-card-body">
                                <p style="margin: 0; color: var(--gray-600); white-space: pre-line;"><?php echo sanitize($viewOrder['shipping_address']); ?></p>
                            </div>
                        </div>

                        <?php if ($viewOrder['prescription_file']): ?>
                            <div class="admin-card mb-3">
                                <div class="admin-card-header">
                                    <h2 class="admin-card-title">Prescription</h2>
                                </div>
                                <div class="admin-card-body">
                                    <a href="<?php echo getBaseUrl(); ?>/uploads/prescriptions/<?php echo sanitize($viewOrder['prescription_file']); ?>" target="_blank" class="btn btn-outline btn-block">
                                        View Prescription
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($viewOrder['notes']): ?>
                            <div class="admin-card">
                                <div class="admin-card-header">
                                    <h2 class="admin-card-title">Notes</h2>
                                </div>
                                <div class="admin-card-body">
                                    <p style="margin: 0; color: var(--gray-600);"><?php echo sanitize($viewOrder['notes']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                <!-- Orders List -->
                <div class="admin-header">
                    <h1 class="admin-title">Orders</h1>
                    <div class="d-flex gap-1">
                        <a href="?status=" class="btn btn-sm <?php echo !$statusFilter ? 'btn-primary' : 'btn-outline'; ?>">All</a>
                        <a href="?status=pending" class="btn btn-sm <?php echo $statusFilter === 'pending' ? 'btn-primary' : 'btn-outline'; ?>">Pending</a>
                        <a href="?status=processing" class="btn btn-sm <?php echo $statusFilter === 'processing' ? 'btn-primary' : 'btn-outline'; ?>">Processing</a>
                        <a href="?status=shipped" class="btn btn-sm <?php echo $statusFilter === 'shipped' ? 'btn-primary' : 'btn-outline'; ?>">Shipped</a>
                        <a href="?status=delivered" class="btn btn-sm <?php echo $statusFilter === 'delivered' ? 'btn-primary' : 'btn-outline'; ?>">Delivered</a>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr><td colspan="7" class="text-center">No orders found</td></tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td>
                                                <?php echo sanitize($order['first_name'] . ' ' . $order['last_name']); ?>
                                                <br><small style="color: var(--gray-500);"><?php echo sanitize($order['email']); ?></small>
                                            </td>
                                            <td><?php echo (int)$order['item_count']; ?></td>
                                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td><?php echo formatDate($order['created_at'], 'M d, Y'); ?></td>
                                            <td>
                                                <span class="badge <?php echo getStatusBadgeClass($order['status']); ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?view=<?php echo (int)$order['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="<?php echo getBaseUrl(); ?>/assets/js/main.js"></script>
</body>
</html>
