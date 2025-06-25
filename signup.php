<?php
$pageTitle = 'Sign Up';
require_once 'functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $fullName = sanitizeInput($_POST['full_name']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $userType = isset($_POST['user_type']) ? $_POST['user_type'] : 'customer';
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $pdo = getDBConnection();
        
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetchColumn() > 0) {
            $error = 'Username or email already exists';
        } else {
            // Create new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, user_type, full_name, phone_number, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashedPassword, $userType, $fullName, $phone, $address]);
                
                $success = 'Account created successfully! You can now login.';
                
                // Clear form data
                $_POST = [];
                
            } catch (PDOException $e) {
                $error = 'Error creating account. Please try again.';
            }
        }
    }
}

require_once 'header.php';
?>

<main class="container mt-4">
    <div class="row justify-center">
        <div class="col-8">
            <div class="card">
                <div class="card-header text-center">
                    <h2>Create Your Account</h2>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                            <br><a href="login.php" class="btn btn-primary mt-2">Login Now</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="needs-validation">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="username" class="form-label">Username *</label>
                                        <input type="text" id="username" name="username" class="form-control" required 
                                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
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
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="password" class="form-label">Password *</label>
                                        <input type="password" id="password" name="password" class="form-control" required>
                                    </div>
                                </div>
                                
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="full_name" class="form-label">Full Name *</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" required 
                                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" class="form-control" 
                                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="user_type" class="form-label">Account Type</label>
                                        <select id="user_type" name="user_type" class="form-control">
                                            <option value="customer" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
                                            <option value="restaurant_owner" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'restaurant_owner') ? 'selected' : ''; ?>>Restaurant Owner</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address" class="form-label">Address</label>
                                <textarea id="address" name="address" class="form-control" rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Create Account</button>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require 'footer.php'; ?>