<?php 

$categories = [
    [
        'number'  => '01',
        'name' => 'Trà sữa',
        'description' => 'Ngọt ngào và đậm vị',
        'icon' => '🧋',
        'class' => 'category-orange'
    ],
    [
        'number' => '02',
        'name' => 'Caocao',
        'description' => 'Đậm đà, thơm béo',
        'icon' => '🍫',
        'class' => 'category-pink'
    ],
    [
        'number' => '03',
        'name' => 'Signature',
        'description' => 'Hương vị đặc biệt',
        'icon' => '⭐',
        'class' => 'category-yellow'
    ]
];

$products = [
    [
        'name' => 'Trà sữa trứng nướng',
        'description' => 'Thơm béo, đậm vị trà',
        'price' => '35.000đ',
        'tag' => 'BEST SELLER',
        'image' => 'images/tra-sua-trung-nuong.png',
        'class' => 'product-organe'
    ],
    [
        'name' => 'Cacao kem trứng',
        'description' => 'Cacao đậm đà, kem trứng béo mịn',
        'price' => '39.000đ',
        'tag' => 'SIGNATURE',
        'image' => 'images/cacao-kem-trung.png',
        'class' => 'product-yellow'
    ]
];

function renderProductImage($image, $name, $class)
    {
        $imagePath = __DIR__ . '/../' . $image;

        if (is_file($imagePath)) {
            echo '<img src="' . htmlspecialchars($image) . '" alt="' . htmlspecialchars($name) . '">';
        } else {
            echo '<div class="product-image-placeholder ' .htmlspecialchars($class) . '">';
            echo '<span>RỐP RẺNG</span>';
            echo '<strong>' . htmlspecialchars($name) . '</strong>';
        }
    }

    ?>

    <!-- HERO SECTION -->

    <section class="hero-section">

        <div class="container hero-container">

            <div class="hero-content">

                <div class="hero-label">
                    <span class="hero-label-dot"></span>
                    GOOD TASTE, GOOD MOOD
                </div>

                <h1>
                    UỐNG NGON.
                    <br>
                    <span>VUI HẾT CỠ!</span>
                </h1>

                <p class="hero-description">
                    Một chút ngọt ngào, một chút vui vẻ.
                    Khám phá thế giới hương vị đầy màu sắc
                    cùng Rốp Rẻng.
                </p>

                <div class="hero-actions">

                    <a href="#menu" class="button-primary">
                        KHÁM PHÁ MENU
                        <span></span>
                    </a>

                    <span class="hero-note">
                        Fresh drinks. Happy moments.
                    </span>

                </div>

            </div>

            <div class="hero-visual">

                <div class="hero-sticker sticker-top">
                    GOOD<br>TASTE!
                </div>

                <div class="hero-drink drink-back">
                    <div class="drink-lid"></div>
                    <div class="drink-body drink-cocoa"></div>
                    <div class="drink-straw"></div>
                </div>

                <div class="hero-drink drink-front">
                    <div class="drink-lid"></div>
                    <div class="drink-body drink-milk"></div>
                    <div class="drink-straw"></div>
                </div>

                <div class="hero-sticker sticker-bottom">
                    YUMMY!
                    <span>✳</span>
                </div>

                <div class="hero-decoration decoration-one">✳</div>
                <div class="hero-decoration decoration-two">✦</div>

            </div>

            <div class="hero-index">
                <span>01</span>
                <span>/</span>
                <span>03</span>
            </div>

        </div>

    </section>


    <!-- CATEGORIES SECTION -->

    <section class="categories-section" id="categories">

        <div class="container">

            <div class="section-heading">

                <div>
                    <span class="section-eyebrow">WHAT'S YOUR MOOD?</span>

                    <h2>
                        CHỌN VỊ BẠN <span>YÊU.</span>
                    </h2>
                </div>

                <p>
                    Mỗi hương vị là một trải nghiệm
                    riêng dành cho bạn.
                </p>

            </div>

            <div class="category-grid">

                <?php foreach ($categories as $category): ?>

                    <a href="#menu"
                        class="category-card" <?= htmlspecialchars($category['class']) ?>">

                        <span class="category-number">
                            <?= htmlspecialchars($category['class']) ?>
                        </span>

                        <span class="category-icon">
                            <?= $category['icon'] ?>
                        </span>

                        <div class="category-information">

                            <h3>
                                <?= htmlspecialchars($category['name']) ?>
                            </h3>

                            <p>
                                <?= htmlspecialchars($category['description']) ?>
                            </p>

                        </div>

                        <span class="category-arrow"></span>

                    </a>

                <?php endforeach; ?>

            </div>

        </div>

    </section>


    <!-- BEST SELLERS SECTION -->

    <section class="products-section" id="menu">

        <div class="container">

            <div class="section-heading">

                <div>
                    <span class="section-eyebrow">OUR FAVORITES</span>

                    <h2>
                        BEST <span>SELLERS.</span>
                    </h2>
                </div>

                <p>
                    Những hương vị được yêu thích
                    tại Rốp Rẻng.
                </p>

            </div>

            <div class="product-grid">
                
                <?php foreach ($products as $product): ?>

                    <article class="product-card">

                        <div class="product-image-wrapper">
                            
                            <span>
                                <?= htmlspecialchars($product['tag']) ?>
                            </span>

                            <?php
                            renderProductImage(
                                $product['image'],
                                $product['name'],
                                $product['class']
                            );
                            ?>

                        </div>

                        <div class="product-information">

                            <div>

                                <h3>
                                    <?= htmlspecialchars($product['name']) ?>
                                </h3>

                                <p>
                                    <?= htmlspecialchars($product['description']) ?>
                                </p>

                            </div>

                            <div class="product-bottom">

                                <strong>
                                    <?= htmlspecialchars($product['price']) ?>
                                </strong>

                                <a href="#contact" class="product-action">
                                    ↗
                                </a>

                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

            <div class="products-more">

                <a href="#contact" class="button-secondary">
                    KHÁM PHÁ THÊM
                    <span>↗</span>
                </a>

            </div>

        </div>

    </section>


    <!-- BRAND INTRODUCTION -->

    <section class="brand-section">

        <div class="container brand-container">

            <div class="brand-decoration">
                <span>✳</span>
                <span>R</span>
                <span>✦</span>
            </div>

            <div class="brand-content">

                <span class="section-eyebrow">
                    A LITTLE BIT ABOUT US
                </span>

                <h2>
                    KHÔNG CHỈ LÀ
                    <br>
                    MỘT LY NƯỚC.
                    <br>
                    <span>LÀ NIỀM VUI!</span>
                </h2>

                <p>
                    Rốp Rẻng mang đến những hương vị
                    thú vị, những khoảnh khắc vui vẻ
                    và một chút ngọt ngào trong cuộc sống.
                </p>

            </div>

        </div>

    </section>


    <!-- CONTACT ANCHOR -->

    <div id="contact"></div>