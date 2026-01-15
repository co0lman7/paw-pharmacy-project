<?php
/**
 * Checkout Page
 *
 * Handles order placement with shipping address and prescription upload
 */

$pageTitle = 'Checkout';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Require login
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to proceed with checkout.', 'warning');
    redirect(getBaseUrl() . '/pages/login.php?redirect=checkout');
}

// Get cart items
$cartItems = [];
$cartTotal = 0;
$hasPrescriptionItems = false;

try {
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, c.quantity, p.*, cat.name as category_name
        FROM cart c
        JOIN products p ON c.product_id = p.id
        JOIN categories cat ON p.category_id = cat.id
        WHERE c.user_id = ? AND p.is_active = 1
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cartItems = $stmt->fetchAll();

    foreach ($cartItems as $item) {
        $cartTotal += $item['price'] * $item['quantity'];
        if ($item['requires_prescription']) {
            $hasPrescriptionItems = true;
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching cart: " . $e->getMessage());
}

// Redirect if cart is empty
if (empty($cartItems)) {
    setFlashMessage('error', 'Your cart is empty.', 'warning');
    redirect(getBaseUrl() . '/pages/cart.php');
}

// Get user data
$user = getCurrentUser();

// Calculate shipping
$shippingCost = $cartTotal >= 50 ? 0 : 5.99;
$orderTotal = $cartTotal + $shippingCost;

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<nav class="breadcrumb">
    <a href="<?php echo getBaseUrl(); ?>/index.php">Home</a>
    <span class="breadcrumb-separator">/</span>
    <a href="<?php echo getBaseUrl(); ?>/pages/cart.php">Cart</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Checkout</span>
</nav>

<h1 class="section-title mb-3">Checkout</h1>

<?php if ($hasPrescriptionItems): ?>
    <div class="prescription-warning">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        <div class="prescription-warning-text">
            <strong>Prescription Upload Required</strong><br>
            Your order contains prescription medications. Please upload a valid prescription image below.
        </div>
    </div>
<?php endif; ?>

<form action="<?php echo getBaseUrl(); ?>/actions/order-actions.php" method="POST" enctype="multipart/form-data" id="checkout-form">
    <?php echo csrfField(); ?>

    <div class="checkout-grid">
        <!-- Checkout Form -->
        <div>
            <!-- Shipping Information -->
            <div class="checkout-section">
                <h2 class="checkout-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 0.5rem;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    Shipping Information
                </h2>

                <div class="form-group">
                    <label class="form-label" for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required
                           value="<?php echo sanitize($user['first_name'] . ' ' . $user['last_name']); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?php echo sanitize($user['email']); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required
                           value="<?php echo sanitize($user['phone'] ?? ''); ?>"
                           placeholder="+1 (555) 123-4567">
                </div>

                <div class="form-group">
                    <label class="form-label" for="address">Shipping Address *</label>
                    <textarea id="address" name="address" class="form-control" rows="3" required
                              placeholder="Street address, apartment, city, state, zip code"><?php echo sanitize($user['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group mb-0">
                    <label class="form-label" for="notes">Order Notes (Optional)</label>
                    <textarea id="notes" name="notes" class="form-control" rows="2"
                              placeholder="Special delivery instructions, gate codes, etc."></textarea>
                </div>
            </div>

            <!-- Prescription Upload (if required) -->
            <?php if ($hasPrescriptionItems): ?>
                <div class="checkout-section">
                    <h2 class="checkout-section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 0.5rem;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        Prescription Upload
                    </h2>

                    <p style="color: var(--gray-600); margin-bottom: 1rem;">
                        Please upload a clear image of your valid prescription from a licensed healthcare provider.
                    </p>

                    <div class="form-group mb-0">
                        <label class="form-label" for="prescription">Prescription Image *</label>
                        <input type="file" id="prescription" name="prescription" class="form-control" required
                               accept=".jpg,.jpeg,.png,.pdf">
                        <span class="form-text">Accepted formats: JPG, PNG, PDF. Maximum size: 5MB</span>
                    </div>

                    <div id="prescription-preview" style="margin-top: 1rem; display: none;">
                        <img src="" alt="Prescription preview" style="max-width: 200px; border-radius: 8px;">
                    </div>
                </div>
            <?php endif; ?>

            <!-- Payment Method -->
            <div class="checkout-section">
                <h2 class="checkout-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 0.5rem;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                    Payment Method
                </h2>

                <div class="alert alert-info mb-0">
                    <strong>Payment on Delivery</strong><br>
                    Pay with cash or card when your order arrives. Our delivery partner will process your payment securely.
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div>
            <div class="cart-summary" style="position: sticky; top: 140px;">
                <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">Order Summary</h3>

                <!-- Order Items -->
                <div style="max-height: 300px; overflow-y: auto; margin-bottom: 1rem;">
                    <?php foreach ($cartItems as $item): ?>
                        <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid var(--gray-200);">
                            <img src="<?php echo getProductImage($item['image']); ?>" alt="<?php echo sanitize($item['name']); ?>"
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                            <div style="flex: 1; min-width: 0;">
                                <p style="font-size: 0.875rem; font-weight: 500; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo sanitize($item['name']); ?>
                                    <?php if ($item['requires_prescription']): ?>
                                        <span class="badge badge-danger" style="font-size: 0.5rem;">Rx</span>
                                    <?php endif; ?>
                                </p>
                                <p style="font-size: 0.75rem; color: var(--gray-500); margin: 0;">
                                    Qty: <?php echo (int)$item['quantity']; ?> x <?php echo formatPrice($item['price']); ?>
                                </p>
                            </div>
                            <span style="font-weight: 500; font-size: 0.875rem;">
                                <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary-row">
                    <span>Subtotal</span>
                    <span><?php echo formatPrice($cartTotal); ?></span>
                </div>

                <div class="cart-summary-row">
                    <span>Shipping</span>
                    <span><?php echo $shippingCost > 0 ? formatPrice($shippingCost) : 'Free'; ?></span>
                </div>

                <div class="cart-summary-row cart-summary-total">
                    <span>Total</span>
                    <span><?php echo formatPrice($orderTotal); ?></span>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg mt-3" id="place-order-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    Place Order
                </button>

                <p style="font-size: 0.75rem; color: var(--gray-500); text-align: center; margin-top: 1rem;">
                    By placing your order, you agree to our Terms of Service and Privacy Policy.
                </p>
            </div>
        </div>
    </div>
</form>

<script>
// Prescription preview
document.getElementById('prescription')?.addEventListener('change', function(e) {
    const preview = document.getElementById('prescription-preview');
    const file = e.target.files[0];

    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.querySelector('img').src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});

// Form submission
document.getElementById('checkout-form').addEventListener('submit', function(e) {
    const btn = document.getElementById('place-order-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Processing...';
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
