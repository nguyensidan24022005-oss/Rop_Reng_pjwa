/* =====================================================
   8. QUẢN TRỊ: DANH SÁCH ĐƠN HÀNG
===================================================== */

async function loadAdminOrders() {

    const tableWrapper =
        document.getElementById(
            'adminOrdersTableWrapper'
        );

    const orderCount =
        document.getElementById(
            'adminOrderCount'
        );

    if (!tableWrapper) return;

    tableWrapper.innerHTML = `
        <div class="admin-loading">
            ĐANG TẢI ĐƠN HÀNG...
        </div>
    `;

    try {

        const response = await fetch(
            'api/orders.php'
        );

        const result = await response.json();

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                'Không thể tải đơn hàng.'
            );
        }

        const orders = Array.isArray(result.data)
            ? result.data
            : [];

        if (orderCount) {
            orderCount.textContent =
                orders.length;
        }

        if (orders.length === 0) {

            tableWrapper.innerHTML = `
                <div class="admin-empty">
                    CHƯA CÓ ĐƠN HÀNG
                </div>
            `;

            return;
        }

        tableWrapper.innerHTML = `
            <table class="admin-orders-table">
            
                <thead>
                    <tr>
                        <th>MÃ ĐƠN</th>
                        <th>KHÁCH HÀNG</th>
                        <th>TỔNG TIỀN</th>
                        <th>TRẠNG THÁI</th>
                        <th>THỜI GIAN</th>
                        <th></th>
                    </tr>
                </thead>
                
                <tbody>
                
                    ${orders.map(order => `
                        
                        <tr>
                        
                            <td>
                                <span class="admin-order-code">
                                    ${escapeHtml(order.order_code)}
                                </span>
                            </td>

                            <td>
                                <span class="admin-order-customer">
                                    ${
                                        escapeHtml(
                                            order.customer_name || '—'
                                        )
                                    }
                                </span>
                            </td>
                            
                            <td>
                                <span class="admin-order-total">
                                    ${
                                        Number(order.total)
                                            .toLocaleString('vi-VN')
                                    }đ
                                </span>
                            </td>
                            
                            <td>
                                <span class="admin-order-status">
                                ${escapeHtml(getOrderStatusLabel(
                                    order.status
                                ))}
                                </span>
                            </td>
                            
                            <td>
                                <span class="admin-order-date">
                                    ${escapeHtml(formatOrderDate(
                                        order.created_at
                                    ))}
                                </span>
                            </td>
                            
                            <td>
                                <button
                                    type="button"
                                    class="admin-order-view"
                                    data-order-id="${escapeHtml(order.id)}"
                                >
                                    XEM
                                </button>
                            </td>
                            
                        </tr>
                        
                    `).join('')}
                    
                </tbody>
                
            </table>
        `;
    } catch (error) {

        console.error(
            'Load admin orders error:',
            error
        );

        tableWrapper.innerHTML = `
            <div class="admin-error">
                KHÔNG THỂ TẢI DANH SÁCH ĐƠN HÀNG
            </div>
        `;
    }
}

function getOrderStatusLabel(status) {

    const labels = {
        pending: 'ĐANG XỬ LÝ',
        confirmed: 'ĐANG THỰC HIỆN MÓN',
        completed: 'HOÀN THÀNH',
        cancelled: 'ĐÃ HỦY'
    };

    return labels[status] || status;
}

function formatOrderDate(dateString) {

    if (!dateString) {
        return '—';
    }

    const date = new Date(
        dateString.replace(' ', 'T')
    );

    if (Number.isNaN(date.getTime())) {
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

/* =====================================================
   9. QUẢN TRỊ: CHUYỂN TRANG & CẬP NHẬT TRẠNG THÁI
===================================================== */

document.addEventListener(
    'click',
    event => {

        const refreshButton =
            event.target.closest(
                '#adminOrdersRefresh'
            );

        if (!refreshButton) return;

        loadAdminOrders();
    }
);

document.addEventListener(
    'DOMContentLoaded',
    () => {

        if (
            document.getElementById(
                'adminOrdersTableWrapper'
            )
        ) {
            loadAdminOrders();
        }

    }
);

document.addEventListener('click', event => {

    const button = event.target.closest(
        '.admin-order-view'
    );

    if (!button) return;

    const orderId = button.dataset.orderId;

    if (!orderId) return;

    window.location.href =
        `admin/order-detail.php?id=${encodeURIComponent(orderId)}`;
});

async function updateOrderStatus() {

    const statusSelect =
        document.getElementById('orderStatus');

    const updateButton =
        document.getElementById('updateOrderStatus');

    if (!statusSelect || !updateButton) {
        return;
    }

    const urlParams =
        new URLSearchParams(
            window.location.search
        );

    const orderId =
        urlParams.get('id');

    const status =
        statusSelect.value;

    if (!orderId || !status) {
        return;
    }

    updateButton.disabled = true;
    updateButton.innerHTML =
        'ĐANG CẬP NHẬT...';

    try {

        const response = await fetch(
            'api/update-order-status.php',
            {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify({
                    order_id: Number(orderId),
                    status: status
                })
            }
        );

        const result =
            await response.json();

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                'Không thể cập nhật trạng thái.'
            );
        }

        alert(
            'Cập nhật trạng thái thành công.'
        );

        window.location.reload();

    } catch (error) {

        console.error(
            'Update order status error:',
            error
        );

        alert(
            error.message ||
            'Có lỗi xảy ra khi cập nhật trạng thái.'
        );

    } finally {

        updateButton.disabled = false;

        updateButton.innerHTML = `
            CẬP NHẬT TRẠNG THÁI
            <span>→</span>
        `;
    }
}

document.addEventListener(
    'click',
    event => {

        const button =
            event.target.closest(
                '#updateOrderStatus'
            );

        if (!button) {
            return;
        }

        updateOrderStatus();
    }
);

