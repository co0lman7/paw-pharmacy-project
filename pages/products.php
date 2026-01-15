<?php
/**
 * Product Listing Page
 *
 * Displays all products with filtering, sorting, and pagination
 */

$pageTitle = 'Products';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Pagination settings
$perPage = 12;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Filter parameters
$categorySlug = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$priceMin = isset($_GET['price_min']) ? (float)$_GET['price_min'] : 0;
$priceMax = isset($_GET['price_max']) ? (float)$_GET['price_max'] : 0;
$prescriptionFilter = isset($_GET['prescription']) ? $_GET['prescription'] : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Build query conditions
$conditions = ['p.is_active = 1'];
$params = [];

// Category filter
if ($categorySlug) {
    $conditions[] = 'c.slug = ?';
    $params[] = $categorySlug;
}

// Price filter
if ($priceMin > 0) {
    $conditions[] = 'p.price >= ?';
    $params[] = $priceMin;
}
if ($priceMax > 0) {
    $conditions[] = 'p.price <= ?';
    $params[] = $priceMax;
}

// Prescription filter
if ($prescriptionFilter === 'yes') {
    $conditions[] = 'p.requires_prescription = 1';
} elseif ($prescriptionFilter === 'no') {
    $conditions[] = 'p.requires_prescription = 0';
}

// Sort order
$orderBy = match($sort) {
    'price_low' => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'name_az' => 'p.name ASC',
    'name_za' => 'p.name DESC',
    default => 'p.created_at DESC'
};

$whereClause = implode(' AND ', $conditions);

// Get total count for pagination
try {
    $countSql = "SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id WHERE $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalProducts = $countStmt->fetchColumn();
    $totalPages = ceil($totalProducts / $perPage);
} catch (PDOException $e) {
    error_log("Error counting products: " . $e->getMessage());
    $totalProducts = 0;
    $totalPages = 1;
}

// Fetch products
try {
    $sql = "
        SELECT p.*, c.name as category_name, c.slug as category_slug
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching products: " . $e->getMessage());
    $products = [];
}

// Get current category info if filtered
$currentCategory = null;
if ($categorySlug) {
    try {
        $catStmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
        $catStmt->execute([$categorySlug]);
        $currentCategory = $catStmt->fetch();
        if ($currentCategory) {
            $pageTitle = $currentCategory['name'];
        }
    } catch (PDOException $e) {
        error_log("Error fetching category: " . $e->getMessage());
    }
}

// Fetch all categories for filter sidebar
$allCategories = getCategories();

require_once __DIR__ . '/../includes/header.php';

// Build query string for pagination links
function buildQueryString($params, $exclude = []) {
    $query = [];
    foreach ($params as $key => $value) {
        if (!in_array($key, $exclude) && $value !== '' && $value !== null) {
            $query[$key] = $value;
        }
    }
    return http_build_query($query);
}

$queryParams = [
    'category' => $categorySlug,
    'price_min' => $priceMin ?: '',
    'price_max' => $priceMax ?: '',
    'prescription' => $prescriptionFilter,
    'sort' => $sort
];
?>

<!-- Breadcrumb -->
<nav class="breadcrumb">
    <a href="<?php echo getBaseUrl(); ?>/index.php">Home</a>
    <span class="breadcrumb-separator">/</span>
    <?php if ($currentCategory): ?>
        <a href="<?php echo getBaseUrl(); ?>/pages/products.php">Products</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current"><?php echo sanitize($currentCategory['name']); ?></span>
    <?php else: ?>
        <span class="breadcrumb-current">All Products</span>
    <?php endif; ?>
</nav>

<div class="page-with-sidebar">
    <!-- Sidebar Filters -->
    <aside class="sidebar">
        <form action="" method="GET" id="filter-form">
            <!-- Preserve sort parameter -->
            <input type="hidden" name="sort" value="<?php echo sanitize($sort); ?>">

            <!-- Categories -->
            <div class="sidebar-section">
                <h3 class="sidebar-title">Categories</h3>
                <div class="filter-list">
                    <div class="filter-item">
                        <input type="radio" name="category" id="cat-all" value="" <?php echo !$categorySlug ? 'checked' : ''; ?>>
                        <label for="cat-all">All Categories</label>
                    </div>
                    <?php foreach ($allCategories as $cat): ?>
                        <div class="filter-item">
                            <input type="radio" name="category" id="cat-<?php echo sanitize($cat['slug']); ?>"
                                   value="<?php echo sanitize($cat['slug']); ?>"
                                   <?php echo $categorySlug === $cat['slug'] ? 'checked' : ''; ?>>
                            <label for="cat-<?php echo sanitize($cat['slug']); ?>"><?php echo sanitize($cat['name']); ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Price Range -->
            <div class="sidebar-section">
                <h3 class="sidebar-title">Price Range</h3>
                <div class="price-range">
                    <input type="number" name="price_min" placeholder="Min" min="0" step="0.01"
                           value="<?php echo $priceMin ?: ''; ?>">
                    <span>-</span>
                    <input type="number" name="price_max" placeholder="Max" min="0" step="0.01"
                           value="<?php echo $priceMax ?: ''; ?>">
                </div>
            </div>

            <!-- Prescription Filter -->
            <div class="sidebar-section">
                <h3 class="sidebar-title">Product Type</h3>
                <div class="filter-list">
                    <div class="filter-item">
                        <input type="radio" name="prescription" id="pres-all" value="" <?php echo !$prescriptionFilter ? 'checked' : ''; ?>>
                        <label for="pres-all">All Products</label>
                    </div>
                    <div class="filter-item">
                        <input type="radio" name="prescription" id="pres-no" value="no" <?php echo $prescriptionFilter === 'no' ? 'checked' : ''; ?>>
                        <label for="pres-no">Over-the-Counter</label>
                    </div>
                    <div class="filter-item">
                        <input type="radio" name="prescription" id="pres-yes" value="yes" <?php echo $prescriptionFilter === 'yes' ? 'checked' : ''; ?>>
                        <label for="pres-yes">Prescription Required</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>

            <?php if ($categorySlug || $priceMin || $priceMax || $prescriptionFilter): ?>
                <a href="<?php echo getBaseUrl(); ?>/pages/products.php" class="btn btn-outline btn-block mt-2">Clear Filters</a>
            <?php endif; ?>
        </form>
    </aside>

    <!-- Products Content -->
    <div class="products-content">
        <?php if ($currentCategory): ?>
            <div class="mb-3">
                <h1 class="section-title"><?php echo sanitize($currentCategory['name']); ?></h1>
                <?php if ($currentCategory['description']): ?>
                    <p class="text-muted"><?php echo sanitize($currentCategory['description']); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Products Header -->
        <div class="products-header">
            <span class="products-count">
                Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products
            </span>
            <div class="d-flex align-center gap-1">
                <label for="sort-select">Sort by:</label>
                <select id="sort-select" class="sort-select" onchange="updateSort(this.value)">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="name_az" <?php echo $sort === 'name_az' ? 'selected' : ''; ?>>Name: A-Z</option>
                    <option value="name_za" <?php echo $sort === 'name_za' ? 'selected' : ''; ?>>Name: Z-A</option>
                </select>
            </div>
        </div>

        <?php if (empty($products)): ?>
            <div class="empty-cart">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <h3>No products found</h3>
                <p>Try adjusting your filters or browse all products.</p>
                <a href="<?php echo getBaseUrl(); ?>/pages/products.php" class="btn btn-primary">View All Products</a>
            </div>
        <?php else: ?>
            <!-- Products Grid -->
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

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="pagination">
                    <?php
                    $baseQuery = buildQueryString($queryParams, ['page']);
                    $baseUrl = getBaseUrl() . '/pages/products.php?' . $baseQuery;
                    ?>

                    <?php if ($page > 1): ?>
                        <a href="<?php echo $baseUrl . ($baseQuery ? '&' : '') . 'page=' . ($page - 1); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        </a>
                    <?php else: ?>
                        <span class="disabled">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        </span>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);

                    if ($startPage > 1) {
                        echo '<a href="' . $baseUrl . ($baseQuery ? '&' : '') . 'page=1">1</a>';
                        if ($startPage > 2) {
                            echo '<span class="disabled">...</span>';
                        }
                    }

                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo $baseUrl . ($baseQuery ? '&' : '') . 'page=' . $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<span class="disabled">...</span>';
                        }
                        echo '<a href="' . $baseUrl . ($baseQuery ? '&' : '') . 'page=' . $totalPages . '">' . $totalPages . '</a>';
                    }
                    ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo $baseUrl . ($baseQuery ? '&' : '') . 'page=' . ($page + 1); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </a>
                    <?php else: ?>
                        <span class="disabled">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </span>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function updateSort(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', value);
    url.searchParams.delete('page'); // Reset to first page on sort change
    window.location.href = url.toString();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
