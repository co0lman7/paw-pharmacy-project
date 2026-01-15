<?php
/**
 * Admin Products Management
 *
 * CRUD operations for products
 */

$pageTitle = 'Manage Products';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin access
if (!isAdmin()) {
    setFlashMessage('error', 'Access denied.', 'error');
    redirect(getBaseUrl() . '/index.php');
}

$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request.', 'error');
        redirect(getBaseUrl() . '/admin/products.php');
    }

    $postAction = $_POST['action'] ?? '';

    switch ($postAction) {
        case 'create':
        case 'update':
            $name = trim($_POST['name'] ?? '');
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $stockQuantity = (int)($_POST['stock_quantity'] ?? 0);
            $requiresPrescription = isset($_POST['requires_prescription']) ? 1 : 0;
            $dosageInfo = trim($_POST['dosage_info'] ?? '');
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            $errors = [];
            if (empty($name)) $errors[] = 'Product name is required.';
            if (!$categoryId) $errors[] = 'Category is required.';
            if ($price <= 0) $errors[] = 'Valid price is required.';

            // Handle image upload
            $imageName = $_POST['existing_image'] ?? '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $maxSize = 2 * 1024 * 1024; // 2MB

                if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                    $errors[] = 'Invalid image type.';
                } elseif ($_FILES['image']['size'] > $maxSize) {
                    $errors[] = 'Image too large (max 2MB).';
                } else {
                    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $imageName = 'product_' . time() . '.' . $ext;
                    $uploadDir = __DIR__ . '/../assets/images/';

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
                }
            }

            if (empty($errors)) {
                $slug = generateSlug($name);

                try {
                    if ($postAction === 'create') {
                        $stmt = $pdo->prepare("
                            INSERT INTO products (category_id, name, slug, description, price, stock_quantity, image, requires_prescription, dosage_info, is_active)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$categoryId, $name, $slug, $description, $price, $stockQuantity, $imageName, $requiresPrescription, $dosageInfo ?: null, $isActive]);
                        setFlashMessage('success', 'Product created successfully.', 'success');
                    } else {
                        $stmt = $pdo->prepare("
                            UPDATE products SET category_id = ?, name = ?, slug = ?, description = ?, price = ?, stock_quantity = ?, image = ?, requires_prescription = ?, dosage_info = ?, is_active = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$categoryId, $name, $slug, $description, $price, $stockQuantity, $imageName, $requiresPrescription, $dosageInfo ?: null, $isActive, $_POST['product_id']]);
                        setFlashMessage('success', 'Product updated successfully.', 'success');
                    }
                } catch (PDOException $e) {
                    error_log("Product save error: " . $e->getMessage());
                    setFlashMessage('error', 'Error saving product.', 'error');
                }
            } else {
                setFlashMessage('error', implode(' ', $errors), 'error');
            }

            redirect(getBaseUrl() . '/admin/products.php');
            break;

        case 'delete':
            $id = (int)($_POST['product_id'] ?? 0);
            if ($id) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                    $stmt->execute([$id]);
                    setFlashMessage('success', 'Product deleted.', 'success');
                } catch (PDOException $e) {
                    setFlashMessage('error', 'Cannot delete product (may have orders).', 'error');
                }
            }
            redirect(getBaseUrl() . '/admin/products.php');
            break;
    }
}

// Fetch data for views
$categories = getCategories();

if ($action === 'edit' && $productId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        if (!$product) {
            setFlashMessage('error', 'Product not found.', 'error');
            redirect(getBaseUrl() . '/admin/products.php');
        }
    } catch (PDOException $e) {
        redirect(getBaseUrl() . '/admin/products.php');
    }
}

// Fetch all products for list view
if ($action === 'list') {
    try {
        $stmt = $pdo->query("
            SELECT p.*, c.name as category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            ORDER BY p.created_at DESC
        ");
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        $products = [];
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

            <?php if ($action === 'list'): ?>
                <div class="admin-header">
                    <h1 class="admin-title">Products</h1>
                    <a href="?action=add" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Add Product
                    </a>
                </div>

                <div class="admin-card">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo getProductImage($p['image']); ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                        </td>
                                        <td>
                                            <?php echo sanitize(truncateText($p['name'], 40)); ?>
                                            <?php if ($p['requires_prescription']): ?>
                                                <span class="badge badge-danger" style="font-size: 0.625rem;">Rx</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo sanitize($p['category_name']); ?></td>
                                        <td><?php echo formatPrice($p['price']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $p['stock_quantity'] < 10 ? ($p['stock_quantity'] == 0 ? 'badge-danger' : 'badge-warning') : 'badge-success'; ?>">
                                                <?php echo (int)$p['stock_quantity']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $p['is_active'] ? 'badge-success' : 'badge-secondary'; ?>">
                                                <?php echo $p['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="admin-actions">
                                                <a href="?action=edit&id=<?php echo (int)$p['id']; ?>" class="admin-action-btn edit" title="Edit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                                </a>
                                                <form action="" method="POST" style="display: inline;" onsubmit="return confirm('Delete this product?');">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                                    <button type="submit" class="admin-action-btn delete" title="Delete">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($action === 'add' || $action === 'edit'): ?>
                <div class="admin-header">
                    <h1 class="admin-title"><?php echo $action === 'add' ? 'Add Product' : 'Edit Product'; ?></h1>
                    <a href="<?php echo getBaseUrl(); ?>/admin/products.php" class="btn btn-outline">Back to List</a>
                </div>

                <div class="admin-card">
                    <div class="admin-card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'create' : 'update'; ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                                <input type="hidden" name="existing_image" value="<?php echo sanitize($product['image'] ?? ''); ?>">
                            <?php endif; ?>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label class="form-label">Product Name *</label>
                                    <input type="text" name="name" class="form-control" required
                                           value="<?php echo sanitize($product['name'] ?? ''); ?>">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Category *</label>
                                    <select name="category_id" class="form-control" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo (int)$cat['id']; ?>"
                                                    <?php echo (isset($product) && $product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                                <?php echo sanitize($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4"><?php echo sanitize($product['description'] ?? ''); ?></textarea>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label class="form-label">Price *</label>
                                    <input type="number" name="price" class="form-control" step="0.01" min="0" required
                                           value="<?php echo $product['price'] ?? ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Stock Quantity *</label>
                                    <input type="number" name="stock_quantity" class="form-control" min="0" required
                                           value="<?php echo $product['stock_quantity'] ?? 0; ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Product Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <?php if (isset($product['image']) && $product['image']): ?>
                                    <div style="margin-top: 0.5rem;">
                                        <img src="<?php echo getProductImage($product['image']); ?>" alt="" style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Dosage Information</label>
                                <textarea name="dosage_info" class="form-control" rows="2"><?php echo sanitize($product['dosage_info'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" name="requires_prescription" id="requires_prescription" class="form-check-input"
                                           <?php echo (isset($product) && $product['requires_prescription']) ? 'checked' : ''; ?>>
                                    <label for="requires_prescription" class="form-check-label">Requires Prescription</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input"
                                           <?php echo (!isset($product) || $product['is_active']) ? 'checked' : ''; ?>>
                                    <label for="is_active" class="form-check-label">Active (visible on store)</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <?php echo $action === 'add' ? 'Create Product' : 'Update Product'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="<?php echo getBaseUrl(); ?>/assets/js/main.js"></script>
</body>
</html>
