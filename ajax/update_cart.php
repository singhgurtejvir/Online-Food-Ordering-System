<?php
require '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$dishId = isset($_POST['dish_id']) ? (int)$_POST['dish_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

if (!$dishId) {
    echo json_encode(['success' => false, 'message' => 'Invalid dish ID']);
    exit;
}

try {
    updateCartQuantity($dishId, $quantity);
    
    echo json_encode([
        'success' => true,
        'cart_count' => count(getCartItems()),
        'cart_total' => formatCurrency(getCartTotal())
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error updating cart']);
}
?>