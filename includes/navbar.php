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