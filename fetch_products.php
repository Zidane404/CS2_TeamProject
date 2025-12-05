<?php
header('Content-Type: application/json');

require __DIR__ . '/db_config.php';

try {
    $stmt = $pdo->query(
        'SELECT 
            product_id AS id,
            name,
            price,
            main_image AS image_url,
            category_id
         FROM products
         WHERE is_active = 1
         ORDER BY category_id ASC, product_id ASC'
    );
    $rows = $stmt->fetchAll();
    echo json_encode(['items' => $rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed']);
}


