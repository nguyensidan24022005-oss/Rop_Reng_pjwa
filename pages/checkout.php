<section class="checkout-page">
    <div class="container checkout-container">

        <div class="checkout-header">
            <span>RỐP RẺNG · QUẢN LÝ ĐƠN</span>

            <h1>TẠO ĐƠN</h1>

            <p>
                Kiểm tra món và xác nhận đơn hàng.
            </p>
        </div>

        <div class="checkout-layout">

            <!-- LEFT: ORDER INFORMATION -->
            <div class="checkout-form-column">

                <section class="checkout-card">

                    <div class="checkout-card-header">
                        <span>01</span>

                        <div>
                            <small>THÔNG TIN ĐƠN</small>
                            <h2>Thông tin đơn hàng</h2>
                        </div>
                    </div>

                    <div class="checkout-form">

                        <div class="checkout-field">
                            <label for="customerName">
                                Tên khách hàng
                            </label>

                            <input 
                                type="text"
                                id="customerName"
                                name="customer_name"
                                placeholder="Nhập tên khách hàng"
                            >
                        </div>

                    </div>

                </section>

                <section class="checkout-card">
                    
                    <div class="checkout-card-header">
                        <span>02</span>

                        <div>
                            <small>XÁC NHẬN</small>
                            <h2>Kiểm tra đơn hàng</h2>
                        </div>
                    </div>

                    <div class="checkout-confirm-note">
                        <strong>Đơn hàng</strong>

                        <p>
                            Kiểm tra lại sản phẩm, số lượng và tổng tiền
                            trước khi xác nhận.
                        </p>
                    </div>

                </section>

            </div>

            <!-- RIGHT: ORDER SUMMARY -->
            <aside class="checkout-summary">

                <section class="checkout-card">

                    <div class="checkout-card-header">
                        <span>03</span>

                        <div>
                            <small>ĐƠN HÀNG</small>
                            <h2>Chi tiết đơn</h2>
                        </div>
                    </div>

                    <div
                        class="checkout-order-items"
                        id="checkoutOrderItems"
                    >
                        <!-- JavaScript render cart items -->
                    </div>

                    <div class="checkout-total">
                        <span>TỔNG CỘNG</span>

                        <strong id="checkoutTotal">
                            0đ
                        </strong>
                    </div>

                    <button
                        type="button"
                        class="checkout-submit-button"
                        id="checkoutSubmit"
                    >
                        XÁC NHẬN ĐƠN
                        <span>→</span>
                    </button>

                </section>

            </aside>

        </div>

    </div>
</section>