<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'logged_in' => true,
        'user_id' => $_SESSION['user_id'],
        'first_name' => $_SESSION['first_name'] ?? '',
        'user_role' => $_SESSION['user_role'] ?? 'customer' // Return the role
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}
?>