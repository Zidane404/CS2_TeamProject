<?php
session_start();

require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

try {

    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    if ($quantity < 1) $quantity = 1;

    $stmt = $pdo->prepare("SELECT cart_id FROM carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart) {
        $cart_id = $cart['cart_id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        $cart_id = $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cart_id, $product_id]);
    $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingItem) {
        $new_quantity = $existingItem['quantity'] + $quantity;
        $updateStmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND product_id = ?");
        $updateStmt->execute([$new_quantity, $cart_id, $product_id]);
    } else {
        $insertStmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
        $insertStmt->execute([$cart_id, $product_id, $quantity]);
    }

    header("Location: cart.html"); 
    exit();

} catch (PDOException $e) {
    die("Error adding to cart: " . $e->getMessage());
}
?>