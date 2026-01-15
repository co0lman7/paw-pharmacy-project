<?php
/**
 * Search Results Page
 *
 * Displays search results based on product name and description
 */

$pageTitle = 'Search Results';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get search query
$query = isset($_GET['q']) ? trim(sanitize($_GET['q'])) : '';

$products = [];
$totalResults = 0;

if ($query && strlen($query) >= 2) {
    $pageTitle = "Search: $query";

    try {
        // Search in product name and description
        $searchTerm = '%' . $query . '%';
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1
            AND (p.name LIKE ? OR p.description LIKE ?)
            ORDER BY
                CASE
                    WHEN p.name LIKE ? THEN 1
                    ELSE 2
                END,
                p.name ASC
            LIMIT 50
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $products = $stmt->fetchAll();
        $totalResults = count($products);
    } catch (PDOException $e) {
        error_log("Search error: " . $e->getMessage());
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<nav class="breadcrumb">
    <a href="<?php echo getBaseUrl(); ?>/index.php">Home</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Search Results</span>
</nav>

<div class="search-results-header">
    <?php if ($query): ?>
        <h1 class="section-title">
            Search results for "<span class="search-query"><?php echo sanitize($query); ?></span>"
        </h1>
        <p class="text-muted"><?php echo $totalResults; ?> product(s) found</p>
    <?php else: ?>
        <h1 class="section-title">Search Products</h1>
        <p class="text-muted">Enter a search term to find products</p>
    <?php endif; ?>
</div>

<?php if ($query && strlen($query) < 2): ?>
    <div class="alert alert-warning">
        Please enter at least 2 characters to search.
    </div>
<?php elseif ($query && empty($products)): ?>
    <div class="no-results">
        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--gray-400); margin-bottom: 1rem;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <h3>No products found</h3>
        <p>We couldn't find any products matching "<?php echo sanitize($query); ?>".</p>
        <p>Try checking your spelling or use more general terms.</p>
        <a href="<?php echo getBaseUrl(); ?>/pages/products.php" class="btn btn-primary mt-2">Browse All Products</a>
    </div>
<?php elseif (!empty($products)): ?>
    <div class="products-grid">
        <?php foreach ($products as $product): ?>
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
<?php else: ?>
    <!-- Show popular products when no search query -->
    <div class="mt-4">
        <h2 class="section-title">Popular Products</h2>
        <?php
        try {
            $popularStmt = $pdo->query("
                SELECT p.*, c.name as category_name
                FROM products p
                JOIN categories c ON p.category_id = c.id
                WHERE p.is_active = 1
                ORDER BY RAND()
                LIMIT 8
            ");
            $popularProducts = $popularStmt->fetchAll();
        } catch (PDOException $e) {
            $popularProducts = [];
        }
        ?>

        <?php if (!empty($popularProducts)): ?>
            <div class="products-grid">
                <?php foreach ($popularProducts as $product): ?>
                    <article class="product-card">
                        <a href="<?php echo getBaseUrl(); ?>/pages/product-detail.php?slug=<?php echo sanitize($product['slug']); ?>" class="product-image">
                            <img src="<?php echo getProductImage($product['image']); ?>" alt="<?php echo sanitize($product['name']); ?>">
                        </a>
                        <div class="product-info">
                            <span class="product-category"><?php echo sanitize($product['category_name']); ?></span>
                            <h3 class="product-name">
                                <a href="<?php echo getBaseUrl(); ?>/pages/product-detail.php?slug=<?php echo sanitize($product['slug']); ?>">
                                    <?php echo sanitize($product['name']); ?>
                                </a>
                            </h3>
                            <div class="product-footer">
                                <span class="product-price"><?php echo formatPrice($product['price']); ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
