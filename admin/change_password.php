<?php
session_start();
header('Content-Type: application/json');

// Ensure db_config.php is correctly referenced from the parent directory
require __DIR__ . '/../db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized. Please log in first.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$new_password = $input['new_password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';

if (empty($new_password) || empty($confirm_password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Both password fields are required.']);
    exit;
}

if ($new_password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(['error' => 'Passwords do not match.']);
    exit;
}

try {
    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the database and remove the force_password_reset flag
    $stmt = $pdo->prepare('
        UPDATE users 
        SET hashed_password = ?, force_password_reset = 0, updated_at = NOW() 
        WHERE user_id = ?
    ');

    $success = $stmt->execute([$hashed_password, $_SESSION['user_id']]);

    if ($success) {
        // Return the user_role so the frontend knows where to redirect
        echo json_encode([
            'success' => true,
            'message' => 'Password updated successfully.',
            'user_role' => $_SESSION['user_role']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update password.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'A database error occurred.']);
}