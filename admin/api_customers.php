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
        // Fetch all active customers
        $stmt = $pdo->prepare("
            SELECT user_id, first_name, last_name, email, phone, created_at 
            FROM users 
            WHERE role = 'customer' AND deleted_at IS NULL 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'customers' => $customers]);

    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $user_id = $input['user_id'] ?? null;
        $first_name = trim($input['first_name'] ?? '');
        $last_name = trim($input['last_name'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');

        if (empty($first_name) || empty($last_name) || empty($email)) {
            http_response_code(400);
            echo json_encode(['error' => 'First name, last name, and email are required.']);
            exit;
        }

        if ($user_id) {
            // UPDATE EXISTING CUSTOMER
            $stmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, email = ?, phone = ?, updated_at = NOW() 
                WHERE user_id = ? AND role = 'customer'
            ");
            $stmt->execute([$first_name, $last_name, $email, $phone, $user_id]);
            echo json_encode(['success' => true, 'message' => 'Customer updated successfully.']);
        } else {
            // CREATE NEW CUSTOMER
            $hashed_password = password_hash('Password123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (first_name, last_name, email, phone, hashed_password, role, force_password_reset) 
                VALUES (?, ?, ?, ?, ?, 'customer', 1)
            ");
            $stmt->execute([$first_name, $last_name, $email, $phone, $hashed_password]);
            echo json_encode(['success' => true, 'message' => 'Customer added successfully. Default password: Password123']);
        }

    } elseif ($method === 'DELETE') {
        // SOFT DELETE CUSTOMER
        $input = json_decode(file_get_contents('php://input'), true);
        $user_id = $input['user_id'] ?? null;

        if (!$user_id) {
            http_response_code(400);
            echo json_encode(['error' => 'User ID is required for deletion.']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET deleted_at = NOW() WHERE user_id = ? AND role = 'customer'");
        $stmt->execute([$user_id]);
        echo json_encode(['success' => true, 'message' => 'Customer deleted successfully.']);

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }

} catch (PDOException $e) {
    // Handle duplicate emails gracefully
    if ($e->getCode() == 23000) { 
        http_response_code(400);
        echo json_encode(['error' => 'Email address is already in use.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database error occurred.']);
    }
}