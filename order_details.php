<?php
$pageTitle = 'Order Details';
require_once 'header.php';

// Require login
requireLogin();

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$currentUser = getCurrentUser();

if (!$orderId) {
    redirect('order_history.php');
}

$pdo = getDBConnection();

// Get order details
$stmt = $pdo->prepare("SELECT o.*, r.name as restaurant_name, r.address as restaurant_address, r.phone as restaurant_phone 
                      FROM orders o 
                      JOIN restaurants r ON o.restaurant_id = r.id 
                      WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$orderId, $currentUser['id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    setFlashMessage('error', 'Order not found.');
    redirect('order_history.php');
}

// Get order items
$stmt = $pdo->prepare("SELECT oi.*, d.name as dish_name, d.description as dish_description 
                      FROM order_items oi 
                      JOIN dishes d ON oi.dish_id = d.id 
                      WHERE oi.order_id = ?");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container mt-4">
    <div class="row">
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <h2>Order #<?php echo $order['id']; ?></h2>
                    <span class="status-badge status-<?php echo $order['order_status']; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-6">
                            <h4>Restaurant Details</h4>
                            <p><strong><?php echo htmlspecialchars($order['restaurant_name']); ?></strong></p>
                            <p><?php echo htmlspecialchars($order['restaurant_address']); ?></p>
                            <p>📞 <?php echo htmlspecialchars($order['restaurant_phone']); ?></p>
                        </div>
                        <div class="col-6">
                            <h4>Delivery Details</h4>
                            <p><strong>Address:</strong></p>
                            <p><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                            <p><strong>Order Date:</strong></p>
                            <p><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></p>
                        </div>
                    </div>
                    
                    <h4>Order Items</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['dish_name']); ?></strong>
                                            <?php if ($item['dish_description']): ?>
                                                <br><small class="text-light"><?php echo htmlspecialchars($item['dish_description']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatCurrency($item['price_at_order']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo formatCurrency($item['price_at_order'] * $item['quantity']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <h3>Order Summary</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-between">
                        <strong>Total Amount:</strong>
                        <strong><?php echo formatCurrency($order['total_amount']); ?></strong>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="order_history.php" class="btn btn-outline w-100">Back to Order History</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>
