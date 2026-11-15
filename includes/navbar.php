<?php

$currentPage = $currentPage ?? 'home'

?>

<header class="site-header">

    <div class="container header-container">

        <a href="index.php" class="brand-logo">
            <img
                src="assets/images/brand/logo-rop-reng.png"
                alt="Rốp Rẻng - Cà phê & Giải khát"
            >
        </a>

        <nav class="main-navigation" id="mainNavigation">

            <a 
            href="index.php"
            class="nav-link <?= $currentPage === 'home' ? 'active' : '' ?>"
            >
                    Trang chủ
            </a>

            <a 
            href="menu.php"
            class="nav-link <?= $currentPage === 'menu' ? 'active' : '' ?>"
            >
                Menu
            </a>

            <a href="#about" class="nav-link">
                Về Rốp Rẻng
            </a>

        </nav>

        <div class="header-actions">

            <button
                type="button"
                class="header-cart-button"
                id="cartToggle"
                aria-label="Mở giỏ hàng"
            >
                GIỎ HÀNG
                <span class="cart-count" id="cartCount">0</span>
            </button>
            <button
                class="menu-toggle"
                id="menuToggle"
                aria-label="Mở menu"
                aria-expanded="false"
            >
                <span></span>
                <span></span>
            </button>

        </div>

    </div>

</header>

<div class="cart-overlay" id="cartOverlay"></div>

<aside
    class="cart-drawer"
    id="cartDrawer"
    aria-hidden="true"
>

    <div class="cart-drawer-header">

        <div>
            <span class="cart-drawer-eyebrow">
                RỐP RẺNG
            </span>

            <h2>GIỎ HÀNG</h2>
        </div>

        <button
            type="button"
            class="cart-close-button"
            id="cartClose"
            aria-label="Đóng giỏ hàng"
        >
            ×
        </button>

    </div>

    <div
        class="cart-drawer-body"
        id="cartDrawerBody"
    >
        <!-- Cart items sẽ được JavaScript render tại đây -->
    </div>

    <div class="cart-drawer-footer">

        <div class="cart-subtotal">

            <span>TẠM TÍNH</span>

            <strong id="cartSubtotal">
                0đ
            </strong>

        </div>

        <button
            type="button"
            class="cart-checkout-button"
            id="cartCheckout"
        >
            ĐẶT HÀNG
            <span>→</span>
        </button>
        
    </div>

</aside>