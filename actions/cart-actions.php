<?php
/**
 * Cart Actions Handler
 *
 * Handles add to cart, update quantity, remove item via AJAX
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Set JSON header
header('Content-Type: application/json');

// Get action from POST or GET
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Response array
$response = [
    'success' => false,
    'message' => '',
    'cart_count' => 0
];

try {
    switch ($action) {
        case 'add':
            $productId = (int)($_POST['product_id'] ?? 0);
            $quantity = max(1, (int)($_POST['quantity'] ?? 1));

            if (!$productId) {
                $response['message'] = 'Invalid product.';
                break;
            }

            // Check if product exists and has stock
            $stmt = $pdo->prepare("SELECT id, stock_quantity, is_active FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if (!$product || !$product['is_active']) {
                $response['message'] = 'Product not available.';
                break;
            }

            if ($product['stock_quantity'] < $quantity) {
                $response['message'] = 'Not enough stock available.';
                break;
            }

            // Check if item already in cart
            if (isLoggedIn()) {
                $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$_SESSION['user_id'], $productId]);
            } else {
                $sessionId = session_id();
                $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?");
                $stmt->execute([$sessionId, $productId]);
            }

            $existingItem = $stmt->fetch();

            if ($existingItem) {
                // Update quantity
                $newQuantity = min($existingItem['quantity'] + $quantity, $product['stock_quantity']);
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$newQuantity, $existingItem['id']]);
                $response['message'] = 'Cart updated successfully.';
            } else {
                // Add new item
                if (isLoggedIn()) {
                    $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt->execute([$_SESSION['user_id'], $productId, $quantity]);
                } else {
                    $sessionId = session_id();
                    $stmt = $pdo->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt->execute([$sessionId, $productId, $quantity]);
                }
                $response['message'] = 'Product added to cart.';
            }

            $response['success'] = true;
            $response['cart_count'] = getCartCount();
            break;

        case 'update':
            $cartId = (int)($_POST['cart_id'] ?? 0);
            $quantity = max(0, (int)($_POST['quantity'] ?? 0));

            if (!$cartId) {
                $response['message'] = 'Invalid cart item.';
                break;
            }

            // Verify ownership and get product info
            if (isLoggedIn()) {
                $stmt = $pdo->prepare("
                    SELECT c.*, p.stock_quantity
                    FROM cart c
                    JOIN products p ON c.product_id = p.id
                    WHERE c.id = ? AND c.user_id = ?
                ");
                $stmt->execute([$cartId, $_SESSION['user_id']]);
            } else {
                $sessionId = session_id();
                $stmt = $pdo->prepare("
                    SELECT c.*, p.stock_quantity
                    FROM cart c
                    JOIN products p ON c.product_id = p.id
                    WHERE c.id = ? AND c.session_id = ?
                ");
                $stmt->execute([$cartId, $sessionId]);
            }

            $cartItem = $stmt->fetch();

            if (!$cartItem) {
                $response['message'] = 'Cart item not found.';
                break;
            }

            if ($quantity <= 0) {
                // Remove item
                $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
                $stmt->execute([$cartId]);
                $response['message'] = 'Item removed from cart.';
            } else {
                // Update quantity (max to stock)
                $quantity = min($quantity, $cartItem['stock_quantity']);
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$quantity, $cartId]);
                $response['message'] = 'Cart updated.';
            }

            $response['success'] = true;
            $response['cart_count'] = getCartCount();
            break;

        case 'remove':
            $cartId = (int)($_POST['cart_id'] ?? 0);

            if (!$cartId) {
                $response['message'] = 'Invalid cart item.';
                break;
            }

            // Verify ownership
            if (isLoggedIn()) {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                $stmt->execute([$cartId, $_SESSION['user_id']]);
            } else {
                $sessionId = session_id();
                $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND session_id = ?");
                $stmt->execute([$cartId, $sessionId]);
            }

            $response['success'] = true;
            $response['message'] = 'Item removed from cart.';
            $response['cart_count'] = getCartCount();
            break;

        case 'get_count':
            $response['success'] = true;
            $response['cart_count'] = getCartCount();
            break;

        case 'clear':
            if (isLoggedIn()) {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
            } else {
                $sessionId = session_id();
                $stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
                $stmt->execute([$sessionId]);
            }

            $response['success'] = true;
            $response['message'] = 'Cart cleared.';
            $response['cart_count'] = 0;
            break;

        default:
            $response['message'] = 'Invalid action.';
    }
} catch (PDOException $e) {
    error_log("Cart action error: " . $e->getMessage());
    $response['message'] = 'An error occurred. Please try again.';
}

echo json_encode($response);
