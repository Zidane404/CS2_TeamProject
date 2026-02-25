<?php

$useLocal = true;

if ($useLocal) {
    // Local MySQL for Testing
    $db_host = '127.0.0.1';
    $db_name = 'cs2team8_db03';
    $db_user = 'root';
    $db_pass = '';
} else {
    // Aston server credentials
    $db_host = 'localhost';
    $db_name = 'cs2team8_db03';
    $db_user = 'cs2team8';
    $db_pass = 'F3lCvksLmJqDqmsyllNrjsF8R';
}

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

