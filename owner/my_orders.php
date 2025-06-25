<?php
$pageTitle = 'My Orders - Owner';
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
    setFlashMessage('error', 'No restaurant found for your account.');
    redirect('dashboard.php');
}

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $_POST['status'];
    
    $validStatuses = ['pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
    
    if ($orderId && in_array($newStatus, $validStatuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ? AND restaurant_id = ?");
            $stmt->execute([$newStatus, $orderId, $restaurant['id']]);
            setFlashMessage('success', 'Order status updated successfully.');
        } catch (PDOException $e) {
            setFlashMessage('error', 'Error updating order status.');
        }
    }
    
    redirect('my_orders.php');
}

// Get filter parameters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$query = "SELECT o.*, u.full_name as customer_name, u.phone_number as customer_phone 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          WHERE o.restaurant_id = ?";
$params = [$restaurant['id']];

if ($statusFilter) {
    $query .= " AND o.order_status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY o.order_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../header.php';
?>
<link rel="stylesheet" href="../style.css">
<main class="container mt-4">
    <div class="row align-center mb-4">
        <div class="col-8">
            <h1>My Orders</h1>
            <p><?php echo htmlspecialchars($restaurant['name']); ?></p>
        </div>
        <div class="col-4 text-right">
            <a href="dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row align-center">
                <div class="col-4">
                    <select name="status" class="form-control">
                        <option value="">All Orders</option>
                        <option value="pending" <?php echo $statusFilter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="preparing" <?php echo $statusFilter == 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                        <option value="out_for_delivery" <?php echo $statusFilter == 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                        <option value="delivered" <?php echo $statusFilter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $statusFilter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
                <?php if ($statusFilter): ?>
                    <div class="col-2">
                        <a href="my_orders.php" class="btn btn-outline">Clear</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Restaurant Orders</h3>
        </div>
        <div class="card-body">
            <?php if (empty($orders)): ?>
                <div class="text-center">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📋</div>
                    <h3>No orders yet</h3>
                    <p>Orders will appear here once customers start ordering from your restaurant.</p>
                </div>
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
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($order['customer_name']); ?>
                                        <?php if ($order['customer_phone']): ?>
                                            <br><small><?php echo htmlspecialchars($order['customer_phone']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" class="form-control" style="width: auto; display: inline-block;" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="preparing" <?php echo $order['order_status'] == 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                                <option value="out_for_delivery" <?php echo $order['order_status'] == 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                                <option value="delivered" <?php echo $order['order_status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </form>
                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">Details</a>
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

<?php require_once '../footer.php'; ?>