<?php
$pageTitle = 'Checkout';
require_once 'includes/header.php';

// Require login
requireLogin();

$cartItems = getCartItems();
if (empty($cartItems)) {
    setFlashMessage('error', 'Your cart is empty.');
    redirect('cart.php');
}

$cartTotal = getCartTotal();
$deliveryFee = 2.99;
$tax = $cartTotal * 0.08;
$finalTotal = $cartTotal + $deliveryFee + $tax;

$currentUser = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $deliveryAddress = sanitizeInput($_POST['delivery_address']);
    $paymentMethod = sanitizeInput($_POST['payment_method']);
    
    if (empty($deliveryAddress)) {
        $error = 'Please provide a delivery address';
    } else {
        try {
            $pdo = getDBConnection();
            $pdo->beginTransaction();
            
            // Get restaurant ID from first cart item
            $firstDishId = array_keys($cartItems)[0];
            $stmt = $pdo->prepare("SELECT restaurant_id FROM dishes WHERE id = ?");
            $stmt->execute([$firstDishId]);
            $restaurantId = $stmt->fetchColumn();
            
            // Create order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, restaurant_id, total_amount, delivery_address, order_status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$currentUser['id'], $restaurantId, $finalTotal, $deliveryAddress]);
            $orderId = $pdo->lastInsertId();
            
            // Add order items
            foreach ($cartItems as $dishId => $quantity) {
                $stmt = $pdo->prepare("SELECT price FROM dishes WHERE id = ?");
                $stmt->execute([$dishId]);
                $price = $stmt->fetchColumn();
                
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, dish_id, quantity, price_at_order) VALUES (?, ?, ?, ?)");
                $stmt->execute([$orderId, $dishId, $quantity, $price]);
            }
            
            $pdo->commit();
            
            // Clear cart
            clearCart();
            
            setFlashMessage('success', 'Order placed successfully! Order ID: #' . $orderId);
            redirect('order_history.php');
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Error placing order. Please try again.';
        }
    }
}

// Get cart details for display
$cartDetails = [];
if (!empty($cartItems)) {
    $pdo = getDBConnection();
    $dishIds = array_keys($cartItems);
    $placeholders = str_repeat('?,', count($dishIds) - 1) . '?';
    
    $stmt = $pdo->prepare("SELECT d.*, r.name as restaurant_name FROM dishes d 
                          JOIN restaurants r ON d.restaurant_id = r.id 
                          WHERE d.id IN ($placeholders)");
    $stmt->execute($dishIds);
    $dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($dishes as $dish) {
        $cartDetails[] = [
            'dish' => $dish,
            'quantity' => $cartItems[$dish['id']]
        ];
    }
}
?>

<main class="container mt-4">
    <div class="row">
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <h2>Checkout</h2>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="delivery_address" class="form-label">Delivery Address *</label>
                            <textarea id="delivery_address" name="delivery_address" class="form-control" rows="3" required><?php echo isset($_POST['delivery_address']) ? htmlspecialchars($_POST['delivery_address']) : htmlspecialchars($currentUser['address']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_method" class="form-label">Payment Method *</label>
                            <select id="payment_method" name="payment_method" class="form-control" required>
                                <option value="">Select Payment Method</option>
                                <option value="cash">Cash on Delivery</option>
                                <option value="card">Credit/Debit Card</option>
                                <option value="digital">Digital Wallet</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">Place Order</button>
                        <a href="cart.php" class="btn btn-outline">Back to Cart</a>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <h3>Order Summary</h3>
                </div>
                <div class="card-body">
                    <?php foreach ($cartDetails as $item): ?>
                        <div class="d-flex justify-between mb-2">
                            <span><?php echo htmlspecialchars($item['dish']['name']); ?> x<?php echo $item['quantity']; ?></span>
                            <span><?php echo formatCurrency($item['dish']['price'] * $item['quantity']); ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <hr>
                    <div class="d-flex justify-between mb-2">
                        <span>Subtotal:</span>
                        <span><?php echo formatCurrency($cartTotal); ?></span>
                    </div>
                    <div class="d-flex justify-between mb-2">
                        <span>Delivery Fee:</span>
                        <span><?php echo formatCurrency($deliveryFee); ?></span>
                    </div>
                    <div class="d-flex justify-between mb-3">
                        <span>Tax:</span>
                        <span><?php echo formatCurrency($tax); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-between">
                        <strong>Total:</strong>
                        <strong><?php echo formatCurrency($finalTotal); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>