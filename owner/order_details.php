<?php
$pageTitle = 'Order Details - Owner';
require_once '../functions.php';

// Require restaurant owner access
requireRestaurantOwner();

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$currentUser = getCurrentUser();

if (!$orderId) {
    redirect('my_orders.php');
}

$pdo = getDBConnection();

// Get owner's restaurant
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE owner_id = ?");
$stmt->execute([$currentUser['id']]);
$restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$restaurant) {
    setFlashMessage('error', 'No restaurant found for your account.');
    redirect('dashboard.php');
}

// Get order details
$stmt = $pdo->prepare("SELECT o.*, u.full_name as customer_name, u.phone_number as customer_phone, u.email as customer_email 
                      FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      WHERE o.id = ? AND o.restaurant_id = ?");
$stmt->execute([$orderId, $restaurant['id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    setFlashMessage('error', 'Order not found.');
    redirect('my_orders.php');
}

// Get order items
$stmt = $pdo->prepare("SELECT oi.*, d.name as dish_name, d.description as dish_description 
                      FROM order_items oi 
                      JOIN dishes d ON oi.dish_id = d.id 
                      WHERE oi.order_id = ?");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../header.php';
?>
<link rel="stylesheet" href="../style.css">
<main class="container mt-4">
    <div class="row align-center mb-4">
        <div class="col-8">
            <h1>Order #<?php echo $order['id']; ?></h1>
            <span class="status-badge status-<?php echo $order['order_status']; ?>">
                <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
            </span>
        </div>
        <div class="col-4 text-right">
            <a href="my_orders.php" class="btn btn-outline">← Back to Orders</a>
        </div>
    </div>

    <div class="row">
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <h3>Order Details</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-6">
                            <h4>Customer Information</h4>
                            <p><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></p>
                            <?php if ($order['customer_phone']): ?>
                                <p>📞 <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                            <?php endif; ?>
                            <p>📧 <?php echo htmlspecialchars($order['customer_email']); ?></p>
                        </div>
                        <div class="col-6">
                            <h4>Delivery Information</h4>
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
                    <div class="d-flex justify-between mb-3">
                        <strong>Total Amount:</strong>
                        <strong><?php echo formatCurrency($order['total_amount']); ?></strong>
                    </div>
                    
                    <div class="d-flex justify-between mb-3">
                        <strong>Status:</strong>
                        <span class="status-badge status-<?php echo $order['order_status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                        </span>
                    </div>
                </div>
                <div class="card-footer">
                    <form method="POST" action="my_orders.php">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <div class="form-group">
                            <label for="status" class="form-label">Update Status:</label>
                            <select name="status" id="status" class="form-control">
                                <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="preparing" <?php echo $order['order_status'] == 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                <option value="out_for_delivery" <?php echo $order['order_status'] == 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                <option value="delivered" <?php echo $order['order_status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../footer.php'; ?>