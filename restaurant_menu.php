<?php
$pageTitle = 'Restaurant Menu';
require_once 'header.php';

// Get restaurant ID
$restaurantId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$restaurantId) {
    redirect('restaurants.php');
}

$pdo = getDBConnection();

// Get restaurant details
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ? AND status = 'approved'");
$stmt->execute([$restaurantId]);
$restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$restaurant) {
    setFlashMessage('error', 'Restaurant not found or not available.');
    redirect('restaurants.php');
}

// Get menu items
$stmt = $pdo->prepare("SELECT * FROM dishes WHERE restaurant_id = ? AND is_available = 1 ORDER BY category, name");
$stmt->execute([$restaurantId]);
$dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group dishes by category
$menuCategories = [];
foreach ($dishes as $dish) {
    $category = $dish['category'] ?: 'Other';
    $menuCategories[$category][] = $dish;
}

$pageTitle = $restaurant['name'] . ' - Menu';
?>

<main class="container mt-4">
    <!-- Restaurant Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-center">
                <div class="col-8">
                    <h1><?php echo htmlspecialchars($restaurant['name']); ?></h1>
                    <p><strong>📍</strong> <?php echo htmlspecialchars($restaurant['address']); ?></p>
                    <p><strong>📞</strong> <?php echo htmlspecialchars($restaurant['phone']); ?></p>
                    <p><strong>📧</strong> <?php echo htmlspecialchars($restaurant['email']); ?></p>
                    <?php if ($restaurant['description']): ?>
                        <p><?php echo htmlspecialchars($restaurant['description']); ?></p>
                    <?php endif; ?>
                    <?php if ($restaurant['cuisine_type']): ?>
                        <span class="cuisine-tag"><?php echo htmlspecialchars($restaurant['cuisine_type']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="col-4 text-center">
                    <div style="font-size: 6rem; margin-bottom: 1rem;">🏪</div>
                    <a href="restaurants.php" class="btn btn-outline">← Back to Restaurants</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu -->
    <?php if (empty($dishes)): ?>
        <div class="text-center">
            <div style="font-size: 4rem; margin-bottom: 1rem;">🍽️</div>
            <h3>Menu Coming Soon</h3>
            <p>This restaurant is still preparing their menu. Check back later!</p>
        </div>
    <?php else: ?>
        <?php foreach ($menuCategories as $category => $categoryDishes): ?>
            <div class="mb-4">
                <h2 class="mb-3"><?php echo htmlspecialchars($category); ?></h2>
                <div class="menu-grid">
                    <?php foreach ($categoryDishes as $dish): ?>
                        <div class="card dish-card">
                            <div class="dish-image">
                                🍽️
                            </div>
                            <div class="dish-info">
                                <h3 class="dish-name"><?php echo htmlspecialchars($dish['name']); ?></h3>
                                <?php if ($dish['description']): ?>
                                    <p class="dish-description"><?php echo htmlspecialchars($dish['description']); ?></p>
                                <?php endif; ?>
                                <div class="dish-price"><?php echo formatCurrency($dish['price']); ?></div>
                                <?php if (isLoggedIn()): ?>
                                    <button class="btn btn-primary w-100 add-to-cart" 
                                            data-dish-id="<?php echo $dish['id']; ?>"
                                            data-dish-name="<?php echo htmlspecialchars($dish['name']); ?>">
                                        Add to Cart
                                    </button>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-outline w-100">Login to Order</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Cart Summary (if user is logged in and has items) -->
    <?php if (isLoggedIn() && !empty(getCartItems())): ?>
        <div class="card" style="position: sticky; bottom: 20px; z-index: 50;">
            <div class="card-body text-center">
                <div class="row align-center">
                    <div class="col-8">
                        <strong>Cart Total: <span id="cartTotal"><?php echo formatCurrency(getCartTotal()); ?></span></strong>
                        <span class="text-light ml-2"><?php echo count(getCartItems()); ?> item(s)</span>
                    </div>
                    <div class="col-4">
                        <a href="cart.php" class="btn btn-primary">View Cart</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once 'footer.php'; ?>