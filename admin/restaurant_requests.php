<?php
$pageTitle = 'Restaurant Requests - Admin';
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
                    setFlashMessage('success', 'Restaurant request approved successfully.');
                    break;
                    
                case 'reject':
                    $stmt = $pdo->prepare("UPDATE restaurants SET status = 'rejected' WHERE id = ?");
                    $stmt->execute([$restaurantId]);
                    setFlashMessage('success', 'Restaurant request rejected.');
                    break;
            }
        } catch (PDOException $e) {
            setFlashMessage('error', 'Error processing request.');
        }
    }
    
    redirect('restaurant_requests.php');
}

// Get pending restaurant requests
$stmt = $pdo->prepare("SELECT r.*, u.username as owner_username, u.full_name as owner_name 
                      FROM restaurants r 
                      LEFT JOIN users u ON r.owner_id = u.id 
                      WHERE r.status = 'pending' 
                      ORDER BY r.created_at DESC");
$stmt->execute();
$pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../header.php';
?>
<link rel="stylesheet" href="../style.css">

<main class="container mt-4">
    <div class="row align-center mb-4">
        <div class="col-8">
            <h1>Restaurant Requests</h1>
            <p>Review and approve new restaurant listing requests</p>
        </div>
        <div class="col-4 text-right">
            <a href="dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
        </div>
    </div>

    <?php if (empty($pendingRequests)): ?>
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 4rem; margin-bottom: 1rem;">📋</div>
                <h3>No Pending Requests</h3>
                <p>All restaurant requests have been processed.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($pendingRequests as $request): ?>
                <div class="col-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h3><?php echo htmlspecialchars($request['name']); ?></h3>
                            <span class="status-badge status-pending">Pending Review</span>
                        </div>
                        <div class="card-body">
                            <p><strong>📍 Address:</strong><br><?php echo htmlspecialchars($request['address']); ?></p>
                            <p><strong>📞 Phone:</strong> <?php echo htmlspecialchars($request['phone']); ?></p>
                            <p><strong>📧 Email:</strong> <?php echo htmlspecialchars($request['email']); ?></p>
                            
                            <?php if ($request['cuisine_type']): ?>
                                <p><strong>🍽️ Cuisine:</strong> <?php echo htmlspecialchars($request['cuisine_type']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($request['owner_name']): ?>
                                <p><strong>👤 Owner:</strong> <?php echo htmlspecialchars($request['owner_name']); ?> (<?php echo htmlspecialchars($request['owner_username']); ?>)</p>
                            <?php endif; ?>
                            
                            <?php if ($request['description']): ?>
                                <p><strong>📝 Description:</strong><br><?php echo htmlspecialchars($request['description']); ?></p>
                            <?php endif; ?>
                            
                            <p><strong>📅 Submitted:</strong> <?php echo date('M j, Y g:i A', strtotime($request['created_at'])); ?></p>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-6">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="restaurant_id" value="<?php echo $request['id']; ?>">
                                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approve this restaurant?');">
                                            ✅ Approve
                                        </button>
                                    </form>
                                </div>
                                <div class="col-6">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="restaurant_id" value="<?php echo $request['id']; ?>">
                                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Reject this restaurant request?');">
                                            ❌ Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once '../footer.php'; ?>