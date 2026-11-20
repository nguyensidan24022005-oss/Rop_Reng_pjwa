<?php

$pageTitle = "Quản lý sản phẩm - Rốp Rẻng";
$baseHref = "../";

require_once __DIR__ . '/../includes/header.php';

?>

<section class="admin-page">

    <div class="container admin-container">

        <div class="admin-header">

            <div>

                <span class="admin-eyebrow">
                    RỐP RẺNG · SẢN PHẨM
                </span>

                <h1>SẢN PHẨM</h1>

                <p>
                    Quản lý toàn bộ sản phẩm trong menu.
                </p>

            </div>

            <div class="admin-order-count">

                <span id="adminProductCount">
                    0
                </span>

                <small>SẢN PHẨM</small>

            </div>

        </div>

        <section class="admin-card">

            <div class="admin-card-header">

                <div>

                    <small>DANH SÁCH</small>

                    <h2>
                        Tất cả sản phẩm
                    </h2>

                </div>

                <button
                    type="button"
                    class="admin-refresh-button"
                    id="adminProductRefresh"
                >
                    ↻ LÀM MỚI
                </button>

            </div>

            <div
                class="admin-products-table-wrapper"
                id="adminProductsTableWrapper"
            >
        
                <div class="admin-loading">
                    ĐANG TẢI SẢN PHẨM...
                </div>

            </div>

        </section>

    </div>

</section>

<?php

require_once __DIR__ . '/../includes/footer.php';

?>