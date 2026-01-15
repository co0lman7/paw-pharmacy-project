<?php
/**
 * Admin Users Management
 *
 * View and manage users
 */

$pageTitle = 'Manage Users';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin access
if (!isAdmin()) {
    setFlashMessage('error', 'Access denied.', 'error');
    redirect(getBaseUrl() . '/index.php');
}

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request.', 'error');
    } else {
        $userId = (int)($_POST['user_id'] ?? 0);
        $newRole = sanitize($_POST['role'] ?? '');

        // Don't allow changing own role
        if ($userId === $_SESSION['user_id']) {
            setFlashMessage('error', 'Cannot change your own role.', 'error');
        } elseif ($userId && in_array($newRole, ['customer', 'admin'])) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$newRole, $userId]);
                setFlashMessage('success', 'User role updated.', 'success');
            } catch (PDOException $e) {
                setFlashMessage('error', 'Error updating role.', 'error');
            }
        }
    }
    redirect(getBaseUrl() . '/admin/users.php');
}

// Fetch users
$roleFilter = isset($_GET['role']) ? sanitize($_GET['role']) : '';

try {
    $sql = "SELECT u.*, COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount), 0) as total_spent
            FROM users u
            LEFT JOIN orders o ON u.id = o.user_id AND o.status != 'cancelled'";

    if ($roleFilter && in_array($roleFilter, ['customer', 'admin'])) {
        $sql .= " WHERE u.role = ?";
    }

    $sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

    $stmt = $pdo->prepare($sql);
    if ($roleFilter && in_array($roleFilter, ['customer', 'admin'])) {
        $stmt->execute([$roleFilter]);
    } else {
        $stmt->execute();
    }
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
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
            <div class="admin-header">
                <h1 class="admin-title">Users</h1>
                <div class="d-flex gap-1">
                    <a href="?role=" class="btn btn-sm <?php echo !$roleFilter ? 'btn-primary' : 'btn-outline'; ?>">All</a>
                    <a href="?role=customer" class="btn btn-sm <?php echo $roleFilter === 'customer' ? 'btn-primary' : 'btn-outline'; ?>">Customers</a>
                    <a href="?role=admin" class="btn btn-sm <?php echo $roleFilter === 'admin' ? 'btn-primary' : 'btn-outline'; ?>">Admins</a>
                </div>
            </div>

            <?php echo displayFlashMessages(); ?>

            <div class="admin-card">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="7" class="text-center">No users found</td></tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem;">
                                                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                                </div>
                                                <span><?php echo sanitize($user['first_name'] . ' ' . $user['last_name']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo sanitize($user['email']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-primary' : 'badge-secondary'; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo (int)$user['order_count']; ?></td>
                                        <td><?php echo formatPrice($user['total_spent']); ?></td>
                                        <td><?php echo formatDate($user['created_at'], 'M d, Y'); ?></td>
                                        <td>
                                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                <form action="" method="POST" style="display: inline;">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="update_role" value="1">
                                                    <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                                    <select name="role" class="form-control" style="width: auto; display: inline-block; padding: 0.25rem 0.5rem; font-size: 0.875rem;" onchange="this.form.submit()">
                                                        <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                    </select>
                                                </form>
                                            <?php else: ?>
                                                <span style="color: var(--gray-500); font-size: 0.875rem;">(You)</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="<?php echo getBaseUrl(); ?>/assets/js/main.js"></script>
</body>
</html>
