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


    $customerName = $input['customer_name'] ?? '';

    if (!is_string($customerName)) {
        throw new InvalidArgumentException(
            'Tên khách hàng không hợp lệ.'
        );
    }

    $customerName = trim($customerName);

    if ($customerName !== '') {
        $customerName = mb_substr(
            $customerName,
            0,
            150
        );
    }


    $items = $input['items'] ?? [];

    if (!is_array($items) || count($items) === 0) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Đơn hàng không có sản phẩm.'
        ]);

        exit;
    }


    if (count($items) > 100) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Đơn hàng có quá nhiều sản phẩm.'
        ]);

        exit;
    }


    $pdo->beginTransaction();

    $validatedItems = [];

    $subtotal = 0;


    $productQuery = $pdo->prepare(
        "SELECT
            p.id,
            p.name AS product_name,
            p.is_active AS product_active,
            p.is_seasonal,
            p.category_id,
            c.is_active AS category_active,
            p.season_start,
            p.season_end
        FROM products p
        LEFT JOIN categories c
            ON c.id = p.category_id
        WHERE p.id = :product_id
        LIMIT 1"
    );

    $variantQuery = $pdo->prepare(
        "SELECT
            id,
            product_id,
            name,
            price,
            is_active
        FROM product_variants
        WHERE id = :variant_id
        LIMIT 1"
    );

    $toppingQuery = $pdo->prepare(
        "SELECT
            id,
            name,
            price,
            is_active
        FROM toppings
        WHERE id = :topping_id
        LIMIT 1"
    );

    foreach ($items as $item) {

        if (!is_array($item)) {
            throw new InvalidArgumentException(
                'Thông tin sản phẩm trong đơn không hợp lệ.'
            );
        }

        $itemType = $item['item_type'] ?? 'product';

        if (!is_string($itemType)) {
            throw new InvalidArgumentException(
                'Loại sản phẩm trong đơn không hợp lệ.'
            );
        }

        $quantity = filter_var(
            $item['quantity'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 99]]
        );

        $note = $item['note'] ?? '';

        if ($quantity === false) {
            throw new InvalidArgumentException(
                'Số lượng sản phẩm không hợp lệ.'
            );
        }

        if (!is_string($note)) {
            throw new InvalidArgumentException(
                'Ghi chú sản phẩm không hợp lệ.'
            );
        }

        $note = trim($note);

        if ($note !== '') {
            $note = mb_substr(
                $note,
                0,
                255
            );
        }

        if ($itemType === 'topping') {

            $toppingId = filter_var(
                $item['topping_id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if ($toppingId === false) {
                throw new InvalidArgumentException(
                    'Thông tin topping trong đơn không hợp lệ.'
                );
            }

            $productId = $item['product_id'] ?? null;
            $variantId = $item['variant_id'] ?? null;

            if (
                $productId !== null ||
                $variantId !== null
            ) {
                throw new InvalidArgumentException(
                    'Topping không được gắn với sản phẩm.'
                );
            }

            $toppingQuery->execute([
                'topping_id' => $toppingId
            ]);

            $topping = $toppingQuery->fetch();

            if (!$topping) {
                throw new InvalidArgumentException(
                    'Không tìm thấy topping.'
                );
            }

            if ((int) $topping['is_active'] !== 1) {
                throw new InvalidArgumentException(
                    'Topping hiện không khả dụng.'
                );
            }

            $unitPrice = (float) $topping['price'];

            $lineTotal = $unitPrice * $quantity;

            $subtotal += $lineTotal;

            $validatedItems[] = [
                'product_id' => null,
                'variant_id' => null,
                'product_name' => $topping['name'],
                'variant_name' => 'Topping',
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'note' => $note !== '' ? $note : null,
                'line_total' => $lineTotal
            ];

            continue;
        }

        if ($itemType !== 'product') {
            throw new InvalidArgumentException(
                'Loại sản phẩm trong đơn không hợp lệ.'
            );
        }

        $productId = filter_var(
            $item['product_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        $variantId = filter_var(
            $item['variant_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if (
            $productId === false ||
            $variantId === false
        ) {
            throw new InvalidArgumentException(
                'Thông tin sản phẩm trong đơn không hợp lệ.'
            );
        }

        $productQuery->execute([
            'product_id' => $productId
        ]);

        $product = $productQuery->fetch();

        if (!$product) {
            throw new InvalidArgumentException(
                'Không tìm thấy sản phẩm.'
            );
        }

        if ((int) $product['product_active'] !== 1) {
            throw new InvalidArgumentException(
                'Sản phẩm hiện không khả dụng.'
            );
        }

        if ((int) $product['is_seasonal'] === 0) {

            if (
                empty($product['category_id']) ||
                (int) $product['category_active'] !== 1
            ) {
                throw new InvalidArgumentException(
                    'Sản phẩm hiện không khả dụng.'
                );
            }
        }

        if ((int) $product['is_seasonal'] === 1) {

            if (
                !empty($product['category_id']) ||
                empty($product['season_start']) ||
                empty($product['season_end'])
            ) {
                throw new InvalidArgumentException(
                    'Sản phẩm theo mùa không hợp lệ.'
                );
            }

            $today = date('Y-m-d');

            if (
                $today < $product['season_start'] ||
                $today > $product['season_end']
            ) {
                throw new InvalidArgumentException(
                    'Sản phẩm theo mùa hiện không được bán.'
                );
            }
        }

        $variantQuery->execute([
            'variant_id' => $variantId
        ]);

        $variant = $variantQuery->fetch();

        if (!$variant) {
            throw new InvalidArgumentException(
                'Không tìm thấy sản phẩm.'
            );
        }

        if (
            (int) $variant['product_id'] !== $productId
        ) {
            throw new InvalidArgumentException(
                'Phiên bản sản phẩm không hợp lệ.'
            );
        }

        if ((int) $variant['is_active'] !== 1) {
            throw new InvalidArgumentException(
                'Phiên bản sản phẩm hiện không khả dụng.'
            );
        }

        $unitPrice = (float) $variant['price'];

        $lineTotal = $unitPrice * $quantity;

        $subtotal += $lineTotal;

        $validatedItems[] = [
            'product_id' => $productId,
            'variant_id' => $variantId,
            'product_name' => $product['product_name'],
            'variant_name' => $variant['name'],
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'note' => $note !== '' ? $note : null,
            'line_total' => $lineTotal
        ];
    }

    $orderCode = 'RR-' .
        date('Ymd-His') .
        '-' .
        strtoupper(
            substr(
                bin2hex(random_bytes(3)),
                0,
                6
            )
        );

    $orderQuery = $pdo->prepare(
        "INSERT INTO orders (
            order_code,
            customer_name,
            status,
            subtotal,
            total
        ) VALUES (
            :order_code,
            :customer_name,
            'pending',
            :subtotal,
            :total
        )"
    );

    $orderQuery->execute([
        'order_code' => $orderCode,
        'customer_name' =>
            $customerName !== ''
                ? $customerName
                : null,
        'subtotal' => $subtotal,
        'total' => $subtotal
    ]);

    $orderId = (int) $pdo->lastInsertId();

    $itemQuery = $pdo->prepare(
        "INSERT INTO order_items (
            order_id,
            product_id,
            variant_id,
            product_name,
            variant_name,
            unit_price,
            quantity,
            note,
            line_total
        ) VALUE (
            :order_id,
            :product_id,
            :variant_id,
            :product_name,
            :variant_name,
            :unit_price,
            :quantity,
            :note,
            :line_total
        )"
    );

    foreach ($validatedItems as $item) {

        $itemQuery->execute([
            'order_id' => $orderId,
            'product_id' => $item['product_id'],
            'variant_id' => $item['variant_id'],
            'product_name' => $item['product_name'],
            'variant_name' => $item['variant_name'],
            'unit_price' => $item['unit_price'],
            'quantity' => $item['quantity'],
            'note' => $item['note'],
            'line_total' => $item['line_total']
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Tạo đơn thành công.',
        'data' => [
            'order_id' => $orderId,
            'order_code' => $orderCode,
            'total' => $subtotal
        ]
    ]);
} catch (InvalidArgumentException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Create order failed: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Không thể tạo đơn hàng. Vui lòng thử lại.'
    ]);
}