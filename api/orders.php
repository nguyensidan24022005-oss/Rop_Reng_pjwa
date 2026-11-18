<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed.'
        ]);

        exit;
    }

    $query = $pdo->query(
        "SELECT
            id,
            order_code,
            customer_name,
            status,
            subtotal,
            total,
            created_at
        FROM orders
        ORDER BY created_at DESC, id DESC"
    );

    $orders = $query->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $orders
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Không thể tải danh sách đơn hàng.'
    ]);
}