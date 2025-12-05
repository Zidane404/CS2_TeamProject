<?php
session_start();
header('Content-Type: application/json');

require __DIR__ . '/db_config.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT cart_id FROM carts WHERE user_id = ?');
    $stmt->execute([$userId]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cart) {
        echo json_encode(['error' => 'Cart not found']);
        exit;
    }

    $cartId = (int) $cart['cart_id'];

    $deleteStmt = $pdo->prepare('DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?');
    $deleteStmt->execute([$cartId, $productId]);

    if ($deleteStmt->rowCount() > 0) {
        echo json_encode(['ok' => true, 'message' => 'Item removed']);
    } else {
        echo json_encode(['error' => 'Item not found in cart']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>