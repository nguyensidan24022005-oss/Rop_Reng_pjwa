</main>

<footer class="site-footer" id="about">

    <div class="container footer-container">

        <div class="footer-brand">

            <a href="index.php" class="footer-logo">
                <img
                    src="assets/images/brand/logo-rop-reng.png"
                    alt="Rốp Rẻng - Cà phê & Giải khát"
                >
            </a>

            <p>
                Good Taste, Good Mood.
                <br>
                Một chút ngọt ngào cho mỗi ngày.
            </p>

        </div>

        <div class="footer-information">

            <span>KHÁM PHÁ</span>

            <a href="#categories">Danh mục</a>

            <a href="#menu">Thực đơn</a>

        </div>

        <div class="footer-information">

            <span>RỐP RẺNG</span>

            <a href="#about">Về chúng tôi</a>

            <a href="#contact">Liên hệ</a>

        </div>

    </div>

    <div class="container footer-bottom">

    <p>
        © <span id="currentYear"></span> Rốp Rẻng. All rights reserved.
    </p>

    <span>MAKE WITH LOVE ♥</span>

    </div>

</footer>

<?php
$featureScripts = [
    'common_feat.js',
    'navigation_feat.js',
    'cart_feat.js',
];

if (($currentPage ?? '') === 'home') {
    $featureScripts[] = 'hero_slider_feat.js';
}

if (($currentPage ?? '') === 'checkout') {
    $featureScripts[] = 'checkout_feat.js';
}

if (($currentPage ?? '') === 'admin') {
    $adminPage = basename($_SERVER['SCRIPT_NAME'] ?? '');

    if (in_array($adminPage, ['orders.php', 'order-detail.php'], true)) {
        $featureScripts[] = 'admin_orders_feat.js';
    } elseif ($adminPage === 'index.php') {
        $featureScripts[] = 'admin_dashboard_feat.js';
    } elseif ($adminPage === 'products.php') {
        $featureScripts[] = 'admin_products_feat.js';
    }
}

foreach ($featureScripts as $featureScript):
    $featureScriptPath = __DIR__ . '/../assets/js/' . $featureScript;
?>
    <script src="assets/js/<?= htmlspecialchars($featureScript, ENT_QUOTES, 'UTF-8') ?>?v=<?= filemtime($featureScriptPath) ?>"></script>
<?php endforeach; ?>

</body>

</html>