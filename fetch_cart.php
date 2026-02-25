<?php
session_start();
header('Content-Type: application/json');

require __DIR__ . '/db_config.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in', 'items' => []]);
    exit;
}

$userId = (int) $_SESSION['user_id'];

try {
    $sql = 'SELECT ci.product_id,
                   ci.quantity,
                   p.name,
                   p.price,
                   p.main_image AS image_url
            FROM carts c
            JOIN cart_items ci ON ci.cart_id = c.cart_id
            JOIN products p ON p.product_id = ci.product_id
            WHERE c.user_id = :user_id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $rows = $stmt->fetchAll();

    $subtotal = 0.0;
    foreach ($rows as $row) {
        $price = is_numeric($row['price']) ? (float) $row['price'] : 0.0;
        $qty   = (int) $row['quantity'];
        $subtotal += $price * $qty;
    }

    echo json_encode([
        'items'    => $rows,
        'subtotal' => $subtotal,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not load cart', 'items' => []]);
}


