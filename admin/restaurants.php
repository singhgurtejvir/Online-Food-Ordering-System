<?php
$pageTitle = 'Manage Restaurants - Admin';
require_once '../functions.php';

// Require admin access
requireAdmin();

$pdo = getDBConnection();

// Handle restaurant actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $restaurantId = (int)$_POST['restaurant_id'];
    
    if ($restaurantId) {
        try {
            switch ($action) {
                case 'approve':
                    $stmt = $pdo->prepare("UPDATE restaurants SET status = 'approved' WHERE id = ?");
                    $stmt->execute([$restaurantId]);
                    setFlashMessage('success', 'Restaurant approved successfully.');
                    break;
                    
                case 'reject':
                    $stmt = $pdo->prepare("UPDATE restaurants SET status = 'rejected' WHERE id = ?");
                    $stmt->execute([$restaurantId]);
                    setFlashMessage('success', 'Restaurant rejected.');
                    break;
                    
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM restaurants WHERE id = ?");
                    $stmt->execute([$restaurantId]);
                    setFlashMessage('success', 'Restaurant deleted successfully.');
                    break;
            }
        } catch (PDOException $e) {
            setFlashMessage('error', 'Error processing request.');
        }
    }
    
    redirect('restaurants.php');
}

// Get all restaurants
$stmt = $pdo->prepare("SELECT r.*, u.username as owner_username FROM restaurants r 
                      LEFT JOIN users u ON r.owner_id = u.id 
                      ORDER BY r.created_at DESC");
$stmt->execute();
$restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../header.php';
?>
<link rel="stylesheet" href="../style.css">

<main class="container mt-4">
    <div class="row align-center mb-4">
        <div class="col-8">
            <h1>Manage Restaurants</h1>
        </div>
        <div class="col-4 text-right">
            <a href="dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>All Restaurants</h3>
        </div>
        <div class="card-body">
            <?php if (empty($restaurants)): ?>
                <p class="text-center">No restaurants found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Owner</th>
                                <th>Cuisine</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($restaurants as $restaurant): ?>
                                <tr>
                                    <td><?php echo $restaurant['id']; ?></td>
                                    <td><?php echo htmlspecialchars($restaurant['name']); ?></td>
                                    <td><?php echo $restaurant['owner_username'] ? htmlspecialchars($restaurant['owner_username']) : 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($restaurant['cuisine_type']); ?></td>
                                    <td><?php echo htmlspecialchars($restaurant['phone']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $restaurant['status']; ?>">
                                            <?php echo ucfirst($restaurant['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($restaurant['created_at'])); ?></td>
                                    <td>
                                        <?php if ($restaurant['status'] == 'pending'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['id']; ?>">
                                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['id']; ?>">
                                                <button type="submit" class="btn btn-warning btn-sm">Reject</button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this restaurant?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
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