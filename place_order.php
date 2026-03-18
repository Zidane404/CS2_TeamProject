<?php
session_start();
header('Content-Type: application/json');

require_once 'db_config.php';

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'You must be logged in to place an order.']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

try {
    // Start database transaction
    $pdo->beginTransaction();

    // 1. Get user's cart and its items
    $stmt = $pdo->prepare('
        SELECT c.cart_id, ci.product_id, ci.quantity, p.price
        FROM carts c
        JOIN cart_items ci ON c.cart_id = ci.cart_id
        JOIN products p ON ci.product_id = p.product_id
        WHERE c.user_id = ?
    ');
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cartItems)) {
        http_response_code(400);
        echo json_encode(['error' => 'Your cart is empty.']);
        exit;
    }

    // 2. Calculate order total
    $total = 0;
    foreach ($cartItems as $item) {
        $total += (float)$item['price'] * (int)$item['quantity'];
    }

    // 3. Insert into orders table
    $stmt = $pdo->prepare('
        INSERT INTO orders (user_id, order_total, currency, order_status, payment_status, placed_at)
        VALUES (?, ?, "GBP", "pending", "unpaid", NOW())
    ');
    $stmt->execute([$userId, $total]);
    $orderId = $pdo->lastInsertId();

    // 4. Insert each item into order_items
    $stmt = $pdo->prepare('
        INSERT INTO order_items (order_id, product_id, quantity, price_each)
        VALUES (?, ?, ?, ?)
    ');
    foreach ($cartItems as $item) {
        $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
    }

    // 5. Clear the cart (delete all cart_items for this user's cart)
    $stmt = $pdo->prepare('
        DELETE ci FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.cart_id
        WHERE c.user_id = ?
    ');
    $stmt->execute([$userId]);

    // Commit transaction
    $pdo->commit();

    // Return success with the new order ID
    echo json_encode(['success' => true, 'order_id' => $orderId]);

} catch (Exception $e) {
    // Rollback on any error
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Failed to place order: ' . $e->getMessage()]);
}