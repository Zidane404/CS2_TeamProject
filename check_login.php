<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['logged_in' => false]);
} else {
    echo json_encode([
        'logged_in' => true,
        'user_id' => $_SESSION['user_id'],
        'first_name' => $_SESSION['first_name'] ?? '',
        'user_name' => $_SESSION['user_name'] ?? ''
    ]);
}

