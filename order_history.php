<?php
$pageTitle = 'Order History';
require_once 'includes/header.php';

// Require login
requireLogin();

$currentUser = getCurrentUser();
$pdo = getDBConnection();

// Get user's orders
$stmt = $pdo->prepare("SELECT o.*, r.name as restaurant_name FROM orders o 
                      JOIN restaurants r ON o.restaurant_id = r.id 
                      WHERE o.user_id = ? ORDER BY o.order_date DESC");
$stmt->execute([$currentUser['id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h2>Order History</h2>
        </div>
        <div class="card-body">
            <?php if (empty($orders)): ?>
                <div class="text-center">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📋</div>
                    <h3>No orders yet</h3>
                    <p>Start ordering from your favorite restaurants!</p>
                    <a href="restaurants.php" class="btn btn-primary">Browse Restaurants</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Restaurant</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['restaurant_name']); ?></td>
                                    <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>