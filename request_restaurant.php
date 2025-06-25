<?php
$pageTitle = 'Request Restaurant Listing';
require_once 'includes/header.php';

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
            $pdo = getDBConnection();
            
            $ownerId = isLoggedIn() ? $_SESSION['user_id'] : null;
            
            $stmt = $pdo->prepare("INSERT INTO restaurants (owner_id, name, address, phone, email, description, cuisine_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$ownerId, $name, $address, $phone, $email, $description, $cuisineType]);
            
            $success = 'Restaurant listing request submitted successfully! We will review your request and get back to you soon.';
            
            // Clear form data
            $_POST = [];
            
        } catch (PDOException $e) {
            $error = 'Error submitting request. Please try again.';
        }
    }
}
?>

<main class="container mt-4">
    <div class="row justify-center">
        <div class="col-8">
            <div class="card">
                <div class="card-header text-center">
                    <h2>List Your Restaurant</h2>
                    <p>Join our platform and reach more customers!</p>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                            <br><a href="index.php" class="btn btn-primary mt-2">Back to Home</a>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="form-group">
                                <label for="name" class="form-label">Restaurant Name *</label>
                                <input type="text" id="name" name="name" class="form-control" required 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="address" class="form-label">Address *</label>
                                <textarea id="address" name="address" class="form-control" rows="3" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="phone" class="form-label">Phone Number *</label>
                                        <input type="tel" id="phone" name="phone" class="form-control" required 
                                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" id="email" name="email" class="form-control" required 
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="cuisine_type" class="form-label">Cuisine Type</label>
                                <select id="cuisine_type" name="cuisine_type" class="form-control">
                                    <option value="">Select Cuisine Type</option>
                                    <option value="Italian" <?php echo (isset($_POST['cuisine_type']) && $_POST['cuisine_type'] == 'Italian') ? 'selected' : ''; ?>>Italian</option>
                                    <option value="Chinese" <?php echo (isset($_POST['cuisine_type']) && $_POST['cuisine_type'] == 'Chinese') ? 'selected' : ''; ?>>Chinese</option>
                                    <option value="Indian" <?php echo (isset($_POST['cuisine_type']) && $_POST['cuisine_type'] == 'Indian') ? 'selected' : ''; ?>>Indian</option>
                                    <option value="Mexican" <?php echo (isset($_POST['cuisine_type']) && $_POST['cuisine_type'] == 'Mexican') ? 'selected' : ''; ?>>Mexican</option>
                                    <option value="American" <?php echo (isset($_POST['cuisine_type']) && $_POST['cuisine_type'] == 'American') ? 'selected' : ''; ?>>American</option>
                                    <option value="Thai" <?php echo (isset($_POST['cuisine_type']) && $_POST['cuisine_type'] == 'Thai') ? 'selected' : ''; ?>>Thai</option>
                                    <option value="Japanese" <?php echo (isset($_POST['cuisine_type']) && $_POST['cuisine_type'] == 'Japanese') ? 'selected' : ''; ?>>Japanese</option>
                                    <option value="Fast Food" <?php echo (isset($_POST['cuisine_type']) && $_POST['cuisine_type'] == 'Fast Food') ? 'selected' : ''; ?>>Fast Food</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Tell us about your restaurant..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100">Submit Request</button>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center">
                    <p>Already have an account? <a href="login.php">Login here</a> to link this restaurant to your account.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>