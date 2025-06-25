<?php
require_once 'database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_type'] === 'admin';
}

// Check if user is restaurant owner
function isRestaurantOwner() {
    return isLoggedIn() && $_SESSION['user_type'] === 'restaurant_owner';
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

// Flash message functions
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Get cart items
function getCartItems() {
    return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
}

// Add item to cart
function addToCart($dishId, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$dishId])) {
        $_SESSION['cart'][$dishId] += $quantity;
    } else {
        $_SESSION['cart'][$dishId] = $quantity;
    }
}

// Remove item from cart
function removeFromCart($dishId) {
    if (isset($_SESSION['cart'][$dishId])) {
        unset($_SESSION['cart'][$dishId]);
    }
}

// Update cart item quantity
function updateCartQuantity($dishId, $quantity) {
    if ($quantity <= 0) {
        removeFromCart($dishId);
    } else {
        $_SESSION['cart'][$dishId] = $quantity;
    }
}

// Clear cart
function clearCart() {
    unset($_SESSION['cart']);
}

// Get cart total
function getCartTotal() {
    $cart = getCartItems();
    if (empty($cart)) return 0;
    
    $pdo = getDBConnection();
    $total = 0;
    foreach ($cart as $dishId => $quantity) {
        $stmt = $pdo->prepare("SELECT price FROM dishes WHERE id = ?");
        $stmt->execute([$dishId]);
        $dish = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($dish) {
            $total += $dish['price'] * $quantity;
        }
    }
    return $total;
}

// Format currency
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

// Require admin
function requireAdmin() {
    if (!isAdmin()) {
        redirect('index.php');
    }
}

// Require restaurant owner
function requireRestaurantOwner() {
    if (!isRestaurantOwner()) {
        redirect('index.php');
    }
}
?>