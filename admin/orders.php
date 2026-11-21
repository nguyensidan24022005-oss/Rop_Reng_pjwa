<?php

$pageTitle = "Quản lý đơn hàng - Rốp Rẻng";
$currentPage = "admin";
$baseHref = "../";

require_once __DIR__ . '/../includes/header.php';

?>

<section class="admin-page">

    <div class="container admin-container">

        <div class="admin-header">

            <div>
                <span class="admin-eyebrow">
                    RỐP RẺNG · QUẢN LÝ
                </span>

                <h1>ĐƠN HÀNG</h1>

                <p>
                    Theo dõi và xử lý đơn hàng.
                </p>
            </div>

            <div class="admin-order-count">
                <span id="adminOrderCount">0</span>
                <small>ĐƠN</small>
            </div>

        </div>

        <section class="admin-card">

            <div class="admin-card-header">

                <div>
                    <small>DANH SÁCH</small>
                    <h2>Tất cả đơn hàng</h2>
                </div>

                <button
                    type="button"
                    class="admin-refresh-button"
                    id="adminOrdersRefresh"
                >
                    ↻ LÀM MỚI
                </button>

            </div>

            <div 
                class="admin-orders-table-wrapper"
                id="adminOrdersTableWrapper"
            >
        
                <div class="admin-loading">
                    ĐANG TẢI ĐƠN HÀNG...
                </div>

            </div>

        </section>

    </div>
    
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>