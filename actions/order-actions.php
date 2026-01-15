<?php
/**
 * Order Actions Handler
 *
 * Handles order placement and processing
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(getBaseUrl() . '/pages/cart.php');
}

// Require login
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to place an order.', 'warning');
    redirect(getBaseUrl() . '/pages/login.php?redirect=checkout');
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Invalid request. Please try again.', 'error');
    redirect(getBaseUrl() . '/pages/checkout.php');
}

// Get form data
$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$notes = trim($_POST['notes'] ?? '');

// Validate required fields
$errors = [];

if (empty($fullName)) {
    $errors[] = 'Full name is required.';
}

if (empty($email) || !isValidEmail($email)) {
    $errors[] = 'Valid email is required.';
}

if (empty($phone)) {
    $errors[] = 'Phone number is required.';
}

if (empty($address)) {
    $errors[] = 'Shipping address is required.';
}

// Get cart items
try {
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, c.quantity, p.*
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ? AND p.is_active = 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cartItems = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching cart: " . $e->getMessage());
    setFlashMessage('error', 'Error processing order. Please try again.', 'error');
    redirect(getBaseUrl() . '/pages/checkout.php');
}

// Check if cart is empty
if (empty($cartItems)) {
    setFlashMessage('error', 'Your cart is empty.', 'warning');
    redirect(getBaseUrl() . '/pages/cart.php');
}

// Check for prescription items
$hasPrescriptionItems = false;
$cartTotal = 0;

foreach ($cartItems as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
    if ($item['requires_prescription']) {
        $hasPrescriptionItems = true;
    }

    // Verify stock availability
    if ($item['quantity'] > $item['stock_quantity']) {
        $errors[] = "Not enough stock for {$item['name']}.";
    }
}

// Handle prescription upload if required
$prescriptionFile = null;

if ($hasPrescriptionItems) {
    if (!isset($_FILES['prescription']) || $_FILES['prescription']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Prescription upload is required for prescription medications.';
    } else {
        $file = $_FILES['prescription'];

        // Validate file
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error uploading prescription file.';
        } elseif (!in_array($file['type'], $allowedTypes)) {
            $errors[] = 'Invalid file type. Please upload JPG, PNG, or PDF.';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'File too large. Maximum size is 5MB.';
        } else {
            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $prescriptionFile = 'rx_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;

            // Create uploads directory if not exists
            $uploadDir = __DIR__ . '/../uploads/prescriptions/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $prescriptionFile)) {
                $errors[] = 'Failed to save prescription file.';
                $prescriptionFile = null;
            }
        }
    }
}

// If there are errors, redirect back
if (!empty($errors)) {
    setFlashMessage('error', implode('<br>', $errors), 'error');
    redirect(getBaseUrl() . '/pages/checkout.php');
}

// Calculate total with shipping
$shippingCost = $cartTotal >= 50 ? 0 : 5.99;
$orderTotal = $cartTotal + $shippingCost;

// Create order
try {
    $pdo->beginTransaction();

    // Insert order
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, status, shipping_address, prescription_file, notes)
        VALUES (?, ?, 'pending', ?, ?, ?)
    ");

    $shippingAddressFull = "$fullName\n$phone\n$email\n$address";

    $stmt->execute([
        $_SESSION['user_id'],
        $orderTotal,
        $shippingAddressFull,
        $prescriptionFile,
        $notes ?: null
    ]);

    $orderId = $pdo->lastInsertId();

    // Insert order items
    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, unit_price)
        VALUES (?, ?, ?, ?)
    ");

    // Update stock
    $stockStmt = $pdo->prepare("
        UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?
    ");

    foreach ($cartItems as $item) {
        $itemStmt->execute([
            $orderId,
            $item['id'],
            $item['quantity'],
            $item['price']
        ]);

        $stockStmt->execute([
            $item['quantity'],
            $item['id']
        ]);
    }

    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    // Update user's phone and address if they changed
    $stmt = $pdo->prepare("UPDATE users SET phone = ?, address = ? WHERE id = ?");
    $stmt->execute([$phone, $address, $_SESSION['user_id']]);

    $pdo->commit();

    // Success - show confirmation
    setFlashMessage('success', 'Order placed successfully! Your order number is #' . str_pad($orderId, 6, '0', STR_PAD_LEFT), 'success');

    // Redirect to order confirmation or profile orders
    redirect(getBaseUrl() . '/pages/profile.php?tab=orders');

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Order creation error: " . $e->getMessage());

    // Delete uploaded prescription if order failed
    if ($prescriptionFile && file_exists($uploadDir . $prescriptionFile)) {
        unlink($uploadDir . $prescriptionFile);
    }

    setFlashMessage('error', 'Error creating order. Please try again.', 'error');
    redirect(getBaseUrl() . '/pages/checkout.php');
}
