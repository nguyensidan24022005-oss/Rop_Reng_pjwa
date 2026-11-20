<?php

$pageTitle = "Chi tiết đơn hàng - Rốp Rẻng";
$baseHref = "../";

require_once __DIR__ . '/../includes/header.php';

?>

<section class="admin-page">

    <div class="container admin-container">

        <!-- BACK -->
        <div class="admin-detail-back">
            <a href="admin/orders.php">
                ← QUAY LẠI ĐƠN HÀNG
            </a>
        </div>


        <!-- HEADER -->
        <div class="admin-header admin-detail-header">

            <div>

                <span class="admin-eyebrow">
                    RỐP RẺNG · CHI TIẾT ĐƠN
                </span>

                <h1 id="orderDetailTitle">
                    ĐƠN HÀNG
                </h1>

                <p id="orderDetailCode">
                    ĐANG TẢI...
                </p>

            </div>

            <div
                class="admin-order-status-large"
                id="orderDetailStatus"
            >
                ĐANG TẢI
            </div>

        </div>


        <!-- LOADING -->
        <div
            class="admin-detail-loading"
            id="orderDetailLoading"
        >
            ĐANG TẢI THÔNG TIN ĐƠN HÀNG...
        </div>


        <!-- ERROR -->
        <div
            class="admin-detail-error"
            id="orderDetailError"
            style="display: none;"
        >
            KHÔNG THỂ TẢI THÔNG TIN ĐƠN HÀNG.
        </div>


        <!-- CONTENT -->
        <div
            class="admin-detail-layout"
            id="orderDetailContent"
            style="display: none;"
        >

            <!-- LEFT -->
            <div class="admin-detail-main">

                <!-- ORDER INFORMATION -->
                <section class="admin-card">

                    <div class="admin-card-header">

                        <div>
                            <small>THÔNG TIN</small>

                            <h2>
                                Thông tin đơn hàng
                            </h2>
                        </div>

                    </div>


                    <div class="admin-order-meta">

                        <div class="admin-order-meta-item">

                            <span>
                                MÃ ĐƠN
                            </span>

                            <strong
                                id="detailOrderCode"
                            >
                                —
                            </strong>

                        </div>


                        <div class="admin-order-meta-item">

                            <span>
                                KHÁCH HÀNG
                            </span>

                            <strong
                                id="detailCustomerName"
                            >
                                —
                            </strong>

                        </div>


                        <div class="admin-order-meta-item">

                            <span>
                                THỜI GIAN
                            </span>

                            <strong
                                id="detailCreatedAt"
                            >
                                —
                            </strong>

                        </div>


                        <div class="admin-order-meta-item">

                            <span>
                                TRẠNG THÁI
                            </span>

                            <strong
                                id="detailStatusText"
                            >
                                —
                            </strong>

                        </div>

                    </div>

                </section>


                <!-- ORDER ITEMS -->
                <section class="admin-card">

                    <div class="admin-card-header">

                        <div>
                            <small>SẢN PHẨM</small>

                            <h2>
                                Chi tiết món
                            </h2>
                        </div>

                    </div>


                    <div
                        class="admin-order-items"
                        id="orderDetailItems"
                    >
                        <!-- JavaScript render -->
                    </div>


                    <!-- TOTAL -->
                    <div class="admin-detail-total">

                        <span>
                            TỔNG CỘNG
                        </span>

                        <strong
                            id="detailOrderTotal"
                        >
                            0đ
                        </strong>

                    </div>

                </section>

            </div>


            <!-- RIGHT -->
            <aside class="admin-detail-sidebar">

                <!-- STATUS -->
                <section class="admin-card">

                    <div class="admin-card-header">

                        <div>
                            <small>XỬ LÝ</small>

                            <h2>
                                Trạng thái đơn
                            </h2>
                        </div>

                    </div>


                    <div class="admin-status-control">

                        <label for="orderStatus">
                            TRẠNG THÁI ĐƠN
                        </label>


                        <select id="orderStatus">

                            <option value="pending">
                                ĐANG XỬ LÝ
                            </option>

                            <option value="confirmed">
                                ĐANG THỰC HIỆN
                            </option>

                            <option value="completed">
                                HOÀN THÀNH
                            </option>

                            <option value="cancelled">
                                ĐÃ HỦY
                            </option>

                        </select>


                        <button
                            type="button"
                            id="updateOrderStatus"
                        >
                            CẬP NHẬT TRẠNG THÁI

                            <span>
                                →
                            </span>

                        </button>

                    </div>

                </section>


                <!-- SUMMARY -->
                <section class="admin-card">

                    <div class="admin-card-header">

                        <div>
                            <small>TỔNG QUAN</small>

                            <h2>
                                Tổng đơn
                            </h2>
                        </div>

                    </div>


                    <div class="admin-summary-row">

                        <span>
                            Tạm tính
                        </span>

                        <strong
                            id="detailOrderSubtotal"
                        >
                            0đ
                        </strong>

                    </div>


                    <div class="admin-summary-row admin-summary-total">

                        <span>
                            Tổng cộng
                        </span>

                        <strong
                            id="detailOrderTotalSide"
                        >
                            0đ
                        </strong>

                    </div>

                </section>

            </aside>

        </div>

    </div>

</section>


<script>

document.addEventListener(
    'DOMContentLoaded',
    () => {

        loadOrderDetail();

    }
);


async function loadOrderDetail() {

    const loading =
        document.getElementById(
            'orderDetailLoading'
        );

    const errorBox =
        document.getElementById(
            'orderDetailError'
        );

    const content =
        document.getElementById(
            'orderDetailContent'
        );


    const params =
        new URLSearchParams(
            window.location.search
        );

    const orderId =
        params.get('id');


    if (!orderId) {

        showOrderDetailError(
            'Không tìm thấy mã đơn hàng.'
        );

        return;

    }


    try {

        const response =
            await fetch(
                `api/order-detail.php?id=${encodeURIComponent(orderId)}`
            );


        const result =
            await response.json();


        if (
            !response.ok ||
            !result.success
        ) {

            throw new Error(
                result.message ||
                'Không thể tải đơn hàng.'
            );

        }


        /*
         * API dự kiến trả:
         *
         * result.data.order
         * result.data.items
         */

        const order =
            result.data.order;

        const items =
            Array.isArray(
                result.data.items
            )
                ? result.data.items
                : [];

        renderOrderDetail(
            order,
            items
        );


        if (loading) {
            loading.style.display = 'none';
        }


        if (errorBox) {
            errorBox.style.display = 'none';
        }


        if (content) {
            content.style.display = 'grid';
        }


    } catch (error) {

        console.error(
            'Load order detail error:',
            error
        );


        showOrderDetailError(
            error.message ||
            'Không thể tải thông tin đơn hàng.'
        );

    }

}


function renderOrderDetail(
    order,
    items
) {

    /*
     * HEADER
     */

    const orderDetailTitle =
        document.getElementById(
            'orderDetailTitle'
        );

    const orderDetailCode =
        document.getElementById(
            'orderDetailCode'
        );

    const orderDetailStatus =
        document.getElementById(
            'orderDetailStatus'
        );


    if (orderDetailTitle) {

        orderDetailTitle.textContent =
            'ĐƠN HÀNG';

    }


    if (orderDetailCode) {

        orderDetailCode.textContent =
            order.order_code || '—';

    }


    if (orderDetailStatus) {

        orderDetailStatus.textContent =
            getOrderStatusLabel(
                order.status
            );

    }


    /*
     * ORDER INFORMATION
     */

    const detailOrderCode =
        document.getElementById(
            'detailOrderCode'
        );

    const detailCustomerName =
        document.getElementById(
            'detailCustomerName'
        );

    const detailCreatedAt =
        document.getElementById(
            'detailCreatedAt'
        );

    const detailStatusText =
        document.getElementById(
            'detailStatusText'
        );


    if (detailOrderCode) {

        detailOrderCode.textContent =
            order.order_code || '—';

    }


    if (detailCustomerName) {

        detailCustomerName.textContent =
            order.customer_name ||
            'Khách lẻ';

    }


    if (detailCreatedAt) {

        detailCreatedAt.textContent =
            formatOrderDate(
                order.created_at
            );

    }


    if (detailStatusText) {

        detailStatusText.textContent =
            getOrderStatusLabel(
                order.status
            );

    }


    /*
     * SET CURRENT STATUS
     */

    const statusSelect =
        document.getElementById(
            'orderStatus'
        );


    if (statusSelect) {

        statusSelect.value =
            order.status;

    }


    /*
     * ORDER ITEMS
     */

    const itemsContainer =
        document.getElementById(
            'orderDetailItems'
        );


    if (!itemsContainer) {
        return;
    }


    if (items.length === 0) {

        itemsContainer.innerHTML = `
            <div class="admin-empty">
                ĐƠN HÀNG KHÔNG CÓ SẢN PHẨM
            </div>
        `;

    } else {

        itemsContainer.innerHTML =
            items.map(
                item => renderOrderItem(item)
            ).join('');

    }


    /*
     * TOTAL
     */

    const total =
        Number(order.total || 0);

    const subtotal =
        Number(order.subtotal || 0);


    const detailOrderTotal =
        document.getElementById(
            'detailOrderTotal'
        );


    const detailOrderSubtotal =
        document.getElementById(
            'detailOrderSubtotal'
        );


    const detailOrderTotalSide =
        document.getElementById(
            'detailOrderTotalSide'
        );


    if (detailOrderTotal) {

        detailOrderTotal.textContent =
            `${total.toLocaleString('vi-VN')}đ`;

    }


    if (detailOrderSubtotal) {

        detailOrderSubtotal.textContent =
            `${subtotal.toLocaleString('vi-VN')}đ`;

    }


    if (detailOrderTotalSide) {

        detailOrderTotalSide.textContent =
            `${total.toLocaleString('vi-VN')}đ`;

    }

}


function renderOrderItem(item) {

    const unitPrice =
        Number(item.unit_price || 0);

    const quantity =
        Number(item.quantity || 0);

    const lineTotal =
        Number(item.line_total || 0);


    return `

        <article class="admin-order-detail-item">

            <div class="admin-order-detail-item-main">

                <h3>
                    ${escapeHtml(
                        item.product_name || '—'
                    )}
                </h3>


                <div class="admin-order-detail-item-variant">

                    ${escapeHtml(
                        item.variant_name || '—'
                    )}

                    <span>
                        · SL ${quantity}
                    </span>

                </div>


                ${
                    item.note
                        ? `
                            <div class="admin-order-detail-item-note">
                                <span>GHI CHÚ</span>

                                <p>
                                    ${escapeHtml(
                                        item.note
                                    )}
                                </p>
                            </div>
                        `
                        : ''
                }

            </div>


            <div class="admin-order-detail-item-price">

                <span>
                    ${unitPrice.toLocaleString('vi-VN')}đ
                    × ${quantity}
                </span>

                <strong>
                    ${lineTotal.toLocaleString('vi-VN')}đ
                </strong>

            </div>

        </article>

    `;

}


function showOrderDetailError(
    message
) {

    const loading =
        document.getElementById(
            'orderDetailLoading'
        );

    const errorBox =
        document.getElementById(
            'orderDetailError'
        );

    const content =
        document.getElementById(
            'orderDetailContent'
        );


    if (loading) {
        loading.style.display = 'none';
    }


    if (content) {
        content.style.display = 'none';
    }


    if (errorBox) {

        errorBox.style.display =
            'block';

        errorBox.textContent =
            message;

    }

}


function getOrderStatusLabel(
    status
) {

    const labels = {

        pending:
            'ĐANG XỬ LÝ',

        confirmed:
            'ĐANG THỰC HIỆN',

        completed:
            'HOÀN THÀNH',

        cancelled:
            'ĐÃ HỦY'

    };


    return (
        labels[status] ||
        status ||
        '—'
    );

}


function formatOrderDate(
    dateString
) {

    if (!dateString) {
        return '—';
    }


    const date =
        new Date(
            dateString.replace(
                ' ',
                'T'
            )
        );


    if (
        Number.isNaN(
            date.getTime()
        )
    ) {

        return dateString;

    }


    return date.toLocaleString(
        'vi-VN',
        {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }
    );

}


</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>