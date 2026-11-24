<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

function jsonResponse(array $data, int $status = 200): never
{
    http_response_code($status);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

function getRequestData(): array
{
    $raw = file_get_contents('php://input');

    if (!$raw) {
        return [];
    }

    $data = json_decode($raw, true);

    return is_array($data) ? $data : [];
}

function validateId(mixed $value): int|false
{
    if (!is_int($value) && !is_string($value)) {
        return false;
    }

    $id = filter_var(
        $value,
        FILTER_VALIDATE_INT
    );

    if ($id === false || $id <= 0) {
        return false;
    }

    return $id;
}

$method = $_SERVER['REQUEST_METHOD'];

try {

    if ($method === 'GET') {

        $stmt = $pdo->query(
            "SELECT
                id,
                name,
                description,
                price,
                image,
                is_active,
                created_at,
                updated_at
             FROM toppings
             ORDER BY id ASC"
        );

        $toppings = $stmt->fetchAll();

        foreach ($toppings as &$topping) {

            $topping['id'] = (int) $topping['id'];
            $topping['price'] = (float) $topping['price'];
            $topping['is_active'] =
                (int) $topping['is_active'];
        }

        unset($topping);

        jsonResponse([
            'success' => true,
            'data' => $toppings
        ]);
    }

    if ($method === 'POST') {

        $data = getRequestData();

        $name = $data['name'] ?? null;
        $description = $data['description'] ?? null;
        $price = $data['price'] ?? null;
        $image = $data['image'] ?? null;

        if (!is_string($name)) {

            jsonResponse([
                'success' => false,
                'message' => 'Tên topping không hợp lệ.'
            ], 422);
        }

        $name = trim($name);

        if ($name === '') {

            jsonResponse([
                'success' => false,
                'message' => 'Tên topping là bắt buộc.'
            ], 422);
        }

        if (
            mb_strlen($name, 'UTF-8') > 100
        ) {

            jsonResponse([
                'success' => false,
                'message' => 'Tên topping không được vượt quá 100 ký tự.'
            ], 422);
        }

        if (
            $description !== null &&
            !is_string($description)
        ) {

            jsonResponse([
                'success' => false,
                'message' => 'Mô tả topping không hợp lệ.'
            ], 422);
        }

        if ($description !== null) {
            $description = trim($description);
        }

        if (
            $price === null ||
            !is_numeric($price) ||
            (float) $price < 0
        ) {

            jsonResponse([
                'success' => false,
                'message' => 'Giá topping không hợp lệ.'
            ], 422);
        }

        $price = (float) $price;

        if (
            $image !== null &&
            !is_string($image)
        ) {

            jsonResponse([
                'success' => false,
                'message' => 'Đường dẫn hình ảnh không hợp lệ.'
            ], 422);
        }

        if ($image !== null) {
            $image = trim($image);

            if ($image === '') {
                $image = null;
            }
        }

        $stmt = $pdo->prepare(
            "SELECT id
             FROM toppings
             WHERE name = ?
             LIMIT 1"
        );

        $stmt->execute([$name]);

        if ($stmt->fetch()) {

            jsonResponse([
                'success' => false,
                'message' => 'Tên topping đã tồn tại.'
            ], 422);
        }

        $stmt = $pdo->prepare(
            "INSERT INTO toppings
                (
                    name,
                    description,
                    price,
                    image,
                    is_active
                )
             VALUES
                (?, ?, ?, ?, 1)"
        );

        $stmt->execute([
            $name,
            $description,
            $price,
            $image
        ]);

        jsonResponse([
            'success' => true,
            'message' => 'Đã thêm topping.',
            'id' => (int) $pdo->lastInsertId()
        ], 201);
    }

    if ($method === 'PUT') {

        $data = getRequestData();

        $id = validateId(
            $data['id'] ?? null
        );

        $name = $data['name'] ?? null;
        $description = $data['description'] ?? null;
        $price = $data['price'] ?? null;
        $image = $data['image'] ?? null;

        if (
            $id === false ||
            !is_string($name)
        ) {

            jsonResponse([
                'success' => false,
                'message' => 'Dữ liệu topping không hợp lệ.'
            ], 422);
        }

        $name = trim($name);

        if (
            $name === '' ||
            mb_strlen($name, 'UTF-8') > 100
        ) {

            jsonResponse([
                'success' => false,
                'message' => 'Tên topping không hợp lệ.'
            ], 422);
        }

        if (
            $description !== null &&
            !is_string($description)
        ) {

            jsonResponse([
                'success' => false,
                'message' => 'Mô tả topping không hợp lệ.'
            ], 422);
        }

        if ($description !== null) {
            $description = trim($description);
        }

        if (
            $price === null ||
            !is_numeric($price) ||
            (float) $price < 0
        ) {

            jsonResponse([
                'success' => false,
                'message' => 'Giá topping không hợp lệ.'
            ], 422);
        }

        $price = (float) $price;

        if (
            $image !== null &&
            !is_string($image)
        ) {

            jsonResponse([
                'success' => false,
                'message' => 'Đường dẫn hình ảnh không hợp lệ.'
            ], 422);
        }

        if ($image !== null) {
            $image = trim($image);

            if ($image === '') {
                $image = null;
            }
        }

        $stmt = $pdo->prepare(
            "SELECT id
             FROM toppings
             WHERE id = ?
             LIMIT 1"
        );

        $stmt->execute([$id]);

        if (!$stmt->fetch()) {

            jsonResponse([
                'success' => false,
                'message' => 'Không tìm thấy topping.'
            ], 404);
        }

        $stmt = $pdo->prepare(
            "SELECT id
             FROM toppings
             WHERE name = ?
               AND id != ?
             LIMIT 1"
        );

        $stmt->execute([
            $name,
            $id
        ]);

        if ($stmt->fetch()) {

            jsonResponse([
                'success' => false,
                'message' => 'Tên topping đã tồn tại.'
            ], 422);
        }

        $stmt = $pdo->prepare(
            "UPDATE toppings
             SET
                name = ?,
                description = ?,
                price = ?,
                image = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $name,
            $description,
            $price,
            $image,
            $id
        ]);

        jsonResponse([
            'success' => true,
            'message' => 'Đã cập nhật topping.'
        ]);
    }

    if ($method === 'PATCH') {

        $data = getRequestData();

        $id = validateId(
            $data['id'] ?? null
        );

        if ($id === false) {

            jsonResponse([
                'success' => false,
                'message' => 'ID topping không hợp lệ.'
            ], 422);
        }

        $stmt = $pdo->prepare(
            "SELECT
                id,
                is_active
             FROM toppings
             WHERE id = ?
             LIMIT 1"
        );

        $stmt->execute([$id]);

        $topping = $stmt->fetch();

        if (!$topping) {

            jsonResponse([
                'success' => false,
                'message' => 'Không tìm thấy topping.'
            ], 404);
        }

        $newStatus =
            ((int) $topping['is_active'] === 1)
                ? 0
                : 1;

        $stmt = $pdo->prepare(
            "UPDATE toppings
             SET is_active = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $newStatus,
            $id
        ]);

        jsonResponse([
            'success' => true,
            'message' => $newStatus === 1
                ? 'Đã hiện topping.'
                : 'Đã ẩn topping.',
            'is_active' => $newStatus
        ]);
    }

    jsonResponse([
        'success' => false,
        'message' => 'Method không được hỗ trợ.'
    ], 405);

} catch (PDOException $e) {

    error_log(
        'Admin toppings database error: '
        . $e->getMessage()
    );

    jsonResponse([
        'success' => false,
        'message' => 'Database error.'
    ], 500);

} catch (Throwable $e) {

    error_log(
        'Admin toppings error: '
        . $e->getMessage()
    );

    jsonResponse([
        'success' => false,
        'message' => 'Không thể xử lý topping.'
    ], 500);
}