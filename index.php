<?php
$pageTitle = 'Home';
require_once 'header.php';

// Get featured restaurants
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE status = 'approved' ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$featuredRestaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get featured dishes
$stmt = $pdo->prepare("SELECT d.*, r.name as restaurant_name, r.cuisine_type 
                      FROM dishes d 
                      JOIN restaurants r ON d.restaurant_id = r.id 
                      WHERE d.is_available = 1 AND r.status = 'approved' 
                      ORDER BY RAND() LIMIT 8");
$stmt->execute();
$featuredDishes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="style.css">
<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Delicious Food Delivered</h1>
            <p>Order from your favorite restaurants and get fresh food delivered to your doorstep</p>
            
            <div class="search-box">
                <input type="text" id="searchInput" class="search-input" placeholder="Search for restaurants or dishes...">
                <button id="searchBtn" class="search-btn">🔍</button>
            </div>
            
            <div class="mt-3">
                <a href="restaurants.php" class="btn search-btn-outline" style="margin-right: 1rem;">Browse Restaurants</a>
                <a href="dishes.php" class="btn search-btn-outline">Browse Dishes</a>
            </div>
        </div>
    </section>

    <!-- Featured Dishes -->
    <?php if (!empty($featuredDishes)): ?>
    <section class="container mt-4">
        <h2 class="text-center mb-4">Featured Dishes</h2>
        
        <div class="menu-grid">
            <?php foreach ($featuredDishes as $dish): ?>
                <div class="card dish-card">
                    <div class="dish-image">
                        🍽️
                    </div>
                    <div class="dish-info">
                        <h3 class="dish-name"><?php echo htmlspecialchars($dish['name']); ?></h3>
                        <p class="restaurant-name">
                            <strong>From:</strong> 
                            <a href="restaurant_menu.php?id=<?php echo $dish['restaurant_id']; ?>" class="text-primary">
                                <?php echo htmlspecialchars($dish['restaurant_name']); ?>
                            </a>
                        </p>
                        <?php if ($dish['description']): ?>
                            <p class="dish-description"><?php echo htmlspecialchars(substr($dish['description'], 0, 80)); ?>...</p>
                        <?php endif; ?>
                        <?php if ($dish['category']): ?>
                            <span class="cuisine-tag"><?php echo htmlspecialchars($dish['category']); ?></span>
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
        
        <div class="text-center mt-4">
            <a href="dishes.php" class="btn btn-outline btn-lg">View All Dishes</a>
        </div>
    </section>
    <?php endif; ?>

    <!-- Featured Restaurants -->
    <section class="container mt-4">
        <h2 class="text-center mb-4">Featured Restaurants</h2>
        
        <?php if (empty($featuredRestaurants)): ?>
            <div class="text-center">
                <p>No restaurants available at the moment. Check back later!</p>
                <?php if (isLoggedIn()): ?>
                    <a href="request_restaurant.php" class="btn btn-primary">List Your Restaurant</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="restaurant-grid">
                <?php foreach ($featuredRestaurants as $restaurant): ?>
                    <div class="card restaurant-card">
                        <div class="restaurant-image">
                            🏪
                        </div>
                        <div class="card-body restaurant-info">
                            <h3><?php echo htmlspecialchars($restaurant['name']); ?></h3>
                            <p><?php echo htmlspecialchars($restaurant['address']); ?></p>
                            <p><?php echo htmlspecialchars(substr($restaurant['description'], 0, 100)); ?>...</p>
                            <?php if ($restaurant['cuisine_type']): ?>
                                <span class="cuisine-tag"><?php echo htmlspecialchars($restaurant['cuisine_type']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="restaurant_menu.php?id=<?php echo $restaurant['id']; ?>" class="btn btn-primary w-100">View Menu</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="restaurants.php" class="btn btn-outline btn-lg">View All Restaurants</a>
            </div>
        <?php endif; ?>
    </section>

    <!-- Features Section -->
    <section class="container mt-4">
        <h2 class="text-center mb-4">Why Choose FoodOrder?</h2>
        
        <div class="row">
            <div class="col-4">
                <div class="card text-center">
                    <div class="card-body">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🚚</div>
                        <h3>Fast Delivery</h3>
                        <p>Get your food delivered quickly and safely to your doorstep with our reliable delivery network.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-4">
                <div class="card text-center">
                    <div class="card-body">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🍽️</div>
                        <h3>Quality Food</h3>
                        <p>We partner with the best restaurants to ensure you get fresh, delicious, and high-quality meals.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-4">
                <div class="card text-center">
                    <div class="card-body">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">💳</div>
                        <h3>Easy Payment</h3>
                        <p>Multiple payment options available for your convenience. Order now, pay later options available.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cart Summary (if user is logged in and has items) -->
    <?php if (isLoggedIn() && !empty(getCartItems())): ?>
        <div class="card" style="position: sticky; bottom: 20px; z-index: 50; margin: 20px;">
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

<style>
.restaurant-name {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.restaurant-name a {
    text-decoration: none;
}

.restaurant-name a:hover {
    text-decoration: underline;
}
.search-btn-outline {
    background: transparent;
    border: 2px solid var(--background-white);
    color: var(--background-white);
}
</style>

<?php require_once 'footer.php'; ?>