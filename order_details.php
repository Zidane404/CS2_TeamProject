<?php
session_start();
 
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorised']);
    exit;
}
 
require_once 'db_config.php';
 
$userId  = (int) $_SESSION['user_id'];
$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
 
if (!$orderId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid order ID']);
    exit;
}
 
try {
    
    $stmt = $pdo->prepare('
        SELECT order_id, order_total, order_status, payment_status, placed_at, currency
        FROM orders
        WHERE order_id = ? AND user_id = ?
    ');
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
 
    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        exit;
    }
 
    
    $stmt = $pdo->prepare('
        SELECT
            oi.quantity,
            oi.price_each AS unit_price,
            (oi.quantity * oi.price_each) AS line_total,
            p.name AS product_name,
            p.main_image AS image_url
        FROM order_items oi
        JOIN products p ON p.product_id = oi.product_id
        WHERE oi.order_id = ?
        ORDER BY oi.order_item_id ASC
    ');
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'order'   => $order,
        'items'   => $items,
    ]);
 
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}