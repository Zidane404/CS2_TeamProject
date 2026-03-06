<?php
session_start();
header('Content-Type: application/json');

require_once 'db_config.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in.'
    ]);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    echo json_encode([
        'success' => false,
        'message' => 'All password fields are required.'
    ]);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode([
        'success' => false,
        'message' => 'New password and confirm password do not match.'
    ]);
    exit;
}

if (
    strlen($newPassword) < 8 ||
    !preg_match('/[A-Z]/', $newPassword) ||
    !preg_match('/[a-z]/', $newPassword) ||
    !preg_match('/\d/', $newPassword)
) {
    echo json_encode([
        'success' => false,
        'message' => 'New password must be at least 8 characters long and include uppercase, lowercase and a number.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT hashed_password
        FROM users
        WHERE user_id = ?
          AND deleted_at IS NULL
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found.'
        ]);
        exit;
    }

    if (!password_verify($currentPassword, $user['hashed_password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect.'
        ]);
        exit;
    }

    if (password_verify($newPassword, $user['hashed_password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Your new password must be different from your current password.'
        ]);
        exit;
    }

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        UPDATE users
        SET hashed_password = ?,
            updated_at = NOW()
        WHERE user_id = ?
          AND deleted_at IS NULL
    ");
    $stmt->execute([$newHash, $userId]);

    echo json_encode([
        'success' => true,
        'message' => 'Password updated successfully.'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}