<?php
$pageTitle = 'Browse Dishes';
require_once 'header.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$cuisineFilter = isset($_GET['cuisine']) ? sanitizeInput($_GET['cuisine']) : '';
$categoryFilter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$priceFilter = isset($_GET['price']) ? sanitizeInput($_GET['price']) : '';
$sortFilter = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'name';
$dishId = isset($_GET['dish_id']) ? (int)$_GET['dish_id'] : 0;

// Build query
$pdo = getDBConnection();
$query = "SELECT d.*, r.name as restaurant_name, r.cuisine_type 
          FROM dishes d 
          JOIN restaurants r ON d.restaurant_id = r.id 
          WHERE d.is_available = 1 AND r.status = 'approved'";
$params = [];

if ($search) {
    $query .= " AND (d.name LIKE ? OR d.description LIKE ? OR r.name LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if ($cuisineFilter) {
    $query .= " AND r.cuisine_type = ?";
    $params[] = $cuisineFilter;
}

if ($categoryFilter) {
    $query .= " AND d.category = ?";
    $params[] = $categoryFilter;
}

if ($priceFilter) {
    switch ($priceFilter) {
        case 'under_10':
            $query .= " AND d.price < 10";
            break;
        case '10_20':
            $query .= " AND d.price BETWEEN 10 AND 20";
            break;
        case '20_30':
            $query .= " AND d.price BETWEEN 20 AND 30";
            break;
        case 'over_30':
            $query .= " AND d.price > 30";
            break;
    }
}

// Add sorting
switch ($sortFilter) {
    case 'price_low':
        $query .= " ORDER BY d.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY d.price DESC";
        break;
    case 'restaurant':
        $query .= " ORDER BY r.name ASC, d.name ASC";
        break;
    default:
        $query .= " ORDER BY d.name ASC";
        break;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get available cuisines for filter
$cuisineStmt = $pdo->prepare("SELECT DISTINCT r.cuisine_type FROM restaurants r 
                             JOIN dishes d ON r.id = d.restaurant_id 
                             WHERE r.status = 'approved' AND d.is_available = 1 
                             AND r.cuisine_type IS NOT NULL ORDER BY r.cuisine_type");
$cuisineStmt->execute();
$cuisines = $cuisineStmt->fetchAll(PDO::FETCH_COLUMN);

// Get available categories for filter
$categoryStmt = $pdo->prepare("SELECT DISTINCT category FROM dishes d 
                              JOIN restaurants r ON d.restaurant_id = r.id 
                              WHERE d.is_available = 1 AND r.status = 'approved' 
                              AND d.category IS NOT NULL ORDER BY d.category");
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

// If specific dish ID is provided, highlight it
$highlightDish = null;
if ($dishId) {
    foreach ($dishes as $dish) {
        if ($dish['id'] == $dishId) {
            $highlightDish = $dish;
            break;
        }
    }
}
?>

<main class="container mt-4">
    <div class="row align-center mb-4">
        <div class="col-8">
            <h1>Browse Dishes</h1>
            <?php if ($search): ?>
                <p>Search results for: <strong><?php echo htmlspecialchars($search); ?></strong></p>
            <?php endif; ?>
            <?php if ($highlightDish): ?>
                <p>Showing dish: <strong><?php echo htmlspecialchars($highlightDish['name']); ?></strong></p>
            <?php endif; ?>
        </div>
        <div class="col-4 text-right">
            <a href="restaurants.php" class="btn btn-outline">Browse Restaurants</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" id="filterForm" class="row align-center">
                <div class="col-3">
                    <input type="text" name="search" class="form-control" placeholder="Search dishes..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-2">
                    <select name="cuisine" id="cuisineFilter" class="form-control">
                        <option value="">All Cuisines</option>
                        <?php foreach ($cuisines as $cuisine): ?>
                            <option value="<?php echo htmlspecialchars($cuisine); ?>" 
                                    <?php echo $cuisineFilter == $cuisine ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cuisine); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-2">
                    <select name="category" id="categoryFilter" class="form-control">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" 
                                    <?php echo $categoryFilter == $category ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-2">
                    <select name="price" id="priceFilter" class="form-control">
                        <option value="">All Prices</option>
                        <option value="under_10" <?php echo $priceFilter == 'under_10' ? 'selected' : ''; ?>>Under $10</option>
                        <option value="10_20" <?php echo $priceFilter == '10_20' ? 'selected' : ''; ?>>$10 - $20</option>
                        <option value="20_30" <?php echo $priceFilter == '20_30' ? 'selected' : ''; ?>>$20 - $30</option>
                        <option value="over_30" <?php echo $priceFilter == 'over_30' ? 'selected' : ''; ?>>Over $30</option>
                    </select>
                </div>
                <div class="col-2">
                    <select name="sort" id="sortFilter" class="form-control">
                        <option value="name" <?php echo $sortFilter == 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                        <option value="price_low" <?php echo $sortFilter == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sortFilter == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="restaurant" <?php echo $sortFilter == 'restaurant' ? 'selected' : ''; ?>>Restaurant</option>
                    </select>
                </div>
                <div class="col-1">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
                <?php if ($search || $cuisineFilter || $categoryFilter || $priceFilter || $sortFilter != 'name'): ?>
                    <div class="col-12 mt-2">
                        <a href="dishes.php" class="btn btn-outline btn-sm">Clear All Filters</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Results -->
    <?php if (empty($dishes)): ?>
        <div class="text-center">
            <div style="font-size: 4rem; margin-bottom: 1rem;">🍽️</div>
            <h3>No dishes found</h3>
            <p>Try adjusting your search criteria or browse our restaurants.</p>
            <a href="restaurants.php" class="btn btn-primary">Browse Restaurants</a>
        </div>
    <?php else: ?>
        <div class="menu-grid">
            <?php foreach ($dishes as $dish): ?>
                <div class="card dish-card <?php echo ($highlightDish && $highlightDish['id'] == $dish['id']) ? 'highlighted' : ''; ?>">
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
                            <p class="dish-description"><?php echo htmlspecialchars($dish['description']); ?></p>
                        <?php endif; ?>
                        <?php if ($dish['category']): ?>
                            <span class="cuisine-tag"><?php echo htmlspecialchars($dish['category']); ?></span>
                        <?php endif; ?>
                        <?php if ($dish['cuisine_type']): ?>
                            <span class="cuisine-tag" style="background: var(--secondary-color);"><?php echo htmlspecialchars($dish['cuisine_type']); ?></span>
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
            <p><?php echo count($dishes); ?> dish(es) found</p>
        </div>
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

<style>
.highlighted {
    border: 3px solid var(--primary-color);
    box-shadow: 0 0 20px rgba(255, 107, 53, 0.3);
}

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
</style>

<?php require_once 'footer.php'; ?>