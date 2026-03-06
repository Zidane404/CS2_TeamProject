<?php
session_start();
header('Content-Type: application/json');

require_once 'db_config.php';


if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'You must be logged in to submit a return request.']);
    exit;
}

$userId = (int) $_SESSION['user_id'];


$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$orderId = trim($_POST['order_id'] ?? '');
$orderDate = trim($_POST['order_date'] ?? '');
$reason = trim($_POST['reason'] ?? '');
$message = trim($_POST['message'] ?? '');


if (empty($name) || empty($email) || empty($orderId) || empty($reason)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please fill all required fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address.']);
    exit;
}


try {
    $stmt = $pdo->prepare('SELECT order_id FROM orders WHERE order_id = ? AND user_id = ?');
    $stmt->execute([$orderId, $userId]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Order not found or does not belong to you.']);
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error while verifying order.']);
    exit;
}


$fullMessage = "Return Request\n\n";
$fullMessage .= "Order #: $orderId\n";
if (!empty($orderDate)) $fullMessage .= "Order Date: $orderDate\n";
$fullMessage .= "Reason: $reason\n";
$fullMessage .= "Additional Details:\n$message\n";

try {
    $stmt = $pdo->prepare('
        INSERT INTO contact_requests (user_id, name, email, subject, message, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ');
    $stmt->execute([$userId, $name, $email, 'Return Request', $fullMessage]);

    echo json_encode(['success' => true, 'message' => 'Return request submitted.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to submit return request.']);
}