<?php
$pageTitle = 'My Restaurant - Owner';
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
    $address = sanitizeInput($_POST['address']);
    $phone = sanitizeInput($_POST['phone']);
    $email = sanitizeInput($_POST['email']);
    $description = sanitizeInput($_POST['description']);
    $cuisineType = sanitizeInput($_POST['cuisine_type']);
    
    if (empty($name) || empty($address) || empty($phone) || empty($email)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE restaurants SET name = ?, address = ?, phone = ?, email = ?, description = ?, cuisine_type = ? WHERE id = ?");
            $stmt->execute([$name, $address, $phone, $email, $description, $cuisineType, $restaurant['id']]);
            
            $success = 'Restaurant details updated successfully!';
            
            // Refresh restaurant data
            $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE owner_id = ?");
            $stmt->execute([$currentUser['id']]);
            $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $error = 'Error updating restaurant details.';
        }
    }
}

require_once '../header.php';
?>
<link rel="stylesheet" href="../style.css">
<main class="container mt-4">
    <div class="row align-center mb-4">
        <div class="col-8">
            <h1>My Restaurant</h1>
        </div>
        <div class="col-4 text-right">
            <a href="dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
        </div>
    </div>

    <div class="row">
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <h3>Restaurant Details</h3>
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
                            <label for="name" class="form-label">Restaurant Name *</label>
                            <input type="text" id="name" name="name" class="form-control" required 
                                   value="<?php echo htmlspecialchars($restaurant['name']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="address" class="form-label">Address *</label>
                            <textarea id="address" name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($restaurant['address']); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" required 
                                           value="<?php echo htmlspecialchars($restaurant['phone']); ?>">
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" id="email" name="email" class="form-control" required 
                                           value="<?php echo htmlspecialchars($restaurant['email']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="cuisine_type" class="form-label">Cuisine Type</label>
                            <select id="cuisine_type" name="cuisine_type" class="form-control">
                                <option value="">Select Cuisine Type</option>
                                <option value="Italian" <?php echo $restaurant['cuisine_type'] == 'Italian' ? 'selected' : ''; ?>>Italian</option>
                                <option value="Chinese" <?php echo $restaurant['cuisine_type'] == 'Chinese' ? 'selected' : ''; ?>>Chinese</option>
                                <option value="Indian" <?php echo $restaurant['cuisine_type'] == 'Indian' ? 'selected' : ''; ?>>Indian</option>
                                <option value="Mexican" <?php echo $restaurant['cuisine_type'] == 'Mexican' ? 'selected' : ''; ?>>Mexican</option>
                                <option value="American" <?php echo $restaurant['cuisine_type'] == 'American' ? 'selected' : ''; ?>>American</option>
                                <option value="Thai" <?php echo $restaurant['cuisine_type'] == 'Thai' ? 'selected' : ''; ?>>Thai</option>
                                <option value="Japanese" <?php echo $restaurant['cuisine_type'] == 'Japanese' ? 'selected' : ''; ?>>Japanese</option>
                                <option value="Fast Food" <?php echo $restaurant['cuisine_type'] == 'Fast Food' ? 'selected' : ''; ?>>Fast Food</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($restaurant['description']); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Restaurant</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <h3>Restaurant Status</h3>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <span class="status-badge status-<?php echo $restaurant['status']; ?>" style="font-size: 1.2rem;">
                            <?php echo ucfirst($restaurant['status']); ?>
                        </span>
                        
                        <div class="mt-3">
                            <?php if ($restaurant['status'] == 'pending'): ?>
                                <p>Your restaurant is under review. We'll notify you once it's approved.</p>
                            <?php elseif ($restaurant['status'] == 'approved'): ?>
                                <p>Your restaurant is live and accepting orders!</p>
                                <a href="../restaurant_menu.php?id=<?php echo $restaurant['id']; ?>" class="btn btn-primary">View Public Page</a>
                            <?php elseif ($restaurant['status'] == 'rejected'): ?>
                                <p>Your restaurant application was rejected. Please contact support.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h3>Quick Stats</h3>
                </div>
                <div class="card-body">
                    <?php
                    // Get quick stats
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM dishes WHERE restaurant_id = ?");
                    $stmt->execute([$restaurant['id']]);
                    $dishCount = $stmt->fetchColumn();
                    
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE restaurant_id = ?");
                    $stmt->execute([$restaurant['id']]);
                    $orderCount = $stmt->fetchColumn();
                    ?>
                    
                    <p><strong>Menu Items:</strong> <?php echo $dishCount; ?></p>
                    <p><strong>Total Orders:</strong> <?php echo $orderCount; ?></p>
                    <p><strong>Joined:</strong> <?php echo date('M j, Y', strtotime($restaurant['created_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../footer.php'; ?>