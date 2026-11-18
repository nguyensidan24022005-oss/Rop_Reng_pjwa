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

    $orderId = filter_var(
        $_GET['id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($orderId === false) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Mã đơn không hợp lệ.'
        ]);

        exit;
    }

    $orderQuery = $pdo->prepare(
        "SELECT
            id,
            order_code,
            customer_name,
            status,
            subtotal,
            total,
            created_at
        FROM orders
        WHERE id = :id
        LIMIT 1"
    );

    $orderQuery->execute([
        'id' => $orderId
    ]);

    $order = $orderQuery->fetch();

    if (!$order) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy đơn hàng.'
        ]);

        exit;
    }

    $itemsQuery = $pdo->prepare(
        "SELECT
            product_id,
            variant_id,
            product_name,
            variant_name,
            unit_price,
            quantity,
            note,
            line_total
        FROM order_items
        WHERE order_id = :order_id"
    );

    $itemsQuery->execute([
        'order_id' => $orderId
    ]);

    echo json_encode([
        'success' => true,
        'data' => [
            'order' => $order,
            'items' => $itemsQuery->fetchAll()
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Không thể tải thông tin đơn hàng.'
    ]);
}
