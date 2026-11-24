<?php

$pageTitle = "Quản lý danh mục - Rốp Rẻng";
$currentPage = "admin";
$baseHref = "../";

require_once __DIR__ . '/../includes/header.php';

?>

<section class="admin-page">

    <div class="container admin-container">

        <div class="admin-header">

            <div>
                <span class="admin-eyebrow">
                    RỐP RẺNG · QUẢN TRỊ
                </span>

                <h1>DANH MỤC</h1>

                <p>
                    Quản lý các danh mục sản phẩm đang sử dụng tại cửa hàng.
                </p>
            </div>

        </div>

        <section class="admin-card">

            <div class="admin-card-header">

                <div>
                    <small>DANH MỤC</small>
                    <h2>Quản lý danh mục</h2>
                </div>

                <button
                    type="button"
                    class="admin-action-button"
                    id="openCreateCategoryModal"
                >
                    + THÊM DANH MỤC
                </button>

            </div>

            <div id="categoryMessage"></div>

            <div class="admin-table-wrapper">

                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên danh mục</th>
                            <th>Slug</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>

                    <tbody id="categoriesTableBody">

                        <tr>
                            <td colspan="5">
                                Đang tải danh mục...
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </section>

    </div>

</section>

<div
    class="admin-modal"
    id="categoryModal"
    aria-hidden="true"
>

    <div class="admin-modal-overlay" data-close-category-modal></div>

    <div class="admin-modal-content">

        <div class="admin-modal-header">

            <div>
                <small id="categoryModalEyebrow">DANH MỤC</small>
                <h2 id="categoryModalTitle">Thêm danh mục</h2>
            </div>

            <button
                type="button"
                class="admin-modal-close"
                data-close-category-modal
                aria-label="Đóng"
            >
                ×
            </button>

        </div>

        <form id="categoryForm">

            <input
                type="hidden"
                id="categoryId"
                name="id"
            >

            <div class="admin-form-group">

                <label for="categoryName">
                    Tên danh mục
                </label>

                <input
                    type="text"
                    id="categoryName"
                    name="name"
                    maxlength="100"
                    required
                >

            </div>

            <div class="admin-form-group">

                <label for="categorySlug">
                    Slug
                </label>

                <input
                    type="text"
                    id="categorySlug"
                    name="slug"
                    maxlength="100"
                    required
                >

                <small>
                    Ví dụ: giaikhatcaphe
                </small>

            </div>

            <div class="admin-modal-actions">

                <button
                    type="button"
                    class="admin-action-button"
                    data-close-category-modal
                >
                    HỦY
                </button>

                <button
                    type="submit"
                    class="admin-action-button"
                >
                    LƯU DANH MỤC
                </button>

            </div>

        </form>

    </div>

</div>

<script src="assets/js/admin-categories.js?v=<?= filemtime(__DIR__ . '/../assets/js/admin-categories.js') ?>"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>