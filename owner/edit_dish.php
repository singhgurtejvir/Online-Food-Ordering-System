<?php
$pageTitle = 'Edit Dish - Owner';
require_once '../functions.php';

// Require restaurant owner access
requireRestaurantOwner();

$currentUser = getCurrentUser();
$dishId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$dishId) {
    redirect('manage_dishes.php');
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

// Get dish details
$stmt = $pdo->prepare("SELECT * FROM dishes WHERE id = ? AND restaurant_id = ?");
$stmt->execute([$dishId, $restaurant['id']]);
$dish = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dish) {
    setFlashMessage('error', 'Dish not found.');
    redirect('manage_dishes.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $price = (float)$_POST['price'];
    $category = sanitizeInput($_POST['category']);
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;
    
    if (empty($name) || $price <= 0) {
        $error = 'Please provide a valid name and price';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE dishes SET name = ?, description = ?, price = ?, category = ?, is_available = ? WHERE id = ? AND restaurant_id = ?");
            $stmt->execute([$name, $description, $price, $category, $isAvailable, $dishId, $restaurant['id']]);
            
            $success = 'Dish updated successfully!';
            
            // Refresh dish data
            $stmt = $pdo->prepare("SELECT * FROM dishes WHERE id = ? AND restaurant_id = ?");
            $stmt->execute([$dishId, $restaurant['id']]);
            $dish = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $error = 'Error updating dish. Please try again.';
        }
    }
}

require_once '../header.php';
?>
<link rel="stylesheet" href="../style.css">
<main class="container mt-4">
    <div class="row align-center mb-4">
        <div class="col-8">
            <h1>Edit Dish</h1>
            <p><?php echo htmlspecialchars($restaurant['name']); ?></p>
        </div>
        <div class="col-4 text-right">
            <a href="manage_dishes.php" class="btn btn-outline">← Back to Menu</a>
        </div>
    </div>

    <div class="row justify-center">
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <h3>Edit Dish Details</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="name" class="form-label">Dish Name *</label>
                            <input type="text" id="name" name="name" class="form-control" required 
                                   value="<?php echo htmlspecialchars($dish['name']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($dish['description']); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="price" class="form-label">Price *</label>
                                    <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required 
                                           value="<?php echo $dish['price']; ?>">
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="category" class="form-label">Category</label>
                                    <select id="category" name="category" class="form-control">
                                        <option value="">Select Category</option>
                                        <option value="Appetizer" <?php echo $dish['category'] == 'Appetizer' ? 'selected' : ''; ?>>Appetizer</option>
                                        <option value="Main Course" <?php echo $dish['category'] == 'Main Course' ? 'selected' : ''; ?>>Main Course</option>
                                        <option value="Dessert" <?php echo $dish['category'] == 'Dessert' ? 'selected' : ''; ?>>Dessert</option>
                                        <option value="Beverage" <?php echo $dish['category'] == 'Beverage' ? 'selected' : ''; ?>>Beverage</option>
                                        <option value="Side Dish" <?php echo $dish['category'] == 'Side Dish' ? 'selected' : ''; ?>>Side Dish</option>
                                        <option value="Salad" <?php echo $dish['category'] == 'Salad' ? 'selected' : ''; ?>>Salad</option>
                                        <option value="Soup" <?php echo $dish['category'] == 'Soup' ? 'selected' : ''; ?>>Soup</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" name="is_available" value="1" <?php echo $dish['is_available'] ? 'checked' : ''; ?>>
                                Available for ordering
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Dish</button>
                        <a href="manage_dishes.php" class="btn btn-outline">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../footer.php'; ?>