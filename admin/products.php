<?php

$pageTitle = "Quản lý sản phẩm - Rốp Rẻng";
$currentPage = "admin";
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

                <div class="admin-products-actions">

                    <button
                        type="button"
                        class="admin-product-create-button"
                        id="adminProductCreateOpen"
                    >
                        + THÊM SẢN PHẨM
                    </button>

                    <button
                        type="button"
                        class="admin-refresh-button"
                        id="adminProductRefresh"
                    >
                        ↻ LÀM MỚI
                    </button>

                </div>

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

<div
    class="admin-modal"
    id="adminProductEditModal"
    hidden
>
    <div
        class="admin-modal-overlay"
        data-close-product-modal
    ></div>

    <div
        class="admin-modal-content"
        role="dialog"
        aria-modal="true"
        aria-labelledby="adminProductEditTitle"
    >
        <div class="admin-modal-header">
            <div>
                <p class="admin-eyebrow">QUẢN LÝ SẢN PHẨM</p>
                <h2 id="adminProductEditTitle">
                    SỬA SẢN PHẨM
                </h2>
            </div>

            <button
                type="button"
                class="admin-modal-close"
                id="adminProductEditClose"
                aria-label="Đóng"
            >
                ×
            </button>
        </div>

        <form id="adminProductEditForm">
            <input 
                type="hidden"
                id="adminEditProductId"
                name="id"
            >

            <div class="admin-form-grid">
                <div class="admin-form-group">
                    <label for="adminEditProductName">
                        Tên sản phẩm
                    </label>

                    <input 
                        type="text"
                        id="adminEditProductName"
                        name="name"
                        required
                    >
                </div>

                <div class="admin-form-group">
                    <label for="adminEditVariantType">
                        Nhóm phân loại
                    </label>
                    <select 
                        name="variant_type" 
                        id="adminEditVariantType"
                        required
                    >
                        <option value="default">Default</option>
                        <option value="size">M / L</option>
                    </select>
                </div>
                <div
                    class="admin-form-group"
                    id="adminEditDefaultPriceGroup"
                >
                    <label for="adminEditProductPrice">
                        Giá Default (VNĐ)
                    </label>
                    <input 
                        type="number" 
                        name="price" 
                        id="adminEditProductPrice" 
                        min="0"
                        step="1000"
                    >
                </div>
                <div
                    class="admin-form-group"
                    id="adminEditPriceMGroup"
                    hidden
                >
                    <label for="adminEditPriceM">
                        Giá size M (VNĐ)
                    </label>
                    <input 
                        type="number"
                        id="adminEditPriceM"
                        name="price_m"
                        min="0"
                        step="1000"
                    >
                </div>
                <div
                    class="admin-form-group"
                    id="adminEditPriceLGroup"
                    hidden
                >
                    <label for="adminEditPriceL">
                        Giá size L (VNĐ)
                    </label>
                    <input 
                        type="number"
                        id="adminEditPriceL"
                        name="price_l"
                        min="0"
                        step="1000"
                    >
                </div>

                <div class="admin-form-group">
                    <label for="adminEditProductType">
                        Loại sản phẩm
                    </label>

                    <select 
                        name="type" 
                        id="adminEditProductType"
                        required
                    >
                        <option value="drink">
                            DRINKS
                        </option>

                        <option value="season">
                            SEASON PRODUCT
                        </option>
                    </select>
                </div>

                <div
                    class="admin-form-group"
                    id="adminEditCategoryGroup"
                >
                    <label for="adminEditProductCategory">
                        Danh mục
                    </label>

                    <select 
                        name="category_id" 
                        id="adminEditProductCategory"
                    >
                        <option value="">
                            -- Chọn danh mục --
                        </option>
                    </select>
                </div>

                <div
                    class="admin-form-group"
                    id="adminEditSeasonStartGroup"
                    hidden
                >
                    <label for="adminEditSeasonStart">
                        Ngày bắt đầu
                    </label>

                    <input 
                        type="date"
                        id="adminEditSeasonStart"
                        name="season_start"
                    >
                </div>

                <div
                    class="admin-form-group"
                    id="adminEditSeasonEndGroup"
                    hidden
                >
                    <label for="adminEditSeasonEnd">
                        Ngày kết thúc
                    </label>

                    <input 
                        type="date"
                        id="adminEditSeasonEnd"
                        name="season_end"
                    >
                </div>

                <div class="admin-form-group">
                    <label for="adminEditProductActive">
                        Trạng thái
                    </label>

                    <select 
                        name="active" 
                        id="adminEditProductActive"
                    >
                        <option value="1">
                            Đang bán
                        </option>

                        <option value="0">
                            Ngừng bán
                        </option>
                    </select>
                </div>

            </div>

            <div class="admin-form-actions">
                <button
                    type="button"
                    class="admin-button-secondary"
                    id="adminProductEditCancel"
                >
                    HỦY
                </button>

                <button
                    type="submit"
                    class="admin-button-primary"
                >
                    LƯU
                </button>
            </div>
        </form>
    </div>
</div>

<!-- CREATE PRODUCT MODAL -->
<div
    class="admin-modal"
    id="adminProductCreateModal"
    hidden
>
    <div
        class="admin-modal-overlay"
        data-close-create-product-modal
    ></div>

    <div
        class="admin-modal-content"
        role="dialog"
        aria-modal="true"
        aria-labelledby="adminProductCreateTitle"
    >
        <div class="admin-modal-header">
            <div>
                <p class="admin-eyebrow">QUẢN LÝ SẢN PHẨM</p>
                <h2 id="adminProductCreateTitle">
                    THÊM SẢN PHẨM
                </h2>
            </div>

            <button
                type="button"
                class="admin-modal-close"
                id="adminProductCreateClose"
                aria-label="Đóng"
            >
                ×
            </button>
        </div>

        <form
            id="adminProductCreateForm"
            enctype="multipart/form-data"
        >
            <div class="admin-form-grid">

                <!-- NAME -->
                <div class="admin-form-group">
                    <label for="adminCreateProductName">
                        Tên sản phẩm
                    </label>

                    <input
                        type="text"
                        id="adminCreateProductName"
                        name="name"
                        maxlength="150"
                        required
                    >
                </div>

                <!-- PRODUCT TYPE -->
                <div class="admin-form-group">
                    <label for="adminCreateProductType">
                        Loại sản phẩm
                    </label>

                    <select
                        id="adminCreateProductType"
                        name="is_seasonal"
                        required
                    >
                        <option value="0">DRINKS</option>
                        <option value="1">SEASON PRODUCT</option>
                    </select>
                </div>

                <!-- CATEGORY -->
                <div
                    class="admin-form-group"
                    id="adminCreateCategoryGroup"
                >
                    <label for="adminCreateProductCategory">
                        Danh mục
                    </label>

                    <select
                        id="adminCreateProductCategory"
                        name="category_id"
                        required
                    >
                        <option value="">
                            -- Chọn danh mục --
                        </option>
                    </select>
                </div>

                <!-- SEASON START -->
                <div
                    class="admin-form-group"
                    id="adminCreateSeasonStartGroup"
                    hidden
                >
                    <label for="adminCreateSeasonStart">
                        Ngày bắt đầu
                    </label>

                    <input
                        type="date"
                        id="adminCreateSeasonStart"
                        name="season_start"
                    >
                </div>

                <!-- SEASON END -->
                <div
                    class="admin-form-group"
                    id="adminCreateSeasonEndGroup"
                    hidden
                >
                    <label for="adminCreateSeasonEnd">
                        Ngày kết thúc
                    </label>

                    <input
                        type="date"
                        id="adminCreateSeasonEnd"
                        name="season_end"
                    >
                </div>

                <!-- VARIANT TYPE -->
                <div class="admin-form-group">
                    <label for="adminCreateVariantType">
                        Cấu trúc biến thể
                    </label>

                    <select
                        id="adminCreateVariantType"
                        name="variant_type"
                        required
                    >
                        <option value="default">Default</option>
                        <option value="size">M / L</option>
                    </select>
                </div>

                <!-- DEFAULT PRICE -->
                <div
                    class="admin-form-group"
                    id="adminCreateDefaultPriceGroup"
                >
                    <label for="adminCreatePrice">
                        Giá Default (VNĐ)
                    </label>

                    <input
                        type="number"
                        id="adminCreatePrice"
                        name="price"
                        min="0"
                        step="1000"
                        required
                    >
                </div>

                <!-- M PRICE -->
                <div
                    class="admin-form-group"
                    id="adminCreatePriceMGroup"
                    hidden
                >
                    <label for="adminCreatePriceM">
                        Giá size M (VNĐ)
                    </label>

                    <input
                        type="number"
                        id="adminCreatePriceM"
                        name="price_m"
                        min="0"
                        step="1000"
                    >
                </div>

                <!-- L PRICE -->
                <div
                    class="admin-form-group"
                    id="adminCreatePriceLGroup"
                    hidden
                >
                    <label for="adminCreatePriceL">
                        Giá size L (VNĐ)
                    </label>

                    <input
                        type="number"
                        id="adminCreatePriceL"
                        name="price_l"
                        min="0"
                        step="1000"
                    >
                </div>

                <!-- IMAGE -->
                <div class="admin-form-group">
                    <label for="adminCreateProductImage">
                        Ảnh sản phẩm
                    </label>

                    <input
                        type="file"
                        id="adminCreateProductImage"
                        name="image"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <small>
                        JPG, PNG hoặc WEBP. Tối đa 5MB.
                    </small>
                </div>

                <!-- FLAGS -->
                <div class="admin-form-group">
                    <label>Nhãn sản phẩm</label>

                    <label class="admin-form-checkbox">
                        <input
                            type="checkbox"
                            name="is_best_seller"
                            value="1"
                        >
                        Best Seller
                    </label>

                    <label class="admin-form-checkbox">
                        <input
                            type="checkbox"
                            name="is_new"
                            value="1"
                        >
                        Sản phẩm mới
                    </label>
                </div>

                <!-- DESCRIPTION -->
                <div
                    class="admin-form-group admin-form-group-full"
                >
                    <label for="adminCreateProductDescription">
                        Mô tả sản phẩm
                    </label>

                    <textarea
                        id="adminCreateProductDescription"
                        name="description"
                        rows="4"
                    ></textarea>
                </div>

            </div>

            <div class="admin-form-actions">

                <button
                    type="button"
                    class="admin-button-secondary"
                    id="adminProductCreateCancel"
                >
                    HỦY
                </button>

                <button
                    type="submit"
                    class="admin-button-primary"
                >
                    TẠO SẢN PHẨM
                </button>

            </div>
        </form>
    </div>
</div>

<?php

require_once __DIR__ . '/../includes/footer.php';

?>