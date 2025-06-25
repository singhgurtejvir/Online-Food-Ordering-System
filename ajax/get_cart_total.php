<?php
require_once '../functions.php';

header('Content-Type: application/json');

$total = 0;
if (isLoggedIn()) {
    $total = getCartTotal();
}

echo json_encode(['total' => formatCurrency($total)]);
?>