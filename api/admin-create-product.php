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

    $categoryRaw = $readString('category_id');

    $variantType = $readString('variant_type');

    $seasonStart = $readString('season_start');
    $seasonEnd = $readString('season_end');

    $bestSeller = isset($_POST['is_best_seller']) ? 1 : 0;
    $isNew = isset($_POST['is_new']) ? 1 : 0;

    $isSeasonalRaw = $readString('is_seasonal');

    if ($isSeasonalRaw === '' || $isSeasonalRaw === '0') {
        $isSeasonal = 0;
    } elseif (
        $isSeasonalRaw === '1' ||
        $isSeasonalRaw === 'on'
    ) {
        $isSeasonal = 1;
    } else {
        throw new InvalidArgumentException(
            'Loại sản phẩm không hợp lệ.'
        );
    }

    $categoryId = null;

    if ($isSeasonal === 0) {
        $categoryId = filter_var(
            $categoryRaw,
            FILTER_VALIDATE_INT
        );
        if ($categoryId === false || $categoryId <= 0) {
            throw new InvalidArgumentException(
                'DRINKS phải có danh mục hợp lệ.'
            );
        }
    } elseif ($categoryRaw !== '') {
        throw new InvalidArgumentException(
            'SEASON PRODUCT không được có danh mục.'
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

    if ($isSeasonal === 1) {
        $seasonStart = $normalizeDate($seasonStart);
        $seasonEnd = $normalizeDate($seasonEnd);
        if (
            $seasonStart === null ||
            $seasonEnd === null
        ) {
            throw new InvalidArgumentException(
                'Season Product phải có ngày bắt đầu và ngày kết thúc.'
            );
        }
        if ($seasonStart > $seasonEnd) {
            throw new InvalidArgumentException(
                'Ngày bắt đầu phải trước hoặc bằng ngày kết thúc.'
            );
        }
    } else {
        $seasonStart = null;
        $seasonEnd = null;
    }

    $uploadedFilePath = null;

    $category = null;
    if ($isSeasonal === 0) {
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

            if ($isSeasonal === 1) {
                $uploadDirectory =
                    __DIR__
                    . '/../assets/images/products/season_products';

                $imageBasePath =
                    'assets/images/products/season_products/';
            } else {
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

                $imageBasePath =
                    'assets/images/products/drinks/'
                    . $categorySlug
                    . '/';
            }

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

            $imagePath = $imageBasePath . $filename;
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

    $productInsert->bindValue(
        ':category_id',
        $categoryId,
        $categoryId === null
            ? PDO::PARAM_NULL
            : PDO::PARAM_INT
    );
    $productInsert->bindValue(
        ':name',
        $name,
        PDO::PARAM_STR
    );
    $productInsert->bindValue(
        ':description',
        $description !== '' ? $description : null,
        $description !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL
    );
    $productInsert->bindValue(':price', $price);
    $productInsert->bindValue(
        ':image',
        $imagePath,
        $imagePath === null ? PDO::PARAM_NULL : PDO::PARAM_STR
    );
    $productInsert->bindValue(
        ':is_best_seller',
        $bestSeller,
        PDO::PARAM_INT
    );
    $productInsert->bindValue(
        ':is_new',
        $isNew,
        PDO::PARAM_INT
    );
    $productInsert->bindValue(
        ':is_seasonal',
        $isSeasonal,
        PDO::PARAM_INT
    );
    $productInsert->bindValue(
        ':season_start',
        $seasonStart,
        $seasonStart === null ? PDO::PARAM_NULL : PDO::PARAM_STR
    );
    $productInsert->bindValue(
        ':season_end',
        $seasonEnd,
        $seasonEnd === null ? PDO::PARAM_NULL : PDO::PARAM_STR
    );
    $productInsert->execute();

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