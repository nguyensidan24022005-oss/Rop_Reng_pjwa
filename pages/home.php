<section class="hero-section">

    <div class="hero-background">

        <div class="container hero-container">

            <div class="hero-content">

                <span class="hero-eyebrow">
                    ✳ GOOD COFFEE. GOOD ENERGY.
                </span>

                <h1>
                    CÀ PHÊ &
                    <br>
                    <span>GIẢI KHÁT.</span>
                </h1>

                <p>
                    Một chút tỉnh táo, một chút sảng khoái.
                    Rốp Rẻng mang đến những hương vị
                    dành cho nhịp sống năng động.
                </p>

                <a href="#menu" class="button-primary">
                    KHÁM PHÁ MENU ↗
                </a>

            </div>

            <div class="hero-symbol">
                <span>✳</span>
                <strong>RỐP<br>RẺNG</strong>
                <span>ϟ</span>
            </div>

        </div>

        <div class="hero-caption">
            COFFEE & REFRESHMENTS — GOOD MOOD EVERY DAY
        </div>

    </div>

</section>


<?php

require_once __DIR__ . '/../config/db.php';

$categoryQuery = $pdo->query(
    "SELECT id, name, slug, image
     FROM categories
     WHERE is_active = 1
     ORDER BY id ASC"
);

$drinkCategories = $categoryQuery->fetchAll();

$drinkProductQuery = $pdo->query(
    "SELECT
        p.id,
        p.name,
        p.price,
        p.image,
        p.is_best_seller,
        p.is_new,
        c.name AS category_name,
        c.slug AS category_slug
    FROM products p
    INNER JOIN categories c
        ON c.id = p.category_id
    WHERE p.is_active = 1
        AND p.is_seasonal = 0
        AND c.is_active = 1
        AND (
            p.is_best_seller = 1
            OR p.is_new = 1
        )
    ORDER BY
        p.is_best_seller DESC,
        p.is_new DESC,
        p.id ASC"
);

$drinkProducts = $drinkProductQuery->fetchAll();

$drinkVariantQuery = $pdo->query(
    "SELECT
        id,
        product_id,
        name,
        price
    FROM product_variants
    WHERE is_active = 1
    ORDER BY product_id ASC, id ASC"
);

$drinkVariants = [];

foreach ($drinkVariantQuery->fetchAll() as $variant) {
    $drinkVariants[$variant['product_id']][] = [
        'id' => (int) $variant['id'],
        'name' => $variant['name'],
        'price' => (int) $variant['price'],
    ];
}

foreach ($drinkProducts as &$drinkProduct) {
    $drinkProduct['variants'] =
        $drinkVariants[$drinkProduct['id']] ?? [];
}

unset($drinkProduct);


$seasonProductQuery = $pdo->query(
    "SELECT
        p.id,
        p.name,
        p.price,
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
            'price' => (int) $row['price'],
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

?>


<section class="category-section" id="categories">

    <div class="container">

        <div class="section-heading">

            <div>

                <span class="section-eyebrow">
                    EXPLORE OUR MENU
                </span>

                <h2>
                    KHÁM PHÁ <span>HƯƠNG VỊ.</span>
                </h2>

            </div>

            <p>
                Từ cà phê đậm vị đến những thức uống
                tươi mát, tìm lựa chọn dành cho bạn.
            </p>

        </div>


        <div class="category-grid">

            <?php foreach ($drinkCategories as $index => $category): ?>

                <a
                    href="menu.php?category=<?= urlencode($category['slug']) ?>"
                    class="category-card"
                >

                    <div class="category-image">

                        <?php if (!empty($category['image'])): ?>

                            <img
                                src="<?= htmlspecialchars($category['image'], ENT_QUOTES, 'UTF-8') ?>"
                                alt="<?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>"
                                loading="lazy"
                            >

                        <?php else: ?>

                            <div class="category-image-placeholder">
                                <span>RỐP RẺNG</span>
                            </div>

                        <?php endif; ?>

                    </div>


                    <div class="category-card-content">

                        <span class="category-number">
                            <?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?>
                        </span>

                        <h3>
                            <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                        </h3>

                    </div>


                    <span class="category-arrow">
                        ↗
                    </span>

                </a>

            <?php endforeach; ?>

        </div>

    </div>

</section>

<section class="product-section" id="featured-products">

        <div class="container">

            <div class="section-heading">

                <div>
                    <span class="section-eyebrow">
                        OUR PICKS
                    </span>

                    <h2>
                        MÓN <span>NỔI BẬT.</span>
                    </h2>
                </div>

                <p>
                    Những thức uống được Rốp Rẻng
                    lựa chọn để bạn khám phá.
                </p>

            </div>

            <div class="product-grid">

                <?php if (empty($drinkProducts)): ?>
                    <p class="product-empty">
                        Hiện chưa có món nổi bật.
                    </p>
                <?php endif; ?>

                <?php foreach ($drinkProducts as $product): ?>
                    <?php
                    $variants = $product['variants'];
                    $hasMultipleVariants = count($variants) > 1;
                    $defaultVariant = $variants[0] ?? null;
                    ?>

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
                                <?= htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8') ?>
                            </span>

                            <h3>
                                <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>

                            <span class="product-price">
                                <?= number_format($defaultVariant['price'] ?? $product['price'], 0, ',', '.') ?>đ
                            </span>

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
                            <?php endif; ?>

                            <?php if ($defaultVariant): ?>
                                <button
                                    type="button"
                                    class="menu-add-to-cart"
                                    data-product-id="<?= (int) $product['id'] ?>"
                                    data-product-name="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-variant-id="<?= (int) $defaultVariant['id'] ?>"
                                    data-variant-name="<?= htmlspecialchars($defaultVariant['name'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-price="<?= (int) $defaultVariant['price'] ?>"
                                >
                                    THÊM VÀO GIỎ
                                </button>
                            <?php endif; ?>

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
                $defaultVariant = $variants[0] ?? null;
                ?>

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

                        <span class="product-price">
                            <?= number_format($defaultVariant['price'] ?? $product['price'], 0, ',', '.') ?>đ
                        </span>

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
                        <?php endif; ?>

                        <?php if ($defaultVariant): ?>
                            <button
                                type="button"
                                class="menu-add-to-cart"
                                data-product-id="<?= (int) $product['id'] ?>"
                                data-product-name="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                                data-variant-id="<?= (int) $defaultVariant['id'] ?>"
                                data-variant-name="<?= htmlspecialchars($defaultVariant['name'], ENT_QUOTES, 'UTF-8') ?>"
                                data-price="<?= (int) $defaultVariant['price'] ?>"
                            >
                                THÊM VÀO GIỎ
                            </button>
                        <?php endif; ?>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    </div>

</section>
