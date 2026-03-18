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

try {

    $pdo->beginTransaction();

    // check user exists first
    $check = $pdo->prepare("
        SELECT user_id
        FROM users
        WHERE user_id = ?
        LIMIT 1
    ");
    $check->execute([$userId]);

    if (!$check->fetch()) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Account not found.'
        ]);
        exit;
    }

    // delete user (foreign keys will cascade if set in DB)
    $stmt = $pdo->prepare("
        DELETE FROM users
        WHERE user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId]);

    if ($stmt->rowCount() !== 1) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Unable to delete account.'
        ]);
        exit;
    }

    $pdo->commit();

    // destroy session safely
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();

    echo json_encode([
        'success' => true,
        'message' => 'Your account has been permanently deleted.'
    ]);

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'success' => false,
        'message' => 'Error deleting account.'
    ]);
}