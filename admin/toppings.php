<?php

$pageTitle = "Quản lý topping - Rốp Rẻng";
$currentPage = "admin";
$baseHref = "../";

require_once __DIR__ . '/../includes/header.php';

?>

<section class="admin-page">

    <div class="container admin-container">

        <div class="admin-header">

            <div>

                <span class="admin-eyebrow">
                    RỐP RẺNG · TOPPING
                </span>

                <h1>TOPPING</h1>

                <p>
                    Quản lý các topping bổ sung cho sản phẩm.
                </p>

            </div>

            <div class="admin-order-count">

                <span id="adminToppingCount">
                    0
                </span>

                <small>TOPPING</small>

            </div>

        </div>

        <section class="admin-card">

            <div class="admin-card-header">

                <div>

                    <small>DANH SÁCH</small>

                    <h2>
                        Tất cả topping
                    </h2>

                </div>

                <div class="admin-products-actions">

                    <button
                        type="button"
                        class="admin-product-create-button"
                        id="adminToppingCreateOpen"
                    >
                        + THÊM TOPPING
                    </button>

                    <button
                        type="button"
                        class="admin-refresh-button"
                        id="adminToppingRefresh"
                    >
                        ↻ LÀM MỚI
                    </button>

                </div>

            </div>

            <div
                class="admin-products-table-wrapper"
                id="adminToppingsTableWrapper"
            >

                <div class="admin-loading">
                    CHƯA CÓ DỮ LIỆU TOPPING...
                </div>

            </div>

        </section>

    </div>

</section>

<!-- CREATE TOPPING MODAL -->
<div
    class="admin-modal"
    id="adminToppingCreateModal"
    hidden
>
    <div
        class="admin-modal-overlay"
        data-close-topping-modal
    ></div>

    <div
        class="admin-modal-content"
        role="dialog"
        aria-modal="true"
        aria-labelledby="adminToppingCreateTitle"
    >

        <div class="admin-modal-header">

            <div>

                <p class="admin-eyebrow">
                    QUẢN LÝ TOPPING
                </p>

                <h2 id="adminToppingCreateTitle">
                    THÊM TOPPING
                </h2>

            </div>

            <button
                type="button"
                class="admin-modal-close"
                id="adminToppingCreateClose"
                aria-label="Đóng"
            >
                ×
            </button>

        </div>

        <form id="adminToppingCreateForm">

            <div class="admin-form-grid">

                <div class="admin-form-group">

                    <label for="adminCreateToppingName">
                        Tên topping
                    </label>

                    <input
                        type="text"
                        id="adminCreateToppingName"
                        name="name"
                        maxlength="100"
                        required
                    >

                </div>

                <div class="admin-form-group">

                    <label for="adminCreateToppingPrice">
                        Giá topping (VNĐ)
                    </label>

                    <input
                        type="number"
                        id="adminCreateToppingPrice"
                        name="price"
                        min="0"
                        step="1000"
                        required
                    >

                </div>

                <div
                    class="admin-form-group"
                    style="grid-column: 1 / -1;"
                >

                    <label for="adminCreateToppingDescription">
                        Mô tả
                    </label>

                    <textarea
                        id="adminCreateToppingDescription"
                        name="description"
                        rows="4"
                        maxlength="500"
                    ></textarea>

                </div>

                <div class="admin-form-group">

                    <label for="adminCreateToppingImage">
                        Đường dẫn hình ảnh
                    </label>

                    <input
                        type="text"
                        id="adminCreateToppingImage"
                        name="image"
                        maxlength="255"
                        placeholder="assets/images/products/toppings/..."
                    >

                </div>

                <div class="admin-form-group">

                    <label for="adminCreateToppingActive">
                        Trạng thái
                    </label>

                    <select
                        id="adminCreateToppingActive"
                        disabled
                    >
                        <option value="1">
                            Đang bán
                        </option>
                    </select>

                </div>

            </div>

            <div class="admin-form-actions">

                <button
                    type="button"
                    class="admin-button-secondary"
                    id="adminToppingCreateCancel"
                >
                    HỦY
                </button>

                <button
                    type="submit"
                    class="admin-button-primary"
                    id="adminToppingCreateSubmit"
                >
                    THÊM TOPPING
                </button>

            </div>

        </form>

    </div>

</div>

<!-- EDIT TOPPING MODAL -->
<div
    class="admin-modal"
    id="adminToppingEditModal"
    hidden
>
    <div
        class="admin-modal-overlay"
        data-close-topping-edit-modal
    ></div>

    <div
        class="admin-modal-content"
        role="dialog"
        aria-modal="true"
        aria-labelledby="adminToppingEditTitle"
    >

        <div class="admin-modal-header">

            <div>

                <p class="admin-eyebrow">
                    QUẢN LÝ TOPPING
                </p>

                <h2 id="adminToppingEditTitle">
                    SỬA TOPPING
                </h2>

            </div>

            <button
                type="button"
                class="admin-modal-close"
                id="adminToppingEditClose"
                aria-label="Đóng"
            >
                ×
            </button>

        </div>

        <form id="adminToppingEditForm">

            <input
                type="hidden"
                id="adminEditToppingId"
            >

            <div class="admin-form-grid">

                <div class="admin-form-group">

                    <label for="adminEditToppingName">
                        Tên topping
                    </label>

                    <input
                        type="text"
                        id="adminEditToppingName"
                        maxlength="100"
                        required
                    >

                </div>

                <div class="admin-form-group">

                    <label for="adminEditToppingPrice">
                        Giá topping (VNĐ)
                    </label>

                    <input
                        type="number"
                        id="adminEditToppingPrice"
                        min="0"
                        step="1000"
                        required
                    >

                </div>

                <div
                    class="admin-form-group"
                    style="grid-column: 1 / -1;"
                >

                    <label for="adminEditToppingDescription">
                        Mô tả
                    </label>

                    <textarea
                        id="adminEditToppingDescription"
                        rows="4"
                        maxlength="500"
                    ></textarea>

                </div>

                <div
                    class="admin-form-group"
                    style="grid-column: 1 / -1;"
                >

                    <label for="adminEditToppingImage">
                        Đường dẫn hình ảnh
                    </label>

                    <input
                        type="text"
                        id="adminEditToppingImage"
                        maxlength="255"
                    >

                </div>

            </div>

            <div class="admin-form-actions">

                <button
                    type="button"
                    class="admin-button-secondary"
                    id="adminToppingEditCancel"
                >
                    HỦY
                </button>

                <button
                    type="submit"
                    class="admin-button-primary"
                    id="adminToppingEditSubmit"
                >
                    LƯU THAY ĐỔI
                </button>

            </div>

        </form>

    </div>

</div>

<?php

require_once __DIR__ . '/../includes/footer.php';

?>