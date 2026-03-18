<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.html");
    exit;
}

$action = $_POST['action'] ?? '';
$product_id = $_POST['product_id'] ?? 1;
$user_id = $_SESSION['user_id'];

try {
    if ($action === 'add') {
        $rating = (int)$_POST['rating'];
        $body = trim($_POST['body']);
        
        if ($rating >= 1 && $rating <= 5 && !empty($body)) {
            $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, body, approved) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$product_id, $user_id, $rating, $body]);
        }
        
    } elseif ($action === 'delete') {
        $review_id = (int)$_POST['review_id'];
        
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE review_id = ? AND user_id = ?");
        $stmt->execute([$review_id, $user_id]);
    }
    
} catch (PDOException $e) {
    die("Error processing review: " . $e->getMessage());
}

header("Location: item_page.php?id=" . $product_id);
exit;