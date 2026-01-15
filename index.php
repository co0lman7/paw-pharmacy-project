<?php
/**
 * Homepage - PharmaCare Pharmacy
 *
 * Displays featured products, categories, and hero banner
 */

$pageTitle = 'Your Trusted Online Pharmacy';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Fetch featured products (8 random active products)
try {
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name, c.slug as category_slug
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1
        ORDER BY RAND()
        LIMIT 8
    ");
    $featuredProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching featured products: " . $e->getMessage());
    $featuredProducts = [];
}

// Fetch all categories with product count
try {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
        GROUP BY c.id
        ORDER BY c.name
    ");
    $categoriesWithCount = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categoriesWithCount = [];
}

// Category icons mapping
$categoryIcons = [
    'pain-relief' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>',
    'cold-flu' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"></path></svg>',
    'vitamins-supplements' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"></path><path d="m8.5 8.5 7 7"></path></svg>',
    'first-aid' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect width="16" height="14" x="4" y="6" rx="2"></rect><path d="M9 14h6"></path><path d="M12 11v6"></path></svg>',
    'personal-care' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>',
    'prescription' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>'
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Your Health, Our Priority</h1>
        <p>Shop quality medications, vitamins, and healthcare products from the comfort of your home. Fast delivery, trusted brands, and expert care.</p>
        <div class="hero-buttons">
            <a href="<?php echo getBaseUrl(); ?>/pages/products.php" class="btn btn-primary">Shop Now</a>
            <a href="<?php echo getBaseUrl(); ?>/pages/products.php?category=prescription" class="btn btn-outline">Prescription Medicines</a>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="mb-4">
    <div class="section-header">
        <h2 class="section-title">Shop by Category</h2>
        <a href="<?php echo getBaseUrl(); ?>/pages/products.php" class="section-link">
            View All Products
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
    </div>

    <div class="categories-grid">
        <?php foreach ($categoriesWithCount as $category): ?>
            <a href="<?php echo getBaseUrl(); ?>/pages/products.php?category=<?php echo sanitize($category['slug']); ?>" class="category-card">
                <div class="category-icon">
                    <?php echo $categoryIcons[$category['slug']] ?? '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>'; ?>
                </div>
                <h3><?php echo sanitize($category['name']); ?></h3>
                <p class="text-muted"><?php echo (int)$category['product_count']; ?> products</p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Featured Products Section -->
<section class="mb-4">
    <div class="section-header">
        <h2 class="section-title">Featured Products</h2>
        <a href="<?php echo getBaseUrl(); ?>/pages/products.php" class="section-link">
            View All
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
    </div>

    <div class="products-grid">
        <?php foreach ($featuredProducts as $product): ?>
            <article class="product-card">
                <a href="<?php echo getBaseUrl(); ?>/pages/product-detail.php?slug=<?php echo sanitize($product['slug']); ?>" class="product-image">
                    <img src="<?php echo getProductImage($product['image']); ?>" alt="<?php echo sanitize($product['name']); ?>">
                    <?php if ($product['requires_prescription']): ?>
                        <span class="product-badge badge-prescription">Rx Required</span>
                    <?php endif; ?>
                </a>
                <div class="product-info">
                    <span class="product-category"><?php echo sanitize($product['category_name']); ?></span>
                    <h3 class="product-name">
                        <a href="<?php echo getBaseUrl(); ?>/pages/product-detail.php?slug=<?php echo sanitize($product['slug']); ?>">
                            <?php echo sanitize($product['name']); ?>
                        </a>
                    </h3>
                    <p class="product-description"><?php echo sanitize(truncateText($product['description'], 80)); ?></p>
                    <div class="product-footer">
                        <span class="product-price"><?php echo formatPrice($product['price']); ?></span>
                        <?php if ($product['stock_quantity'] > 10): ?>
                            <span class="product-stock stock-in">In Stock</span>
                        <?php elseif ($product['stock_quantity'] > 0): ?>
                            <span class="product-stock stock-low">Low Stock</span>
                        <?php else: ?>
                            <span class="product-stock stock-out">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2">
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <button class="btn btn-primary btn-block add-to-cart-btn"
                                    data-product-id="<?php echo (int)$product['id']; ?>"
                                    <?php echo $product['requires_prescription'] ? 'data-prescription="true"' : ''; ?>>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                                Add to Cart
                            </button>
                        <?php else: ?>
                            <button class="btn btn-outline btn-block" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<!-- Info Banners -->
<section class="mb-4">
    <div class="categories-grid">
        <div class="category-card">
            <div class="category-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
            </div>
            <h3>Free Delivery</h3>
            <p class="text-muted">On orders over $50</p>
        </div>

        <div class="category-card">
            <div class="category-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            </div>
            <h3>Secure Payment</h3>
            <p class="text-muted">100% secure checkout</p>
        </div>

        <div class="category-card">
            <div class="category-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            <h3>Quality Products</h3>
            <p class="text-muted">Authentic & certified</p>
        </div>

        <div class="category-card">
            <div class="category-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            </div>
            <h3>24/7 Support</h3>
            <p class="text-muted">Always here to help</p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
