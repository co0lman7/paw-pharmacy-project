<?php
/**
 * Product Detail Page
 *
 * Displays single product with full details, images, and add to cart
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get product slug from URL
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (!$slug) {
    setFlashMessage('error', 'Product not found.', 'error');
    redirect(getBaseUrl() . '/pages/products.php');
}

// Fetch product details
try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.slug = ? AND p.is_active = 1
    ");
    $stmt->execute([$slug]);
    $product = $stmt->fetch();

    if (!$product) {
        setFlashMessage('error', 'Product not found.', 'error');
        redirect(getBaseUrl() . '/pages/products.php');
    }
} catch (PDOException $e) {
    error_log("Error fetching product: " . $e->getMessage());
    setFlashMessage('error', 'Error loading product.', 'error');
    redirect(getBaseUrl() . '/pages/products.php');
}

$pageTitle = $product['name'];

// Fetch related products (same category, excluding current)
try {
    $relatedStmt = $pdo->prepare("
        SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1
        ORDER BY RAND()
        LIMIT 4
    ");
    $relatedStmt->execute([$product['category_id'], $product['id']]);
    $relatedProducts = $relatedStmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching related products: " . $e->getMessage());
    $relatedProducts = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<nav class="breadcrumb">
    <a href="<?php echo getBaseUrl(); ?>/index.php">Home</a>
    <span class="breadcrumb-separator">/</span>
    <a href="<?php echo getBaseUrl(); ?>/pages/products.php">Products</a>
    <span class="breadcrumb-separator">/</span>
    <a href="<?php echo getBaseUrl(); ?>/pages/products.php?category=<?php echo sanitize($product['category_slug']); ?>">
        <?php echo sanitize($product['category_name']); ?>
    </a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current"><?php echo sanitize($product['name']); ?></span>
</nav>

<!-- Product Detail -->
<div class="product-detail">
    <!-- Product Gallery -->
    <div class="product-gallery">
        <img src="<?php echo getProductImage($product['image']); ?>" alt="<?php echo sanitize($product['name']); ?>">
    </div>

    <!-- Product Info -->
    <div class="product-detail-info">
        <a href="<?php echo getBaseUrl(); ?>/pages/products.php?category=<?php echo sanitize($product['category_slug']); ?>" class="product-detail-category">
            <?php echo sanitize($product['category_name']); ?>
        </a>

        <h1 class="product-detail-name"><?php echo sanitize($product['name']); ?></h1>

        <?php if ($product['requires_prescription']): ?>
            <div class="prescription-warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                <div class="prescription-warning-text">
                    <strong>Prescription Required</strong><br>
                    This medication requires a valid prescription from a licensed healthcare provider. You will need to upload your prescription during checkout.
                </div>
            </div>
        <?php endif; ?>

        <div class="product-detail-price"><?php echo formatPrice($product['price']); ?></div>

        <p class="product-detail-description"><?php echo nl2br(sanitize($product['description'])); ?></p>

        <!-- Product Meta -->
        <div class="product-detail-meta">
            <div class="product-meta-item">
                <span class="product-meta-label">Availability:</span>
                <span class="product-meta-value">
                    <?php if ($product['stock_quantity'] > 10): ?>
                        <span class="badge badge-success">In Stock (<?php echo (int)$product['stock_quantity']; ?> available)</span>
                    <?php elseif ($product['stock_quantity'] > 0): ?>
                        <span class="badge badge-warning">Low Stock (<?php echo (int)$product['stock_quantity']; ?> left)</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Out of Stock</span>
                    <?php endif; ?>
                </span>
            </div>

            <div class="product-meta-item">
                <span class="product-meta-label">SKU:</span>
                <span class="product-meta-value">PHM-<?php echo str_pad($product['id'], 5, '0', STR_PAD_LEFT); ?></span>
            </div>

            <div class="product-meta-item">
                <span class="product-meta-label">Category:</span>
                <span class="product-meta-value">
                    <a href="<?php echo getBaseUrl(); ?>/pages/products.php?category=<?php echo sanitize($product['category_slug']); ?>">
                        <?php echo sanitize($product['category_name']); ?>
                    </a>
                </span>
            </div>

            <?php if ($product['requires_prescription']): ?>
                <div class="product-meta-item">
                    <span class="product-meta-label">Type:</span>
                    <span class="product-meta-value">
                        <span class="badge badge-danger">Prescription Medicine</span>
                    </span>
                </div>
            <?php else: ?>
                <div class="product-meta-item">
                    <span class="product-meta-label">Type:</span>
                    <span class="product-meta-value">
                        <span class="badge badge-success">Over-the-Counter (OTC)</span>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Dosage Information -->
        <?php if ($product['dosage_info']): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 0.25rem;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        Dosage Information
                    </h3>
                    <p style="color: var(--gray-600); font-size: 0.9375rem; margin: 0;"><?php echo nl2br(sanitize($product['dosage_info'])); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Add to Cart -->
        <?php if ($product['stock_quantity'] > 0): ?>
            <div class="product-actions">
                <div class="product-quantity">
                    <label for="quantity">Quantity:</label>
                    <div class="cart-item-quantity">
                        <button type="button" class="quantity-btn" onclick="decreaseQuantity()">-</button>
                        <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="<?php echo (int)$product['stock_quantity']; ?>">
                        <button type="button" class="quantity-btn" onclick="increaseQuantity(<?php echo (int)$product['stock_quantity']; ?>)">+</button>
                    </div>
                </div>

                <button class="btn btn-primary btn-lg add-to-cart-btn"
                        data-product-id="<?php echo (int)$product['id']; ?>"
                        <?php echo $product['requires_prescription'] ? 'data-prescription="true"' : ''; ?>
                        id="add-to-cart-detail">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    Add to Cart
                </button>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <strong>Out of Stock</strong> - This product is currently unavailable. Please check back later.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Related Products -->
<?php if (!empty($relatedProducts)): ?>
    <section class="related-products">
        <div class="section-header">
            <h2 class="section-title">Related Products</h2>
            <a href="<?php echo getBaseUrl(); ?>/pages/products.php?category=<?php echo sanitize($product['category_slug']); ?>" class="section-link">
                View All in <?php echo sanitize($product['category_name']); ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
        </div>

        <div class="products-grid">
            <?php foreach ($relatedProducts as $related): ?>
                <article class="product-card">
                    <a href="<?php echo getBaseUrl(); ?>/pages/product-detail.php?slug=<?php echo sanitize($related['slug']); ?>" class="product-image">
                        <img src="<?php echo getProductImage($related['image']); ?>" alt="<?php echo sanitize($related['name']); ?>">
                        <?php if ($related['requires_prescription']): ?>
                            <span class="product-badge badge-prescription">Rx Required</span>
                        <?php endif; ?>
                    </a>
                    <div class="product-info">
                        <span class="product-category"><?php echo sanitize($related['category_name']); ?></span>
                        <h3 class="product-name">
                            <a href="<?php echo getBaseUrl(); ?>/pages/product-detail.php?slug=<?php echo sanitize($related['slug']); ?>">
                                <?php echo sanitize($related['name']); ?>
                            </a>
                        </h3>
                        <div class="product-footer">
                            <span class="product-price"><?php echo formatPrice($related['price']); ?></span>
                            <?php if ($related['stock_quantity'] > 0): ?>
                                <span class="product-stock stock-in">In Stock</span>
                            <?php else: ?>
                                <span class="product-stock stock-out">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<script>
function decreaseQuantity() {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
    }
}

function increaseQuantity(max) {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    if (currentValue < max) {
        input.value = currentValue + 1;
    }
}

// Override add to cart for detail page to use quantity input
document.getElementById('add-to-cart-detail')?.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();

    const productId = this.dataset.productId;
    const quantity = document.getElementById('quantity').value;
    const isPrescription = this.dataset.prescription === 'true';

    if (isPrescription) {
        if (!confirm('This product requires a prescription. You will need to upload your prescription during checkout. Continue?')) {
            return;
        }
    }

    addToCart(productId, quantity);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
