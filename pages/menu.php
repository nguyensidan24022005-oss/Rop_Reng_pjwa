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
            AND c.is_active = 1
        ORDER BY p.id ASC"
    );
}

$products = $productQuery->fetchAll();

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
</main>