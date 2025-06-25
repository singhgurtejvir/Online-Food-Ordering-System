<?php
$pageTitle = 'Restaurants';
require_once 'header.php';

// Get search parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$cuisineFilter = isset($_GET['cuisine']) ? sanitizeInput($_GET['cuisine']) : '';
$sortFilter = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'name';

// Build query
$pdo = getDBConnection();
$query = "SELECT * FROM restaurants WHERE status = 'approved'";
$params = [];

if ($search) {
    $query .= " AND (name LIKE ? OR description LIKE ? OR cuisine_type LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if ($cuisineFilter) {
    $query .= " AND cuisine_type = ?";
    $params[] = $cuisineFilter;
}

// Add sorting
switch ($sortFilter) {
    case 'cuisine':
        $query .= " ORDER BY cuisine_type ASC, name ASC";
        break;
    case 'newest':
        $query .= " ORDER BY created_at DESC";
        break;
    default:
        $query .= " ORDER BY name ASC";
        break;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get available cuisines for filter
$cuisineStmt = $pdo->prepare("SELECT DISTINCT cuisine_type FROM restaurants WHERE status = 'approved' AND cuisine_type IS NOT NULL ORDER BY cuisine_type");
$cuisineStmt->execute();
$cuisines = $cuisineStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<link rel="stylesheet" href="style.css">
<main class="container mt-4">
    <div class="row align-center mb-4">
        <div class="col-8">
            <h1>Restaurants</h1>
            <?php if ($search): ?>
                <p>Search results for: <strong><?php echo htmlspecialchars($search); ?></strong></p>
            <?php endif; ?>
        </div>
        <div class="col-4 text-right">
            <a href="dishes.php" class="btn btn-outline" style="margin-right: 1rem;">Browse Dishes</a>
            <a href="request_restaurant.php" class="btn btn-primary">List Your Restaurant</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" id="filterForm" class="row align-center">
                <div class="col-4">
                    <input type="text" name="search" class="form-control" placeholder="Search restaurants..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-3">
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
                    <select name="sort" id="sortFilter" class="form-control">
                        <option value="name" <?php echo $sortFilter == 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                        <option value="cuisine" <?php echo $sortFilter == 'cuisine' ? 'selected' : ''; ?>>Cuisine Type</option>
                        <option value="newest" <?php echo $sortFilter == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    </select>
                </div>
                <div class="col-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
                <?php if ($search || $cuisineFilter || $sortFilter != 'name'): ?>
                    <div class="col-1">
                        <a href="restaurants.php" class="btn btn-outline">Clear</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Results -->
    <?php if (empty($restaurants)): ?>
        <div class="text-center">
            <div style="font-size: 4rem; margin-bottom: 1rem;">🍽️</div>
            <h3>No restaurants found</h3>
            <p>Try adjusting your search criteria or check back later for new restaurants.</p>
            <a href="request_restaurant.php" class="btn btn-primary">Be the first to list your restaurant!</a>
        </div>
    <?php else: ?>
        <div class="restaurant-grid">
            <?php foreach ($restaurants as $restaurant): ?>
                <div class="card restaurant-card">
                    <div class="restaurant-image">
                        🏪
                    </div>
                    <div class="card-body restaurant-info">
                        <h3><?php echo htmlspecialchars($restaurant['name']); ?></h3>
                        <p><strong>📍</strong> <?php echo htmlspecialchars($restaurant['address']); ?></p>
                        <p><strong>📞</strong> <?php echo htmlspecialchars($restaurant['phone']); ?></p>
                        <?php if ($restaurant['description']): ?>
                            <p><?php echo htmlspecialchars(substr($restaurant['description'], 0, 100)); ?>...</p>
                        <?php endif; ?>
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
            <p><?php echo count($restaurants); ?> restaurant(s) found</p>
        </div>
    <?php endif; ?>
</main>

<?php require_once 'footer.php'; ?>