<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

$imageFilePath = null;

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed.'
        ]);

        exit;
    }

    $readString = static function (string $key): string {
        $value = $_POST[$key] ?? '';

        if (!is_string($value)) {
            throw new InvalidArgumentException(
                'Dữ liệu sản phẩm không hợp lệ.'
            );
        }

        return trim($value);
    };

    $name = $readString('name');
    $description = $readString('description');
    $categoryId = filter_var(
        $_POST['category_id'] ?? null,
        FILTER_VALIDATE_INT
    );
    $variantType = $readString('variant_type');
    $seasonStart = $readString('season_start');
    $seasonEnd = $readString('season_end');

    $bestSeller = isset($_POST['is_best_seller']) ? 1 : 0;
    $isNew = isset($_POST['is_new']) ? 1 : 0;
    $isSeasonal = isset($_POST['is_seasonal']) ? 1 : 0;

    if ($categoryId === false || $categoryId <= 0) {
        throw new InvalidArgumentException(
            'Vui lòng chọn danh mục hợp lệ.'
        );
    }

    if ($name === '' || mb_strlen($name, 'UTF-8') > 150) {
        throw new InvalidArgumentException(
            'Tên sản phẩm không hợp lệ.'
        );
    }

    if ($variantType === '') {
        $variantType = 'default';
    }

    if ($variantType === 'default') {

        $priceInput = $readString('price');

        if (!is_numeric($priceInput)) {
            throw new InvalidArgumentException(
                'Giá sản phẩm không hợp lệ.'
            );
        }

        $price = (float) $priceInput;

        if (!is_finite($price) || $price <= 0) {

            throw new InvalidArgumentException(
                'Giá sản phẩm phải lớn hơn 0.'
            );
        }

        $variants = [
            [
                'name' => 'Default',
                'price' => $price
            ]
        ];

    } elseif ($variantType === 'size') {

        $priceMInput = $readString('price_m');
        $priceLInput = $readString('price_l');

        if (
            !is_numeric($priceMInput) ||
            !is_numeric($priceLInput)
        ) {
            throw new InvalidArgumentException(
                'Giá M và L không hợp lệ.'
            );
        }

        $priceM = (float) $priceMInput;
        $priceL = (float) $priceLInput;

        if (
            !is_finite($priceM) ||
            !is_finite($priceL) ||
            $priceM <= 0 ||
            $priceL <= 0
        ) {

            throw new InvalidArgumentException(
                'Giá M và L phải lớn hơn 0.'
            );
        }

        $price = $priceM;
        $variants = [
            [
                'name' => 'M',
                'price' => $priceM
            ],
            [
                'name' => 'L',
                'price' => $priceL
            ]
        ];

    } else {

        throw new InvalidArgumentException(
            'Loại sản phẩm không hợp lệ.'
        );
    }

    if ($price > 99999999.99) {
        throw new InvalidArgumentException(
            'Giá sản phẩm vượt quá giới hạn cho phép.'
        );
    }

    foreach ($variants as $variant) {
        if ($variant['price'] > 99999999.99) {
            throw new InvalidArgumentException(
                'Giá sản phẩm vượt quá giới hạn cho phép.'
            );
        }
    }

    $normalizeDate = static function (string $value): ?string {
        if ($value === '') {
            return null;
        }

        $date = DateTime::createFromFormat('!Y-m-d', $value);

        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException(
                'Ngày sản phẩm mùa vụ không hợp lệ.'
            );
        }

        return $value;
    };

    if ($isSeasonal) {
        $seasonStart = $normalizeDate($seasonStart);
        $seasonEnd = $normalizeDate($seasonEnd);

        if (
            $seasonStart !== null &&
            $seasonEnd !== null &&
            $seasonStart > $seasonEnd
        ) {
            throw new InvalidArgumentException(
                'Ngày bắt đầu phải trước hoặc bằng ngày kết thúc.'
            );
        }
    } else {
        $seasonStart = null;
        $seasonEnd = null;
    }

    $uploadedFilePath = null;

    $categoryQuery = $pdo->prepare(
        "SELECT id, slug
        FROM categories
        WHERE id = :id
            AND is_active = 1
        LIMIT 1"
    );

    $categoryQuery->execute([
        'id' => $categoryId
    ]);

    $category = $categoryQuery->fetch();

    if (!$category) {
        throw new InvalidArgumentException(
            'Danh mục không tồn tại hoặc bị ẩn.'
        );
    }

    $imagePath = null;
    $imageFile = $_FILES['image'] ?? null;

    if ($imageFile !== null && !is_array($imageFile)) {
        throw new InvalidArgumentException(
            'Tệp ảnh không hợp lệ.'
        );
    }

    if (is_array($imageFile)) {
        $uploadError = $imageFile['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($uploadError !== UPLOAD_ERR_NO_FILE) {
            if ($uploadError !== UPLOAD_ERR_OK) {
                throw new InvalidArgumentException(
                'Không thể tải ảnh lên.'
                );
            }

            $tmpFile = $imageFile['tmp_name'] ?? '';
            $fileSize = (int) ($imageFile['size'] ?? 0);

            if ($fileSize <= 0 || $fileSize > 5 * 1024 * 1024) {
                throw new InvalidArgumentException(
                'Ảnh không được vượt quá 5MB.'
                );
            }

            if (!is_uploaded_file($tmpFile)) {
                throw new InvalidArgumentException(
                'Tệp ảnh tải lên không hợp lệ.'
                );
            }

            $mimeType = mime_content_type($tmpFile);

            $allowedMimeTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            if (
                $mimeType === false ||
                !isset($allowedMimeTypes[$mimeType])
            ) {
                throw new InvalidArgumentException(
                'Chỉ hỗ trợ ảnh JPG, PNG hoặc WEBP.'
                );
            }

            $categorySlug = $category['slug'];

            if (!preg_match('/\A[a-z0-9_-]+\z/i', $categorySlug)) {
                throw new RuntimeException(
                'Danh mục có mã đường dẫn không hợp lệ.'
                );
            }

            $uploadDirectory =
                __DIR__
                . '/../assets/images/products/drinks/'
                . $categorySlug;

            if (
                !is_dir($uploadDirectory) &&
                !mkdir($uploadDirectory, 0775, true) &&
                !is_dir($uploadDirectory)
            ) {
                throw new RuntimeException(
                'Không thể tạo thư mục lưu ảnh sản phẩm.'
                );
            }

            $filename =
                'product_'
                . bin2hex(random_bytes(16))
                . '.'
                . $allowedMimeTypes[$mimeType];

            $imageFilePath =
                $uploadDirectory
                . DIRECTORY_SEPARATOR
                . $filename;

            if (!move_uploaded_file($tmpFile, $imageFilePath)) {
                $imageFilePath = null;

                throw new RuntimeException(
                'Không thể lưu ảnh sản phẩm.'
                );
            }

            $imagePath =
                'assets/images/products/drinks/'
                . $categorySlug
                . '/'
                . $filename;
        }
    }

    $pdo->beginTransaction();

    $productInsert = $pdo->prepare(
        "INSERT INTO products (
            category_id,
            name,
            description,
            price,
            image,
            is_active,
            is_best_seller,
            is_new,
            is_seasonal,
            season_start,
            season_end
        ) VALUES (
            :category_id,
            :name,
            :description,
            :price,
            :image,
            1,
            :is_best_seller,
            :is_new,
            :is_seasonal,
            :season_start,
            :season_end
        )"
    );

    $productInsert->execute([
        'category_id' => $categoryId,
        'name' => $name,
        'description' => $description !== '' ? $description : null,
        'price' => $price,
        'image' => $imagePath,
        'is_best_seller' => $bestSeller,
        'is_new' => $isNew,
        'is_seasonal' => $isSeasonal,
        'season_start' => $seasonStart,
        'season_end' => $seasonEnd
    ]);

    $productId = (int) $pdo->lastInsertId();

    $variantInsert = $pdo->prepare(
        "INSERT INTO product_variants (
            product_id,
            name,
            price,
            is_active
        ) VALUES (
            :product_id,
            :name,
            :price,
            1
        )"
    );

    foreach ($variants as $variant) {
        $variantInsert->execute([
            'product_id' => $productId,
            'name' => $variant['name'],
            'price' => $variant['price']
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Tạo sản phẩm thành công.',
        'data' => [
            'id' => $productId
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (InvalidArgumentException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($imageFilePath !== null && is_file($imageFilePath)) {
        if (!unlink($imageFilePath)) {
            error_log(
                'Could not remove uploaded product image: '
                . $imageFilePath
            );
        }
    }

    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($imageFilePath !== null && is_file($imageFilePath)) {
        if (!unlink($imageFilePath)) {
            error_log(
                'Could not remove uploaded product image: '
                . $imageFilePath
            );
        }
    }

    error_log(
        'Admin create product error: ' . $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Không thể tạo sản phẩm. Vui lòng thử lại.'
    ], JSON_UNESCAPED_UNICODE);
}