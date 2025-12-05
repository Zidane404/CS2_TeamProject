<?php
session_start();
header('Content-Type: application/json');

require __DIR__ . '/db_config.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$userId    = (int) $_SESSION['user_id'];
$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$quantity  = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing product_id']);
    exit;
}

if ($quantity < 1) {
    $quantity = 1;
}

try {
    $stmt = $pdo->prepare('SELECT cart_id FROM carts WHERE user_id = ?');
    $stmt->execute([$userId]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart) {
        $cartId = (int) $cart['cart_id'];
    } else {
        $stmt = $pdo->prepare('INSERT INTO carts (user_id) VALUES (?)');
        $stmt->execute([$userId]);
        $cartId = (int) $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare('SELECT quantity FROM cart_items WHERE cart_id = ? AND product_id = ?');
    $stmt->execute([$cartId, $productId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $newQty = (int) $existing['quantity'] + $quantity;
        $update = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND product_id = ?');
        $update->execute([$newQty, $cartId, $productId]);
    } else {
        $insert = $pdo->prepare('INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)');
        $insert->execute([$cartId, $productId, $quantity]);
    }

    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    error_log('add_to_cart_function.php failed: ' . $e->getMessage());
    echo json_encode(['error' => 'Could not add to cart']);
}
