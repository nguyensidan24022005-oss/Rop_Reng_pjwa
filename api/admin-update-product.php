<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

try {

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON body'
    ]);
    exit;
}

$id = isset($input['id']) ? (int)$input['id'] : 0;
$name = trim($input['name'] ?? '');
$description = $input['description'] ?? null;
$price = isset($input['price']) ? (float)$input['price'] : 0;
$image = $input['image'] ?? null;
$is_active = isset($input['is_active']) ? (int)$input['is_active'] : 1;

if (
    ($description !== null && !is_string($description)) ||
    ($image !== null && !is_string($image))
) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product data'
    ]);
    exit;
}

$description = $description !== null
    ? trim($description)
    : null;

$image = $image !== null
    ? trim($image)
    : null;

$is_seasonal = isset($input['is_seasonal'])
    ? (int)$input['is_seasonal']
    : 0;

$category_id = isset($input['category_id']) && $input['category_id'] !== ''
    ? (int)$input['category_id']
    : null;

$season_start = !empty($input['season_start'])
    ? $input['season_start']
    : null;

$season_end = !empty($input['season_end'])
    ? $input['season_end']
    : null;

if ($id <= 0) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product ID'
    ]);
    exit;
}

if ($name === '') {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Product name is required'
    ]);
    exit;
}

if ($price < 0) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Price must be greater than or equal to 0'
    ]);
    exit;
}

if ($is_seasonal === 1) {

    if ($category_id !== null) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Season product must not have a category'
        ]);
        exit;
    }

    if ($season_start === null || $season_end === null) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Season product requires season start and end dates'
        ]);
        exit;
    }

    if ($season_start > $season_end) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Season start date must be before or equal to season end date'
        ]);
        exit;
    }

} else {

    if ($category_id === null || $category_id <= 0) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Drink product requires a category'
        ]);
        exit;
    }

    $season_start = null;
    $season_end = null;
}

$stmt = $pdo->prepare("
    SELECT id, description, image
    FROM products
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);
$existingProduct = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$existingProduct) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Product not found'
    ]);
    exit;
}

if ($description === null) {
    $description = $existingProduct['description'];
}

if ($image === null || $image === '') {
    $image = $existingProduct['image'];
}

if ($is_seasonal === 0) {
    $stmt = $pdo->prepare("
        SELECT id
        FROM categories
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$category_id]);

    if (!$stmt->fetch()) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Category not found'
        ]);
        exit;
    }
}

$stmt = $pdo->prepare("
    UPDATE products
    SET
        category_id = :category_id,
        name = :name,
        description = :description,
        price = :price,
        image = :image,
        is_active = :is_active,
        is_seasonal = :is_seasonal,
        season_start = :season_start,
        season_end = :season_end
    WHERE id = :id
");

$stmt->bindValue(':category_id', $category_id, $category_id === null
    ? PDO::PARAM_NULL
    : PDO::PARAM_INT
);

$stmt->bindValue(':name', $name);
$stmt->bindValue(
    ':description',
    $description,
    $description === null
        ? PDO::PARAM_NULL
        : PDO::PARAM_STR
);
$stmt->bindValue(':price', $price);
$stmt->bindValue(
    ':image',
    $image,
    $image === null
        ? PDO::PARAM_NULL
        : PDO::PARAM_STR
);
$stmt->bindValue(':is_active', $is_active, PDO::PARAM_INT);
$stmt->bindValue(':is_seasonal', $is_seasonal, PDO::PARAM_INT);

$stmt->bindValue(':season_start', $season_start);
$stmt->bindValue(':season_end', $season_end);

$stmt->bindValue(':id', $id, PDO::PARAM_INT);

$stmt->execute();

echo json_encode([
    'success' => true,
    'message' => 'Product updated successfully',
    'product_id' => $id
]);

} catch (Throwable $e) {
    error_log('Admin update product error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Không thể cập nhật sản phẩm. Vui lòng thử lại.'
    ], JSON_UNESCAPED_UNICODE);
}
