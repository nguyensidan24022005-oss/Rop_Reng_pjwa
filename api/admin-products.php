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

    $productQuery = $pdo->query(
        "SELECT
            p.id,
            p.category_id,
            p.name,
            p.description,
            p.price,
            p.image,
            p.is_active,
            p.is_best_seller,
            p.is_new,
            p.is_seasonal,
            p.season_start,
            p.season_end,
            p.created_at,
            p.updated_at,
            c.name AS category_name,
            c.slug AS category_slug
        FROM products p
        LEFT JOIN categories c
            ON c.id = p.category_id
        ORDER BY p.id ASC"
    );

    $products = $productQuery->fetchAll();

    $categoryQuery = $pdo->query(
        "SELECT
            id,
            name,
            slug,
            is_active
        FROM categories
        WHERE is_active = 1
        ORDER BY id ASC"
    );
    $categories = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);

    foreach ($categories as &$category) {
        $category['id'] = (int) $category['id'];
        $category['is_active'] = (int) $category['is_active'];
    }

    unset($category);

    $variantQuery = $pdo->query(
        "SELECT
            id,
            product_id,
            name,
            price,
            is_active
        FROM product_variants
        ORDER BY product_id ASC, id ASC"
    );

    $variants = $variantQuery->fetchAll();

    $productVariants = [];

    foreach ($variants as $variant) {

        $productVariants[$variant['product_id']][] = [
            'id' => (int) $variant['id'],
            'name' => $variant['name'],
            'price' => (float) $variant['price'],
            'is_active' => (int) $variant['is_active']
        ];
    }

    foreach ($products as &$product) {
        
        $product['id'] = (int) $product['id'];
        $product['category_id'] = 
            $product['category_id'] !== null
                ? (int) $product['category_id']
                : null;
        $product['price'] = (float) $product['price'];

        $product['is_active'] = (int) $product['is_active'];
        $product['is_best_seller'] = (int) $product['is_best_seller'];
        $product['is_new'] = (int) $product['is_new'];
        $product['is_seasonal'] = (int) $product['is_seasonal'];

        $product['variants'] =
            $productVariants[$product['id']] ?? [];
    }

    unset($product);

    echo json_encode([
        'success' => true,
        'data' => $products,
        'categories' => $categories
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    error_log(
        'Admin products error: ' .$e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Không thể tải danh sách sản phẩm.'
    ]);
}