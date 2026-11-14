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

$productQuery = $pdo->query(
    "SELECT
        p.id,
        p.name,
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

$products = $productQuery->fetchAll();

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
                    <span class="section-heading">
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

                <?php foreach ($products as $product): ?>

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
                                <?= number_format($product['price'], 0, ',', '.') ?>đ
                            </span>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        </div>

</section>