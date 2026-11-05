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

$drinkCategories = [
    [
        'folder' => 'thongdongcaphe',
        'name' => 'Thong Dong Cà Phê',
        'description' => 'Cà phê Espresso được rang đậm, xay và chiết xuất.'
    ],
    [
        'folder' => 'quocdancaphe',
        'name' => 'Quốc Dân Cà Phê',
        'description' => 'Cà phê phin truyền thống được phê chế khác nhau.'
    ],
    [
        'folder' => 'phedi',
        'name' => 'Phê Đi',
        'description' => 'Cà phê phin truyền thống rang đậm dùng cùng với syrup hoặc foarm cream.'
    ],
    [
        'folder' => 'giaikhatcaphe',
        'name' => 'Giải Khát Cà Phê',
        'description' => 'Nền cà phê ủ lạnh, hậu vị chua ngọt tùy theo cách pha chế.'
    ],
    [
        'folder' => 'latte',
        'name' => 'Lát te',
        'description' => 'Matcha hay Coffee kết hợp cùng sữa thơm ngọt.'
    ],
    [
        'folder' => 'trasua',
        'name' => 'Trà Sữa',
        'description' => 'Thơm ngát vị trà, kết hợp cùng với sữa tươi béo ngậy.'
    ],
    [
        'folder' => 'nuocgiaikhat',
        'name' => 'Nước Giải Khát',
        'description' => 'Gu say caffeine nhưng lại thích đi cà phê.'
    ]
];

function getCategoryImage($folder)
{
    $directory = __DIR__ . '/../assets/images/products/drinks/' . $folder;

    $images = [];

    foreach (['jpg', 'jpeg', 'png', 'webp'] as $extension) {
        $matched = glob($directory . '/*.' . $extension);
        if ($matched) {
            $images = array_merge($images, $matched);
        }

        $matchedUpper = glob($directory . '/*.' . strtoupper($extension));
        if ($matchedUpper) {
            $images = array_merge($images, $matchedUpper);
        }
    }

    sort($images, SORT_NATURAL | SORT_FLAG_CASE);

    if (empty($images)) {
        return null;
    }

    $relativePath = 'assets/images/products/drinks/' . $folder . '/' . basename($images[0]);
    return str_replace('\\', '/', $relativePath);
}

?>

<section class="category-section" id="categories">

    <div class="container">

        <div class="section-heading">

            <div>
                <span class="section-eyebrow">EXPLORE OUR MENU</span>

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

            <?php $image = getCategoryImage($category['folder']); ?>

            <a href="menu.php?category=<?= urldecode($category['folder']) ?>"
                class="category-card">

                <div class="category-image">

                    <?php if ($image): ?>

                        <img
                            src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>"
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

                        <p>
                            <?= htmlspecialchars($category['description'], ENT_QUOTES, 'UTF-8') ?>
                        </p>

                </div>

                <span class="category-arrow">↗</span>

            </a>

        <?php endforeach; ?>

        </div>

    </div>

</section>