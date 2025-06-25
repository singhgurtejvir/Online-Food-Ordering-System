<?php
$pageTitle = 'Restaurant Owner Dashboard';
require_once '../functions.php';

// Require restaurant owner access
requireRestaurantOwner();

$currentUser = getCurrentUser();
$pdo = getDBConnection();

// Get owner's restaurant
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE owner_id = ?");
$stmt->execute([$currentUser['id']]);
$restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$restaurant) {
    setFlashMessage('error', 'No restaurant found for your account. Please contact admin.');
    redirect('../index.php');
}

// Get restaurant statistics
$stats = [];

// Total dishes
$stmt = $pdo->prepare("SELECT COUNT(*) FROM dishes WHERE restaurant_id = ?");
$stmt->execute([$restaurant['id']]);
$stats['total_dishes'] = $stmt->fetchColumn();

// Total orders
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE restaurant_id = ?");
$stmt->execute([$restaurant['id']]);
$stats['total_orders'] = $stmt->fetchColumn();

// Pending orders
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE restaurant_id = ? AND order_status IN ('pending', 'preparing')");
$stmt->execute([$restaurant['id']]);
$stats['pending_orders'] = $stmt->fetchColumn();

// Total revenue
$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE restaurant_id = ? AND order_status != 'cancelled'");
$stmt->execute([$restaurant['id']]);
$stats['total_revenue'] = $stmt->fetchColumn();

// Recent orders
$stmt = $pdo->prepare("SELECT o.*, u.full_name as customer_name FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      WHERE o.restaurant_id = ? 
                      ORDER BY o.order_date DESC LIMIT 5");
$stmt->execute([$restaurant['id']]);
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../header.php';
?>
<link rel="stylesheet" href="../style.css">
<main class="container mt-4">
    <div class="row align-center mb-4">
        <div class="col-8">
            <h1><?php echo htmlspecialchars($restaurant['name']); ?></h1>
            <p>Welcome back, <?php echo htmlspecialchars($currentUser['full_name']); ?>!</p>
            <span class="status-badge status-<?php echo $restaurant['status']; ?>">
                <?php echo ucfirst($restaurant['status']); ?>
            </span>
        </div>
        <div class="col-4 text-right">
            <a href="../restaurant_menu.php?id=<?php echo $restaurant['id']; ?>" class="btn btn-outline">View Public Menu</a>
        </div>
    </div>

    <?php if ($restaurant['status'] != 'approved'): ?>
        <div class="alert alert-warning">
            <strong>Restaurant Status: <?php echo ucfirst($restaurant['status']); ?></strong><br>
            <?php if ($restaurant['status'] == 'pending'): ?>
                Your restaurant is pending approval. You can manage your restaurant details and menu, but customers won't be able to see your restaurant until it's approved.
            <?php elseif ($restaurant['status'] == 'rejected'): ?>
                Your restaurant application was rejected. Please contact admin for more information.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-3">
            <div class="card text-center">
                <div class="card-body">
                    <div style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem;">🍽️</div>
                    <h3><?php echo number_format($stats['total_dishes']); ?></h3>
                    <p>Menu Items</p>
                </div>
            </div>
        </div>
        
        <div class="col-3">
            <div class="card text-center">
                <div class="card-body">
                    <div style="font-size: 2rem; color: var(--secondary-color); margin-bottom: 0.5rem;">📋</div>
                    <h3><?php echo number_format($stats['total_orders']); ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
        </div>
        
        <div class="col-3">
            <div class="card text-center">
                <div class="card-body">
                    <div style="font-size: 2rem; color: var(--warning-color); margin-bottom: 0.5rem;">⏳</div>
                    <h3><?php echo number_format($stats['pending_orders']); ?></h3>
                    <p>Pending Orders</p>
                </div>
            </div>
        </div>
        
        <div class="col-3">
            <div class="card text-center">
                <div class="card-body">
                    <div style="font-size: 2rem; color: var(--success-color); margin-bottom: 0.5rem;">💰</div>
                    <h3><?php echo formatCurrency($stats['total_revenue']); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-3">
                    <a href="my_restaurant.php" class="btn btn-primary w-100">Restaurant Details</a>
                </div>
                <div class="col-3">
                    <a href="manage_dishes.php" class="btn btn-secondary w-100">Manage Menu</a>
                </div>
                <div class="col-3">
                    <a href="my_orders.php" class="btn btn-warning w-100">View Orders</a>
                </div>
                <div class="col-3">
                    <a href="add_dish.php" class="btn btn-success w-100">Add New Dish</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header">
            <h3>Recent Orders</h3>
        </div>
        <div class="card-body">
            <?php if (empty($recentOrders)): ?>
                <p class="text-center text-light">No orders yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="my_orders.php" class="btn btn-outline">View All Orders</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once '../footer.php'; ?>