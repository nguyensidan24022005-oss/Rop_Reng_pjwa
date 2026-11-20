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

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $price = (float) ($_POST['price'] ?? 0);

    $variantType =
        $_POST['variant_type'] ?? 'default';

    $bestSeller =
        isset($_POST['is_best_seller']) ? 1 : 0;

    $isNew =
        isset($_POST['is_new']) ? 1 : 0;

    $isSeasonal =
        isset($_POST['is_seasonal']) ? 1 : 0;

    $seasonStart =
        trim($_POST['season_start'] ?? '');

    $seasonEnd =
        trim($_POST['season_end'] ?? '');

    if ($name === '') {

        throw new RuntimeException(
            'Vui lòng nhập tên sản phẩm.'
        );
    }

    if ($categoryId <= 0) {

        throw new RuntimeException(
            'Vui lòng chọn danh mục.'
        );
    }

    if ($variantType === 'default') {

        if ($price <= 0) {

            throw new RuntimeException(
                'Giá sản phẩm phải lớn hơn 0.'
            );
        }

    } elseif ($variantType === 'size') {

        $priceM =
            (float) ($_POST['price_m'] ?? 0);

        $priceL =
            (float) ($_POST['price_l'] ?? 0);

        if ($priceM <= 0 || $priceL <= 0) {

            throw new RuntimeException(
                'Giá M và L phải lớn hơn 0.'
            );
        }

        $price = $priceM;

    } else {

        throw new RuntimeException(
            'Loại sản phẩm không hợp lệ.'
        );
    }

    $categoryQuery = $pdo->prepare(
        "SELECT id
        FROM categories
        WHERE id = :id
            AND is_active = 1
        LIMIT 1"
    );

    $categoryQuery->execute([
        'id' => $categoryId
    ]);

    if (!$categoryQuery->fetch()) {

        throw new RuntimeException(
            'Danh mục không tồn tại hoặc bị ẩn.'
        );
    }

    $imagePath = null;

    if (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if (
            $_FILES['image']['error']
            !== UPLOAD_ERR_OK
        ) {

            throw new RuntimeException(
                'Không thể tải ảnh lên.'
            );
        }

        if (
            $_FILES['image']['size']
            > 5 * 1024 * 1024
        ) {

            throw new RuntimeException(
                'Ảnh không được vượt quá 5MB.'
            );
        }

        $tmpFile =
            $_FILES['image']['tmp_name'];

        $mimeType =
            mime_content_type($tmpFile);

        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if (
            !isset(
                $allowedMimeTypes[$mimeType]
            )
        ) {

            throw new RuntimeException(
                'Chỉ hỗ trợ ảnh JPG, PNG hoặc WEBP.'
            );
        }

        $extension =
            $allowedMimeTypes[$mimeType];

        $uploadDirectory =
            __DIR__
            . '/../assets/images/products/drinks';
    }
}