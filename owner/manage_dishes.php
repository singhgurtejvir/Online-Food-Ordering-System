<?php
$pageTitle = 'Manage Menu - Owner';
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

// Handle dish actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $dishId = (int)$_POST['dish_id'];
    
    if ($action == 'toggle_availability' && $dishId) {
        try {
            $stmt = $pdo->prepare("UPDATE dishes SET is_available = NOT is_available WHERE id = ? AND restaurant_id = ?");
            $stmt->execute([$dishId, $restaurant['id']]);
            setFlashMessage('success', 'Dish availability updated.');
        } catch (PDOException $e) {
            setFlashMessage('error', 'Error updating dish.');
        }
    } elseif ($action == 'delete' && $dishId) {
        try {
            $stmt = $pdo->prepare("DELETE FROM dishes WHERE id = ? AND restaurant_id = ?");
            $stmt->execute([$dishId, $restaurant['id']]);
            setFlashMessage('success', 'Dish deleted successfully.');
        } catch (PDOException $e) {
            setFlashMessage('error', 'Error deleting dish.');
        }
    }
    
    redirect('manage_dishes.php');
}

// Get restaurant dishes
$stmt = $pdo->prepare("SELECT * FROM dishes WHERE restaurant_id = ? ORDER BY category, name");
$stmt->execute([$restaurant['id']]);
$dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group dishes by category
$categories = [];
foreach ($dishes as $dish) {
    $category = $dish['category'] ?: 'Other';
    $categories[$category][] = $dish;
}

require_once '../header.php';
?>
<link rel="stylesheet" href="../style.css">
<main class="container mt-4">
    <div class="row align-center mb-4">
        <div class="col-8">
            <h1>Manage Menu</h1>
            <p><?php echo htmlspecialchars($restaurant['name']); ?></p>
        </div>
        <div class="col-4 text-right">
            <a href="add_dish.php" class="btn btn-primary">Add New Dish</a>
            <a href="dashboard.php" class="btn btn-outline">← Dashboard</a>
        </div>
    </div>

    <?php if (empty($dishes)): ?>
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🍽️</div>
                <h3>No Menu Items Yet</h3>
                <p>Start building your menu by adding your first dish!</p>
                <a href="add_dish.php" class="btn btn-primary">Add Your First Dish</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($categories as $category => $categoryDishes): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h3><?php echo htmlspecialchars($category); ?></h3>
                    <span class="text-light"><?php echo count($categoryDishes); ?> item(s)</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Available</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categoryDishes as $dish): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($dish['name']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(substr($dish['description'], 0, 100)); ?>
                                            <?php if (strlen($dish['description']) > 100): ?>...<?php endif; ?>
                                        </td>
                                        <td><?php echo formatCurrency($dish['price']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $dish['is_available'] ? 'approved' : 'rejected'; ?>">
                                                <?php echo $dish['is_available'] ? 'Available' : 'Unavailable'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit_dish.php?id=<?php echo $dish['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                                            
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_availability">
                                                <input type="hidden" name="dish_id" value="<?php echo $dish['id']; ?>">
                                                <button type="submit" class="btn btn-sm <?php echo $dish['is_available'] ? 'btn-warning' : 'btn-success'; ?>">
                                                    <?php echo $dish['is_available'] ? 'Disable' : 'Enable'; ?>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this dish?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="dish_id" value="<?php echo $dish['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<?php require_once '../footer.php'; ?>