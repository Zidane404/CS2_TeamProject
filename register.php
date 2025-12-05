<?php
session_start();
header('Content-Type: application/json');

require __DIR__ . '/db_config.php';

// Prevent registration while already logged in
if (!empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'You are already logged in. Please log out before creating another account.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$first_name = trim($input['first_name'] ?? '');
$last_name = trim($input['last_name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 6 characters']);
    exit;
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ? AND deleted_at IS NULL');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Email already registered']);
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $pdo->prepare('
        INSERT INTO users (first_name, last_name, email, hashed_password, role)
        VALUES (?, ?, ?, ?, "customer")
    ');
    $stmt->execute([$first_name, $last_name, $email, $hashed_password]);

    $user_id = $pdo->lastInsertId();

    // Auto-login: set session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_email'] = $email;
    $_SESSION['first_name'] = $first_name;
    $_SESSION['user_name'] = $first_name . ' ' . $last_name;
    $_SESSION['user_role'] = 'customer';

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'user_id' => $user_id
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Registration failed']);
}

