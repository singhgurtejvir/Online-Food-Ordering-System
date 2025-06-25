<?php
$pageTitle = 'Shopping Cart';
require_once 'header.php';

// Require login to view cart
requireLogin();

$cartItems = getCartItems();
$cartTotal = getCartTotal();

// Get cart items with dish details
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
                    <h2>Shopping Cart</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($cartDetails)): ?>
                        <div class="text-center">
                            <div style="font-size: 4rem; margin-bottom: 1rem;">🛒</div>
                            <h3>Your cart is empty</h3>
                            <p>Browse our restaurants and add some delicious items to your cart!</p>
                            <a href="restaurants.php" class="btn btn-primary">Browse Restaurants</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cartDetails as $item): ?>
                            <div class="cart-item" data-cart-item="<?php echo $item['dish']['id']; ?>">
                                <div class="cart-item-image">
                                    🍽️
                                </div>
                                <div class="cart-item-info">
                                    <div class="cart-item-name"><?php echo htmlspecialchars($item['dish']['name']); ?></div>
                                    <div class="text-light"><?php echo htmlspecialchars($item['dish']['restaurant_name']); ?></div>
                                    <div class="cart-item-price"><?php echo formatCurrency($item['dish']['price']); ?></div>
                                </div>
                                <div class="quantity-controls">
                                    <button class="quantity-btn" data-action="decrease" data-dish-id="<?php echo $item['dish']['id']; ?>">-</button>
                                    <span data-quantity-for="<?php echo $item['dish']['id']; ?>"><?php echo $item['quantity']; ?></span>
                                    <button class="quantity-btn" data-action="increase" data-dish-id="<?php echo $item['dish']['id']; ?>">+</button>
                                </div>
                                <div>
                                    <button class="btn btn-danger btn-sm remove-from-cart" data-dish-id="<?php echo $item['dish']['id']; ?>">Remove</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if (!empty($cartDetails)): ?>
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <h3>Order Summary</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-between mb-2">
                        <span>Subtotal:</span>
                        <span id="cartTotal"><?php echo formatCurrency($cartTotal); ?></span>
                    </div>
                    <div class="d-flex justify-between mb-2">
                        <span>Delivery Fee:</span>
                        <span>$2.99</span>
                    </div>
                    <div class="d-flex justify-between mb-3">
                        <span>Tax:</span>
                        <span><?php echo formatCurrency($cartTotal * 0.08); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-between mb-3">
                        <strong>Total:</strong>
                        <strong><?php echo formatCurrency($cartTotal + 2.99 + ($cartTotal * 0.08)); ?></strong>
                    </div>
                    <a href="checkout.php" class="btn btn-primary w-100">Proceed to Checkout</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'footer.php'; ?>