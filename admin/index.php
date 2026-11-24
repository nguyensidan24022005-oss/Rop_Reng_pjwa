<?php

$pageTitle = "Quản trị - Rốp Rẻng";
$currentPage = "admin";
$baseHref = "../";

require_once __DIR__ . '/../includes/header.php';

?>

<section class="admin-page">

    <div class="container admin-container">

        <div class="admin-header admin-dashboard-header">

            <div>
                <span class="admin-eyebrow">
                    RỐP RẺNG · QUẢN TRỊ
                </span>

                <h1>DASHBOARD</h1>

                <p>
                    Tổng quan hoạt động và quản lý cửa hàng.
                </p>
            </div>

            <div class="admin-dashboard-header-actions">
                <a
                    href="admin/orders.php"
                    class="button-primary admin-dashboard-cta"
                >
                    <span>QUẢN LÝ ĐƠN HÀNG</span>
                    <span aria-hidden="true">→</span>
                </a>
            </div>

        </div>

        <section class="admin-card">

            <div class="admin-card-header">

                <div>
                    <small>QUẢN LÝ</small>
                    <h2>Trung tâm quản trị</h2>
                </div>

            </div>

            <div class="admin-stats-section">

                <div class="admin-stats-group">

                    <div class="admin-stats-group-header">
                        <small>ĐƠN HÀNG</small>
                        <h3>Tình hình đơn hàng</h3>
                    </div>

                    <div class="admin-stats-grid">

                        <article class="admin-stat-card">
                            <span>TỔNG ĐƠN</span>
                            <strong id="dashboardTotalOrders">0</strong>
                        </article>

                        <article class="admin-stat-card">
                            <span>ĐANG XỬ LÝ</span>
                            <strong id="dashboardPendingOrders">0</strong>
                        </article>

                        <article class="admin-stat-card">
                            <span>ĐANG THỰC HIỆN MÓN</span>
                            <strong id="dashboardConfirmedOrders">0</strong>
                        </article>

                        <article class="admin-stat-card">
                            <span>HOÀN THÀNH</span>
                            <strong id="dashboardCompletedOrders">0</strong>
                        </article>


                        <article class="admin-stat-card">
                            <span>ĐÃ HỦY</span>
                            <strong id="dashboardCancelledOrders">0</strong>
                        </article>

                    </div>

                </div>

                <div class="admin-stats-group" id="dashboardRevenue">

                    <div class="admin-stats-group-header">
                        <small>DOANH THU</small>
                        <h3>Hiệu quả kinh doanh</h3>
                    </div>

                    <div class="admin-stats-grid admin-stats-grid-revenue">

                        <article class="admin-stat-card admin-stat-card-highlight">
                            <span>HÔM NAY</span>
                            <strong id="dashboardTodayRevenue">0đ</strong>
                        </article>

                        <article class="admin-stat-card admin-stat-card-highlight">
                            <span>THÁNG NÀY</span>
                            <strong id="dashboardMonthRevenue">0đ</strong>
                        </article>

                        <article class="admin-stat-card admin-stat-card-highlight">
                            <span>TỔNG DOANH THU</span>
                            <strong id="dashboardTotalRevenue">0đ</strong>
                        </article>

                    </div>

                </div>

                <div class="admin-stats-group">

                    <div class="admin-stats-grid">

                        <article class="admin-stat-card">
                            <span>TỔNG SẢN PHẨM</span>
                            <strong id="dashboardTotalProducts">0</strong>
                        </article>

                        <article class="admin-stat-card">
                            <span>ĐANG BÁN</span>
                            <strong id="dashboardActiveProducts">0</strong>
                        </article>

                        <article class="admin-stat-card">
                            <span>ĐANG ẨN</span>
                            <strong id="dashboardInactiveProducts">0</strong>
                        </article>

                    </div>

                </div>
                
            </div>

            <div class="admin-dashboard-grid">

                <a 
                    href="admin/orders.php"
                    class="admin-dashboard-card"
                >
                    <span class="admin-dashboard-card-number">
                        01
                    </span>

                    <div>
                        <small>QUẢN LÝ</small>
                        <h3>Đơn hàng</h3>
                        <p>
                            Theo dõi và xử lý đơn hàng.
                        </p>
                    </div>

                    <span class="admin-dashboard-card-arrow">
                        →
                    </span>
                </a>

                <a 
                    href="admin/products.php"
                    class="admin-dashboard-card"
                >
                    <span class="admin-dashboard-card-number">
                        02
                    </span>

                    <div>
                        <small>SẢN PHẨM</small>
                        <h3>Sản phẩm</h3>
                        <p>
                            Thêm, chỉnh sửa và quản lý sản phẩm.
                        </p>
                    </div>

                    <span class="admin-dashboard-card-arrow">
                        →
                    </span>
                </a>

                <a 
                    href="admin/categories.php"
                    class="admin-dashboard-card"
                >
                    <span class="admin-dashboard-card-number">
                        03
                    </span>

                    <div>
                        <small>DANH MỤC</small>
                        <h3>Danh mục</h3>
                        <p>
                            Chọn danh mục khi quản lý sản phẩm.
                        </p>
                    </div>

                    <span class="admin-dashboard-card-arrow">
                        →
                    </span>
                </a>

                <a 
                    href="admin/index.php#dashboardRevenue"
                    class="admin-dashboard-card"
                >
                    <span class="admin-dashboard-card-number">
                        04
                    </span>

                    <div>
                        <small>THỐNG KÊ</small>
                        <h3>Doanh thu</h3>
                        <p>
                            Xem thống kê doanh thu ở phần tổng quan.
                        </p>
                    </div>

                    <span class="admin-dashboard-card-arrow">
                        →
                    </span>
                </a>

            </div>

        </section>

        <section class="admin-card">

            <div class="admin-card-header">

                <div>
                    <small>TRUY CẬP</small>
                    <h2>Thao tác nhanh</h2>
                </div>

            </div>

            <div class="admin-quick-actions">

                <a 
                    href="menu.php"
                    class="admin-action-button"
                >
                    XEM MENU →
                </a>

                <a 
                    href="index.php"
                    class="admin-action-button"
                >
                    VỀ TRANG CHÍNH →
                </a>

            </div>

        </section>

    </div>

</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>