<?php
/**
 * Shopping Cart Page
 *
 * Displays cart items with quantity update and checkout link
 */

$pageTitle = 'Shopping Cart';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get cart items
$cartItems = [];
$cartTotal = 0;
$hasPrescriptionItems = false;

try {
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("
            SELECT c.id as cart_id, c.quantity, p.*, cat.name as category_name
            FROM cart c
            JOIN products p ON c.product_id = p.id
            JOIN categories cat ON p.category_id = cat.id
            WHERE c.user_id = ? AND p.is_active = 1
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $sessionId = session_id();
        $stmt = $pdo->prepare("
            SELECT c.id as cart_id, c.quantity, p.*, cat.name as category_name
            FROM cart c
            JOIN products p ON c.product_id = p.id
            JOIN categories cat ON p.category_id = cat.id
            WHERE c.session_id = ? AND p.is_active = 1
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$sessionId]);
    }

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

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<nav class="breadcrumb">
    <a href="<?php echo getBaseUrl(); ?>/index.php">Home</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Shopping Cart</span>
</nav>

<h1 class="section-title mb-3">Shopping Cart</h1>

<?php if (empty($cartItems)): ?>
    <div class="empty-cart">
        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added any products to your cart yet.</p>
        <a href="<?php echo getBaseUrl(); ?>/pages/products.php" class="btn btn-primary">Start Shopping</a>
    </div>
<?php else: ?>
    <?php if ($hasPrescriptionItems): ?>
        <div class="prescription-warning">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            <div class="prescription-warning-text">
                <strong>Prescription Required</strong><br>
                Your cart contains prescription medications. You will need to upload a valid prescription from a licensed healthcare provider during checkout.
            </div>
        </div>
    <?php endif; ?>

    <div class="checkout-grid">
        <!-- Cart Items -->
        <div class="card">
            <div class="card-body">
                <div class="cart-items" id="cart-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" data-cart-id="<?php echo (int)$item['cart_id']; ?>">
                            <a href="<?php echo getBaseUrl(); ?>/pages/product-detail.php?slug=<?php echo sanitize($item['slug']); ?>" class="cart-item-image">
                                <img src="<?php echo getProductImage($item['image']); ?>" alt="<?php echo sanitize($item['name']); ?>">
                            </a>

                            <div class="cart-item-info">
                                <h3 class="cart-item-name">
                                    <a href="<?php echo getBaseUrl(); ?>/pages/product-detail.php?slug=<?php echo sanitize($item['slug']); ?>">
                                        <?php echo sanitize($item['name']); ?>
                                    </a>
                                    <?php if ($item['requires_prescription']): ?>
                                        <span class="badge badge-danger" style="font-size: 0.625rem; margin-left: 0.5rem;">Rx</span>
                                    <?php endif; ?>
                                </h3>
                                <p class="cart-item-price"><?php echo formatPrice($item['price']); ?> each</p>
                            </div>

                            <div class="cart-item-quantity">
                                <button type="button" class="quantity-btn" onclick="updateCartQuantity(<?php echo (int)$item['cart_id']; ?>, <?php echo (int)$item['quantity'] - 1; ?>)">-</button>
                                <input type="number" class="quantity-input" value="<?php echo (int)$item['quantity']; ?>"
                                       min="1" max="<?php echo (int)$item['stock_quantity']; ?>"
                                       onchange="updateCartQuantity(<?php echo (int)$item['cart_id']; ?>, this.value)">
                                <button type="button" class="quantity-btn" onclick="updateCartQuantity(<?php echo (int)$item['cart_id']; ?>, <?php echo (int)$item['quantity'] + 1; ?>)">+</button>
                            </div>

                            <div class="cart-item-total">
                                <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                            </div>

                            <button type="button" class="cart-item-remove" onclick="removeFromCart(<?php echo (int)$item['cart_id']; ?>)" title="Remove item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-3">
                    <a href="<?php echo getBaseUrl(); ?>/pages/products.php" class="btn btn-outline">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>

        <!-- Cart Summary -->
        <div>
            <div class="cart-summary">
                <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">Order Summary</h3>

                <div class="cart-summary-row">
                    <span>Subtotal (<?php echo count($cartItems); ?> items)</span>
                    <span id="cart-subtotal"><?php echo formatPrice($cartTotal); ?></span>
                </div>

                <div class="cart-summary-row">
                    <span>Shipping</span>
                    <span><?php echo $cartTotal >= 50 ? 'Free' : formatPrice(5.99); ?></span>
                </div>

                <?php if ($cartTotal < 50): ?>
                    <div class="alert alert-info" style="margin-top: 1rem; margin-bottom: 0;">
                        Add <?php echo formatPrice(50 - $cartTotal); ?> more for free shipping!
                    </div>
                <?php endif; ?>

                <div class="cart-summary-row cart-summary-total">
                    <span>Total</span>
                    <span id="cart-total"><?php echo formatPrice($cartTotal + ($cartTotal >= 50 ? 0 : 5.99)); ?></span>
                </div>

                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo getBaseUrl(); ?>/pages/checkout.php" class="btn btn-primary btn-block btn-lg mt-3">
                        Proceed to Checkout
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                <?php else: ?>
                    <a href="<?php echo getBaseUrl(); ?>/pages/login.php?redirect=checkout" class="btn btn-primary btn-block btn-lg mt-3">
                        Login to Checkout
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                    <p class="text-center mt-2" style="font-size: 0.875rem; color: var(--gray-600);">
                        Don't have an account? <a href="<?php echo getBaseUrl(); ?>/pages/register.php">Sign up</a>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Accepted Payments -->
            <div class="card mt-3">
                <div class="card-body text-center">
                    <p style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 0.5rem;">We accept</p>
                    <div class="d-flex justify-center gap-2" style="justify-content: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="24" viewBox="0 0 40 24" fill="#1A1F71"><rect width="40" height="24" rx="4" fill="#1A1F71"/><text x="20" y="16" fill="white" font-size="8" text-anchor="middle" font-weight="bold">VISA</text></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="24" viewBox="0 0 40 24" fill="#EB001B"><rect width="40" height="24" rx="4" fill="#333"/><circle cx="15" cy="12" r="7" fill="#EB001B"/><circle cx="25" cy="12" r="7" fill="#F79E1B"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="24" viewBox="0 0 40 24"><rect width="40" height="24" rx="4" fill="#003087"/><text x="20" y="16" fill="white" font-size="6" text-anchor="middle" font-weight="bold">PayPal</text></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
