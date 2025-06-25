<?php
require_once '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$dishId = isset($_POST['dish_id']) ? (int)$_POST['dish_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if (!$dishId || $quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid dish ID or quantity']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Verify dish exists and is available
    $stmt = $pdo->prepare("SELECT d.*, r.status as restaurant_status FROM dishes d 
                          JOIN restaurants r ON d.restaurant_id = r.id 
                          WHERE d.id = ? AND d.is_available = 1 AND r.status = 'approved'");
    $stmt->execute([$dishId]);
    $dish = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$dish) {
        echo json_encode(['success' => false, 'message' => 'Dish not available']);
        exit;
    }
    
    // Add to cart
    addToCart($dishId, $quantity);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Item added to cart',
        'cart_count' => count(getCartItems()),
        'cart_total' => formatCurrency(getCartTotal())
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error adding item to cart']);
}
?>
