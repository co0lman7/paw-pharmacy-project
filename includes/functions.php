<?php
/**
 * Common Functions
 *
 * This file contains reusable utility functions used throughout the application.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitize user input
 *
 * @param string $input The input to sanitize
 * @return string Sanitized input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 *
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if current user is admin
 *
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirect to a URL
 *
 * @param string $url The URL to redirect to
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Format price with currency symbol
 *
 * @param float $amount The amount to format
 * @return string Formatted price
 */
function formatPrice($amount) {
    return '$' . number_format((float)$amount, 2);
}

/**
 * Get cart item count
 *
 * @return int Number of items in cart
 */
function getCartCount() {
    global $pdo;

    if (!isset($pdo)) {
        require_once __DIR__ . '/../config/database.php';
    }

    try {
        if (isLoggedIn()) {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as count FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        } else {
            $sessionId = session_id();
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as count FROM cart WHERE session_id = ?");
            $stmt->execute([$sessionId]);
        }

        $result = $stmt->fetch();
        return (int)$result['count'];
    } catch (PDOException $e) {
        error_log("Error getting cart count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Generate CSRF token
 *
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 *
 * @param string $token The token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Output CSRF token field for forms
 *
 * @return string HTML hidden input with CSRF token
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

/**
 * Get current user data
 *
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    global $pdo;

    if (!isLoggedIn()) {
        return null;
    }

    if (!isset($pdo)) {
        require_once __DIR__ . '/../config/database.php';
    }

    try {
        $stmt = $pdo->prepare("SELECT id, email, first_name, last_name, phone, address, role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting current user: " . $e->getMessage());
        return null;
    }
}

/**
 * Display flash message
 *
 * @param string $key The message key
 * @param string $message The message to store
 * @param string $type The message type (success, error, warning, info)
 * @return void
 */
function setFlashMessage($key, $message, $type = 'info') {
    $_SESSION['flash'][$key] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear flash message
 *
 * @param string $key The message key
 * @return array|null The message data or null
 */
function getFlashMessage($key) {
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

/**
 * Display all flash messages as HTML
 *
 * @return string HTML for flash messages
 */
function displayFlashMessages() {
    $html = '';
    if (isset($_SESSION['flash']) && is_array($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $key => $data) {
            $type = $data['type'];
            $message = sanitize($data['message']);
            $html .= "<div class=\"alert alert-{$type}\">{$message}</div>";
            unset($_SESSION['flash'][$key]);
        }
    }
    return $html;
}

/**
 * Truncate text to specified length
 *
 * @param string $text The text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to append
 * @return string Truncated text
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length - strlen($suffix)) . $suffix;
}

/**
 * Generate a URL-friendly slug
 *
 * @param string $text The text to convert
 * @return string URL-friendly slug
 */
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Check if cart contains prescription items
 *
 * @return bool True if cart has prescription items
 */
function cartHasPrescriptionItems() {
    global $pdo;

    if (!isset($pdo)) {
        require_once __DIR__ . '/../config/database.php';
    }

    try {
        if (isLoggedIn()) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM cart c
                JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ? AND p.requires_prescription = 1
            ");
            $stmt->execute([$_SESSION['user_id']]);
        } else {
            $sessionId = session_id();
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM cart c
                JOIN products p ON c.product_id = p.id
                WHERE c.session_id = ? AND p.requires_prescription = 1
            ");
            $stmt->execute([$sessionId]);
        }

        $result = $stmt->fetch();
        return $result['count'] > 0;
    } catch (PDOException $e) {
        error_log("Error checking prescription items: " . $e->getMessage());
        return false;
    }
}

/**
 * Get categories for navigation
 *
 * @return array List of categories
 */
function getCategories() {
    global $pdo;

    if (!isset($pdo)) {
        require_once __DIR__ . '/../config/database.php';
    }

    try {
        $stmt = $pdo->query("SELECT id, name, slug FROM categories ORDER BY name");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting categories: " . $e->getMessage());
        return [];
    }
}

/**
 * Validate email format
 *
 * @param string $email The email to validate
 * @return bool True if valid
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check password strength
 *
 * @param string $password The password to check
 * @return array Validation result with 'valid' and 'message' keys
 */
function validatePassword($password) {
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => 'Password must be at least 8 characters long.'];
    }

    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one uppercase letter.'];
    }

    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one lowercase letter.'];
    }

    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one number.'];
    }

    return ['valid' => true, 'message' => 'Password is strong.'];
}

/**
 * Get base URL of the application
 *
 * @return string Base URL
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);

    // Get the pharmacy root directory
    $path = preg_replace('#/(pages|admin|actions)$#', '', $path);

    return $protocol . '://' . $host . $path;
}

/**
 * Get product image URL or placeholder
 *
 * @param string|null $image Image filename
 * @return string Image URL
 */
function getProductImage($image) {
    $baseUrl = getBaseUrl();
    if ($image && file_exists(__DIR__ . '/../assets/images/' . $image)) {
        return $baseUrl . '/assets/images/' . $image;
    }
    return $baseUrl . '/assets/images/placeholder.jpg';
}

/**
 * Format date for display
 *
 * @param string $date Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Get order status badge class
 *
 * @param string $status Order status
 * @return string CSS class
 */
function getStatusBadgeClass($status) {
    $classes = [
        'pending' => 'badge-warning',
        'processing' => 'badge-info',
        'shipped' => 'badge-primary',
        'delivered' => 'badge-success',
        'cancelled' => 'badge-danger'
    ];

    return $classes[$status] ?? 'badge-secondary';
}
