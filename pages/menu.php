<?php

require_once __DIR__ . '/../config/db.php';

$categorySlug = $_GET['category'] ?? '';

$categoryQuery = $pdo->query(
    "SELECT id, name, slug, image
    FROM categories
    WHERE is_active = 1
    ORDER BY id ASC"
);

$categories = $categoryQuery->fetchAll();

if ($categorySlug !== '') {

    $productQuery = $pdo->prepare(
        "SELECT
            p.id,
            p.category_id,
            p.name,
            p.description,
            p.price,
            p.image,
            c.name AS category_name,
            c.slug AS category_slug
        FROM products p
        INNER JOIN categories c
            ON c.id = p.category_id
        WHERE p.is_active = 1
            AND p.is_seasonal = 0
            AND c.is_active = 1
            AND c.slug = :slug
        ORDER BY p.id ASC"
    );

    $productQuery->execute([
        'slug' => $categorySlug
    ]);
} else {

    $productQuery = $pdo->query(
        "SELECT
            p.id,
            p.category_id,
            p.name,
            p.description,
            p.price,
            p.image,
            c.name AS category_name,
            c.slug AS category_slug
        FROM products p
        INNER JOIN categories c
            ON c.id = p.category_id
        WHERE p.is_active = 1
            AND p.is_seasonal = 0
            AND c.is_active = 1
        ORDER BY p.id ASC"
    );
}
$products = $productQuery->fetchAll();

$toppingQuery = $pdo->query(
    "SELECT
        id,
        name,
        description,
        price,
        image
    FROM toppings
    WHERE is_active = 1
    ORDER BY id ASC"
);

$toppings = $toppingQuery->fetchAll();

$seasonProductQuery = $pdo->query(
    "SELECT
        p.id,
        p.name,
        p.image,
        p.season_start,
        p.season_end,
        pv.id AS variant_id,
        pv.name AS variant_name,
        pv.price AS variant_price
    FROM products p
    INNER JOIN product_variants pv
        ON pv.product_id = p.id
        AND pv.is_active = 1
    WHERE p.is_active = 1
        AND p.is_seasonal = 1
        AND p.category_id IS NULL
        AND p.season_start IS NOT NULL
        AND p.season_end IS NOT NULL
        AND p.season_start <= CURRENT_DATE()
        AND p.season_end >= CURRENT_DATE()
    ORDER BY p.id ASC, pv.id ASC"
);

$seasonProductRows = $seasonProductQuery->fetchAll();

$seasonProducts = [];

foreach ($seasonProductRows as $row) {

    $productId = (int) $row['id'];

    if (!isset($seasonProducts[$productId])) {

        $seasonProducts[$productId] = [
            'id' => $productId,
            'name' => $row['name'],
            'image' => $row['image'],
            'season_start' => $row['season_start'],
            'season_end' => $row['season_end'],
            'variants' => [],
        ];
    }

    $seasonProducts[$productId]['variants'][] = [
        'id' => (int) $row['variant_id'],
        'name' => $row['variant_name'],
        'price' => (int) $row['variant_price'],
    ];
}

$seasonProducts = array_values($seasonProducts);

$variantQuery = $pdo->query(
    "SELECT
        id,
        product_id,
        name,
        price
    FROM product_variants
    WHERE is_active = 1
    ORDER BY product_id ASC, id ASC"
);

$variants = $variantQuery->fetchAll();

$productVariants = [];

foreach ($variants as $variant) {
    $productVariants[$variant['product_id']][] = $variant;
}

?>

<main class="menu-page">
    <section class="menu-hero">
        <div class="container">
            <span class="section-eyebrow">
                RỐP RẺNG MENU
            </span>

            <h1>
                CHỌN MÓN <span>BẠN THÍCH.</span>
            </h1>

            <p>
                Khám phá toàn bộ thức uống của Rốp Rẻng.
            </p>
        </div>
    </section>

    <section class="menu-section">
        <div class="container">
            <nav class="menu-categories">
                <a
                    href="menu.php"
                    class="<?= $categorySlug === '' ? 'active' : '' ?>"
                >
                    Tất cả
                </a>

                <?php foreach ($categories as $category): ?>
                    <a
                        href="menu.php?category=<?= urlencode($category['slug']) ?>"
                        class="<?= $categorySlug === $category['slug'] ? 'active' : '' ?>"
                    >
                        <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="menu-product-grid">
                <?php foreach ($products as $product): ?>
                    <article class="menu-product-card">
                        <div class="menu-product-image">
                            <?php if (!empty($product['image'])): ?>
                                <img
                                    src="<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') ?>"
                                    alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                                    loading="lazy"
                                >
                            <?php else: ?>
                                <div class="menu-product-placeholder">
                                    RỐP RẺNG
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="menu-product-content">
                            <span class="menu-product-category">
                                <?= htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8') ?>
                            </span>

                            <h2>
                                <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>
                            </h2>

                            <?php if (!empty($productVariants[$product['id']])): ?>
                                <div class="menu-product-variants">
                                    <?php foreach ($productVariants[$product['id']] as $variant): ?>
                                        <?php if ($variant['name'] === 'Default'): ?>
                                            <span class="menu-variant menu-variant-default">
                                                <strong>
                                                    <?= number_format($variant['price'], 0, ',', '.') ?>đ
                                                </strong>
                                            </span>
                                        <?php else: ?>
                                            <span class="menu-variant">
                                                <span>
                                                    <?= htmlspecialchars($variant['name'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>

                                                <strong>
                                                    <?= number_format($variant['price'], 0, ',', '.') ?>đ
                                                </strong>
                                            </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="menu-product-actions">

                                <?php
                                $productVariantList = $productVariants[$product['id']] ?? [];

                                $hasSelectableVariants = false;
                                $defaultVariant = null;

                                foreach ($productVariantList as $variant) {

                                            if ($variant['name'] === 'Default') {
                                                $defaultVariant = $variant;
                                            } else {
                                                $hasSelectableVariants = true;
                                            }

                                        }
                                        ?>

                                        <?php if ($hasSelectableVariants): ?>

                                            <select 
                                            class="menu-variant-select" 
                                            data-product-id="<?= $product['id'] ?>"
                                            >
                                            
                                                <?php foreach ($productVariantList as $variant): ?>

                                                    <?php if ($variant['name'] !== 'Default'): ?>

                                                        <option 
                                                            value="<?= $variant['id'] ?>"
                                                            data-price="<?= $variant['price'] ?>"
                                                            data-variant-name="<?= htmlspecialchars($variant['name'], ENT_QUOTES, 'UTF-8') ?>"
                                                        >
                                                            <?= htmlspecialchars($variant['name'], ENT_QUOTES, 'UTF-8') ?>
                                                            —
                                                            <?= number_format($variant['price'], 0, ',', '.') ?>đ
                                                        </option>

                                                    <?php endif; ?>

                                                <?php endforeach; ?>

                                            </select>

                                        <?php endif; ?>

                                        <button
                                            type="button"
                                            class="menu-add-to-cart"
                                            data-product-id="<?= $product['id'] ?>"
                                            data-product-name="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                                            <?php if (!$hasSelectableVariants && $defaultVariant): ?>
                                                data-variant-id="<?= $defaultVariant['id'] ?>"
                                                data-variant-name="Default"
                                                data-price="<?= $defaultVariant['price'] ?>"
                                            <?php endif; ?>
                                        >
                                            THÊM VÀO GIỎ
                                        </button>

                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="product-section season-product-section" id="season-products">

        <div class="container">

            <div class="section-heading">

                <div>
                    <span class="section-eyebrow">
                        SEASONAL SPECIAL
                    </span>

                    <h2>
                        MÓN <span>THEO MÙA.</span>
                    </h2>
                </div>

                <p>
                    Những thức uống đặc biệt chỉ xuất hiện
                    trong một khoảng thời gian nhất định.
                </p>

            </div>

            <div class="product-grid">

                <?php if (empty($seasonProducts)): ?>

                    <p class="product-empty">
                        Hiện chưa có món theo mùa.
                    </p>

                <?php endif; ?>

                <?php foreach ($seasonProducts as $product): ?>

                    <?php
                    $variants = $product['variants'];
                    $hasMultipleVariants = count($variants) > 1;
                    $firstVariant = $variants[0] ?? null;
                    ?>

                    <?php if (!$firstVariant): ?>
                        <?php continue; ?>
                    <?php endif; ?>

                    <article class="product-card">

                        <div class="product-image">

                            <?php if (!empty($product['image'])): ?>

                                <img
                                    src="<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') ?>"
                                    alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                                    loading="lazy"
                                >

                            <?php else: ?>

                                <div class="product-image-placeholder">
                                    <span>RỐP RẺNG</span>
                                </div>

                            <?php endif; ?>

                        </div>

                        <div class="product-card-content">

                            <span class="product-category">
                                MÓN THEO MÙA
                            </span>

                            <h3>
                                <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>

                            <?php if ($hasMultipleVariants): ?>

                                <select
                                    class="menu-variant-select"
                                    aria-label="Chọn kích thước"
                                >

                                    <?php foreach ($variants as $variant): ?>

                                        <option
                                            value="<?= (int) $variant['id'] ?>"
                                            data-variant-name="<?= htmlspecialchars($variant['name'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-price="<?= (int) $variant['price'] ?>"
                                        >
                                            <?= htmlspecialchars($variant['name'], ENT_QUOTES, 'UTF-8') ?>
                                            -
                                            <?= number_format($variant['price'], 0, ',', '.') ?>đ
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                                <span class="product-price">
                                    <?= number_format($firstVariant['price'], 0, ',', '.') ?>đ
                                </span>

                            <?php else: ?>

                                <span class="product-price">
                                    <?= number_format($firstVariant['price'], 0, ',', '.') ?>đ
                                </span>

                            <?php endif; ?>

                            <button
                                type="button"
                                class="menu-add-to-cart"
                                data-product-id="<?= (int) $product['id'] ?>"
                                data-product-name="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                                data-variant-id="<?= (int) $firstVariant['id'] ?>"
                                data-variant-name="<?= htmlspecialchars($firstVariant['name'], ENT_QUOTES, 'UTF-8') ?>"
                                data-price="<?= (int) $firstVariant['price'] ?>"
                            >
                                THÊM VÀO GIỎ
                            </button>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        </div>

    </section>

    <section class="product-section topping-section" id="toppings">
        <div class="container">

            <div class="section-heading">
                <div>
                    <span class="section-eyebrow">
                        TOPPING
                    </span>

                    <h2>
                        THÊM <span>TOPPING.</span>
                    </h2>
                </div>

                <p>
                    Chọn thêm topping yêu thích cho đơn hàng của bạn.
                </p>
            </div>

            <div class="product-grid">

                <?php if (empty($toppings)): ?>

                    <p class="product-empty">
                        Hiện chưa có topping.
                    </p>

                <?php endif; ?>

                <?php foreach ($toppings as $topping): ?>

                    <article class="product-card">

                        <div class="product-image">

                            <?php if (!empty($topping['image'])): ?>

                                <img
                                    src="<?= htmlspecialchars($topping['image'], ENT_QUOTES, 'UTF-8') ?>"
                                    alt="<?= htmlspecialchars($topping['name'], ENT_QUOTES, 'UTF-8') ?>"
                                    loading="lazy"
                                >

                            <?php else: ?>

                                <div class="product-image-placeholder">
                                    <span>RỐP RẺNG</span>
                                </div>

                            <?php endif; ?>

                        </div>

                        <div class="product-card-content">

                            <span class="product-category">
                                TOPPING
                            </span>

                            <h3>
                                <?= htmlspecialchars($topping['name'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>

                            <?php if (!empty($topping['description'])): ?>
                                <p>
                                    <?= htmlspecialchars($topping['description'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            <?php endif; ?>

                            <span class="product-price">
                                <?= number_format($topping['price'], 0, ',', '.') ?>đ
                            </span>

                            <button
                                type="button"
                                class="menu-add-to-cart"
                                data-topping-id="<?= (int) $topping['id'] ?>"
                                data-topping-name="<?= htmlspecialchars($topping['name'], ENT_QUOTES, 'UTF-8') ?>"
                                data-price="<?= (int) $topping['price'] ?>"
                            >
                                THÊM VÀO GIỎ
                            </button>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        </div>
    </section>
</main>