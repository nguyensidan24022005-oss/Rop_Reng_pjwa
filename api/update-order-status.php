<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed.'
        ]);

        exit;
    }

    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($input)) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.'
        ]);

        exit;
    }

    $orderId = (int) ($input['order_id'] ?? 0);
    $newStatus = trim(
        $input['status'] ?? ''
    );

    $allowedStatuses = [
        'pending',
        'confirmed',
        'completed',
        'cancelled'
    ];

    if ($orderId <= 0) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Mã đơn không hợp lệ.'
        ]);

        exit;
    }

    if (!in_array(
        $newStatus,
        $allowedStatuses,
        true
    )) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Trạng thái không hợp lệ.'
        ]);

        exit;
    }

    $orderQuery = $pdo->prepare(
        "SELECT
            id,
            status
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
            'message' => 'Không tìm thấy đơn.'
        ]);

        exit;
    }

    $currentStatus = $order['status'];

    $allowedTransitions = [
        'pending' => [
            'confirmed',
            'cancelled'
        ],

        'confirmed' => [
            'completed',
            'cancelled'
        ],

        'completed' => [],

        'cancelled' => []
    ];

    if (
        $newStatus !== $currentStatus &&
        !in_array(
            $newStatus,
            $allowedTransitions[$currentStatus],
            true
        )
    ) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' =>
                'Không thể chuyển trạng thái đơn.'
        ]);

        exit;
    }

    if ($newStatus === $currentStatus) {

        echo json_encode([
            'success' => true,
            'message' => 'Trạng thái không thay đổi.',
            'data' => [
                'order_id' => $orderId,
                'status' => $currentStatus
            ]
        ]);

        exit;
    }

    $updateQuery = $pdo->prepare(
        "UPDATE orders
        SET status = :status
        WHERE id = :id"
    );

    $updateQuery->execute([
        'status' => $newStatus,
        'id' => $orderId
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật trạng thái thành công.',
        'data' => [
            'order_id' => $orderId,
            'old_status' => $currentStatus,
            'status' => $newStatus
        ]
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Không thể cập nhật trạng thái.'
    ]);
}