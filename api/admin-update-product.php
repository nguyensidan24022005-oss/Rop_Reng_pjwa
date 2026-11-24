
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

    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON body.'
        ]);
        exit;
    }

    $readString = static function (string $key) use ($input): string {
        $value = $input[$key] ?? '';

        if (!is_string($value) && !is_numeric($value)) {
            throw new InvalidArgumentException(
                'Dữ liệu sản phẩm không hợp lệ.'
            );
        }

        return trim((string) $value);
    };

    $readPrice = static function ($value, string $label): string {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException(
                $label . ' không hợp lệ.'
            );
        }

        $number = (float) $value;

        if (
            !is_finite($number) ||
            $number <= 0 ||
            $number > 99999999.99
        ) {
            throw new InvalidArgumentException(
                $label . ' phải lớn hơn 0 và không vượt quá giới hạn.'
            );
        }

        return number_format($number, 2, '.', '');
    };

    $readFlag = static function ($value, string $label): int {
        $flag = filter_var($value, FILTER_VALIDATE_INT);

        if ($flag === false || !in_array($flag, [0, 1], true)) {
            throw new InvalidArgumentException(
                $label . ' không hợp lệ.'
            );
        }

        return $flag;
    };

    $normalizeDate = static function ($value): ?string {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException(
                'Ngày sản phẩm mùa vụ không hợp lệ.'
            );
        }

        $date = DateTime::createFromFormat('!Y-m-d', $value);

        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException(
                'Ngày sản phẩm mùa vụ không hợp lệ.'
            );
        }

        return $value;
    };

    // 1. Validate basic product data.
    $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id <= 0) {
        throw new InvalidArgumentException('Mã sản phẩm không hợp lệ.');
    }

    $name = $readString('name');

    if ($name === '' || mb_strlen($name, 'UTF-8') > 150) {
        throw new InvalidArgumentException('Tên sản phẩm không hợp lệ.');
    }

    $isActive = $readFlag(
        $input['is_active'] ?? 1,
        'Trạng thái sản phẩm'
    );

    $isSeasonal = $readFlag(
        $input['is_seasonal'] ?? 0,
        'Loại sản phẩm'
    );

    // 2. Validate DRINKS / SEASON PRODUCT taxonomy.
    $categoryRaw = $input['category_id'] ?? null;

    if ($isSeasonal === 1) {
        if ($categoryRaw !== null && $categoryRaw !== '') {
            throw new InvalidArgumentException(
                'SEASON PRODUCT không được có danh mục.'
            );
        }

        $categoryId = null;

        $seasonStart = $normalizeDate(
            $input['season_start'] ?? null
        );

        $seasonEnd = $normalizeDate(
            $input['season_end'] ?? null
        );

        if ($seasonStart === null || $seasonEnd === null) {
            throw new InvalidArgumentException(
                'SEASON PRODUCT phải có ngày bắt đầu và ngày kết thúc.'
            );
        }

        if ($seasonStart > $seasonEnd) {
            throw new InvalidArgumentException(
                'Ngày bắt đầu phải trước hoặc bằng ngày kết thúc.'
            );
        }
    } else {
        $categoryId = filter_var(
            $categoryRaw,
            FILTER_VALIDATE_INT
        );

        if ($categoryId === false || $categoryId === null || $categoryId <= 0) {
            throw new InvalidArgumentException(
                'DRINKS phải có danh mục hợp lệ.'
            );
        }

        $seasonStart = null;
        $seasonEnd = null;
    }

    // 3. Optional fields: null means preserve existing value.
    $description = $input['description'] ?? null;
    $image = $input['image'] ?? null;

    if (
        ($description !== null && !is_string($description)) ||
        ($image !== null && !is_string($image))
    ) {
        throw new InvalidArgumentException(
            'Mô tả hoặc ảnh sản phẩm không hợp lệ.'
        );
    }

    if ($description !== null) {
        $description = trim($description);
    }

    if ($image !== null) {
        $image = trim($image);
    }

    $bestSeller = array_key_exists('is_best_seller', $input)
        ? $readFlag($input['is_best_seller'], 'Nhãn Best Seller')
        : null;
    $isNew = array_key_exists('is_new', $input)
        ? $readFlag($input['is_new'], 'Nhãn sản phẩm mới')
        : null;

    // 4. Determine whether the request uses the new or legacy variant format.
    $variantType = $readString('variant_type');

    if (
        $variantType !== '' &&
        !in_array($variantType, ['default', 'size'], true)
    ) {
        throw new InvalidArgumentException(
            'Loại biến thể không hợp lệ.'
        );
    }

    $targetVariants = null;
    $productPrice = null;

    if ($variantType === 'default') {
        $defaultPrice = $readPrice(
            $input['price'] ?? null,
            'Giá Default'
        );

        $targetVariants = [
            'Default' => $defaultPrice
        ];

        $productPrice = $defaultPrice;

    } elseif ($variantType === 'size') {
        $priceM = $readPrice(
            $input['price_m'] ?? null,
            'Giá M'
        );

        $priceL = $readPrice(
            $input['price_l'] ?? null,
            'Giá L'
        );

        $targetVariants = [
            'M' => $priceM,
            'L' => $priceL
        ];

        $productPrice = $priceM;

    } else {
        // Legacy Edit Modal: only sends "price".
        // Determine the current variant structure from the database later.
        $productPrice = $readPrice(
            $input['price'] ?? null,
            'Giá sản phẩm'
        );
    }

    // 5. Begin transaction and lock the product.
    $pdo->beginTransaction();

    $existingQuery = $pdo->prepare("
        SELECT
            id,
            description,
            image,
            is_best_seller,
            is_new
        FROM products
        WHERE id = :id
        LIMIT 1
        FOR UPDATE
    ");

    $existingQuery->execute(['id' => $id]);
    $existingProduct = $existingQuery->fetch(PDO::FETCH_ASSOC);

    if (!$existingProduct) {
        $pdo->rollBack();

        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy sản phẩm.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($description === null) {
        $description = $existingProduct['description'];
    }

    if ($image === null || $image === '') {
        $image = $existingProduct['image'];
    }

    if ($bestSeller === null) {
        $bestSeller = (int) $existingProduct['is_best_seller'];
    }

    if ($isNew === null) {
        $isNew = (int) $existingProduct['is_new'];
    }

    // 6. Verify category is active for DRINKS.
    if ($isSeasonal === 0) {
        $categoryQuery = $pdo->prepare("
            SELECT id
            FROM categories
            WHERE id = :category_id
                AND is_active = 1
            LIMIT 1
        ");

        $categoryQuery->execute([
            'category_id' => $categoryId
        ]);

        if (!$categoryQuery->fetch()) {
            throw new InvalidArgumentException(
                'Danh mục không tồn tại hoặc đang bị ẩn.'
            );
        }
    }

    // 7. Load current active variants.
    $variantQuery = $pdo->prepare("
        SELECT id, name, price
        FROM product_variants
        WHERE product_id = :product_id
            AND is_active = 1
        ORDER BY id ASC
        FOR UPDATE
    ");

    $variantQuery->execute(['product_id' => $id]);
    $currentVariants = $variantQuery->fetchAll(PDO::FETCH_ASSOC);

    // 8. Keep backward compatibility with the existing Edit Modal.
    if ($targetVariants === null) {
        $currentMap = [];

        foreach ($currentVariants as $variant) {
            if (isset($currentMap[$variant['name']])) {
                throw new InvalidArgumentException(
                    'Sản phẩm có biến thể trùng tên. Cần kiểm tra dữ liệu trước khi cập nhật.'
                );
            }

            $currentMap[$variant['name']] = $variant['price'];
        }

        if (
            count($currentMap) === 1 &&
            isset($currentMap['Default'])
        ) {
            $targetVariants = [
                'Default' => $productPrice
            ];

        } elseif (
            count($currentMap) === 2 &&
            isset($currentMap['M']) &&
            isset($currentMap['L'])
        ) {
            $targetVariants = [
                'M' => $productPrice,
                'L' => $readPrice(
                    $currentMap['L'],
                    'Giá L hiện tại'
                )
            ];

        } else {
            throw new InvalidArgumentException(
                'Cấu trúc biến thể hiện tại không hợp lệ. Vui lòng kiểm tra sản phẩm.'
            );
        }
    }

    // 9. Keep products.price synchronized with Default or M.
    $productPrice = $targetVariants['Default']
        ?? $targetVariants['M'];

    $updateProduct = $pdo->prepare("
        UPDATE products
        SET
            category_id = :category_id,
            name = :name,
            description = :description,
            price = :price,
            image = :image,
            is_active = :is_active,
            is_best_seller = :is_best_seller,
            is_new = :is_new,
            is_seasonal = :is_seasonal,
            season_start = :season_start,
            season_end = :season_end
        WHERE id = :id
    ");

    $updateProduct->bindValue(
        ':category_id',
        $categoryId,
        $categoryId === null ? PDO::PARAM_NULL : PDO::PARAM_INT
    );

    $updateProduct->bindValue(':name', $name);
    $updateProduct->bindValue(
        ':description',
        $description,
        $description === null ? PDO::PARAM_NULL : PDO::PARAM_STR
    );
    $updateProduct->bindValue(':price', $productPrice);
    $updateProduct->bindValue(
        ':image',
        $image,
        $image === null ? PDO::PARAM_NULL : PDO::PARAM_STR
    );
    $updateProduct->bindValue(':is_active', $isActive, PDO::PARAM_INT);
    $updateProduct->bindValue(
        ':is_best_seller',
        $bestSeller,
        PDO::PARAM_INT
    );
    $updateProduct->bindValue(':is_new', $isNew, PDO::PARAM_INT);
    $updateProduct->bindValue(':is_seasonal', $isSeasonal, PDO::PARAM_INT);
    $updateProduct->bindValue(
        ':season_start',
        $seasonStart,
        $seasonStart === null ? PDO::PARAM_NULL : PDO::PARAM_STR
    );
    $updateProduct->bindValue(
        ':season_end',
        $seasonEnd,
        $seasonEnd === null ? PDO::PARAM_NULL : PDO::PARAM_STR
    );
    $updateProduct->bindValue(':id', $id, PDO::PARAM_INT);

    $updateProduct->execute();

    // 10. Deactivate old variants instead of deleting them.
    $deactivateVariants = $pdo->prepare("
        UPDATE product_variants
        SET is_active = 0
        WHERE product_id = :product_id
    ");

    $deactivateVariants->execute([
        'product_id' => $id
    ]);

    // 11. Reactivate matching variants or insert new ones.
    $findVariant = $pdo->prepare("
        SELECT id
        FROM product_variants
        WHERE product_id = :product_id
            AND name = :name
        ORDER BY id ASC
        LIMIT 1
        FOR UPDATE
    ");

    $updateVariant = $pdo->prepare("
        UPDATE product_variants
        SET
            price = :price,
            is_active = 1
        WHERE id = :id
    ");

    $insertVariant = $pdo->prepare("
        INSERT INTO product_variants (
            product_id,
            name,
            price,
            is_active
        ) VALUES (
            :product_id,
            :name,
            :price,
            1
        )
    ");

    foreach ($targetVariants as $variantName => $variantPrice) {
        $findVariant->execute([
            'product_id' => $id,
            'name' => $variantName
        ]);

        $existingVariant = $findVariant->fetch(PDO::FETCH_ASSOC);

        if ($existingVariant) {
            $updateVariant->execute([
                'price' => $variantPrice,
                'id' => $existingVariant['id']
            ]);
        } else {
            $insertVariant->execute([
                'product_id' => $id,
                'name' => $variantName,
                'price' => $variantPrice
            ]);
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật sản phẩm thành công.',
        'data' => [
            'id' => $id,
            'price' => (float) $productPrice,
            'variants' => $targetVariants
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (InvalidArgumentException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
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

    error_log(
        'Admin update product error: ' . $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Không thể cập nhật sản phẩm. Vui lòng thử lại.'
    ], JSON_UNESCAPED_UNICODE);
}
