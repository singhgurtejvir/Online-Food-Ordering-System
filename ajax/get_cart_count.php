<?php
require_once '../functions.php';

header('Content-Type: application/json');

$count = 0;
if (isLoggedIn()) {
    $count = count(getCartItems());
}

echo json_encode(['count' => $count]);
?>