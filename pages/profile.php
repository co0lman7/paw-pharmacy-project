<?php
/**
 * User Profile Page
 *
 * View and edit profile, order history
 */

$pageTitle = 'My Profile';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Require login
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to view your profile.', 'warning');
    redirect(getBaseUrl() . '/pages/login.php');
}

$user = getCurrentUser();
$activeTab = isset($_GET['tab']) ? sanitize($_GET['tab']) : 'profile';
$errors = [];
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid request. Please try again.';
    } else {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($firstName) || empty($lastName)) {
            $errors['general'] = 'First name and last name are required.';
        } else {
            try {
                $stmt = $pdo->prepare("
                    UPDATE users SET first_name = ?, last_name = ?, phone = ?, address = ?
                    WHERE id = ?
                ");
                $stmt->execute([$firstName, $lastName, $phone ?: null, $address ?: null, $_SESSION['user_id']]);

                $_SESSION['user_name'] = $firstName;
                $user = getCurrentUser(); // Refresh user data
                setFlashMessage('success', 'Profile updated successfully!', 'success');
                redirect(getBaseUrl() . '/pages/profile.php');
            } catch (PDOException $e) {
                error_log("Profile update error: " . $e->getMessage());
                $errors['general'] = 'An error occurred. Please try again.';
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['password'] = 'Invalid request. Please try again.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Verify current password
        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $userData = $stmt->fetch();

            if (!password_verify($currentPassword, $userData['password'])) {
                $errors['password'] = 'Current password is incorrect.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors['password'] = 'New passwords do not match.';
            } else {
                $passwordValidation = validatePassword($newPassword);
                if (!$passwordValidation['valid']) {
                    $errors['password'] = $passwordValidation['message'];
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashedPassword, $_SESSION['user_id']]);

                    setFlashMessage('success', 'Password changed successfully!', 'success');
                    redirect(getBaseUrl() . '/pages/profile.php?tab=security');
                }
            }
        } catch (PDOException $e) {
            error_log("Password change error: " . $e->getMessage());
            $errors['password'] = 'An error occurred. Please try again.';
        }
    }
    $activeTab = 'security';
}

// Fetch order history
$orders = [];
try {
    $stmt = $pdo->prepare("
        SELECT o.*, COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching orders: " . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<nav class="breadcrumb">
    <a href="<?php echo getBaseUrl(); ?>/index.php">Home</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">My Profile</span>
</nav>

<!-- Profile Header -->
<div class="profile-header">
    <div class="profile-avatar">
        <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
    </div>
    <div class="profile-info">
        <h1><?php echo sanitize($user['first_name'] . ' ' . $user['last_name']); ?></h1>
        <p><?php echo sanitize($user['email']); ?></p>
    </div>
</div>

<!-- Profile Tabs -->
<div class="profile-tabs">
    <a href="?tab=profile" class="profile-tab <?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 0.25rem;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        Profile
    </a>
    <a href="?tab=orders" class="profile-tab <?php echo $activeTab === 'orders' ? 'active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 0.25rem;"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
        Orders
    </a>
    <a href="?tab=security" class="profile-tab <?php echo $activeTab === 'security' ? 'active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 0.25rem;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        Security
    </a>
</div>

<?php if ($activeTab === 'profile'): ?>
    <!-- Profile Tab -->
    <div class="card">
        <div class="card-body">
            <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Personal Information</h2>

            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-error"><?php echo sanitize($errors['general']); ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <?php echo csrfField(); ?>
                <input type="hidden" name="update_profile" value="1">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required
                               value="<?php echo sanitize($user['first_name']); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required
                               value="<?php echo sanitize($user['last_name']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" class="form-control" disabled
                           value="<?php echo sanitize($user['email']); ?>">
                    <span class="form-text">Email cannot be changed</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                           value="<?php echo sanitize($user['phone'] ?? ''); ?>"
                           placeholder="+1 (555) 123-4567">
                </div>

                <div class="form-group">
                    <label class="form-label" for="address">Shipping Address</label>
                    <textarea id="address" name="address" class="form-control" rows="3"
                              placeholder="Your default shipping address"><?php echo sanitize($user['address'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

<?php elseif ($activeTab === 'orders'): ?>
    <!-- Orders Tab -->
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Order History</h2>

    <?php if (empty($orders)): ?>
        <div class="card">
            <div class="card-body text-center" style="padding: 3rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--gray-400); margin-bottom: 1rem;"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                <h3 style="font-size: 1.125rem; color: var(--gray-700); margin-bottom: 0.5rem;">No orders yet</h3>
                <p style="color: var(--gray-500); margin-bottom: 1rem;">Start shopping to see your orders here.</p>
                <a href="<?php echo getBaseUrl(); ?>/pages/products.php" class="btn btn-primary">Browse Products</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <span class="order-number">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                        <span class="order-date"><?php echo formatDate($order['created_at'], 'M d, Y \a\t h:i A'); ?></span>
                    </div>
                    <span class="badge <?php echo getStatusBadgeClass($order['status']); ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </div>
                <div class="order-body">
                    <?php
                    // Fetch order items
                    try {
                        $itemStmt = $pdo->prepare("
                            SELECT oi.*, p.name, p.slug
                            FROM order_items oi
                            JOIN products p ON oi.product_id = p.id
                            WHERE oi.order_id = ?
                        ");
                        $itemStmt->execute([$order['id']]);
                        $orderItems = $itemStmt->fetchAll();
                    } catch (PDOException $e) {
                        $orderItems = [];
                    }
                    ?>
                    <div class="order-items">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="order-item">
                                <span>
                                    <a href="<?php echo getBaseUrl(); ?>/pages/product-detail.php?slug=<?php echo sanitize($item['slug']); ?>">
                                        <?php echo sanitize($item['name']); ?>
                                    </a>
                                    x <?php echo (int)$item['quantity']; ?>
                                </span>
                                <span><?php echo formatPrice($item['unit_price'] * $item['quantity']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="order-footer">
                    <span class="order-total">
                        Total: <span><?php echo formatPrice($order['total_amount']); ?></span>
                    </span>
                    <span style="font-size: 0.875rem; color: var(--gray-600);">
                        <?php echo (int)$order['item_count']; ?> item(s)
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

<?php elseif ($activeTab === 'security'): ?>
    <!-- Security Tab -->
    <div class="card">
        <div class="card-body">
            <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Change Password</h2>

            <?php if (isset($errors['password'])): ?>
                <div class="alert alert-error"><?php echo sanitize($errors['password']); ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <?php echo csrfField(); ?>
                <input type="hidden" name="change_password" value="1">

                <div class="form-group">
                    <label class="form-label" for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                    <span class="form-text">At least 8 characters with uppercase, lowercase, and number</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Account Information</h2>
            <p style="color: var(--gray-600); margin-bottom: 0.5rem;">
                <strong>Account Type:</strong> <?php echo ucfirst($user['role']); ?>
            </p>
            <p style="color: var(--gray-600); margin: 0;">
                <strong>Member Since:</strong> <?php echo formatDate($user['created_at'] ?? date('Y-m-d'), 'F Y'); ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
