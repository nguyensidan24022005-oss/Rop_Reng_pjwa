<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

function jsonResponse($data, int $status = 200): never
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

$method = $_SERVER['REQUEST_METHOD'];

try {

    if ($method === 'GET') {

        $stmt = $pdo->query(
            "SELECT
                id,
                name,
                slug,
                image,
                is_active,
                created_at
             FROM categories
             ORDER BY id ASC"
        );

        jsonResponse([
            'success' => true,
            'categories' => $stmt->fetchAll()
        ]);
    }

    if ($method === 'POST') {

        $data = getRequestData();

        $name = $data['name'] ?? null;
        $slug = $data['slug'] ?? null;

        if (!is_string($name) || !is_string($slug)) {
            jsonResponse([
                'success' => false,
                'message' => 'Dữ liệu danh mục không hợp lệ.'
            ], 422);
        }

        $name = trim($name);
        $slug = trim($slug);

        if ($name === '' || $slug === '') {
            jsonResponse([
                'success' => false,
                'message' => 'Tên và slug danh mục là bắt buộc.'
            ], 422);
        }

        if (
            mb_strlen($name, 'UTF-8') > 100 ||
            mb_strlen($slug, 'UTF-8') > 100
        ) {
            jsonResponse([
                'success' => false,
                'message' => 'Dữ liệu danh mục không hợp lệ.'
            ], 422);
        }

        $stmt = $pdo->prepare(
            "SELECT id
             FROM categories
             WHERE slug = ?
             LIMIT 1"
        );

        $stmt->execute([$slug]);

        if ($stmt->fetch()) {
            jsonResponse([
                'success' => false,
                'message' => 'Slug danh mục đã tồn tại.'
            ], 422);
        }

        $stmt = $pdo->prepare(
            "INSERT INTO categories
                (name, slug, is_active)
             VALUES
                (?, ?, 1)"
        );

        $stmt->execute([
            $name,
            $slug
        ]);

        jsonResponse([
            'success' => true,
            'message' => 'Đã thêm danh mục.',
            'id' => (int) $pdo->lastInsertId()
        ], 201);
    }

    if ($method === 'PUT') {

        $data = getRequestData();

        $idValue = $data['id'] ?? null;
        $id = is_int($idValue) || is_string($idValue)
            ? filter_var($idValue, FILTER_VALIDATE_INT)
            : false;
        $name = $data['name'] ?? null;
        $slug = $data['slug'] ?? null;

        if (!is_string($name) || !is_string($slug)) {
            jsonResponse([
                'success' => false,
                'message' => 'Dữ liệu danh mục không hợp lệ.'
            ], 422);
        }

        $name = trim($name);
        $slug = trim($slug);

        if (
            $id === false ||
            $id === null ||
            $id <= 0 ||
            $name === '' ||
            $slug === '' ||
            mb_strlen($name, 'UTF-8') > 100 ||
            mb_strlen($slug, 'UTF-8') > 100
        ) {
            jsonResponse([
                'success' => false,
                'message' => 'Dữ liệu danh mục không hợp lệ.'
            ], 422);
        }

        $stmt = $pdo->prepare(
            "SELECT id
             FROM categories
             WHERE id = ?
             LIMIT 1"
        );

        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            jsonResponse([
                'success' => false,
                'message' => 'Không tìm thấy danh mục.'
            ], 404);
        }

        $stmt = $pdo->prepare(
            "SELECT id
             FROM categories
             WHERE slug = ?
               AND id != ?
             LIMIT 1"
        );

        $stmt->execute([
            $slug,
            $id
        ]);

        if ($stmt->fetch()) {
            jsonResponse([
                'success' => false,
                'message' => 'Slug danh mục đã tồn tại.'
            ], 422);
        }

        $stmt = $pdo->prepare(
            "UPDATE categories
             SET name = ?,
                 slug = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $name,
            $slug,
            $id
        ]);

        jsonResponse([
            'success' => true,
            'message' => 'Đã cập nhật danh mục.'
        ]);
    }

    if ($method === 'PATCH') {

        $data = getRequestData();

        $idValue = $data['id'] ?? null;
        $id = is_int($idValue) || is_string($idValue)
            ? filter_var($idValue, FILTER_VALIDATE_INT)
            : false;

        if ($id === false || $id === null || $id <= 0) {
            jsonResponse([
                'success' => false,
                'message' => 'ID danh mục không hợp lệ.'
            ], 422);
        }

        $stmt = $pdo->prepare(
            "SELECT
                id,
                is_active
             FROM categories
             WHERE id = ?
             LIMIT 1"
        );

        $stmt->execute([$id]);

        $category = $stmt->fetch();

        if (!$category) {
            jsonResponse([
                'success' => false,
                'message' => 'Không tìm thấy danh mục.'
            ], 404);
        }

        $newStatus = ((int) $category['is_active'] === 1)
            ? 0
            : 1;

        $stmt = $pdo->prepare(
            "UPDATE categories
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
                ? 'Đã hiện danh mục.'
                : 'Đã ẩn danh mục.',
            'is_active' => $newStatus
        ]);
    }

    jsonResponse([
        'success' => false,
        'message' => 'Method không được hỗ trợ.'
    ], 405);

} catch (PDOException $e) {

    jsonResponse([
        'success' => false,
        'message' => 'Database error.'
    ], 500);
}