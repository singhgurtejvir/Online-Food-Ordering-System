<?php
$pageTitle = 'Add New Dish - Owner';
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
            $stmt = $pdo->prepare("INSERT INTO dishes (restaurant_id, name, description, price, category, is_available) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$restaurant['id'], $name, $description, $price, $category, $isAvailable]);
            
            setFlashMessage('success', 'Dish added successfully!');
            redirect('manage_dishes.php');
            
        } catch (PDOException $e) {
            $error = 'Error adding dish. Please try again.';
        }
    }
}

require_once '../header.php';
?>
<link rel="stylesheet" href="../style.css">
<main class="container mt-4">
    <div class="row align-center mb-4">
        <div class="col-8">
            <h1>Add New Dish</h1>
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
                    <h3>Dish Details</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="name" class="form-label">Dish Name *</label>
                            <input type="text" id="name" name="name" class="form-control" required 
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4" 
                                      placeholder="Describe your dish..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="price" class="form-label">Price *</label>
                                    <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required 
                                           value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="category" class="form-label">Category</label>
                                    <select id="category" name="category" class="form-control">
                                        <option value="">Select Category</option>
                                        <option value="Appetizer" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Appetizer') ? 'selected' : ''; ?>>Appetizer</option>
                                        <option value="Main Course" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Main Course') ? 'selected' : ''; ?>>Main Course</option>
                                        <option value="Dessert" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Dessert') ? 'selected' : ''; ?>>Dessert</option>
                                        <option value="Beverage" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Beverage') ? 'selected' : ''; ?>>Beverage</option>
                                        <option value="Side Dish" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Side Dish') ? 'selected' : ''; ?>>Side Dish</option>
                                        <option value="Salad" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Salad') ? 'selected' : ''; ?>>Salad</option>
                                        <option value="Soup" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Soup') ? 'selected' : ''; ?>>Soup</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" name="is_available" value="1" <?php echo (isset($_POST['is_available']) || !isset($_POST['name'])) ? 'checked' : ''; ?>>
                                Available for ordering
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Add Dish</button>
                        <a href="manage_dishes.php" class="btn btn-outline">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../footer.php'; ?>