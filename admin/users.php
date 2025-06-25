<?php
$pageTitle = 'Manage Users - Admin';
require_once '../functions.php';

// Require admin access
requireAdmin();

$pdo = getDBConnection();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $userId = (int)$_POST['user_id'];
    
    if ($action == 'delete' && $userId) {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type != 'admin'");
            $stmt->execute([$userId]);
            setFlashMessage('success', 'User deleted successfully.');
        } catch (PDOException $e) {
            setFlashMessage('error', 'Error deleting user.');
        }
    }
    
    redirect('users.php');
}

// Get all users
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../header.php';
?>
<link rel="stylesheet" href="../style.css">

<main class="container mt-4">
    <div class="row align-center mb-4">
        <div class="col-8">
            <h1>Manage Users</h1>
        </div>
        <div class="col-4 text-right">
            <a href="dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>All Users</h3>
        </div>
        <div class="card-body">
            <?php if (empty($users)): ?>
                <p class="text-center">No users found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>User Type</th>
                                <th>Phone</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $user['user_type'] == 'admin' ? 'approved' : 'pending'; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $user['user_type'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['user_type'] != 'admin'): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-light">Protected</span>
                                        <?php endif; ?>
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