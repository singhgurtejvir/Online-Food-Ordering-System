<?php
require_once '../functions.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = getDBConnection();
    $results = [];
    
    // Search restaurants
    $stmt = $pdo->prepare("SELECT id, name, cuisine_type FROM restaurants 
                          WHERE status = 'approved' AND (name LIKE ? OR cuisine_type LIKE ?) 
                          LIMIT 5");
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($restaurants as $restaurant) {
        $results[] = [
            'type' => 'restaurant',
            'id' => $restaurant['id'],
            'name' => $restaurant['name'],
            'subtitle' => $restaurant['cuisine_type'] ? $restaurant['cuisine_type'] . ' Restaurant' : 'Restaurant'
        ];
    }
    
    // Search dishes
    $stmt = $pdo->prepare("SELECT d.id, d.name, d.price, r.name as restaurant_name 
                          FROM dishes d 
                          JOIN restaurants r ON d.restaurant_id = r.id 
                          WHERE d.is_available = 1 AND r.status = 'approved' 
                          AND (d.name LIKE ? OR d.description LIKE ?) 
                          LIMIT 5");
    $stmt->execute([$searchTerm, $searchTerm]);
    $dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($dishes as $dish) {
        $results[] = [
            'type' => 'dish',
            'id' => $dish['id'],
            'name' => $dish['name'],
            'subtitle' => 'From ' . $dish['restaurant_name'] . ' - ' . formatCurrency($dish['price'])
        ];
    }
    
    echo json_encode($results);
    
} catch (Exception $e) {
    echo json_encode([]);
}
?>