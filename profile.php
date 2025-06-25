<?php
$pageTitle = 'Profile';
require_once 'header.php';

// Require login
requireLogin();

$currentUser = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($fullName) || empty($email)) {
        $error = 'Full name and email are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $pdo = getDBConnection();
        
        // Check if email is already taken by another user
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $currentUser['id']]);
        
        if ($stmt->fetchColumn() > 0) {
            $error = 'Email is already taken by another user';
        } else {
            try {
                // Update basic info
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone_number = ?, address = ? WHERE id = ?");
                $stmt->execute([$fullName, $email, $phone, $address, $currentUser['id']]);
                
                // Update password if provided
                if (!empty($newPassword)) {
                    if (empty($currentPassword)) {
                        $error = 'Current password is required to change password';
                    } elseif (!password_verify($currentPassword, $currentUser['password'])) {
                        $error = 'Current password is incorrect';
                    } elseif ($newPassword !== $confirmPassword) {
                        $error = 'New passwords do not match';
                    } elseif (strlen($newPassword) < 6) {
                        $error = 'New password must be at least 6 characters long';
                    } else {
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt->execute([$hashedPassword, $currentUser['id']]);
                        $success = 'Profile and password updated successfully!';
                    }
                } else {
                    $success = 'Profile updated successfully!';
                }
                
                // Refresh user data
                $currentUser = getCurrentUser();
                
            } catch (PDOException $e) {
                $error = 'Error updating profile. Please try again.';
            }
        }
    }
}
?>

<main class="container mt-4">
    <div class="row justify-center">
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <h2>My Profile</h2>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" id="username" class="form-control" value="<?php echo htmlspecialchars($currentUser['username']); ?>" disabled>
                                    <small class="text-light">Username cannot be changed</small>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="user_type" class="form-label">Account Type</label>
                                    <input type="text" id="user_type" class="form-control" value="<?php echo ucfirst(str_replace('_', ' ', $currentUser['user_type'])); ?>" disabled>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" id="full_name" name="full_name" class="form-control" required 
                                           value="<?php echo htmlspecialchars($currentUser['full_name']); ?>">
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" id="email" name="email" class="form-control" required 
                                           value="<?php echo htmlspecialchars($currentUser['email']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($currentUser['phone_number']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="address" class="form-label">Address</label>
                            <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($currentUser['address']); ?></textarea>
                        </div>
                        
                        <hr>
                        <h4>Change Password</h4>
                        <p class="text-light">Leave blank if you don't want to change your password</p>
                        
                        <div class="form-group">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control">
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control">
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <a href="index.php" class="btn btn-outline">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>