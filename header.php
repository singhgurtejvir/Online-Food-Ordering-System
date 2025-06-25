<?php
require_once 'functions.php';
$currentUser = getCurrentUser();
$cartCount = count(getCartItems());
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>FoodOrder</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo">
                    Foodie Hub
                </a>

                <ul class="nav-menu">
                    <li><a href="index.php"
                            class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a>
                    </li>
                    <li><a href="restaurants.php"
                            class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'restaurants.php' ? 'active' : ''; ?>">Restaurants</a>
                    </li>
                    <li><a href="dishes.php"
                            class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dishes.php' ? 'active' : ''; ?>">Dishes</a>
                    </li>

                    <?php if (isLoggedIn()): ?>
                        <?php if ($currentUser['user_type'] == 'admin'): ?>
                            <li><a href="admin/dashboard.php" class="nav-link">Admin Panel</a></li>
                        <?php elseif ($currentUser['user_type'] == 'restaurant_owner'): ?>
                            <li><a href="owner/dashboard.php" class="nav-link">My Restaurant</a></li>
                        <?php endif; ?>

                        <li><a href="order_history.php" class="nav-link">My Orders</a></li>
                        <li><a href="cart.php" class="nav-link">Cart <?php if ($cartCount > 0): ?><span
                                        id="cartCount">(<?php echo $cartCount; ?>)</span><?php endif; ?></a></li>
                        <li><a href="profile.php" class="nav-link">Profile</a></li>
                        <li><a href="<?php
                        if ($currentUser['user_type'] == 'admin' || $currentUser['user_type'] == 'restaurant_owner')
                            echo "../logout.php";
                        else
                            echo "logout.php"; ?>" class="btn btn-outline">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="nav-link">Login</a></li>
                        <li><a href="signup.php" class="btn btn-primary">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <?php
    // Display flash messages
    $flashMessage = getFlashMessage();
    if ($flashMessage):
        ?>
        <div class="alert alert-<?php echo $flashMessage['type'] == 'success' ? 'success' : 'error'; ?>">
            <div class="container">
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        </div>
    <?php endif; ?>