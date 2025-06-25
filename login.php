<?php
$pageTitle = 'Login';
require 'functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $pdo = getDBConnection();
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            
            setFlashMessage('success', 'Login successful! Welcome back.');
            
            // Redirect based on user type
            if ($user['user_type'] == 'admin') {
                redirect('admin/dashboard.php');
            } elseif ($user['user_type'] == 'restaurant_owner') {
                redirect('owner/dashboard.php');
            } else {
                redirect('index.php');
            }
        } else {
            $error = 'Invalid username or password';
        }
    }
}

require_once 'header.php';
?>

<main class="container mt-4">
    <div class="row justify-center">
        <div class="col-6">
            <div class="card">
                <div class="card-header text-center">
                    <h2>Login to Your Account</h2>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation">
                        <div class="form-group">
                            <label for="username" class="form-label">Username or Email</label>
                            <input type="text" id="username" name="username" class="form-control" required 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
                    <div class="mt-2">
                        <small class="text-light">
                            Demo Admin: admin / admin123<br>
                            Demo Customer: demo / demo123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require 'footer.php'; ?>