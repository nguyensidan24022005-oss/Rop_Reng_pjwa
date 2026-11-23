<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed.'
        ], JSON_UNESCAPED_UNICODE);

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
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $productId = filter_var(
        $input['id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $activeRaw = $input['is_active'] ?? null;

    if (
        $productId === false ||
        $productId === null ||
        $productId <= 0
    ) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Mã sản phẩm không hợp lệ.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (!in_array($activeRaw, [0, 1, '0', '1'], true)) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Trạng thái sản phẩm không hợp lệ.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $isActive = (int) $activeRaw;

    $findProduct = $pdo->prepare("
        SELECT id, is_active
        FROM products
        WHERE id = :id
        LIMIT 1
    ");

    $findProduct->execute([
        'id' => $productId
    ]);

    $product = $findProduct->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy sản phẩm.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if ((int) $product['is_active'] === $isActive) {
        echo json_encode([
            'success' => true,
            'message' => 'Trạng thái sản phẩm không thay đổi.',
            'data' => [
                'id' => (int) $productId,
                'is_active' => $isActive
            ]
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $updateProduct = $pdo->prepare("
        UPDATE products
        SET is_active = :is_active
        WHERE id = :id
    ");

    $updateProduct->execute([
        'is_active' => $isActive,
        'id' => $productId
    ]);

    echo json_encode([
        'success' => true,
        'message' => $isActive === 1
            ? 'Đã hiển thị sản phẩm.'
            : 'Đã ẩn sản phẩm.',
        'data' => [
            'id' => (int) $productId,
            'is_active' => $isActive
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    error_log(
        'Admin toggle product error: ' . $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Không thể cập nhật trạng thái sản phẩm.'
    ], JSON_UNESCAPED_UNICODE);
}