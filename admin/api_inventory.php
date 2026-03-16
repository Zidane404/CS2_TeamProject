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
$admin_id = $_SESSION['user_id'];

try {
    if ($method === 'GET') {
        $action = $_GET['action'] ?? '';

        if ($action === 'reports') {
            // 1. Total distinct products low on stock
            $stmtLow = $pdo->query("SELECT COUNT(*) FROM inventory WHERE stock_quantity <= threshold_level");
            $lowStockCount = $stmtLow->fetchColumn();

            // 2. Total value of current inventory
            $stmtVal = $pdo->query("
                SELECT SUM(i.stock_quantity * p.price) as total_value 
                FROM inventory i 
                JOIN products p ON i.product_id = p.product_id
            ");
            $totalValue = $stmtVal->fetchColumn() ?: 0;

            // 3. Last 10 inventory activities
            $stmtLogs = $pdo->query("
                SELECT l.change_type, l.quantity_changed, l.created_at, p.name 
                FROM inventory_logs l 
                JOIN products p ON l.product_id = p.product_id 
                ORDER BY l.created_at DESC LIMIT 10
            ");
            $recentActivity = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

            // 4. Creative Feature: Restock Urgency Score
            $stmtUrgency = $pdo->query("
                SELECT p.product_id, p.name, p.sku, i.stock_quantity, i.threshold_level, 
                       (i.threshold_level - i.stock_quantity) as urgency_score 
                FROM inventory i 
                JOIN products p ON i.product_id = p.product_id 
                WHERE i.stock_quantity <= i.threshold_level 
                ORDER BY urgency_score DESC 
                LIMIT 10
            ");
            $urgencyList = $stmtUrgency->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'low_stock_count' => $lowStockCount,
                'total_value' => number_format((float)$totalValue, 2, '.', ''),
                'recent_activity' => $recentActivity,
                'urgency_list' => $urgencyList
            ]);
            exit;
        }

        // Default GET: Fetch all products with their inventory data
        $stmt = $pdo->prepare("
            SELECT p.product_id, p.sku, p.name, p.short_description, p.price, p.main_image, p.category_id,
                   i.stock_quantity, i.threshold_level 
            FROM products p 
            LEFT JOIN inventory i ON p.product_id = i.product_id 
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'products' => $products]);

    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';

        if ($action === 'update_stock') {
            $product_id = $input['product_id'] ?? null;
            $quantity_changed = (int)($input['quantity_changed'] ?? 0);
            
            if (!$product_id || $quantity_changed === 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid product or quantity.']);
                exit;
            }

            $change_type = $quantity_changed > 0 ? 'incoming' : 'outgoing';
            $reason = "Manual adjustment via dashboard";

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("UPDATE inventory SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
                $stmt->execute([$quantity_changed, $product_id]);

                $logStmt = $pdo->prepare("
                    INSERT INTO inventory_logs (product_id, change_type, quantity_changed, reason, performed_by) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $logStmt->execute([$product_id, $change_type, $quantity_changed, $reason, $admin_id]);

                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Stock updated successfully.']);
            } catch (Exception $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['error' => 'Transaction failed. No changes were made.']);
            }
        } elseif ($action === 'save_product') {
            // (Same as previously provided save_product logic)
            $product_id = $input['product_id'] ?? null;
            $sku = trim($input['sku'] ?? '');
            $name = trim($input['name'] ?? '');
            $price = $input['price'] ?? 0;
            $short_desc = trim($input['short_description'] ?? '');
            $category_id = $input['category_id'] ?? 1; 

            if (empty($sku) || empty($name)) {
                http_response_code(400);
                echo json_encode(['error' => 'SKU and Name are required.']);
                exit;
            }

            if ($product_id) {
                $stmt = $pdo->prepare("UPDATE products SET sku = ?, name = ?, price = ?, short_description = ?, category_id = ?, updated_at = NOW() WHERE product_id = ?");
                $stmt->execute([$sku, $name, $price, $short_desc, $category_id, $product_id]);
                echo json_encode(['success' => true, 'message' => 'Product updated.']);
            } else {
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare("INSERT INTO products (sku, name, price, short_description, category_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$sku, $name, $price, $short_desc, $category_id]);
                    $new_product_id = $pdo->lastInsertId();

                    $invStmt = $pdo->prepare("INSERT INTO inventory (product_id, stock_quantity, threshold_level) VALUES (?, 0, 5)");
                    $invStmt->execute([$new_product_id]);

                    $pdo->commit();
                    echo json_encode(['success' => true, 'message' => 'Product created with default inventory.']);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to create product.']);
                }
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action specified.']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error occurred.']);
}