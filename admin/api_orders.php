<?php
session_start();
header('Content-Type: application/json');

require __DIR__ . '/../db_config.php';

// Verify Admin Session
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['order_id'])) {
            // Fetch items for a specific order
            $stmt = $pdo->prepare("
                SELECT oi.quantity, oi.price_each, oi.total_price, p.name, p.sku, p.main_image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.product_id 
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$_GET['order_id']]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'items' => $items]);
        } else {
            // Fetch all orders with user and address details
            $stmt = $pdo->prepare("
                SELECT o.order_id, o.order_total, o.currency, o.order_status, o.payment_status, o.placed_at, 
                       u.first_name, u.last_name, u.email,
                       a.address_line1, a.city, a.postcode, a.country
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.user_id 
                LEFT JOIN addresses a ON o.address_id = a.address_id 
                ORDER BY o.placed_at DESC
            ");
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'orders' => $orders]);
        }

    } elseif ($method === 'POST') {
        // Update Order Status
        $input = json_decode(file_get_contents('php://input'), true);
        $order_id = $input['order_id'] ?? null;
        $order_status = $input['order_status'] ?? null;

        $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];

        if (!$order_id || !in_array($order_status, $valid_statuses)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid order ID or status.']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, updated_at = NOW() WHERE order_id = ?");
        $stmt->execute([$order_status, $order_id]);
        
        echo json_encode(['success' => true, 'message' => "Order #$order_id updated to $order_status."]);

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error occurred.']);
}