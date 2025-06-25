<?php
$pageTitle = 'Admin Dashboard';
require_once '../functions.php';

// Require admin access
requireAdmin();

$pdo = getDBConnection();

// Get statistics
$stats = [];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type != 'admin'");
$stats['total_users'] = $stmt->fetchColumn();

// Total restaurants
$stmt = $pdo->query("SELECT COUNT(*) FROM restaurants");
$stats['total_restaurants'] = $stmt->fetchColumn();

// Pending restaurant requests
$stmt = $pdo->query("SELECT COUNT(*) FROM restaurants WHERE status = 'pending'");
$stats['pending_requests'] = $stmt->fetchColumn();

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$stats['total_orders'] = $stmt->fetchColumn();

// Total revenue
$stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status != 'cancelled'");
$stats['total_revenue'] = $stmt->fetchColumn();

// Recent orders
$stmt = $pdo->prepare("SELECT o.*, u.full_name as customer_name, r.name as restaurant_name 
                      FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      JOIN restaurants r ON o.restaurant_id = r.id 
                      ORDER BY o.order_date DESC LIMIT 5");
$stmt->execute();
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../header.php';
?>
<link rel="stylesheet" href="../style.css">
<main class="container mt-4">
    <div class="row align-center mb-4">
        <div class="col-8">
            <h1>Admin Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        </div>
        <div class="col-4 text-right">
            <a href="../index.php" class="btn btn-outline">View Site</a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-3">
            <div class="card text-center">
                <div class="card-body">
                    <div style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem;">👥</div>
                    <h3><?php echo number_format($stats['total_users']); ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
        </div>
        
        <div class="col-3">
            <div class="card text-center">
                <div class="card-body">
                    <div style="font-size: 2rem; color: var(--secondary-color); margin-bottom: 0.5rem;">🏪</div>
                    <h3><?php echo number_format($stats['total_restaurants']); ?></h3>
                    <p>Total Restaurants</p>
                </div>
            </div>
        </div>
        
        <div class="col-3">
            <div class="card text-center">
                <div class="card-body">
                    <div style="font-size: 2rem; color: var(--warning-color); margin-bottom: 0.5rem;">⏳</div>
                    <h3><?php echo number_format($stats['pending_requests']); ?></h3>
                    <p>Pending Requests</p>
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
                    <a href="users.php" class="btn btn-primary w-100">Manage Users</a>
                </div>
                <div class="col-3">
                    <a href="restaurants.php" class="btn btn-secondary w-100">Manage Restaurants</a>
                </div>
                <div class="col-3">
                    <a href="restaurant_requests.php" class="btn btn-warning w-100">Review Requests</a>
                </div>
                <div class="col-3">
                    <a href="orders.php" class="btn btn-success w-100">View Orders</a>
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
                                <th>Restaurant</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['restaurant_name']); ?></td>
                                    <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="orders.php" class="btn btn-outline">View All Orders</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once '../footer.php'; ?>
