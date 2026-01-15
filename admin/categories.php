<?php
/**
 * Admin Categories Management
 *
 * CRUD operations for categories
 */

$pageTitle = 'Manage Categories';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin access
if (!isAdmin()) {
    setFlashMessage('error', 'Access denied.', 'error');
    redirect(getBaseUrl() . '/index.php');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request.', 'error');
        redirect(getBaseUrl() . '/admin/categories.php');
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                setFlashMessage('error', 'Category name is required.', 'error');
            } else {
                $slug = generateSlug($name);
                try {
                    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
                    $stmt->execute([$name, $slug, $description ?: null]);
                    setFlashMessage('success', 'Category created.', 'success');
                } catch (PDOException $e) {
                    setFlashMessage('error', 'Error creating category.', 'error');
                }
            }
            break;

        case 'update':
            $id = (int)($_POST['category_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($id && !empty($name)) {
                $slug = generateSlug($name);
                try {
                    $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
                    $stmt->execute([$name, $slug, $description ?: null, $id]);
                    setFlashMessage('success', 'Category updated.', 'success');
                } catch (PDOException $e) {
                    setFlashMessage('error', 'Error updating category.', 'error');
                }
            }
            break;

        case 'delete':
            $id = (int)($_POST['category_id'] ?? 0);
            if ($id) {
                // Check if category has products
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                $stmt->execute([$id]);
                if ($stmt->fetchColumn() > 0) {
                    setFlashMessage('error', 'Cannot delete category with products.', 'error');
                } else {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                        $stmt->execute([$id]);
                        setFlashMessage('success', 'Category deleted.', 'success');
                    } catch (PDOException $e) {
                        setFlashMessage('error', 'Error deleting category.', 'error');
                    }
                }
            }
            break;
    }

    redirect(getBaseUrl() . '/admin/categories.php');
}

// Fetch categories with product count
try {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id
        GROUP BY c.id
        ORDER BY c.name
    ");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($categories as $cat) {
        if ($cat['id'] == $editId) {
            $editCategory = $cat;
            break;
        }
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
            <div class="admin-header">
                <h1 class="admin-title">Categories</h1>
            </div>

            <?php echo displayFlashMessages(); ?>

            <div style="display: grid; grid-template-columns: 1fr 350px; gap: 1.5rem;">
                <!-- Categories List -->
                <div class="admin-card">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Products</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><strong><?php echo sanitize($cat['name']); ?></strong></td>
                                        <td><code><?php echo sanitize($cat['slug']); ?></code></td>
                                        <td><span class="badge badge-info"><?php echo (int)$cat['product_count']; ?></span></td>
                                        <td>
                                            <div class="admin-actions">
                                                <a href="?edit=<?php echo (int)$cat['id']; ?>" class="admin-action-btn edit" title="Edit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                                </a>
                                                <?php if ($cat['product_count'] == 0): ?>
                                                    <form action="" method="POST" style="display: inline;" onsubmit="return confirm('Delete this category?');">
                                                        <?php echo csrfField(); ?>
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="category_id" value="<?php echo (int)$cat['id']; ?>">
                                                        <button type="submit" class="admin-action-btn delete" title="Delete">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Add/Edit Form -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title"><?php echo $editCategory ? 'Edit Category' : 'Add Category'; ?></h2>
                        <?php if ($editCategory): ?>
                            <a href="<?php echo getBaseUrl(); ?>/admin/categories.php" class="btn btn-sm btn-outline">Cancel</a>
                        <?php endif; ?>
                    </div>
                    <div class="admin-card-body">
                        <form action="" method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="<?php echo $editCategory ? 'update' : 'create'; ?>">
                            <?php if ($editCategory): ?>
                                <input type="hidden" name="category_id" value="<?php echo (int)$editCategory['id']; ?>">
                            <?php endif; ?>

                            <div class="form-group">
                                <label class="form-label">Category Name *</label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?php echo sanitize($editCategory['name'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?php echo sanitize($editCategory['description'] ?? ''); ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <?php echo $editCategory ? 'Update Category' : 'Add Category'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="<?php echo getBaseUrl(); ?>/assets/js/main.js"></script>
</body>
</html>
